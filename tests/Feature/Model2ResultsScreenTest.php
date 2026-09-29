<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use App\Services\OverrideCommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model2ResultsScreenTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the calculate endpoint returns the exact required summary metrics,
     * commission by person, and unmerged commission ledger per sale.
     */
    public function test_calculate_endpoint_returns_exact_model2_structure(): void
    {
        $payload = [
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'edges' => [
                ['parent' => 'A', 'child' => 'B', 'rate' => 2],
                ['parent' => 'A', 'child' => 'C', 'rate' => 5],
                ['parent' => 'B', 'child' => 'E', 'rate' => 3],
                ['parent' => 'B', 'child' => 'F', 'rate' => 5],
                ['parent' => 'E', 'child' => 'E1', 'rate' => 5],
            ],
            'sales' => [
                ['salesperson' => 'E1', 'amount' => 200],
                ['salesperson' => 'F', 'amount' => 400],
                ['salesperson' => 'C', 'amount' => 300],
            ],
        ];

        $response = $this->postJson(route('override-models.calculate'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    // 1. SUMMARY METRICS
                    'total_personal_sales' => 900,
                    'total_commission_generated' => 63,
                    'number_of_sales' => 3,
                    'number_of_commission_entries' => 6,
                    'max_generations' => 5,
                ],
            ]);

        $data = $response->json('data');

        // 2. COMMISSION BY PERSON
        // Expected ordering & metrics:
        // A:  Personal Sales: 0,   Commission Earned: 27, Overrides: 3
        // B:  Personal Sales: 0,   Commission Earned: 26, Overrides: 2
        // E:  Personal Sales: 0,   Commission Earned: 10, Overrides: 1
        // C:  Personal Sales: 300, Commission Earned: 0,  Overrides: 0
        // F:  Personal Sales: 400, Commission Earned: 0,  Overrides: 0
        // E1: Personal Sales: 200, Commission Earned: 0,  Overrides: 0
        $byPerson = $data['commission_by_person'];

        $this->assertEquals(0, $byPerson['A']['personal_sales']);
        $this->assertEquals(27, $byPerson['A']['total_commission']);
        $this->assertEquals(3, $byPerson['A']['commissions_received_count']);

        $this->assertEquals(0, $byPerson['B']['personal_sales']);
        $this->assertEquals(26, $byPerson['B']['total_commission']);
        $this->assertEquals(2, $byPerson['B']['commissions_received_count']);

        $this->assertEquals(0, $byPerson['E']['personal_sales']);
        $this->assertEquals(10, $byPerson['E']['total_commission']);
        $this->assertEquals(1, $byPerson['E']['commissions_received_count']);

        $this->assertEquals(300, $byPerson['C']['personal_sales']);
        $this->assertEquals(0, $byPerson['C']['total_commission']);
        $this->assertEquals(0, $byPerson['C']['commissions_received_count']);

        $this->assertEquals(400, $byPerson['F']['personal_sales']);
        $this->assertEquals(0, $byPerson['F']['total_commission']);
        $this->assertEquals(0, $byPerson['F']['commissions_received_count']);

        $this->assertEquals(200, $byPerson['E1']['personal_sales']);
        $this->assertEquals(0, $byPerson['E1']['total_commission']);
        $this->assertEquals(0, $byPerson['E1']['commissions_received_count']);

        // 3. COMMISSION LEDGER (Unmerged distinct sales entries)
        $bySale = $data['commission_by_sale'];
        $this->assertCount(3, $bySale, 'Sales must not be merged into one ledger entry');

        // Sale #1: E1, 200
        $this->assertEquals('E1', $bySale[0]['seller']);
        $this->assertEquals(200, $bySale[0]['amount']);
        $this->assertCount(3, $bySale[0]['commissions']);
        // Row 1: E1 | 200 | E | Gen 1 | 5% | 10
        $this->assertEquals('E', $bySale[0]['commissions'][0]['earner']);
        $this->assertEquals(1, $bySale[0]['commissions'][0]['generation']);
        $this->assertEquals(5, $bySale[0]['commissions'][0]['rate']);
        $this->assertEquals(10, $bySale[0]['commissions'][0]['commission']);
        $this->assertEquals(200, $bySale[0]['commissions'][0]['original_sale_amount']);
        // Row 2: E1 | 200 | B | Gen 2 | 3% | 6
        $this->assertEquals('B', $bySale[0]['commissions'][1]['earner']);
        $this->assertEquals(2, $bySale[0]['commissions'][1]['generation']);
        $this->assertEquals(3, $bySale[0]['commissions'][1]['rate']);
        $this->assertEquals(6, $bySale[0]['commissions'][1]['commission']);
        $this->assertEquals(200, $bySale[0]['commissions'][1]['original_sale_amount']);
        // Row 3: E1 | 200 | A | Gen 3 | 2% | 4
        $this->assertEquals('A', $bySale[0]['commissions'][2]['earner']);
        $this->assertEquals(3, $bySale[0]['commissions'][2]['generation']);
        $this->assertEquals(2, $bySale[0]['commissions'][2]['rate']);
        $this->assertEquals(4, $bySale[0]['commissions'][2]['commission']);
        $this->assertEquals(200, $bySale[0]['commissions'][2]['original_sale_amount']);

        // Sale #2: F, 400
        $this->assertEquals('F', $bySale[1]['seller']);
        $this->assertEquals(400, $bySale[1]['amount']);
        $this->assertCount(2, $bySale[1]['commissions']);
        // Row 1: F | 400 | B | Gen 1 | 5% | 20
        $this->assertEquals('B', $bySale[1]['commissions'][0]['earner']);
        $this->assertEquals(1, $bySale[1]['commissions'][0]['generation']);
        $this->assertEquals(5, $bySale[1]['commissions'][0]['rate']);
        $this->assertEquals(20, $bySale[1]['commissions'][0]['commission']);
        $this->assertEquals(400, $bySale[1]['commissions'][0]['original_sale_amount']);
        // Row 2: F | 400 | A | Gen 2 | 2% | 8
        $this->assertEquals('A', $bySale[1]['commissions'][1]['earner']);
        $this->assertEquals(2, $bySale[1]['commissions'][1]['generation']);
        $this->assertEquals(2, $bySale[1]['commissions'][1]['rate']);
        $this->assertEquals(8, $bySale[1]['commissions'][1]['commission']);
        $this->assertEquals(400, $bySale[1]['commissions'][1]['original_sale_amount']);

        // Sale #3: C, 300
        $this->assertEquals('C', $bySale[2]['seller']);
        $this->assertEquals(300, $bySale[2]['amount']);
        $this->assertCount(1, $bySale[2]['commissions']);
        // Row 1: C | 300 | A | Gen 1 | 5% | 15
        $this->assertEquals('A', $bySale[2]['commissions'][0]['earner']);
        $this->assertEquals(1, $bySale[2]['commissions'][0]['generation']);
        $this->assertEquals(5, $bySale[2]['commissions'][0]['rate']);
        $this->assertEquals(15, $bySale[2]['commissions'][0]['commission']);
        $this->assertEquals(300, $bySale[2]['commissions'][0]['original_sale_amount']);
    }

    /**
     * Test that the Model 2 show view renders all required sections and headings:
     * - SUMMARY
     * - COMMISSION BY PERSON
     * - COMMISSION LEDGER
     * - Original sale amount clarity
     * - Unmerged ledger entries
     */
    public function test_show_view_renders_all_required_sections_and_data(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Spec Tree Results Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 900.00,
            'final_commission' => 63.00,
        ]);

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5],
        ];

        $sales = [
            ['salesperson' => 'E1', 'amount' => 200],
            ['salesperson' => 'F', 'amount' => 400],
            ['salesperson' => 'C', 'amount' => 300],
        ];

        $calculator->persistModel($model, $edges, $sales, 5);

        $response = $this->get(route('override-models.show', $model));

        $response->assertStatus(200);

        // Section 1: SUMMARY
        $response->assertSee('SUMMARY');
        $response->assertSee('Total Personal Sales');
        $response->assertSee('Total Commission Generated');
        $response->assertSee('Number of Sales');
        $response->assertSee('Number of Commission Entries');
        $response->assertSee('Maximum Generations');
        $response->assertSee('₹900');
        $response->assertSee('₹63');

        // Section 2: COMMISSION BY PERSON
        $response->assertSee('COMMISSION BY PERSON');
        $response->assertSee('Personal Sales');
        $response->assertSee('Commission Earned');
        $response->assertSee('Number of Override Commissions');
        $response->assertSee('₹27'); // A
        $response->assertSee('₹26'); // B
        $response->assertSee('₹10'); // E

        // Section 3: COMMISSION LEDGER
        $response->assertSee('COMMISSION LEDGER');
        $response->assertSee('Sale Person');
        $response->assertSee('Sale Amount');
        $response->assertSee('Earner');
        $response->assertSee('Generation');
        $response->assertSee('Rate');
        $response->assertSee('Commission');

        // Clarity that commissions are generated from original sale amount
        $response->assertSee('Original Sale Amount');
        $response->assertSee('original sale amount', false);

        // Unmerged distinct sale entries
        $response->assertSee('Sale #1');
        $response->assertSee('Sale #2');
        $response->assertSee('Sale #3');
        $response->assertSee('Sale Person: <span class="font-mono text-emerald-800 font-bold">E1</span>', false);
        $response->assertSee('Sale Person: <span class="font-mono text-emerald-800 font-bold">F</span>', false);
        $response->assertSee('Sale Person: <span class="font-mono text-emerald-800 font-bold">C</span>', false);
    }

    /**
     * Test that unified create page and override create page include Model 2 results container.
     */
    public function test_create_pages_include_model2_results_view(): void
    {
        $resUnified = $this->get(route('models.create'));
        $resUnified->assertStatus(200);
        $resUnified->assertSee('model2-results-container');
        $resUnified->assertSee('renderModel2ResultsView');

        $resOverride = $this->get(route('override-models.create'));
        $resOverride->assertStatus(200);
        $resOverride->assertSee('model2-results-container');
        $resOverride->assertSee('renderModel2ResultsView');
    }

    /**
     * Test that the visual commission-flow diagram and tree cards are rendered correctly.
     */
    public function test_show_view_renders_visual_commission_flow_and_tree_hierarchy_cards(): void
    {
        $calculator = app(OverrideCommissionCalculator::class);

        $model = CommissionModel::create([
            'name' => 'Visual Flow Test',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 900.00,
            'final_commission' => 63.00,
        ]);

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5],
        ];

        $sales = [
            ['salesperson' => 'E1', 'amount' => 200],
            ['salesperson' => 'F', 'amount' => 400],
            ['salesperson' => 'C', 'amount' => 300],
        ];

        $calculator->persistModel($model, $edges, $sales, 5);

        $response = $this->get(route('override-models.show', $model));
        $response->assertStatus(200);

        // 1. VISUAL COMMISSION FLOW SECTION
        $response->assertSee('VISUAL COMMISSION FLOW');
        $response->assertSee('Same Original Sale Base Used Everywhere');
        $response->assertSee('Original Sale: ₹200');
        $response->assertSee('E: 5% = ₹10');
        $response->assertSee('B: 3% = ₹6');
        $response->assertSee('A: 2% = ₹4');

        $response->assertSee('Original Sale: ₹400');
        $response->assertSee('B: 5% = ₹20');
        $response->assertSee('A: 2% = ₹8');

        $response->assertSee('Original Sale: ₹300');
        $response->assertSee('A: 5% = ₹15');

        // Verify downward propagation indicators
        $response->assertSee('↓');
        $response->assertSee('Passes Upward');

        // Verify anti-pattern clarity
        $response->assertSee('₹200 → ₹10 → ₹6 → ₹4 (Incorrect)');

        // 2. HIERARCHICAL COMMISSION TREE SECTION
        $response->assertSee('HIERARCHICAL COMMISSION TREE');
        $response->assertSee('Leaf Node');
        $response->assertSee('₹200 sale');
        $response->assertSee('₹400 sale');
        $response->assertSee('₹300 sale');

        // Relationship override %
        $response->assertSee('2% Override');
        $response->assertSee('3% Override');
        $response->assertSee('5% Override');

        // ASCII Diagram
        $response->assertSee('├── B [2%]');
        $response->assertSee('└── C [5%]');
    }
}
