<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverrideModelFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test create page loads successfully.
     */
    public function test_override_model_create_page_renders_successfully(): void
    {
        $response = $this->get(route('override-models.create'));
        $response->assertStatus(200);
        $response->assertSee('Level / Generation Override Commission Model');
        $response->assertSee('Model 2 Engine');
    }

    /**
     * Test AJAX calculate endpoint with exact user example specification.
     */
    public function test_override_model_calculate_endpoint_computes_exact_specification(): void
    {
        $payload = [
            'name' => 'Spec Example',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
                ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
                ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
                ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
                ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
            ],
            'sales' => [
                ['salesperson' => 'C', 'amount' => 300.0],
                ['salesperson' => 'F', 'amount' => 400.0],
                ['salesperson' => 'E1', 'amount' => 200.0],
            ],
        ];

        $response = $this->postJson(route('override-models.calculate'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'model_type' => 'generation_override',
                'max_generations' => 5,
                'total_sales' => 900.00,
                'total_commission' => 63.00,
                'final_commission' => 63.00,
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals(27.00, $data['earnings_by_person']['A']['override_commission']);
        $this->assertEquals(26.00, $data['earnings_by_person']['B']['override_commission']);
        $this->assertEquals(10.00, $data['earnings_by_person']['E']['override_commission']);
    }

    /**
     * Test storing Model 2 into database inside transaction.
     */
    public function test_override_model_store_persists_model_and_relations_in_db(): void
    {
        $payload = [
            'name' => 'Q4 Override Model Test',
            'description' => 'Test notes',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
                ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
                ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
                ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
                ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
            ],
            'sales' => [
                ['salesperson' => 'C', 'amount' => 300.0],
                ['salesperson' => 'F', 'amount' => 400.0],
                ['salesperson' => 'E1', 'amount' => 200.0],
            ],
        ];

        $response = $this->post(route('override-models.store'), $payload);

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Q4 Override Model Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 900.00,
            'final_commission' => 63.00,
        ]);

        $model = CommissionModel::where('name', 'Q4 Override Model Test')->first();
        $this->assertNotNull($model);
        $this->assertTrue($model->isOverrideModel());
        $this->assertFalse($model->isWeakestLinkModel());

        // Verify relationships persisted
        $this->assertCount(5, $model->relationships);
        $this->assertDatabaseHas('override_relationships', [
            'commission_model_id' => $model->id,
            'parent_name' => 'A',
            'child_name' => 'B',
            'rate' => 2.0000,
        ]);

        // Verify sales persisted
        $this->assertCount(3, $model->overrideSales);
        $this->assertDatabaseHas('override_sales', [
            'commission_model_id' => $model->id,
            'salesperson_name' => 'E1',
            'amount' => 200.00,
        ]);

        // Verify commissions generated and persisted
        $this->assertDatabaseHas('override_commissions', [
            'commission_model_id' => $model->id,
            'seller_name' => 'E1',
            'recipient_name' => 'E',
            'generation' => 1,
            'rate' => 5.0000,
            'commission_amount' => 10.00,
            'is_eligible' => true,
        ]);

        $this->assertDatabaseHas('override_commissions', [
            'commission_model_id' => $model->id,
            'seller_name' => 'E1',
            'recipient_name' => 'B',
            'generation' => 2,
            'rate' => 3.0000,
            'commission_amount' => 6.00,
            'is_eligible' => true,
        ]);

        $this->assertDatabaseHas('override_commissions', [
            'commission_model_id' => $model->id,
            'seller_name' => 'E1',
            'recipient_name' => 'A',
            'generation' => 3,
            'rate' => 2.0000,
            'commission_amount' => 4.00,
            'is_eligible' => true,
        ]);

        $response->assertRedirect(route('override-models.show', $model));
    }

    /**
     * Test show page renders correctly with model details.
     */
    public function test_override_model_show_page_renders(): void
    {
        $model = CommissionModel::create([
            'name' => 'Show View Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 900.00,
            'final_commission' => 63.00,
        ]);

        $model->relationships()->createMany([
            ['parent_name' => 'A', 'child_name' => 'B', 'rate' => 2.0],
            ['parent_name' => 'B', 'child_name' => 'C', 'rate' => 5.0],
        ]);

        $model->overrideSales()->createMany([
            ['salesperson_name' => 'C', 'amount' => 200.0],
        ]);

        $response = $this->get(route('override-models.show', $model));
        $response->assertStatus(200);
        $response->assertSee('Show View Test');
        $response->assertSee('Model 2: Generation Override');
        $response->assertSee('₹200');
    }

    /**
     * Test deletion removes model and all cascaded children.
     */
    public function test_override_model_destroy_cascades(): void
    {
        $model = CommissionModel::create([
            'name' => 'Delete Test Model',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 100.00,
            'final_commission' => 5.00,
        ]);

        $model->relationships()->create(['parent_name' => 'A', 'child_name' => 'B', 'rate' => 5.0]);
        $model->overrideSales()->create(['salesperson_name' => 'B', 'amount' => 100.0]);
        $model->overrideCommissions()->create([
            'seller_name' => 'B',
            'sale_amount' => 100.00,
            'recipient_name' => 'A',
            'child_name' => 'B',
            'generation' => 1,
            'rate' => 5.0,
            'commission_amount' => 5.0,
            'is_eligible' => true,
        ]);

        $modelId = $model->id;

        $response = $this->delete(route('override-models.destroy', $model));
        $response->assertRedirect(route('commission-models.index'));

        $this->assertDatabaseMissing('commission_models', ['id' => $modelId]);
        $this->assertDatabaseMissing('override_relationships', ['commission_model_id' => $modelId]);
        $this->assertDatabaseMissing('override_sales', ['commission_model_id' => $modelId]);
        $this->assertDatabaseMissing('override_commissions', ['commission_model_id' => $modelId]);
    }
}
