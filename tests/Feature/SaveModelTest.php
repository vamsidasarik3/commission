<?php

namespace Tests\Feature;

use App\Models\CommissionLevel;
use App\Models\CommissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaveModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful save model with database transaction, snapshot calculations, and redirection.
     */
    public function test_save_model_successfully_persists_model_and_levels_in_transaction(): void
    {
        $payload = [
            'name' => '10 Level Integration Test',
            'commission_rate' => 5.00,
            'number_of_levels' => 10,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 1000],
                ['level' => 2, 'main_person' => 'B', 'main_sales' => 900, 'side_person' => 'S2', 'side_sales' => 900],
                ['level' => 3, 'main_person' => 'C', 'main_sales' => 800, 'side_person' => 'S3', 'side_sales' => 800],
                ['level' => 4, 'main_person' => 'D', 'main_sales' => 700, 'side_person' => 'S4', 'side_sales' => 700],
                ['level' => 5, 'main_person' => 'E', 'main_sales' => 600, 'side_person' => 'S5', 'side_sales' => 600],
                ['level' => 6, 'main_person' => 'F', 'main_sales' => 500, 'side_person' => 'S6', 'side_sales' => 500],
                ['level' => 7, 'main_person' => 'G', 'main_sales' => 400, 'side_person' => 'S7', 'side_sales' => 400],
                ['level' => 8, 'main_person' => 'H', 'main_sales' => 300, 'side_person' => 'S8', 'side_sales' => 300],
                ['level' => 9, 'main_person' => 'I', 'main_sales' => 200, 'side_person' => 'S9', 'side_sales' => 250],
                ['level' => 10, 'main_person' => 'J', 'main_sales' => 40, 'side_person' => 'S10', 'side_sales' => 200],
            ],
        ];

        $response = $this->post(route('models.store'), $payload);

        // Verify model was created
        $this->assertDatabaseHas('commission_models', [
            'name' => '10 Level Integration Test',
            'commission_rate' => 5.0000,
            'number_of_levels' => 10,
            'final_commission' => 2.00,
            'weakest_person' => 'J',
            'weakest_sales' => 40.00,
            'weakest_commission' => 2.00,
        ]);

        $model = CommissionModel::where('name', '10 Level Integration Test')->first();
        $this->assertNotNull($model);

        // Verify all 10 levels were created
        $this->assertCount(10, $model->levels);

        // Check specific level calculation snapshots
        // Level 10: main J (sales 40, comm 2.00), side S10 (sales 200, comm 10.00), min 2.00
        $level10 = $model->levels->where('level', 10)->first();
        $this->assertEquals('J', $level10->main_person);
        $this->assertEquals(40.00, (float) $level10->main_sales);
        $this->assertEquals(2.00, (float) $level10->main_commission);
        $this->assertEquals('S10', $level10->side_person);
        $this->assertEquals(200.00, (float) $level10->side_sales);
        $this->assertEquals(10.00, (float) $level10->side_commission);
        $this->assertEquals(2.00, (float) $level10->selected_commission);
        $this->assertEquals(2.00, (float) $level10->leader_commission);

        // Verify redirect to show view with clear success message
        $response->assertRedirect(route('commission-models.show', $model));
        $response->assertSessionHas('success', "Commission model '10 Level Integration Test' successfully calculated and saved to the database!");
    }

    /**
     * Test validation failure rejects request without touching database.
     */
    public function test_validation_fails_on_negative_sales_or_missing_fields(): void
    {
        $initialCount = CommissionModel::count();

        // Negative sales
        $response = $this->post(route('models.store'), [
            'name' => 'Invalid Negative Model',
            'commission_rate' => 5.00,
            'number_of_levels' => 1,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => -100, 'side_person' => 'S1', 'side_sales' => 500],
            ],
        ]);

        $response->assertSessionHasErrors(['levels.0.main_sales']);
        $this->assertEquals($initialCount, CommissionModel::count());

        // Negative rate
        $response2 = $this->post(route('models.store'), [
            'name' => 'Invalid Rate Model',
            'commission_rate' => -5.00,
            'number_of_levels' => 1,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => 100, 'side_person' => 'S1', 'side_sales' => 500],
            ],
        ]);

        $response2->assertSessionHasErrors(['commission_rate']);
        $this->assertEquals($initialCount, CommissionModel::count());
    }

    /**
     * Test transaction rollback guarantees no partially completed models are saved.
     */
    public function test_transaction_rollback_prevents_partial_models_on_failure(): void
    {
        $initialModelsCount = CommissionModel::count();
        $initialLevelsCount = CommissionLevel::count();

        // Simulate a database failure during level insertion by hooking into model event
        CommissionLevel::saving(function ($level) {
            if ($level->level === 2 && $level->main_person === 'FAIL_TRIGGER') {
                throw new \RuntimeException('Simulated catastrophic database failure at level 2');
            }
        });

        $payload = [
            'name' => 'Should Be Rolled Back',
            'commission_rate' => 5.00,
            'number_of_levels' => 2,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => 1000, 'side_person' => 'S1', 'side_sales' => 1000],
                ['level' => 2, 'main_person' => 'FAIL_TRIGGER', 'main_sales' => 500, 'side_person' => 'S2', 'side_sales' => 500],
            ],
        ];

        $response = $this->post(route('models.store'), $payload);

        // Assert error returned and no orphan records exist
        $response->assertSessionHasErrors('error');
        $this->assertEquals($initialModelsCount, CommissionModel::count(), 'Parent commission_models record was NOT rolled back!');
        $this->assertEquals($initialLevelsCount, CommissionLevel::count(), 'Partial commission_levels records were NOT rolled back!');
    }
}
