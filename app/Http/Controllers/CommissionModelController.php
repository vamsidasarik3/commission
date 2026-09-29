<?php

namespace App\Http\Controllers;

use App\Models\CommissionModel;
use App\Services\CommissionCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CommissionModelController extends Controller
{
    public function __construct(
        protected CommissionCalculator $calculator
    ) {}

    /**
     * Display a listing of saved commission models.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type');

        $query = CommissionModel::with(['levels', 'relationships', 'overrideSales', 'overrideCommissions'])->latest();

        if ($type === 'weakest_link') {
            $query->where(function ($q) {
                $q->where('model_type', 'weakest_link')
                    ->orWhereNull('model_type');
            });
        } elseif ($type === 'generation_override' || $type === 'override') {
            $query->where('model_type', 'generation_override');
        }

        $models = $query->paginate(10)->withQueryString();
        $counts = [
            'all' => CommissionModel::count(),
            'weakest_link' => CommissionModel::where('model_type', 'weakest_link')->orWhereNull('model_type')->count(),
            'generation_override' => CommissionModel::where('model_type', 'generation_override')->count(),
        ];

        return view('models.index', compact('models', 'type', 'counts'));
    }

    /**
     * Show the Commission Model Builder page.
     */
    public function create(): View
    {
        return view('models.create');
    }

    /**
     * Calculate commission via AJAX without saving, returning the live calculation results.
     */
    public function calculate(Request $request): JsonResponse
    {
        if ($request->input('model_type') === 'generation_override' || $request->input('model_type') === 'override') {
            return app(OverrideCommissionModelController::class)->calculate($request);
        }

        $validator = Validator::make($request->all(), [
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'number_of_levels' => ['required', 'integer', 'min:1', 'max:100'],
            'levels' => ['required', 'array', 'min:1'],
            'levels.*.level' => ['required', 'integer', 'min:1'],
            'levels.*.commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'levels.*.main_person' => ['required', 'string', 'max:255'],
            'levels.*.main_sales' => ['required', 'numeric', 'min:0'],
            'levels.*.side_person' => ['required', 'string', 'max:255'],
            'levels.*.side_sales' => ['required', 'numeric', 'min:0'],
        ], [
            'commission_rate.required' => 'Commission rate is required.',
            'commission_rate.numeric' => 'Commission rate must be a valid number.',
            'commission_rate.min' => 'Commission rate must not be negative.',
            'commission_rate.max' => 'Commission rate cannot exceed 100%.',
            'number_of_levels.required' => 'Number of levels is required.',
            'number_of_levels.integer' => 'Number of levels must be an integer.',
            'number_of_levels.min' => 'Number of levels must be a positive integer.',
            'levels.required' => 'At least one level is required.',
            'levels.*.main_sales.min' => 'Main sales must not be negative.',
            'levels.*.side_sales.min' => 'Side sales must not be negative.',
            'levels.*.main_person.required' => 'Main person identifier is required for all levels.',
            'levels.*.side_person.required' => 'Side person identifier is required for all levels.',
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
                $validated['commission_rate'],
                $validated['levels']
            );

            return response()->json([
                'success' => true,
                'message' => 'Commission model successfully calculated by the server engine.',
                'data' => [
                    'total_sales' => $results['total_sales'],
                    'total_potential_commission' => $results['total_potential_commission'],
                    'weakest_person' => $results['weakest_person'],
                    'weakest_sales' => $results['weakest_sales'],
                    'weakest_commission' => $results['weakest_commission'],
                    'final_commission' => $results['final_commission'],
                    'commission_rate' => $results['commission_rate'],
                    'number_of_levels' => $results['number_of_levels'],
                    'top_leader' => $results['top_leader'],
                    'levels' => $results['levels'],
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'A calculation engine error occurred. Please check your level values and try again.',
            ], 500);
        }
    }

    /**
     * Store and calculate a newly created commission model with database transaction protection.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->input('model_type') === 'generation_override' || $request->input('model_type') === 'override') {
            return app(OverrideCommissionModelController::class)->store($request);
        }

        // 1. Validate the complete request
        $validated = $request->validate([
            'name'                        => ['required', 'string', 'max:255'],
            'commission_rate'             => ['required', 'numeric', 'min:0', 'max:100'],
            'number_of_levels'            => ['required', 'integer', 'min:1', 'max:100'],
            'levels'                      => ['required', 'array', 'min:1'],
            'levels.*.level'              => ['required', 'integer', 'min:1'],
            'levels.*.commission_rate'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'levels.*.main_person'        => ['required', 'string', 'max:255'],
            'levels.*.main_sales'         => ['required', 'numeric', 'min:0'],
            'levels.*.side_person'        => ['required', 'string', 'max:255'],
            'levels.*.side_sales'         => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'              => 'Model Name is required.',
            'commission_rate.required'   => 'A global fallback commission rate is required.',
            'commission_rate.min'        => 'Commission rate must not be negative.',
            'number_of_levels.required'  => 'Number of levels is required.',
            'number_of_levels.min'       => 'Number of levels must be a positive integer.',
            'levels.required'            => 'At least one level is required.',
            'levels.*.main_sales.min'    => 'Main salesperson sales must not be negative.',
            'levels.*.side_sales.min'    => 'Side salesperson sales must not be negative.',
        ]);

        // 2. Calculate using CommissionCalculator (authoritative engine calculation snapshot)
        $calcResult = $this->calculator->calculate(
            $validated['commission_rate'],
            $validated['levels']
        );

        // 3. Begin a database transaction
        DB::beginTransaction();

        try {
            // 4. Create the commission_models record with calculated snapshot
            $model = CommissionModel::create([
                'name' => $validated['name'],
                'commission_rate' => $calcResult['commission_rate'],
                'number_of_levels' => $calcResult['number_of_levels'],
                'total_sales' => $calcResult['total_sales'],
                'total_potential_commission' => $calcResult['total_potential_commission'],
                'final_commission' => $calcResult['final_commission'],
                'weakest_person' => $calcResult['weakest_person'],
                'weakest_sales' => $calcResult['weakest_sales'],
                'weakest_commission' => $calcResult['weakest_commission'],
            ]);

            // 5. Create all commission_levels records
            foreach ($calcResult['levels'] as $lvlData) {
                $model->levels()->create([
                    'level'              => $lvlData['level'],
                    'commission_rate'    => $lvlData['commission_rate'],
                    'main_person'        => $lvlData['main_person'],
                    'main_sales'         => $lvlData['main_sales'],
                    'main_commission'    => $lvlData['main_commission'],
                    'side_person'        => $lvlData['side_person'],
                    'side_sales'         => $lvlData['side_sales'],
                    'side_commission'    => $lvlData['side_commission'],
                    'selected_commission' => $lvlData['selected_commission'],
                    'leader_commission'  => $lvlData['leader_commission'],
                ]);
            }

            // 6. Commit the transaction
            DB::commit();

        } catch (\Throwable $e) {
            // Rollback the transaction if anything fails (no partial models saved)
            DB::rollBack();
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save the commission model. Database transaction rolled back.',
                ], 500);
            }

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while saving the model. Database changes were safely rolled back.',
            ]);
        }

        // Return JSON if requested via AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Commission model '{$model->name}' successfully calculated and saved to the database!",
                'redirect_url' => route('commission-models.show', $model),
                'model_id' => $model->id,
            ]);
        }

        // Standard Redirect after successful save with clear success message
        return redirect()->route('commission-models.show', $model)
            ->with('success', "Commission model '{$model->name}' successfully calculated and saved to the database!");
    }

    /**
     * Display the specified commission model and its calculation breakdown.
     */
    public function show(CommissionModel $model): View|RedirectResponse
    {
        if ($model->isOverrideModel()) {
            return redirect()->route('override-models.show', $model);
        }

        $model->load('levels');

        $treeData = [
            'model_type' => 'weakest_link',
            'commission_rate' => (float) $model->commission_rate,
            'final_commission' => (float) $model->final_commission,
            'weakest_person' => $model->weakest_person,
            'weakest_sales' => (float) $model->weakest_sales,
            'weakest_commission' => (float) $model->weakest_commission,
            'top_leader' => 'A',
            'levels' => $model->levels->map(fn ($lvl) => [
                'level' => (int) $lvl->level,
                'commission_rate' => $lvl->commission_rate !== null ? (float) $lvl->commission_rate : (float) $model->commission_rate,
                'main_person' => $lvl->main_person,
                'main_sales' => (float) $lvl->main_sales,
                'main_commission' => (float) $lvl->main_commission,
                'side_person' => $lvl->side_person,
                'side_sales' => (float) $lvl->side_sales,
                'side_commission' => (float) $lvl->side_commission,
                'selected_commission' => (float) $lvl->selected_commission,
                'leader_commission' => (float) $lvl->leader_commission,
            ])->toArray(),
        ];

        return view('models.show', compact('model', 'treeData'));
    }

    /**
     * Show the edit form for an existing commission model.
     */
    public function edit(CommissionModel $model): View|RedirectResponse
    {
        if ($model->isOverrideModel()) {
            return redirect()->route('override-models.edit', $model);
        }

        $model->load('levels');

        $editLevels = $model->levels->mapWithKeys(function ($lvl) use ($model) {
            return [
                $lvl->level => [
                    'commission_rate' => $lvl->commission_rate !== null ? (float) $lvl->commission_rate : (float) $model->commission_rate,
                    'main_person' => $lvl->main_person,
                    'main_sales' => (float) $lvl->main_sales,
                    'side_person' => $lvl->side_person,
                    'side_sales' => (float) $lvl->side_sales,
                ],
            ];
        })->toArray();

        return view('models.edit', [
            'model' => $model,
            'editLevels' => $editLevels,
        ]);
    }

    /**
     * Update an existing commission model with new calculation snapshots inside a transaction.
     */
    public function update(Request $request, CommissionModel $model): RedirectResponse|JsonResponse
    {
        // 1. Validate the complete request
        $validated = $request->validate([
            'name'                      => ['required', 'string', 'max:255'],
            'commission_rate'           => ['required', 'numeric', 'min:0', 'max:100'],
            'number_of_levels'          => ['required', 'integer', 'min:1', 'max:100'],
            'levels'                    => ['required', 'array', 'min:1'],
            'levels.*.level'            => ['required', 'integer', 'min:1'],
            'levels.*.commission_rate'  => ['nullable', 'numeric', 'min:0', 'max:100'],
            'levels.*.main_person'      => ['required', 'string', 'max:255'],
            'levels.*.main_sales'       => ['required', 'numeric', 'min:0'],
            'levels.*.side_person'      => ['required', 'string', 'max:255'],
            'levels.*.side_sales'       => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'             => 'Model Name is required.',
            'commission_rate.required'  => 'A global fallback commission rate is required.',
            'commission_rate.numeric'   => 'Commission rate must be a valid number.',
            'commission_rate.min'       => 'Commission rate must not be negative.',
            'commission_rate.max'       => 'Commission rate cannot exceed 100%.',
            'number_of_levels.required' => 'Number of levels is required.',
            'number_of_levels.integer'  => 'Number of levels must be an integer.',
            'number_of_levels.min'      => 'Number of levels must be a positive integer.',
            'levels.required'           => 'At least one level is required.',
            'levels.*.main_sales.min'   => 'Main salesperson sales must not be negative.',
            'levels.*.side_sales.min'   => 'Side salesperson sales must not be negative.',
        ]);

        // 2. Calculate using CommissionCalculator
        $calcResult = $this->calculator->calculate(
            $validated['commission_rate'],
            $validated['levels']
        );

        // 3. Begin database transaction
        DB::beginTransaction();

        try {
            // 4. Update the commission_models record with calculated snapshot
            $model->update([
                'name' => $validated['name'],
                'commission_rate' => $calcResult['commission_rate'],
                'number_of_levels' => $calcResult['number_of_levels'],
                'total_sales' => $calcResult['total_sales'],
                'total_potential_commission' => $calcResult['total_potential_commission'],
                'final_commission' => $calcResult['final_commission'],
                'weakest_person' => $calcResult['weakest_person'],
                'weakest_sales' => $calcResult['weakest_sales'],
                'weakest_commission' => $calcResult['weakest_commission'],
            ]);

            // 5. Replace commission_levels records
            $model->levels()->delete();
            foreach ($calcResult['levels'] as $lvlData) {
                $model->levels()->create([
                    'level'              => $lvlData['level'],
                    'commission_rate'    => $lvlData['commission_rate'],
                    'main_person'        => $lvlData['main_person'],
                    'main_sales'         => $lvlData['main_sales'],
                    'main_commission'    => $lvlData['main_commission'],
                    'side_person'        => $lvlData['side_person'],
                    'side_sales'         => $lvlData['side_sales'],
                    'side_commission'    => $lvlData['side_commission'],
                    'selected_commission' => $lvlData['selected_commission'],
                    'leader_commission'  => $lvlData['leader_commission'],
                ]);
            }

            // 6. Commit transaction
            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update commission model. Database transaction rolled back.',
                ], 500);
            }

            return back()->withInput()->withErrors([
                'error' => 'An error occurred while updating the model. Database changes were safely rolled back.',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Commission model '{$model->name}' successfully updated and recalculated!",
                'redirect_url' => route('commission-models.show', $model),
                'model_id' => $model->id,
            ]);
        }

        return redirect()->route('commission-models.show', $model)
            ->with('success', "Commission model '{$model->name}' successfully updated and recalculated!");
    }

    /**
     * Duplicate an existing commission model by prefilling a new editable model builder.
     */
    public function duplicate(CommissionModel $model): View|RedirectResponse
    {
        if ($model->isOverrideModel()) {
            return redirect()->route('override-models.duplicate', $model);
        }

        $model->load('levels');

        $duplicateLevels = $model->levels->mapWithKeys(function ($lvl) {
            return [
                $lvl->level => [
                    'main_person' => $lvl->main_person,
                    'main_sales' => (float) $lvl->main_sales,
                    'side_person' => $lvl->side_person,
                    'side_sales' => (float) $lvl->side_sales,
                ],
            ];
        })->toArray();

        return view('models.create', [
            'duplicateModel' => $model,
            'duplicateLevels' => $duplicateLevels,
        ]);
    }

    /**
     * Delete a commission model and all its associated levels inside a database transaction.
     */
    public function destroy(CommissionModel $model): RedirectResponse
    {
        $name = $model->name;

        DB::beginTransaction();
        try {
            $model->levels()->delete();
            $model->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors([
                'error' => 'Failed to delete the commission model. Database transaction rolled back.',
            ]);
        }

        return redirect()->route('commission-models.index')
            ->with('success', "Commission model '{$name}' has been successfully deleted.");
    }
}
