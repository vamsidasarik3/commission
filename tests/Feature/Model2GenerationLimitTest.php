<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use App\Models\ModelEdge;
use App\Models\ModelNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model2GenerationLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the exact specification deep chain example:
     * Sale: J = ₹1000
     * Upline:
     * I = Generation 1 (paid)
     * H = Generation 2 (paid)
     * G = Generation 3 (paid)
     * F = Generation 4 (paid)
     * E = Generation 5 (paid)
     * D = Generation 6 (NOT PAID)
     *
     * With maximum generations = 5:
     * I = paid
     * H = paid
     * G = paid
     * F = paid
     * E = paid
     * D = NOT PAID
     */
    public function test_exact_specification_deep_chain_generation_limit(): void
    {
        $payload = [
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'D', 'child' => 'E', 'rate' => 2],
                ['parent' => 'E', 'child' => 'F', 'rate' => 2],
                ['parent' => 'F', 'child' => 'G', 'rate' => 2],
                ['parent' => 'G', 'child' => 'H', 'rate' => 2],
                ['parent' => 'H', 'child' => 'I', 'rate' => 2],
                ['parent' => 'I', 'child' => 'J', 'rate' => 2],
            ],
            'sales' => [
                ['salesperson' => 'J', 'amount' => 1000],
            ],
        ];

        $response = $this->postJson(route('override-models.calculate'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_personal_sales' => 1000,
                    'total_commission_generated' => 100, // 5 paid uplines * (1000 * 2%) = 100
                    'number_of_sales' => 1,
                    'number_of_commission_entries' => 5,
                    'max_generations' => 5,
                ],
            ]);

        $data = $response->json('data');

        // Check commission_by_sale breakdown
        $this->assertCount(1, $data['commission_by_sale']);
        $saleBreakdown = $data['commission_by_sale'][0];
        $this->assertEquals(5, $saleBreakdown['paid_through_generation']);
        $this->assertEquals(5, $saleBreakdown['cutoff_generation']);
        $this->assertTrue($saleBreakdown['has_excluded_uplines']);
        $this->assertCount(6, $saleBreakdown['commissions']);

        // Check each upline status and generation
        $commissions = $saleBreakdown['commissions'];

        // I = Generation 1 => paid
        $this->assertEquals('I', $commissions[0]['earner']);
        $this->assertEquals(1, $commissions[0]['generation']);
        $this->assertTrue($commissions[0]['is_eligible']);
        $this->assertEquals('paid', $commissions[0]['status']);
        $this->assertEquals(20.0, $commissions[0]['commission']);

        // H = Generation 2 => paid
        $this->assertEquals('H', $commissions[1]['earner']);
        $this->assertEquals(2, $commissions[1]['generation']);
        $this->assertTrue($commissions[1]['is_eligible']);
        $this->assertEquals('paid', $commissions[1]['status']);
        $this->assertEquals(20.0, $commissions[1]['commission']);

        // G = Generation 3 => paid
        $this->assertEquals('G', $commissions[2]['earner']);
        $this->assertEquals(3, $commissions[2]['generation']);
        $this->assertTrue($commissions[2]['is_eligible']);
        $this->assertEquals('paid', $commissions[2]['status']);
        $this->assertEquals(20.0, $commissions[2]['commission']);

        // F = Generation 4 => paid
        $this->assertEquals('F', $commissions[3]['earner']);
        $this->assertEquals(4, $commissions[3]['generation']);
        $this->assertTrue($commissions[3]['is_eligible']);
        $this->assertEquals('paid', $commissions[3]['status']);
        $this->assertEquals(20.0, $commissions[3]['commission']);

        // E = Generation 5 => paid
        $this->assertEquals('E', $commissions[4]['earner']);
        $this->assertEquals(5, $commissions[4]['generation']);
        $this->assertTrue($commissions[4]['is_eligible']);
        $this->assertEquals('paid', $commissions[4]['status']);
        $this->assertEquals(20.0, $commissions[4]['commission']);

        // D = Generation 6 => NOT PAID
        $this->assertEquals('D', $commissions[5]['earner']);
        $this->assertEquals(6, $commissions[5]['generation']);
        $this->assertFalse($commissions[5]['is_eligible']);
        $this->assertEquals('NOT PAID', $commissions[5]['status']);
        $this->assertEquals(0.0, $commissions[5]['commission']);

        // Verify earnings by person: D earned 0
        $this->assertEquals(0.0, $data['commission_by_person']['D']['total_commission']);
        $this->assertEquals(0, $data['commission_by_person']['D']['commissions_received_count']);

        // I, H, G, F, E earned 20 each
        $this->assertEquals(20.0, $data['commission_by_person']['I']['total_commission']);
        $this->assertEquals(20.0, $data['commission_by_person']['H']['total_commission']);
        $this->assertEquals(20.0, $data['commission_by_person']['G']['total_commission']);
        $this->assertEquals(20.0, $data['commission_by_person']['F']['total_commission']);
        $this->assertEquals(20.0, $data['commission_by_person']['E']['total_commission']);
    }

    /**
     * Test that generation limit applies independently to every sale.
     * When J sells, D is Gen 6 (NOT PAID).
     * When F sells, E is Gen 1 and D is Gen 2 (D is PAID!).
     */
    public function test_generation_limit_applies_independently_to_every_sale(): void
    {
        $payload = [
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'D', 'child' => 'E', 'rate' => 2],
                ['parent' => 'E', 'child' => 'F', 'rate' => 2],
                ['parent' => 'F', 'child' => 'G', 'rate' => 2],
                ['parent' => 'G', 'child' => 'H', 'rate' => 2],
                ['parent' => 'H', 'child' => 'I', 'rate' => 2],
                ['parent' => 'I', 'child' => 'J', 'rate' => 2],
            ],
            'sales' => [
                ['salesperson' => 'J', 'amount' => 1000],
                ['salesperson' => 'F', 'amount' => 500],
            ],
        ];

        $response = $this->postJson(route('override-models.calculate'), $payload);
        $response->assertStatus(200);
        $data = $response->json('data');

        // Sale 1 (J = 1000): D is Gen 6 => NOT PAID
        $sale1Commissions = $data['commission_by_sale'][0]['commissions'];
        $dSale1 = collect($sale1Commissions)->firstWhere('earner', 'D');
        $this->assertEquals(6, $dSale1['generation']);
        $this->assertEquals('NOT PAID', $dSale1['status']);
        $this->assertEquals(0.0, $dSale1['commission']);

        // Sale 2 (F = 500): Uplines are E (Gen 1) and D (Gen 2) => Both PAID!
        $sale2Commissions = $data['commission_by_sale'][1]['commissions'];
        $dSale2 = collect($sale2Commissions)->firstWhere('earner', 'D');
        $this->assertEquals(2, $dSale2['generation']);
        $this->assertEquals('paid', $dSale2['status']);
        $this->assertEquals(10.0, $dSale2['commission']); // 500 * 2% = 10

        // D's overall total commission includes the earnings from F's sale
        $this->assertEquals(10.0, $data['commission_by_person']['D']['total_commission']);
        $this->assertEquals(1, $data['commission_by_person']['D']['commissions_received_count']);
    }

    /**
     * Test deep tree with 20 levels:
     * Does not interpret max_generations as maximum tree depth.
     * A tree can contain 20 levels while only 5 uplines receive commission for each sale.
     */
    public function test_twenty_level_tree_with_five_max_generations(): void
    {
        $edges = [];
        for ($i = 1; $i < 20; $i++) {
            $edges[] = [
                'parent' => "L{$i}",
                'child' => 'L'.($i + 1),
                'rate' => 3.0,
            ];
        }

        // Sale is at level 20: L20 sells 1000
        $payload = [
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'edges' => $edges,
            'sales' => [
                ['salesperson' => 'L20', 'amount' => 1000],
            ],
        ];

        $response = $this->postJson(route('override-models.calculate'), $payload);
        $response->assertStatus(200);
        $data = $response->json('data');

        // Tree depth is 20
        $this->assertEquals(20, $data['tree_summary']['max_depth']);
        $this->assertEquals(20, $data['tree_summary']['total_nodes']);

        // Total commissions: only 5 uplines (L19, L18, L17, L16, L15) receive commission
        // 1000 * 3% = 30 each. 5 * 30 = 150.
        $this->assertEquals(150.0, $data['total_commission_generated']);
        $this->assertEquals(5, $data['number_of_commission_entries']);

        // Uplines from L14 up to L1 (14 nodes) are all NOT PAID
        $saleLedger = $data['commission_by_sale'][0]['commissions'];
        $this->assertCount(19, $saleLedger); // 19 uplines in total

        for ($idx = 0; $idx < 5; $idx++) {
            $this->assertEquals('paid', $saleLedger[$idx]['status']);
            $this->assertTrue($saleLedger[$idx]['is_eligible']);
            $this->assertEquals(30.0, $saleLedger[$idx]['commission']);
        }

        for ($idx = 5; $idx < 19; $idx++) {
            $this->assertEquals('NOT PAID', $saleLedger[$idx]['status']);
            $this->assertFalse($saleLedger[$idx]['is_eligible']);
            $this->assertEquals(0.0, $saleLedger[$idx]['commission']);
        }
    }

    /**
     * Test that the UI view renders the generation limit messages and badges.
     */
    public function test_ui_view_displays_generation_limit_callouts_and_badges(): void
    {
        // Create model in database with D -> E -> F -> G -> H -> I -> J
        $model = CommissionModel::create([
            'name' => 'Generation Limit Showcase Model',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'description' => 'Test model for generation limit visualization',
        ]);

        $letters = ['D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $nodeModels = [];

        foreach ($letters as $idx => $letter) {
            $nodeModels[$letter] = ModelNode::create([
                'commission_model_id' => $model->id,
                'name' => $letter,
                'parent_id' => $idx === 0 ? null : $nodeModels[$letters[$idx - 1]]->id,
                'node_type' => $idx === 0 ? 'Root Leader' : ($idx === count($letters) - 1 ? 'Salesperson' : 'Manager'),
                'personal_sale' => $letter === 'J' ? 1000.0 : 0.0,
            ]);
        }

        for ($i = 0; $i < count($letters) - 1; $i++) {
            ModelEdge::create([
                'commission_model_id' => $model->id,
                'parent_node_id' => $nodeModels[$letters[$i]]->id,
                'child_node_id' => $nodeModels[$letters[$i + 1]]->id,
                'override_percentage' => 2.0,
            ]);
        }

        $response = $this->get(route('override-models.show', $model));

        $response->assertStatus(200);
        $response->assertSee('Paid through generation 5');
        $response->assertSee('Generation 6+ excluded by model limit');
        $response->assertSee('paid');
        $response->assertSee('NOT PAID');
        $response->assertSee('The generation limit applies independently to every sale.');
        $response->assertSee('Do not interpret max_generations as maximum tree depth.');
    }
}
