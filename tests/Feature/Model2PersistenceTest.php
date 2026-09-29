<?php

namespace Tests\Feature;

use App\Models\CommissionLedger;
use App\Models\CommissionModel;
use App\Models\ModelEdge;
use App\Models\ModelNode;
use App\Services\OverrideCommissionCalculator;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model2PersistenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test saving a Model 2 persists all 8 required items accurately:
     * 1. Commission model
     * 2. All nodes
     * 3. All parent-child relationships
     * 4. All override percentages
     * 5. All personal sales
     * 6. Maximum generations
     * 7. Calculation results
     * 8. Commission ledger
     */
    public function test_saving_model_2_persists_all_eight_required_items_accurately(): void
    {
        $payload = [
            'name' => 'Q4 Regional Override Plan',
            'description' => 'Audited hierarchy with multiple upline overrides',
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

        $response = $this->post(route('override-models.store'), $payload);

        $model = CommissionModel::where('name', 'Q4 Regional Override Plan')->first();
        $this->assertNotNull($model);
        $response->assertRedirect(route('override-models.show', $model));

        // 1. Commission model
        $this->assertEquals('generation_override', $model->model_type);
        $this->assertEquals(900.00, (float) $model->total_sales);
        $this->assertEquals(63.00, (float) $model->final_commission);
        $this->assertEquals(63.00, (float) $model->total_potential_commission);

        // 6. Maximum generations
        $this->assertEquals(5, $model->max_generations);

        // 7. Calculation results snapshot
        $this->assertNotNull($model->calculation_results);
        $this->assertEquals(900.00, $model->calculation_results['total_personal_sales']);
        $this->assertEquals(63.00, $model->calculation_results['total_commission_generated']);
        $this->assertEquals(3, $model->calculation_results['number_of_sales']);
        $this->assertEquals(6, $model->calculation_results['number_of_commission_entries']);
        $this->assertEquals(5, $model->calculation_results['max_generations']);

        // 2. All nodes (A, B, C, E, F, E1)
        $nodes = $model->nodes->keyBy('name');
        $this->assertCount(6, $nodes);
        $this->assertTrue($nodes->has(['A', 'B', 'C', 'E', 'F', 'E1']));

        // Check parent-child hierarchy in nodes
        $this->assertNull($nodes['A']->parent_id);
        $this->assertEquals($nodes['A']->id, $nodes['B']->parent_id);
        $this->assertEquals($nodes['A']->id, $nodes['C']->parent_id);
        $this->assertEquals($nodes['B']->id, $nodes['E']->parent_id);
        $this->assertEquals($nodes['B']->id, $nodes['F']->parent_id);
        $this->assertEquals($nodes['E']->id, $nodes['E1']->parent_id);

        // 5. All personal sales
        $this->assertEquals(200.00, (float) $nodes['E1']->personal_sale);
        $this->assertEquals(400.00, (float) $nodes['F']->personal_sale);
        $this->assertEquals(300.00, (float) $nodes['C']->personal_sale);
        $this->assertEquals(0.00, (float) $nodes['A']->personal_sale);
        $this->assertEquals(0.00, (float) $nodes['B']->personal_sale);
        $this->assertEquals(0.00, (float) $nodes['E']->personal_sale);

        // 3. All parent-child relationships & 4. All override percentages
        $edges = $model->edges;
        $this->assertCount(5, $edges);

        $edgeLookup = $edges->mapWithKeys(function ($e) use ($nodes) {
            $parentName = $nodes->firstWhere('id', $e->parent_node_id)->name;
            $childName = $nodes->firstWhere('id', $e->child_node_id)->name;

            return ["{$parentName}->{$childName}" => (float) $e->override_rate];
        })->toArray();

        $this->assertEquals(2.0, $edgeLookup['A->B']);
        $this->assertEquals(5.0, $edgeLookup['A->C']);
        $this->assertEquals(3.0, $edgeLookup['B->E']);
        $this->assertEquals(5.0, $edgeLookup['B->F']);
        $this->assertEquals(5.0, $edgeLookup['E->E1']);

        // 8. Commission ledger
        $ledger = $model->ledger()->with(['saleNode', 'earnerNode'])->get();
        $this->assertCount(6, $ledger);

        // Verify every commission ledger record preserves:
        // - Model ID
        // - Sale node
        // - Earner node
        // - Generation
        // - Original sale amount
        // - Rate applied
        // - Commission amount
        foreach ($ledger as $entry) {
            $this->assertEquals($model->id, $entry->commission_model_id);
            $this->assertNotNull($entry->sale_node_id);
            $this->assertNotNull($entry->earner_node_id);
            $this->assertNotNull($entry->saleNode);
            $this->assertNotNull($entry->earnerNode);
            $this->assertGreaterThan(0, $entry->generation);
            $this->assertGreaterThan(0, (float) $entry->sale_amount);
            $this->assertGreaterThan(0, (float) $entry->rate_applied);
            $this->assertGreaterThan(0, (float) $entry->commission_amount);
            $this->assertTrue((bool) $entry->is_eligible);
            $this->assertEquals('paid', $entry->status);
        }

        // Test specific ledger rows for E1 sale = ₹200:
        // E (Gen 1): 200 * 5% = 10
        // B (Gen 2): 200 * 3% = 6
        // A (Gen 3): 200 * 2% = 4
        $e1Sales = $ledger->filter(fn ($l) => $l->saleNode->name === 'E1')->values();
        $this->assertCount(3, $e1Sales);

        $this->assertEquals('E', $e1Sales[0]->earnerNode->name);
        $this->assertEquals(1, $e1Sales[0]->generation);
        $this->assertEquals(200.00, (float) $e1Sales[0]->sale_amount);
        $this->assertEquals(5.0, (float) $e1Sales[0]->rate_applied);
        $this->assertEquals(10.00, (float) $e1Sales[0]->commission_amount);

        $this->assertEquals('B', $e1Sales[1]->earnerNode->name);
        $this->assertEquals(2, $e1Sales[1]->generation);
        $this->assertEquals(200.00, (float) $e1Sales[1]->sale_amount);
        $this->assertEquals(3.0, (float) $e1Sales[1]->rate_applied);
        $this->assertEquals(6.00, (float) $e1Sales[1]->commission_amount);

        $this->assertEquals('A', $e1Sales[2]->earnerNode->name);
        $this->assertEquals(3, $e1Sales[2]->generation);
        $this->assertEquals(200.00, (float) $e1Sales[2]->sale_amount);
        $this->assertEquals(2.0, (float) $e1Sales[2]->rate_applied);
        $this->assertEquals(4.00, (float) $e1Sales[2]->commission_amount);
    }

    /**
     * Test database transaction: If any part fails, ROLLBACK EVERYTHING.
     */
    public function test_database_transaction_rolls_back_everything_if_any_part_fails(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Transaction Failure Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
        ]);

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
        ];

        // Valid sales
        $sales = [
            ['salesperson' => 'B', 'amount' => 100.0],
        ];

        // Ensure database is initially empty of nodes/edges/ledger
        $this->assertEquals(0, ModelNode::count());
        $this->assertEquals(0, ModelEdge::count());
        $this->assertEquals(0, CommissionLedger::count());

        // We simulate a failure inside persistModel by passing an invalid cycle or triggering exception
        try {
            $calculator->persistModel(
                $model,
                [
                    ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
                    ['parent' => 'B', 'child' => 'A', 'rate' => 3.0], // Circular cycle -> triggers InvalidArgumentException
                ],
                $sales,
                5
            );
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\InvalidArgumentException $e) {
            // Expected
        }

        // Verify that ROLLBACK EVERYTHING occurred:
        // No orphaned nodes, edges, or ledger rows remain
        $this->assertEquals(0, ModelNode::count(), 'Nodes must be 0 after rollback');
        $this->assertEquals(0, ModelEdge::count(), 'Edges must be 0 after rollback');
        $this->assertEquals(0, CommissionLedger::count(), 'Ledger entries must be 0 after rollback');
    }

    /**
     * Test historical integrity:
     * A historical model must not change merely because the calculation rules are changed later.
     * Do not recalculate historical records when viewing them unless explicitly requested.
     */
    public function test_historical_model_does_not_change_when_viewed_unless_explicitly_requested(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Historical Audit Model',
            'model_type' => 'generation_override',
            'max_generations' => 5,
        ]);

        $edges = [
            ['parent' => 'Supervisor', 'child' => 'Agent', 'rate' => 10.0],
        ];
        $sales = [
            ['salesperson' => 'Agent', 'amount' => 500.0],
        ];

        $calculator->persistModel($model, $edges, $sales, 5);
        $model->refresh();

        // Historical commission was: 500 * 10% = 50.00
        $this->assertEquals(50.00, (float) $model->final_commission);

        // Now simulate someone tampering with the ledger or altering node sale values in the DB:
        // Or imagine the calculation formula or rates changed in the code:
        // For audit verification, viewing the model normally loads the locked historical results:
        $response = $this->get(route('override-models.show', $model));
        $response->assertStatus(200);

        // Verify it displays the historical snapshot notice
        $response->assertSee('Audited Historical Record');
        $response->assertSee('Historical calculation results locked to preserve audit integrity');
        $response->assertDontSee('Recalculated Preview');

        // When explicitly requested via ?recalculate=1:
        $recalculatedResponse = $this->get(route('override-models.show', [$model, 'recalculate' => 1]));
        $recalculatedResponse->assertStatus(200);
        $recalculatedResponse->assertSee('Recalculated Preview');
        $recalculatedResponse->assertSee('Recalculation was explicitly requested');
    }

    /**
     * Test that every commission ledger record is auditable and preserves all requested columns:
     * - Model ID
     * - Sale node
     * - Earner node
     * - Generation
     * - Original sale amount
     * - Rate applied
     * - Commission amount
     */
    public function test_commission_ledger_preserves_all_auditable_attributes(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Auditable Ledger Verification Model',
            'model_type' => 'generation_override',
            'max_generations' => 5,
        ]);

        $edges = [
            ['parent' => 'Root', 'child' => 'Lead', 'rate' => 4.0],
            ['parent' => 'Lead', 'child' => 'Closer', 'rate' => 6.0],
        ];
        $sales = [
            ['salesperson' => 'Closer', 'amount' => 1000.0],
        ];

        $calculator->persistModel($model, $edges, $sales, 5);

        $entries = CommissionLedger::where('commission_model_id', $model->id)
            ->with(['saleNode', 'earnerNode'])
            ->orderBy('generation')
            ->get();

        $this->assertCount(2, $entries);

        // Entry 1: Lead (Gen 1) -> 1000 * 6% = 60
        $e1 = $entries[0];
        $this->assertEquals($model->id, $e1->commission_model_id);
        $this->assertEquals('Closer', $e1->saleNode->name);
        $this->assertEquals('Lead', $e1->earnerNode->name);
        $this->assertEquals(1, $e1->generation);
        $this->assertEquals(1000.00, (float) $e1->sale_amount);
        $this->assertEquals(6.0, (float) $e1->rate_applied);
        $this->assertEquals(60.00, (float) $e1->commission_amount);
        $this->assertTrue((bool) $e1->is_eligible);
        $this->assertEquals('paid', $e1->status);

        // Entry 2: Root (Gen 2) -> 1000 * 4% = 40
        $e2 = $entries[1];
        $this->assertEquals($model->id, $e2->commission_model_id);
        $this->assertEquals('Closer', $e2->saleNode->name);
        $this->assertEquals('Root', $e2->earnerNode->name);
        $this->assertEquals(2, $e2->generation);
        $this->assertEquals(1000.00, (float) $e2->sale_amount);
        $this->assertEquals(4.0, (float) $e2->rate_applied);
        $this->assertEquals(40.00, (float) $e2->commission_amount);
        $this->assertTrue((bool) $e2->is_eligible);
        $this->assertEquals('paid', $e2->status);
    }
}
