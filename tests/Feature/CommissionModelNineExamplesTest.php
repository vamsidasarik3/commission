<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use Database\Seeders\CommissionModelExamplesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionModelNineExamplesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that all 9 examples across the 3 models are seeded with >= 10 levels and varying non-fixed commissions.
     */
    public function test_all_nine_examples_seeded_and_calculated_properly(): void
    {
        $this->seed(CommissionModelExamplesSeeder::class);

        $models = CommissionModel::all();
        $this->assertCount(9, $models);

        // Group by model_type
        $weakestModels = CommissionModel::where('model_type', 'weakest_link')->get();
        $overrideModels = CommissionModel::where('model_type', 'generation_override')->get();
        $unilevelModels = CommissionModel::where('model_type', 'unilevel')->get();

        $this->assertCount(3, $weakestModels, 'Must have at least 3 examples of Model 1 (Weakest Link)');
        $this->assertCount(3, $overrideModels, 'Must have at least 3 examples of Model 2 (Generation Override)');
        $this->assertCount(3, $unilevelModels, 'Must have at least 3 examples of Model 3 (Unilevel MLM)');

        // =========================================================================
        // Verify Model 1 (Weakest Link)
        // =========================================================================
        foreach ($weakestModels as $m) {
            $this->assertGreaterThanOrEqual(10, $m->number_of_levels, "Model 1 '{$m->name}' must have at least 10 levels");
            $this->assertGreaterThanOrEqual(10, $m->levels()->count());

            // Verify rates are not fixed - distinct rates present across levels
            $rates = $m->levels()->pluck('commission_rate')->filter()->unique();
            $this->assertGreaterThan(1, $rates->count(), "Model 1 '{$m->name}' must have varying non-fixed commission rates");

            $this->assertNotEmpty($m->weakest_person);
            $this->assertGreaterThan(0, $m->weakest_commission);
            $this->assertGreaterThan(0, $m->final_commission);
        }

        // =========================================================================
        // Verify Model 2 (Generation Override)
        // =========================================================================
        foreach ($overrideModels as $m) {
            $this->assertGreaterThanOrEqual(10, $m->max_generations, "Model 2 '{$m->name}' must have at least 10 levels");

            // Verify edges have distinct rates
            $edgeRates = $m->edges()->pluck('override_rate')->unique();
            $this->assertGreaterThan(1, $edgeRates->count(), "Model 2 '{$m->name}' must have varying non-fixed override rates");

            $this->assertGreaterThan(0, $m->total_sales);
            $this->assertGreaterThan(0, $m->final_commission);
            $this->assertGreaterThan(0, $m->ledger()->count());
        }

        // =========================================================================
        // Verify Model 3 (Unilevel MLM)
        // =========================================================================
        foreach ($unilevelModels as $m) {
            $this->assertGreaterThanOrEqual(10, $m->max_generations, "Model 3 '{$m->name}' must have at least 10 levels");

            $results = $m->calculation_results;
            $this->assertIsArray($results);
            $this->assertArrayHasKey('rate_schedule', $results);

            // Verify schedule has varying rates across depth
            $scheduleRates = array_unique(array_values($results['rate_schedule']));
            $this->assertGreaterThan(1, count($scheduleRates), "Model 3 '{$m->name}' must have varying non-fixed generation rates");

            $this->assertGreaterThan(0, $m->total_sales);
            $this->assertGreaterThan(0, $m->final_commission);
        }
    }
}
