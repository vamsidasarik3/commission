<?php

namespace Tests\Unit;

use App\Services\OverrideCommissionCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class OverrideCommissionCalculatorTest extends TestCase
{
    private OverrideCommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new OverrideCommissionCalculator;
    }

    /**
     * Test exact user example tree from specification:
     * A
     * ├── B
     * │   ├── E
     * │   │   └── E1
     * │   └── F
     * └── C
     *
     * Rates:
     * A → B = 2%
     * A → C = 5%
     * B → E = 3%
     * B → F = 5%
     * E → E1 = 5%
     *
     * Sales:
     * C = ₹300
     * F = ₹400
     * E1 = ₹200
     *
     * Expected results:
     * For E1's ₹200 sale:
     * E earns ₹10 (5%)
     * B earns ₹6 (3%)
     * A earns ₹4 (2%)
     *
     * For F's ₹400 sale:
     * B earns ₹20 (5%)
     * A earns ₹8 (2%)
     *
     * For C's ₹300 sale:
     * A earns ₹15 (5%)
     *
     * Total earnings:
     * E = ₹10
     * B = ₹6 + ₹20 = ₹26
     * A = ₹4 + ₹8 + ₹15 = ₹27
     * Total commission = ₹63
     * Total sales = ₹900
     */
    public function test_user_example_tree_calculation(): void
    {
        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'C', 'amount' => 300.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'E1', 'amount' => 200.0],
        ];

        $result = $this->calculator->calculate($edges, $sales, 5);

        // Verify totals
        $this->assertEquals(900.00, $result['total_sales']);
        $this->assertEquals(63.00, $result['total_commission']);
        $this->assertEquals(63.00, $result['final_commission']);

        // Verify earnings by person
        $earnings = $result['earnings_by_person'];
        $this->assertEquals(27.00, $earnings['A']['override_commission']);
        $this->assertEquals(26.00, $earnings['B']['override_commission']);
        $this->assertEquals(10.00, $earnings['E']['override_commission']);

        // Personal sales recorded correctly
        $this->assertEquals(300.00, $earnings['C']['personal_sales']);
        $this->assertEquals(400.00, $earnings['F']['personal_sales']);
        $this->assertEquals(200.00, $earnings['E1']['personal_sales']);

        // Verify breakdown for E1's sale
        $e1Sale = collect($result['sales_breakdown'])->firstWhere('seller', 'E1');
        $this->assertNotNull($e1Sale);
        $this->assertEquals(200.00, $e1Sale['amount']);
        $this->assertEquals(20.00, $e1Sale['total_commission_paid']); // 10 + 6 + 4 = 20

        $e1Commissions = $e1Sale['commissions'];
        $this->assertCount(3, $e1Commissions);

        // Generation 1: E earns 10 (base 200, rate 5%)
        $this->assertEquals('E', $e1Commissions[0]['recipient']);
        $this->assertEquals(1, $e1Commissions[0]['generation']);
        $this->assertEquals(5.0, $e1Commissions[0]['rate']);
        $this->assertEquals(200.00, $e1Commissions[0]['original_sale_amount']);
        $this->assertEquals(10.00, $e1Commissions[0]['commission_amount']);
        $this->assertTrue($e1Commissions[0]['is_eligible']);

        // Generation 2: B earns 6 (base 200, rate 3%)
        $this->assertEquals('B', $e1Commissions[1]['recipient']);
        $this->assertEquals(2, $e1Commissions[1]['generation']);
        $this->assertEquals(3.0, $e1Commissions[1]['rate']);
        $this->assertEquals(200.00, $e1Commissions[1]['original_sale_amount']);
        $this->assertEquals(6.00, $e1Commissions[1]['commission_amount']);
        $this->assertTrue($e1Commissions[1]['is_eligible']);

        // Generation 3: A earns 4 (base 200, rate 2%)
        $this->assertEquals('A', $e1Commissions[2]['recipient']);
        $this->assertEquals(3, $e1Commissions[2]['generation']);
        $this->assertEquals(2.0, $e1Commissions[2]['rate']);
        $this->assertEquals(200.00, $e1Commissions[2]['original_sale_amount']);
        $this->assertEquals(4.00, $e1Commissions[2]['commission_amount']);
        $this->assertTrue($e1Commissions[2]['is_eligible']);

        // Verify breakdown for F's sale
        $fSale = collect($result['sales_breakdown'])->firstWhere('seller', 'F');
        $this->assertNotNull($fSale);
        $this->assertEquals(400.00, $fSale['amount']);
        $this->assertEquals(28.00, $fSale['total_commission_paid']); // 20 + 8 = 28
        $this->assertEquals('B', $fSale['commissions'][0]['recipient']);
        $this->assertEquals(20.00, $fSale['commissions'][0]['commission_amount']);
        $this->assertEquals('A', $fSale['commissions'][1]['recipient']);
        $this->assertEquals(8.00, $fSale['commissions'][1]['commission_amount']);

        // Verify breakdown for C's sale
        $cSale = collect($result['sales_breakdown'])->firstWhere('seller', 'C');
        $this->assertNotNull($cSale);
        $this->assertEquals(300.00, $cSale['amount']);
        $this->assertEquals(15.00, $cSale['total_commission_paid']);
        $this->assertEquals('A', $cSale['commissions'][0]['recipient']);
        $this->assertEquals(15.00, $cSale['commissions'][0]['commission_amount']);
    }

    /**
     * Test exact max generation depth specification:
     * J → I → H → G → F → E → D → C → B → A
     * If MAX_GENERATIONS = 5 and J makes a sale:
     * I = paid (Gen 1)
     * H = paid (Gen 2)
     * G = paid (Gen 3)
     * F = paid (Gen 4)
     * E = paid (Gen 5)
     * D and above = not paid for this sale.
     * The fifth generation is eligible.
     * The sixth generation is not.
     */
    public function test_max_generation_depth_limit(): void
    {
        // Chain: A -> B -> C -> D -> E -> F -> G -> H -> I -> J
        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 1.0],
            ['parent' => 'B', 'child' => 'C', 'rate' => 1.0],
            ['parent' => 'C', 'child' => 'D', 'rate' => 1.0],
            ['parent' => 'D', 'child' => 'E', 'rate' => 1.0],
            ['parent' => 'E', 'child' => 'F', 'rate' => 1.0],
            ['parent' => 'F', 'child' => 'G', 'rate' => 1.0],
            ['parent' => 'G', 'child' => 'H', 'rate' => 1.0],
            ['parent' => 'H', 'child' => 'I', 'rate' => 1.0],
            ['parent' => 'I', 'child' => 'J', 'rate' => 1.0],
        ];

        $sales = [
            ['salesperson' => 'J', 'amount' => 1000.00],
        ];

        // Default max generations = 5
        $result = $this->calculator->calculate($edges, $sales, 5);

        $jSale = $result['sales_breakdown'][0];
        $this->assertEquals(1000.00, $jSale['amount']);

        // 9 uplines in chain (I, H, G, F, E, D, C, B, A)
        $this->assertCount(9, $jSale['commissions']);

        // Gen 1: I (Paid)
        $this->assertEquals('I', $jSale['commissions'][0]['recipient']);
        $this->assertEquals(1, $jSale['commissions'][0]['generation']);
        $this->assertTrue($jSale['commissions'][0]['is_eligible']);
        $this->assertEquals(10.00, $jSale['commissions'][0]['commission_amount']);

        // Gen 2: H (Paid)
        $this->assertEquals('H', $jSale['commissions'][1]['recipient']);
        $this->assertEquals(2, $jSale['commissions'][1]['generation']);
        $this->assertTrue($jSale['commissions'][1]['is_eligible']);
        $this->assertEquals(10.00, $jSale['commissions'][1]['commission_amount']);

        // Gen 3: G (Paid)
        $this->assertEquals('G', $jSale['commissions'][2]['recipient']);
        $this->assertEquals(3, $jSale['commissions'][2]['generation']);
        $this->assertTrue($jSale['commissions'][2]['is_eligible']);
        $this->assertEquals(10.00, $jSale['commissions'][2]['commission_amount']);

        // Gen 4: F (Paid)
        $this->assertEquals('F', $jSale['commissions'][3]['recipient']);
        $this->assertEquals(4, $jSale['commissions'][3]['generation']);
        $this->assertTrue($jSale['commissions'][3]['is_eligible']);
        $this->assertEquals(10.00, $jSale['commissions'][3]['commission_amount']);

        // Gen 5: E (Paid)
        $this->assertEquals('E', $jSale['commissions'][4]['recipient']);
        $this->assertEquals(5, $jSale['commissions'][4]['generation']);
        $this->assertTrue($jSale['commissions'][4]['is_eligible']);
        $this->assertEquals(10.00, $jSale['commissions'][4]['commission_amount']);

        // Gen 6: D (NOT paid - exceeds max generations)
        $this->assertEquals('D', $jSale['commissions'][5]['recipient']);
        $this->assertEquals(6, $jSale['commissions'][5]['generation']);
        $this->assertFalse($jSale['commissions'][5]['is_eligible']);
        $this->assertEquals(0.00, $jSale['commissions'][5]['commission_amount']);

        // Gen 7: C (NOT paid)
        $this->assertEquals('C', $jSale['commissions'][6]['recipient']);
        $this->assertFalse($jSale['commissions'][6]['is_eligible']);
        $this->assertEquals(0.00, $jSale['commissions'][6]['commission_amount']);

        // Gen 8: B (NOT paid)
        $this->assertEquals('B', $jSale['commissions'][7]['recipient']);
        $this->assertFalse($jSale['commissions'][7]['is_eligible']);

        // Gen 9: A (NOT paid)
        $this->assertEquals('A', $jSale['commissions'][8]['recipient']);
        $this->assertFalse($jSale['commissions'][8]['is_eligible']);

        // Total commission paid is 5 * 10 = 50.00
        $this->assertEquals(50.00, $result['total_commission']);
        $this->assertEquals(50.00, $jSale['total_commission_paid']);

        // D, C, B, A earned 0.00
        $this->assertEquals(0.00, $result['earnings_by_person']['D']['override_commission']);
        $this->assertEquals(0.00, $result['earnings_by_person']['C']['override_commission']);
        $this->assertEquals(0.00, $result['earnings_by_person']['B']['override_commission']);
        $this->assertEquals(0.00, $result['earnings_by_person']['A']['override_commission']);
    }

    /**
     * Test rates are configurable per parent-child relationship (edge), not globally fixed.
     */
    public function test_different_rates_per_relationship(): void
    {
        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0], // 2%
            ['parent' => 'A', 'child' => 'C', 'rate' => 5.0], // 5%
        ];

        $sales = [
            ['salesperson' => 'B', 'amount' => 1000.0],
            ['salesperson' => 'C', 'amount' => 1000.0],
        ];

        $result = $this->calculator->calculate($edges, $sales);

        // A earns 2% of B's 1000 (20) + 5% of C's 1000 (50) = 70
        $this->assertEquals(70.00, $result['earnings_by_person']['A']['override_commission']);
    }

    /**
     * Test original sale amount remains constant as base (never reduced after each commission).
     */
    public function test_original_base_amount_not_reduced(): void
    {
        $edges = [
            ['parent' => 'Root', 'child' => 'Mid', 'rate' => 10.0],
            ['parent' => 'Mid', 'child' => 'Seller', 'rate' => 10.0],
        ];

        $sales = [
            ['salesperson' => 'Seller', 'amount' => 500.00],
        ];

        $result = $this->calculator->calculate($edges, $sales);

        // Mid earns 10% of 500 = 50
        // Root earns 10% of 500 = 50 (NOT 10% of (500 - 50) = 45)
        $this->assertEquals(50.00, $result['earnings_by_person']['Mid']['override_commission']);
        $this->assertEquals(50.00, $result['earnings_by_person']['Root']['override_commission']);
        $this->assertEquals(100.00, $result['total_commission']);
    }

    /**
     * Test tree validation catches cycle.
     */
    public function test_cycle_detection_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cycle detected');

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 5.0],
            ['parent' => 'B', 'child' => 'C', 'rate' => 5.0],
            ['parent' => 'C', 'child' => 'A', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'C', 'amount' => 100.0],
        ];

        $this->calculator->calculate($edges, $sales);
    }

    /**
     * Test tree validation catches multiple parents for a single child.
     */
    public function test_multi_parent_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('has multiple distinct parents');

        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 5.0],
            ['parent' => 'C', 'child' => 'B', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'B', 'amount' => 100.0],
        ];

        $this->calculator->calculate($edges, $sales);
    }

    /**
     * Test exact requested return format and breakdowns:
     * - total_personal_sales
     * - total_commission_generated
     * - commission_by_person (B total = 26, A total = 27)
     * - commission_by_sale
     * - commission_ledger with seller, earner, generation, original_sale_amount, rate, commission
     * - generation_breakdown
     * - tree_summary
     */
    public function test_exact_specification_return_format_and_breakdowns(): void
    {
        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'C', 'amount' => 300.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'E1', 'amount' => 200.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        // 1. Verify top-level totals
        $this->assertEquals(900.00, $result['total_personal_sales']);
        $this->assertEquals(63.00, $result['total_commission_generated']);

        // 2. Verify commission_by_person:
        // B: E1 (200 * 3% = 6) + F (400 * 5% = 20) => B total = 26
        // A: E1 (200 * 2% = 4) + F (400 * 2% = 8) + C (300 * 5% = 15) => A total = 27
        $byPerson = $result['commission_by_person'];
        $this->assertArrayHasKey('B', $byPerson);
        $this->assertEquals(26.00, $byPerson['B']['total_commission']);
        $this->assertCount(2, $byPerson['B']['breakdown']);

        $this->assertArrayHasKey('A', $byPerson);
        $this->assertEquals(27.00, $byPerson['A']['total_commission']);
        $this->assertCount(3, $byPerson['A']['breakdown']);

        $this->assertArrayHasKey('E', $byPerson);
        $this->assertEquals(10.00, $byPerson['E']['total_commission']);
        $this->assertCount(1, $byPerson['E']['breakdown']);

        // 3. Verify commission_ledger entry fields
        $ledger = $result['commission_ledger'];
        $this->assertNotEmpty($ledger);

        foreach ($ledger as $entry) {
            $this->assertArrayHasKey('seller', $entry);
            $this->assertArrayHasKey('earner', $entry);
            $this->assertArrayHasKey('generation', $entry);
            $this->assertArrayHasKey('original_sale_amount', $entry);
            $this->assertArrayHasKey('rate', $entry);
            $this->assertArrayHasKey('commission', $entry);
        }

        // Check specific entry: E1 sale -> earner E (Gen 1, rate 5%, commission 10)
        $entryE1_E = collect($ledger)->first(fn ($e) => $e['seller'] === 'E1' && $e['earner'] === 'E');
        $this->assertNotNull($entryE1_E);
        $this->assertEquals(1, $entryE1_E['generation']);
        $this->assertEquals(200.00, $entryE1_E['original_sale_amount']);
        $this->assertEquals(5.0, $entryE1_E['rate']);
        $this->assertEquals(10.00, $entryE1_E['commission']);

        // Check specific entry: E1 sale -> earner B (Gen 2, rate 3%, commission 6)
        $entryE1_B = collect($ledger)->first(fn ($e) => $e['seller'] === 'E1' && $e['earner'] === 'B');
        $this->assertNotNull($entryE1_B);
        $this->assertEquals(2, $entryE1_B['generation']);
        $this->assertEquals(200.00, $entryE1_B['original_sale_amount']);
        $this->assertEquals(3.0, $entryE1_B['rate']);
        $this->assertEquals(6.00, $entryE1_B['commission']);

        // Check specific entry: E1 sale -> earner A (Gen 3, rate 2%, commission 4)
        $entryE1_A = collect($ledger)->first(fn ($e) => $e['seller'] === 'E1' && $e['earner'] === 'A');
        $this->assertNotNull($entryE1_A);
        $this->assertEquals(3, $entryE1_A['generation']);
        $this->assertEquals(200.00, $entryE1_A['original_sale_amount']);
        $this->assertEquals(2.0, $entryE1_A['rate']);
        $this->assertEquals(4.00, $entryE1_A['commission']);

        // 4. Verify commission_by_sale
        $bySale = $result['commission_by_sale'];
        $this->assertCount(3, $bySale);
        $saleE1 = collect($bySale)->firstWhere('seller', 'E1');
        $this->assertNotNull($saleE1);
        $this->assertEquals(200.00, $saleE1['original_sale_amount']);
        $this->assertEquals(20.00, $saleE1['total_commission_paid']); // 10 + 6 + 4

        // 5. Verify generation_breakdown
        $genBreakdown = $result['generation_breakdown'];
        $this->assertArrayHasKey(1, $genBreakdown);
        // Gen 1 payouts: E from E1 (10) + B from F (20) + A from C (15) = 45
        $this->assertEquals(45.00, $genBreakdown[1]['total_commission']);
        // Gen 2 payouts: B from E1 (6) + A from F (8) = 14
        $this->assertEquals(14.00, $genBreakdown[2]['total_commission']);
        // Gen 3 payouts: A from E1 (4) = 4
        $this->assertEquals(4.00, $genBreakdown[3]['total_commission']);

        // 6. Verify tree_summary
        $summary = $result['tree_summary'];
        $this->assertEquals(6, $summary['total_nodes']); // A, B, C, E, E1, F
        $this->assertEquals(5, $summary['total_edges']);
        $this->assertEquals(['A'], $summary['root_nodes']);
        $this->assertEquals(4, $summary['max_depth']); // A -> B -> E -> E1
    }
}
