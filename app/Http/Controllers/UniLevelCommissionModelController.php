<?php

namespace App\Http\Controllers;

use App\Models\CommissionModel;
use App\Services\UniLevelCommissionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class UniLevelCommissionModelController extends Controller
{
    public function __construct(
        protected UniLevelCommissionCalculator $calculator
    ) {}

    /**
     * Show the Unilevel Commission Model Builder page.
     */
    public function create(): View
    {
        $defaultRates = UniLevelCommissionCalculator::DEFAULT_RATE_SCHEDULE;

        return view('unilevel-models.create', compact('defaultRates'));
    }

    /**
     * Calculate commission via AJAX without saving.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'max_depth'                  => ['required', 'integer', 'min:1', 'max:20'],
            'nodes'                      => ['required', 'array', 'min:1'],
            'nodes.*.name'               => ['required', 'string', 'max:255'],
            'nodes.*.parent'             => ['nullable', 'string', 'max:255'],
            'sales'                      => ['required', 'array', 'min:1'],
            'sales.*.distributor'        => ['required', 'string', 'max:255'],
            'sales.*.amount'             => ['required', 'numeric', 'min:0'],
            'rate_schedule'              => ['nullable', 'array'],
            'rate_schedule.*'            => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'max_depth.required'          => 'Maximum depth is required.',
            'nodes.required'              => 'At least one distributor node is required.',
            'nodes.*.name.required'       => 'Each node must have a name.',
            'sales.required'              => 'At least one sale is required.',
            'sales.*.distributor.required' => 'Each sale must identify the distributor.',
            'sales.*.amount.required'     => 'Each sale must have an amount.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()->all(),
            ], 422);
        }

        try {
            $validated    = $validator->validated();
            $rateSchedule = $this->buildRateSchedule($validated['rate_schedule'] ?? []);

            $results = $this->calculator->calculate(
                $validated['nodes'],
                $validated['sales'],
                $rateSchedule,
                (int) $validated['max_depth']
            );

            return response()->json([
                'success' => true,
                'message' => 'Unilevel commission calculated successfully.',
                'data'    => $results,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => [$e->getMessage()],
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Calculation engine error: '.$e->getMessage(),
                'errors'  => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Store a newly created Unilevel Commission Model in a DB transaction.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'                       => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string', 'max:1000'],
            'max_depth'                  => ['required', 'integer', 'min:1', 'max:20'],
            'nodes'                      => ['required', 'array', 'min:1'],
            'nodes.*.name'               => ['required', 'string', 'max:255'],
            'nodes.*.parent'             => ['nullable', 'string', 'max:255'],
            'sales'                      => ['required', 'array', 'min:1'],
            'sales.*.distributor'        => ['required', 'string', 'max:255'],
            'sales.*.amount'             => ['required', 'numeric', 'min:0'],
            'rate_schedule'              => ['nullable', 'array'],
            'rate_schedule.*'            => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $rateSchedule = $this->buildRateSchedule($validated['rate_schedule'] ?? []);

        DB::beginTransaction();
        try {
            $model = CommissionModel::create([
                'name'                     => $validated['name'],
                'model_type'               => 'unilevel',
                'description'              => $validated['description'] ?? null,
                'max_generations'          => (int) $validated['max_depth'],
                'commission_rate'          => null,
                'number_of_levels'         => (int) $validated['max_depth'],
                'total_sales'              => 0.00,
                'final_commission'         => 0.00,
                'total_potential_commission' => 0.00,
            ]);

            $this->calculator->persistModel(
                $model,
                $validated['nodes'],
                $validated['sales'],
                $rateSchedule,
                (int) $validated['max_depth']
            );

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => "Unilevel Model '{$model->name}' saved successfully!",
                    'redirect_url' => route('unilevel-models.show', $model),
                    'model_id'     => $model->id,
                ]);
            }

            return redirect()->route('unilevel-models.show', $model)
                ->with('success', "Unilevel Commission Model '{$model->name}' created and calculated successfully!");

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
                'error' => 'An error occurred while saving: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified Unilevel Commission Model.
     */
    public function show(CommissionModel $model): View
    {
        $results = $this->calculator->loadFromModel($model);

        return view('unilevel-models.show', compact('model', 'results'));
    }

    /**
     * Show the edit form for a Unilevel Commission Model.
     */
    public function edit(CommissionModel $model): View
    {
        $results      = $this->calculator->loadFromModel($model);
        $defaultRates = UniLevelCommissionCalculator::DEFAULT_RATE_SCHEDULE;

        return view('unilevel-models.edit', compact('model', 'results', 'defaultRates'));
    }

    /**
     * Update the Unilevel Commission Model.
     */
    public function update(Request $request, CommissionModel $model): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'                       => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string', 'max:1000'],
            'max_depth'                  => ['required', 'integer', 'min:1', 'max:20'],
            'nodes'                      => ['required', 'array', 'min:1'],
            'nodes.*.name'               => ['required', 'string', 'max:255'],
            'nodes.*.parent'             => ['nullable', 'string', 'max:255'],
            'sales'                      => ['required', 'array', 'min:1'],
            'sales.*.distributor'        => ['required', 'string', 'max:255'],
            'sales.*.amount'             => ['required', 'numeric', 'min:0'],
            'rate_schedule'              => ['nullable', 'array'],
            'rate_schedule.*'            => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $rateSchedule = $this->buildRateSchedule($validated['rate_schedule'] ?? []);

        DB::beginTransaction();
        try {
            $model->update([
                'name'            => $validated['name'],
                'description'     => $validated['description'] ?? null,
                'max_generations' => (int) $validated['max_depth'],
                'number_of_levels' => (int) $validated['max_depth'],
            ]);

            $this->calculator->persistModel(
                $model,
                $validated['nodes'],
                $validated['sales'],
                $rateSchedule,
                (int) $validated['max_depth']
            );

            DB::commit();

            return redirect()->route('unilevel-models.show', $model)
                ->with('success', "Unilevel Model '{$model->name}' updated and recalculated successfully!");

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withInput()->withErrors([
                'error' => 'Update failed: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Duplicate an existing Unilevel Commission Model.
     */
    public function duplicate(CommissionModel $model): RedirectResponse
    {
        $results = $this->calculator->loadFromModel($model);
        $copy    = $model->replicate();
        $copy->name = 'Copy of '.$model->name;
        $copy->save();

        if (! empty($results['commission_by_sale'])) {
            $nodes = $results['hierarchy_tree']
                ? $this->flattenTree($results['hierarchy_tree'])
                : [];

            $sales = collect($results['commission_by_sale'])
                ->map(fn ($s) => ['distributor' => $s['seller'], 'amount' => $s['amount']])
                ->toArray();

            $rateSchedule = $results['rate_schedule'] ?? UniLevelCommissionCalculator::DEFAULT_RATE_SCHEDULE;

            $this->calculator->persistModel(
                $copy,
                $nodes,
                $sales,
                $rateSchedule,
                $model->max_generations ?? 10
            );
        }

        return redirect()->route('unilevel-models.show', $copy)
            ->with('success', "Model duplicated as '{$copy->name}'!");
    }

    /**
     * Delete a Unilevel Commission Model.
     */
    public function destroy(CommissionModel $model): RedirectResponse
    {
        $name = $model->name;
        $model->delete();

        return redirect()->route('commission-models.index')
            ->with('success', "Unilevel model '{$name}' deleted.");
    }

    /**
     * Build indexed rate schedule from request input.
     *
     * @param  array  $raw  [1 => 10.0, 2 => 5.0, ...]
     */
    protected function buildRateSchedule(array $raw): array
    {
        if (empty($raw)) {
            return UniLevelCommissionCalculator::DEFAULT_RATE_SCHEDULE;
        }

        $schedule = UniLevelCommissionCalculator::DEFAULT_RATE_SCHEDULE;
        foreach ($raw as $depth => $rate) {
            $schedule[(int) $depth] = (float) $rate;
        }

        return $schedule;
    }

    /**
     * Flatten a nested hierarchy tree back to a flat nodes list.
     */
    protected function flattenTree(array $tree, ?string $parent = null): array
    {
        $nodes = [];
        foreach ($tree as $node) {
            $nodes[] = ['name' => $node['name'], 'parent' => $parent];
            if (! empty($node['children'])) {
                foreach ($this->flattenTree($node['children'], $node['name']) as $child) {
                    $nodes[] = $child;
                }
            }
        }

        return $nodes;
    }
}
