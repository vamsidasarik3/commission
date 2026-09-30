<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelTypeSelectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Create Commission Model page renders with Model Type selection cards.
     */
    public function test_create_page_renders_model_type_selection(): void
    {
        $response = $this->get(route('commission-models.create'));

        $response->assertStatus(200);
        $response->assertSee('Weakest Link Commission Model');
        $response->assertSee('Model 1 Engine');
        $response->assertSee('Model 2 (Override)');
        $response->assertSee('Model 3 (Unilevel)');
        $response->assertSee('Default 10-Level');

        // Verify query redirect to Model 2 and Model 3
        $resOverride = $this->get(route('commission-models.create', ['type' => 'generation_override']));
        $resOverride->assertRedirect(route('override-models.create'));

        $resUnilevel = $this->get(route('commission-models.create', ['type' => 'unilevel']));
        $resUnilevel->assertRedirect(route('unilevel-models.create'));
    }

    /**
     * Test Model 1 submission through the create page endpoints.
     */
    public function test_submitting_model_1_creates_weakest_link_model(): void
    {
        $payload = [
            'model_type' => 'weakest_link',
            'name' => 'Model 1 Selection Test',
            'commission_rate' => 5.0,
            'number_of_levels' => 2,
            'levels' => [
                ['level' => 1, 'main_person' => 'A', 'main_sales' => 500, 'side_person' => 'S1', 'side_sales' => 500],
                ['level' => 2, 'main_person' => 'B', 'main_sales' => 300, 'side_person' => 'S2', 'side_sales' => 400],
            ],
        ];

        $response = $this->post(route('commission-models.store'), $payload);

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Model 1 Selection Test',
            'model_type' => 'weakest_link',
            'commission_rate' => 5.0,
            'number_of_levels' => 2,
            'final_commission' => 15.00, // min(300, 400) * 5% = 15
        ]);

        $model = CommissionModel::where('name', 'Model 1 Selection Test')->first();
        $this->assertNotNull($model);
        $this->assertCount(2, $model->levels);
    }

    /**
     * Test Model 2 submission through the create page endpoints.
     */
    public function test_submitting_model_2_creates_generation_override_model(): void
    {
        $payload = [
            'model_type' => 'generation_override',
            'name' => 'Level Override Example',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
                ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
                ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
                ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
                ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
            ],
            'sales' => [
                ['salesperson' => 'E1', 'amount' => 200.0],
                ['salesperson' => 'F', 'amount' => 400.0],
                ['salesperson' => 'C', 'amount' => 300.0],
            ],
        ];

        $response = $this->post(route('commission-models.store'), $payload);

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Level Override Example',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 900.00,
            'final_commission' => 63.00,
        ]);

        $model = CommissionModel::where('name', 'Level Override Example')->first();
        $this->assertNotNull($model);
        $this->assertCount(6, $model->nodes);
        $this->assertCount(5, $model->edges);
        $this->assertCount(6, $model->ledger);
    }

    /**
     * Test live calculate endpoint delegation when model_type is generation_override.
     */
    public function test_calculate_endpoint_delegates_to_override_calculator(): void
    {
        $payload = [
            'model_type' => 'generation_override',
            'name' => 'Ajax Test',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'A', 'child' => 'B', 'rate' => 5.0],
            ],
            'sales' => [
                ['salesperson' => 'B', 'amount' => 1000.0],
            ],
        ];

        $response = $this->postJson(route('commission-models.calculate'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_personal_sales' => 1000.00,
                'total_commission_generated' => 50.00,
            ],
        ]);
    }
}
