<?php

namespace App\Http\Controllers;

use App\Models\CommissionModel;
use App\Services\OverrideCommissionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class OverrideCommissionModelController extends Controller
{
    public function __construct(
        protected OverrideCommissionCalculator $calculator
    ) {}

    /**
     * Show the Level / Generation Override Commission Model Builder page.
     */
    public function create(): View
    {
        return view('override-models.create');
    }

    /**
     * Calculate commission via AJAX without saving, returning the live calculation results.
     */
    public function calculate(Request $request): JsonResponse
    {
        $this->normalizeNodesInput($request);

        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'max_generations' => ['required', 'integer', 'min:1', 'max:100'],
            'edges' => ['required', 'array', 'min:1'],
            'edges.*.parent' => ['required', 'string', 'max:255'],
            'edges.*.child' => ['required', 'string', 'max:255'],
            'edges.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'sales' => ['required', 'array', 'min:1'],
            'sales.*.salesperson' => ['required', 'string', 'max:255'],
            'sales.*.amount' => ['required', 'numeric', 'min:0'],
        ], [
            'max_generations.required' => 'Maximum generation limit is required.',
            'max_generations.min' => 'Maximum generations must be at least 1.',
            'edges.required' => 'At least one parent-child relationship is required.',
            'edges.*.parent.required' => 'Parent name is required for all relationships.',
            'edges.*.child.required' => 'Child name is required for all relationships.',
            'edges.*.rate.required' => 'Override rate is required for all relationships.',
            'edges.*.rate.min' => 'Override rate cannot be negative.',
            'edges.*.rate.max' => 'Override rate cannot exceed 100%.',
            'sales.required' => 'At least one personal sale is required.',
            'sales.*.salesperson.required' => 'Salesperson name is required for all sales.',
            'sales.*.amount.required' => 'Sale amount is required for all sales.',
            'sales.*.amount.min' => 'Sale amount cannot be negative.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please correct the highlighted errors.',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $results = $this->calculator->calculate(
                $validated['edges'],
                $validated['sales'],
                (int) $validated['max_generations']
            );

            $dummyModel = new CommissionModel([
                'id' => 0,
                'name' => 'Override Preview',
                'model_type' => 'generation_override',
                'max_generations' => (int) $validated['max_generations'],
                'final_commission' => $results['total_commission_generated'],
            ]);
            $results['tree_data'] = $this->calculator->buildDiagramData($dummyModel, $results);

            return response()->json([
                'success' => true,
                'message' => 'Override commission model successfully calculated.',
                'data' => $results,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => [$e->getMessage()],
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'A calculation engine error occurred. Please verify your tree hierarchy and try again.',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Store and calculate a newly created Override Commission Model inside a DB transaction.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->normalizeNodesInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_generations' => ['required', 'integer', 'min:1', 'max:100'],
            'edges' => ['required', 'array', 'min:1'],
            'edges.*.parent' => ['required', 'string', 'max:255'],
            'edges.*.child' => ['required', 'string', 'max:255'],
            'edges.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'sales' => ['required', 'array', 'min:1'],
            'sales.*.salesperson' => ['required', 'string', 'max:255'],
            'sales.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $model = CommissionModel::create([
                'name' => $validated['name'],
                'model_type' => 'generation_override',
                'description' => $validated['description'] ?? null,
                'max_generations' => (int) $validated['max_generations'],
                'commission_rate' => 0.00, // In Model 2, rates vary per edge
                'number_of_levels' => 0,
                'total_sales' => 0.00,
                'final_commission' => 0.00,
            ]);

            $this->calculator->persistModel(
                $model,
                $validated['edges'],
                $validated['sales'],
                (int) $validated['max_generations']
            );

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Override Commission Model '{$model->name}' saved successfully!",
                    'redirect_url' => route('override-models.show', $model),
                    'model_id' => $model->id,
                ]);
            }

            return redirect()->route('override-models.show', $model)
                ->with('success', "Level / Generation Override Model '{$model->name}' created and calculated successfully!");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save model: '.$e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while saving the model: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified Override Commission Model and its full breakdown.
     * Historical models do not recalculate on viewing, preserving audit integrity,
     * unless explicitly requested via ?recalculate=1.
     */
    public function show(CommissionModel $model, Request $request): View
    {
        $model->load([
            'relationships',
            'overrideSales',
            'overrideCommissions',
            'nodes.parent',
            'edges.parentNode',
            'edges.childNode',
            'ledger.saleNode',
            'ledger.earnerNode',
        ]);

        $isExplicitRecalculate = $request->boolean('recalculate');

        if ($isExplicitRecalculate) {
            $results = $this->calculator->calculateFromModel($model);
            $results['is_recalculated'] = true;
        } else {
            $results = $this->calculator->loadHistoricalResults($model);
        }

        $treeData = $this->calculator->buildDiagramData($model, $results);

        return view('override-models.show', compact('model', 'results', 'isExplicitRecalculate', 'treeData'));
    }

    /**
     * Show the edit form for an existing Override Commission Model.
     */
    public function edit(CommissionModel $model): View
    {
        $model->load(['relationships', 'overrideSales']);

        $editEdges = $model->relationships->map(fn ($r) => [
            'parent' => $r->parent_name,
            'child' => $r->child_name,
            'rate' => (float) $r->rate,
        ])->toArray();

        $editSales = $model->overrideSales->map(fn ($s) => [
            'salesperson' => $s->salesperson_name,
            'amount' => (float) $s->amount,
        ])->toArray();

        return view('override-models.edit', [
            'model' => $model,
            'editEdges' => $editEdges,
            'editSales' => $editSales,
            'maxGenerations' => $model->max_generations ?? 5,
        ]);
    }

    /**
     * Update an existing Override Commission Model.
     */
    public function update(Request $request, CommissionModel $model): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'max_generations' => ['required', 'integer', 'min:1', 'max:100'],
            'edges' => ['required', 'array', 'min:1'],
            'edges.*.parent' => ['required', 'string', 'max:255'],
            'edges.*.child' => ['required', 'string', 'max:255'],
            'edges.*.rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'sales' => ['required', 'array', 'min:1'],
            'sales.*.salesperson' => ['required', 'string', 'max:255'],
            'sales.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $model->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'max_generations' => (int) $validated['max_generations'],
            ]);

            $this->calculator->persistModel(
                $model,
                $validated['edges'],
                $validated['sales'],
                (int) $validated['max_generations']
            );

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Override Commission Model '{$model->name}' updated successfully!",
                    'redirect_url' => route('override-models.show', $model),
                    'model_id' => $model->id,
                ]);
            }

            return redirect()->route('override-models.show', $model)
                ->with('success', "Override Model '{$model->name}' updated and recalculated successfully!");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update model: '.$e->getMessage(),
                ], 422);
            }

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while updating the model: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Duplicate an existing Override Commission Model by prefilling the builder.
     */
    public function duplicate(CommissionModel $model): View
    {
        $model->load(['relationships', 'overrideSales']);

        $duplicateEdges = $model->relationships->map(fn ($r) => [
            'parent' => $r->parent_name,
            'child' => $r->child_name,
            'rate' => (float) $r->rate,
        ])->toArray();

        $duplicateSales = $model->overrideSales->map(fn ($s) => [
            'salesperson' => $s->salesperson_name,
            'amount' => (float) $s->amount,
        ])->toArray();

        return view('override-models.create', [
            'duplicateModel' => $model,
            'duplicateEdges' => $duplicateEdges,
            'duplicateSales' => $duplicateSales,
            'duplicateMaxGenerations' => $model->max_generations ?? 5,
        ]);
    }

    /**
     * Delete an Override Commission Model inside a transaction.
     */
    public function destroy(CommissionModel $model): RedirectResponse
    {
        $name = $model->name;

        DB::beginTransaction();
        try {
            $model->relationships()->delete();
            $model->overrideSales()->delete();
            $model->overrideCommissions()->delete();
            $model->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors([
                'error' => 'Failed to delete the model: '.$e->getMessage(),
            ]);
        }

        return redirect()->route('commission-models.index')
            ->with('success', "Commission model '{$name}' has been deleted.");
    }

    /**
     * Normalize nodes input into edges and personal sales if explicit edges/sales are omitted.
     */
    protected function normalizeNodesInput(Request $request): void
    {
        if ($request->has('nodes') && is_array($request->input('nodes'))) {
            $nodes = $request->input('nodes');
            $edges = $request->input('edges', []);
            $sales = $request->input('sales', []);

            if (empty($edges)) {
                foreach ($nodes as $node) {
                    $name = trim((string) ($node['name'] ?? ''));
                    $parent = trim((string) ($node['parent'] ?? ''));
                    $rate = (float) ($node['rate'] ?? ($node['override_rate'] ?? 0));

                    if ($name !== '' && $parent !== '' && $parent !== 'null' && $parent !== $name) {
                        $edges[] = [
                            'parent' => $parent,
                            'child' => $name,
                            'rate' => $rate,
                        ];
                    }
                }
            }

            if (empty($sales)) {
                foreach ($nodes as $node) {
                    $name = trim((string) ($node['name'] ?? ''));
                    if ($name !== '' && (isset($node['personal_sale']) || isset($node['amount']))) {
                        $rawAmount = $node['personal_sale'] ?? $node['amount'];
                        if (is_numeric($rawAmount) && (float) $rawAmount != 0.0) {
                            $sales[] = [
                                'salesperson' => $name,
                                'amount' => (float) $rawAmount,
                            ];
                        }
                    }
                }
            }

            if (! empty($edges)) {
                $request->merge(['edges' => $edges]);
            }
            if (! empty($sales)) {
                $request->merge(['sales' => $sales]);
            }
        }
    }
}
