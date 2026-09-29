<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model2TreeBuilderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an arbitrary-named, arbitrary-depth hierarchical tree can be created and calculated.
     */
    public function test_arbitrary_tree_hierarchy_creation_and_calculation(): void
    {
        $payload = [
            'name' => 'Corporate Hierarchy Q3',
            'max_generations' => 4,
            'description' => 'Arbitrary multi-level hierarchy',
            'nodes' => [
                [
                    'name' => 'Leader_Alpha',
                    'parent' => null,
                    'node_type' => 'Root Leader',
                    'override_rate' => 0.0,
                    'personal_sale' => 0.0,
                ],
                [
                    'name' => 'Manager_Beta',
                    'parent' => 'Leader_Alpha',
                    'node_type' => 'Regional Manager',
                    'override_rate' => 4.0,
                    'personal_sale' => 0.0,
                ],
                [
                    'name' => 'Seller_Gamma',
                    'parent' => 'Manager_Beta',
                    'node_type' => 'Salesperson',
                    'override_rate' => 6.0,
                    'personal_sale' => 500.0,
                ],
                [
                    'name' => 'Branch_Delta',
                    'parent' => 'Leader_Alpha',
                    'node_type' => 'Salesperson',
                    'override_rate' => 7.0,
                    'personal_sale' => 800.0,
                ],
            ],
        ];

        $response = $this->post(route('override-models.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('commission_models', [
            'name' => 'Corporate Hierarchy Q3',
            'model_type' => 'generation_override',
            'max_generations' => 4,
            'total_sales' => 1300.00,
            'final_commission' => 106.00,
        ]);

        $model = CommissionModel::where('name', 'Corporate Hierarchy Q3')->firstOrFail();
        $this->assertCount(4, $model->nodes);
        $this->assertCount(3, $model->edges);

        // Earnings checks:
        // Seller_Gamma sale (500):
        //   - Manager_Beta (Gen 1): 500 * 6% = 30
        //   - Leader_Alpha (Gen 2): 500 * 4% = 20
        // Branch_Delta sale (800):
        //   - Leader_Alpha (Gen 1): 800 * 7% = 56
        // Leader_Alpha total = 20 + 56 = 76
        // Manager_Beta total = 30
        // Total = 106
        $this->assertEquals(76.00, $model->ledger()->whereHas('earnerNode', fn ($q) => $q->where('name', 'Leader_Alpha'))->sum('commission_amount'));
        $this->assertEquals(30.00, $model->ledger()->whereHas('earnerNode', fn ($q) => $q->where('name', 'Manager_Beta'))->sum('commission_amount'));
    }

    /**
     * Test that negative personal sales are rejected.
     */
    public function test_negative_sales_are_rejected(): void
    {
        $payload = [
            'name' => 'Invalid Sale Model',
            'max_generations' => 5,
            'nodes' => [
                ['name' => 'Root', 'parent' => null, 'node_type' => 'Leader', 'override_rate' => 0, 'personal_sale' => 0],
                ['name' => 'Child', 'parent' => 'Root', 'node_type' => 'Rep', 'override_rate' => 5, 'personal_sale' => -100],
            ],
        ];

        $response = $this->post(route('override-models.store'), $payload);
        $response->assertSessionHasErrors(['sales.0.amount']);
    }

    /**
     * Test that invalid override percentages (>100 or <0) are rejected.
     */
    public function test_invalid_override_rates_are_rejected(): void
    {
        $payloadOver = [
            'name' => 'Invalid Rate Model Over',
            'max_generations' => 5,
            'nodes' => [
                ['name' => 'Root', 'parent' => null, 'node_type' => 'Leader', 'override_rate' => 0, 'personal_sale' => 0],
                ['name' => 'Child', 'parent' => 'Root', 'node_type' => 'Rep', 'override_rate' => 105.0, 'personal_sale' => 100],
            ],
        ];

        $responseOver = $this->post(route('override-models.store'), $payloadOver);
        $responseOver->assertSessionHasErrors(['edges.0.rate']);

        $payloadNegative = [
            'name' => 'Invalid Rate Model Neg',
            'max_generations' => 5,
            'nodes' => [
                ['name' => 'Root', 'parent' => null, 'node_type' => 'Leader', 'override_rate' => 0, 'personal_sale' => 0],
                ['name' => 'Child', 'parent' => 'Root', 'node_type' => 'Rep', 'override_rate' => -2.5, 'personal_sale' => 100],
            ],
        ];

        $responseNeg = $this->post(route('override-models.store'), $payloadNegative);
        $responseNeg->assertSessionHasErrors(['edges.0.rate']);
    }

    /**
     * Test that circular relationships are caught and rejected by calculation engine.
     */
    public function test_circular_relationships_are_prevented(): void
    {
        $payload = [
            'name' => 'Cycle Model',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'NodeA', 'child' => 'NodeB', 'rate' => 5.0],
                ['parent' => 'NodeB', 'child' => 'NodeA', 'rate' => 5.0],
            ],
            'sales' => [
                ['salesperson' => 'NodeA', 'amount' => 500.0],
            ],
        ];

        $response = $this->post(route('override-models.store'), $payload);
        $response->assertSessionHasErrors();
    }
}
