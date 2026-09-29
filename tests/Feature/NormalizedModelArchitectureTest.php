<?php

namespace Tests\Feature;

use App\Models\CommissionLevel;
use App\Models\CommissionModel;
use App\Models\ModelEdge;
use App\Models\ModelNode;
use App\Services\OverrideCommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormalizedModelArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_into_normalized_nodes_edges_and_commission_ledger(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Spec Tree Normalized Architecture Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
        ]);

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'C', 'amount' => 300.0],
        ];

        $savedModel = $calculator->persistModel($model, $edges, $sales, 5);

        // 1. Verify model_nodes table
        $this->assertDatabaseHas('model_nodes', [
            'commission_model_id' => $model->id,
            'name' => 'A',
            'node_type' => 'root',
            'parent_id' => null,
            'personal_sale' => 0.00,
        ]);

        $this->assertDatabaseHas('model_nodes', [
            'commission_model_id' => $model->id,
            'name' => 'E1',
            'personal_sale' => 200.00,
        ]);

        $nodeE1 = ModelNode::where('commission_model_id', $model->id)->where('name', 'E1')->first();
        $nodeE = ModelNode::where('commission_model_id', $model->id)->where('name', 'E')->first();
        $nodeB = ModelNode::where('commission_model_id', $model->id)->where('name', 'B')->first();
        $nodeA = ModelNode::where('commission_model_id', $model->id)->where('name', 'A')->first();

        $this->assertNotNull($nodeE1);
        $this->assertNotNull($nodeE);
        $this->assertNotNull($nodeB);
        $this->assertNotNull($nodeA);

        $this->assertEquals($nodeE->id, $nodeE1->parent_id);
        $this->assertEquals($nodeB->id, $nodeE->parent_id);
        $this->assertEquals($nodeA->id, $nodeB->parent_id);

        // 2. Verify model_edges table with override rates
        $edgeE_E1 = ModelEdge::where('commission_model_id', $model->id)
            ->where('parent_node_id', $nodeE->id)
            ->where('child_node_id', $nodeE1->id)
            ->first();
        $this->assertNotNull($edgeE_E1);
        $this->assertEquals('5.0000', $edgeE_E1->override_rate);

        $edgeB_E = ModelEdge::where('commission_model_id', $model->id)
            ->where('parent_node_id', $nodeB->id)
            ->where('child_node_id', $nodeE->id)
            ->first();
        $this->assertNotNull($edgeB_E);
        $this->assertEquals('3.0000', $edgeB_E->override_rate);

        $edgeA_B = ModelEdge::where('commission_model_id', $model->id)
            ->where('parent_node_id', $nodeA->id)
            ->where('child_node_id', $nodeB->id)
            ->first();
        $this->assertNotNull($edgeA_B);
        $this->assertEquals('2.0000', $edgeA_B->override_rate);

        // 3. Verify commission_ledger table auditability
        // Check for E1's 200 sale -> E earns 10 at Gen 1
        $this->assertDatabaseHas('commission_ledger', [
            'commission_model_id' => $model->id,
            'sale_node_id' => $nodeE1->id,
            'earner_node_id' => $nodeE->id,
            'generation' => 1,
            'sale_amount' => 200.00,
            'rate_applied' => 5.0000,
            'commission_amount' => 10.00,
            'is_eligible' => 1,
        ]);

        // Check for E1's 200 sale -> B earns 6 at Gen 2
        $this->assertDatabaseHas('commission_ledger', [
            'commission_model_id' => $model->id,
            'sale_node_id' => $nodeE1->id,
            'earner_node_id' => $nodeB->id,
            'generation' => 2,
            'sale_amount' => 200.00,
            'rate_applied' => 3.0000,
            'commission_amount' => 6.00,
            'is_eligible' => 1,
        ]);

        // Check for E1's 200 sale -> A earns 4 at Gen 3
        $this->assertDatabaseHas('commission_ledger', [
            'commission_model_id' => $model->id,
            'sale_node_id' => $nodeE1->id,
            'earner_node_id' => $nodeA->id,
            'generation' => 3,
            'sale_amount' => 200.00,
            'rate_applied' => 2.0000,
            'commission_amount' => 4.00,
            'is_eligible' => 1,
        ]);

        // Check total commission in ledger
        $ledgerTotal = $savedModel->ledger()->where('is_eligible', true)->sum('commission_amount');
        $this->assertEquals(63.00, (float) $ledgerTotal);

        // 4. Verify cascade on delete
        $savedModel->delete();
        $this->assertDatabaseMissing('model_nodes', ['commission_model_id' => $model->id]);
        $this->assertDatabaseMissing('model_edges', ['commission_model_id' => $model->id]);
        $this->assertDatabaseMissing('commission_ledger', ['commission_model_id' => $model->id]);
    }

    public function test_existing_model_1_weakest_link_data_is_unaffected(): void
    {
        $weakestModel = CommissionModel::create([
            'name' => 'Original Model 1 Weakest Link Test',
            'model_type' => 'weakest_link',
            'commission_rate' => 10.0000,
            'number_of_levels' => 2,
            'total_sales' => 1000.00,
            'final_commission' => 100.00,
            'weakest_person' => 'Bob',
            'weakest_sales' => 200.00,
            'weakest_commission' => 20.00,
        ]);

        CommissionLevel::create([
            'commission_model_id' => $weakestModel->id,
            'level' => 1,
            'main_person' => 'Alice',
            'main_sales' => 500.00,
            'main_commission' => 50.00,
        ]);

        CommissionLevel::create([
            'commission_model_id' => $weakestModel->id,
            'level' => 2,
            'main_person' => 'Bob',
            'main_sales' => 200.00,
            'main_commission' => 20.00,
        ]);

        $this->assertDatabaseHas('commission_models', [
            'id' => $weakestModel->id,
            'name' => 'Original Model 1 Weakest Link Test',
            'model_type' => 'weakest_link',
            'commission_rate' => 10.0000,
        ]);

        $this->assertDatabaseHas('commission_levels', [
            'commission_model_id' => $weakestModel->id,
            'main_person' => 'Bob',
            'main_sales' => 200.00,
        ]);

        $this->assertCount(2, $weakestModel->fresh()->levels);
        $this->assertCount(0, $weakestModel->fresh()->nodes);
        $this->assertCount(0, $weakestModel->fresh()->edges);
        $this->assertCount(0, $weakestModel->fresh()->ledger);
    }
}
