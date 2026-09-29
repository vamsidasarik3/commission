<?php

namespace Tests\Unit;

use App\Services\OverrideCommissionCalculator;
use PHPUnit\Framework\TestCase;

class Model2SpecificationArithmeticTest extends TestCase
{
    private OverrideCommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new OverrideCommissionCalculator;
    }

    /**
     * Common exact tree from specification:
     * A
     * ├── B (2%)
     * │   ├── E (3%)
     * │   │   └── E1 (5%)
     * │   └── F (5%)
     * └── C (5%)
     */
    private function getSpecificationTreeEdges(): array
    {
        return [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
            ['parent' => 'A', 'child' => 'C', 'rate' => 5.0],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
            ['parent' => 'B', 'child' => 'F', 'rate' => 5.0],
            ['parent' => 'E', 'child' => 'E1', 'rate' => 5.0],
        ];
    }

    /**
     * Exact specification test:
     * E1 = ₹200, F = ₹400, C = ₹300
     * Max generations = 5
     *
     * Expected results:
     * Sale E1 = ₹200: E (₹10), B (₹6), A (₹4)
     * Sale F = ₹400:  B (₹20), A (₹8)
     * Sale C = ₹300:  A (₹15)
     *
     * Totals: E = ₹10, B = ₹26, A = ₹27
     * Total commissions generated: ₹63
     * Total personal sales: ₹900
     */
    public function test_exact_specification_tree_and_arithmetic(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'C', 'amount' => 300.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        // 1. Verify totals
        $this->assertSame(900.00, $result['total_personal_sales']);
        $this->assertSame(63.00, $result['total_commission_generated']);

        // 2. Verify sale by sale breakdown
        $salesByPerson = collect($result['commission_by_sale'])->keyBy('seller');

        // Sale E1: ₹200
        $e1Sale = $salesByPerson['E1'];
        $this->assertEquals(200.00, $e1Sale['original_sale_amount']);
        $this->assertEquals(20.00, $e1Sale['total_commission_paid']); // 10 + 6 + 4
        $e1Overrides = collect($e1Sale['commissions'])->keyBy('earner');
        $this->assertEquals(10.00, $e1Overrides['E']['commission']); // 200 * 5% = 10
        $this->assertEquals(6.00, $e1Overrides['B']['commission']);  // 200 * 3% = 6
        $this->assertEquals(4.00, $e1Overrides['A']['commission']);  // 200 * 2% = 4

        // Sale F: ₹400
        $fSale = $salesByPerson['F'];
        $this->assertEquals(400.00, $fSale['original_sale_amount']);
        $this->assertEquals(28.00, $fSale['total_commission_paid']); // 20 + 8
        $fOverrides = collect($fSale['commissions'])->keyBy('earner');
        $this->assertEquals(20.00, $fOverrides['B']['commission']); // 400 * 5% = 20
        $this->assertEquals(8.00, $fOverrides['A']['commission']);  // 400 * 2% = 8

        // Sale C: ₹300
        $cSale = $salesByPerson['C'];
        $this->assertEquals(300.00, $cSale['original_sale_amount']);
        $this->assertEquals(15.00, $cSale['total_commission_paid']); // 15
        $cOverrides = collect($cSale['commissions'])->keyBy('earner');
        $this->assertEquals(15.00, $cOverrides['A']['commission']); // 300 * 5% = 15

        // 3. Verify totals by earner
        $earners = $result['commission_by_person'];
        $this->assertEquals(10.00, $earners['E']['total_commission']);
        $this->assertEquals(26.00, $earners['B']['total_commission']); // 6 + 20
        $this->assertEquals(27.00, $earners['A']['total_commission']); // 4 + 8 + 15
    }

    /**
     * 1. Test Maximum Generation = 1
     * Only generation 1 is paid. Higher generations receive 0.
     * E1 (₹200) -> E (Gen 1): ₹10 paid. B (Gen 2) & A (Gen 3) not paid.
     * F (₹400)  -> B (Gen 1): ₹20 paid. A (Gen 2) not paid.
     * C (₹300)  -> A (Gen 1): ₹15 paid.
     * Total = 10 + 20 + 15 = ₹45.
     */
    public function test_maximum_generation_equals_one(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'C', 'amount' => 300.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 1);

        $this->assertEquals(900.00, $result['total_personal_sales']);
        $this->assertEquals(45.00, $result['total_commission_generated']);

        $earners = $result['commission_by_person'];
        $this->assertEquals(10.00, $earners['E']['total_commission']);
        $this->assertEquals(20.00, $earners['B']['total_commission']);
        $this->assertEquals(15.00, $earners['A']['total_commission']);

        // Check ledger for E1 sale
        $ledger = collect($result['commission_ledger'])->where('seller', 'E1');
        $eEntry = $ledger->firstWhere('earner', 'E');
        $bEntry = $ledger->firstWhere('earner', 'B');
        $aEntry = $ledger->firstWhere('earner', 'A');

        $this->assertTrue($eEntry['is_eligible']);
        $this->assertEquals(10.00, $eEntry['commission']);

        $this->assertFalse($bEntry['is_eligible']);
        $this->assertEquals(0.00, $bEntry['commission']);

        $this->assertFalse($aEntry['is_eligible']);
        $this->assertEquals(0.00, $aEntry['commission']);
    }

    /**
     * 2. Test Maximum Generation = 2
     * Generations 1 and 2 are paid; Generation 3 is not paid.
     * E1 (₹200) -> E (Gen 1): ₹10, B (Gen 2): ₹6. A (Gen 3): not paid (₹0).
     * F (₹400)  -> B (Gen 1): ₹20, A (Gen 2): ₹8.
     * C (₹300)  -> A (Gen 1): ₹15.
     *
     * Total = (10 + 6) + (20 + 8) + 15 = 16 + 28 + 15 = ₹59.
     * E = ₹10, B = ₹26 (6 + 20), A = ₹23 (8 + 15).
     */
    public function test_maximum_generation_equals_two(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'C', 'amount' => 300.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 2);

        $this->assertEquals(900.00, $result['total_personal_sales']);
        $this->assertEquals(59.00, $result['total_commission_generated']);

        $earners = $result['commission_by_person'];
        $this->assertEquals(10.00, $earners['E']['total_commission']);
        $this->assertEquals(26.00, $earners['B']['total_commission']);
        $this->assertEquals(23.00, $earners['A']['total_commission']);
    }

    /**
     * 3. Test Maximum Generation = 5
     * With 5 max generations, all uplines in this 3-level tree are eligible and paid.
     */
    public function test_maximum_generation_equals_five(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'F', 'amount' => 400.0],
            ['salesperson' => 'C', 'amount' => 300.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(63.00, $result['total_commission_generated']);
        $this->assertEquals(27.00, $result['commission_by_person']['A']['total_commission']);
        $this->assertEquals(26.00, $result['commission_by_person']['B']['total_commission']);
        $this->assertEquals(10.00, $result['commission_by_person']['E']['total_commission']);
    }

    /**
     * 4. Test Missing Parent (Root salesperson or person without upline)
     * A makes a sale of ₹500. A has no parent in the tree.
     * Personal sales = ₹500, but commission generated = ₹0.
     */
    public function test_missing_parent_generates_zero_commission(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'A', 'amount' => 500.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(500.00, $result['total_personal_sales']);
        $this->assertEquals(0.00, $result['total_commission_generated']);
        $this->assertEmpty($result['commission_ledger']);
        $this->assertEquals(500.00, $result['commission_by_person']['A']['personal_sales']);
        $this->assertEquals(0.00, $result['commission_by_person']['A']['total_commission']);
    }

    /**
     * 5. Test Zero Sale (₹0.00 sale amount)
     * Personal sale of 0 generates exactly 0 commission.
     */
    public function test_zero_sale_amount(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 0.00],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(0.00, $result['total_personal_sales']);
        $this->assertEquals(0.00, $result['total_commission_generated']);
        $this->assertEquals(0.00, $result['commission_by_person']['E']['total_commission']);
        $this->assertEquals(0.00, $result['commission_by_person']['B']['total_commission']);
        $this->assertEquals(0.00, $result['commission_by_person']['A']['total_commission']);
    }

    /**
     * 6. Test Multiple Sales From Same Person
     * E1 makes two distinct sales: ₹200 and ₹150.
     * Sale 1 (₹200): E earns ₹10, B earns ₹6, A earns ₹4 (total ₹20)
     * Sale 2 (₹150): E earns ₹7.50, B earns ₹4.50, A earns ₹3.00 (total ₹15)
     * Totals:
     * E = 10 + 7.50 = ₹17.50
     * B = 6 + 4.50 = ₹10.50
     * A = 4 + 3.00 = ₹7.00
     * Total commission: ₹35.00
     * Total personal sales: ₹350.00
     */
    public function test_multiple_sales_from_same_person(): void
    {
        $edges = $this->getSpecificationTreeEdges();
        $sales = [
            ['salesperson' => 'E1', 'amount' => 200.0],
            ['salesperson' => 'E1', 'amount' => 150.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(350.00, $result['total_personal_sales']);
        $this->assertEquals(35.00, $result['total_commission_generated']);

        $earners = $result['commission_by_person'];
        $this->assertEquals(17.50, $earners['E']['total_commission']);
        $this->assertEquals(10.50, $earners['B']['total_commission']);
        $this->assertEquals(7.00, $earners['A']['total_commission']);
        $this->assertEquals(350.00, $earners['E1']['personal_sales']);
        $this->assertEquals(2, $earners['E1']['sales_count']);
    }

    /**
     * 7. Test Different Rates on Different Edges
     * Under the same parent (B):
     * B → E = 3%
     * B → F = 7.5%
     * Under the same parent (A):
     * A → B = 2%
     * A → C = 6.25%
     */
    public function test_different_rates_on_different_edges(): void
    {
        $edges = [
            ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
            ['parent' => 'A', 'child' => 'C', 'rate' => 6.25],
            ['parent' => 'B', 'child' => 'E', 'rate' => 3.0],
            ['parent' => 'B', 'child' => 'F', 'rate' => 7.5],
        ];

        $sales = [
            ['salesperson' => 'E', 'amount' => 1000.0],
            ['salesperson' => 'F', 'amount' => 1000.0],
            ['salesperson' => 'C', 'amount' => 1000.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        // For E's 1000 sale: B earns 3% = 30, A earns 2% = 20
        // For F's 1000 sale: B earns 7.5% = 75, A earns 2% = 20
        // For C's 1000 sale: A earns 6.25% = 62.50
        // B total = 30 + 75 = 105.00
        // A total = 20 + 20 + 62.50 = 102.50
        $this->assertEquals(105.00, $result['commission_by_person']['B']['total_commission']);
        $this->assertEquals(102.50, $result['commission_by_person']['A']['total_commission']);
        $this->assertEquals(207.50, $result['total_commission_generated']);
    }

    /**
     * 8. Test 10+ Level Tree (12 levels deep) with 5-generation cutoff
     * Chain: L12 → L11 → L10 → L9 → L8 → L7 → L6 → L5 → L4 → L3 → L2 → L1
     * Each edge rate = 5.0%
     * L12 makes sale of ₹1,000.
     * Max generations = 5:
     * - Gen 1: L11 earns ₹1,000 × 5% = ₹50
     * - Gen 2: L10 earns ₹1,000 × 5% = ₹50
     * - Gen 3: L9  earns ₹1,000 × 5% = ₹50
     * - Gen 4: L8  earns ₹1,000 × 5% = ₹50
     * - Gen 5: L7  earns ₹1,000 × 5% = ₹50
     * - Gen 6: L6  receives ₹0 (cutoff exceeded)
     * - Gen 7..11: L5..L1 receive ₹0
     *
     * Total paid: ₹250.00
     */
    public function test_ten_plus_level_tree_with_cutoff(): void
    {
        $edges = [
            ['parent' => 'L1', 'child' => 'L2', 'rate' => 5.0],
            ['parent' => 'L2', 'child' => 'L3', 'rate' => 5.0],
            ['parent' => 'L3', 'child' => 'L4', 'rate' => 5.0],
            ['parent' => 'L4', 'child' => 'L5', 'rate' => 5.0],
            ['parent' => 'L5', 'child' => 'L6', 'rate' => 5.0],
            ['parent' => 'L6', 'child' => 'L7', 'rate' => 5.0],
            ['parent' => 'L7', 'child' => 'L8', 'rate' => 5.0],
            ['parent' => 'L8', 'child' => 'L9', 'rate' => 5.0],
            ['parent' => 'L9', 'child' => 'L10', 'rate' => 5.0],
            ['parent' => 'L10', 'child' => 'L11', 'rate' => 5.0],
            ['parent' => 'L11', 'child' => 'L12', 'rate' => 5.0],
        ];

        $sales = [
            ['salesperson' => 'L12', 'amount' => 1000.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(1000.00, $result['total_personal_sales']);
        $this->assertEquals(250.00, $result['total_commission_generated']);

        // Check individual earners
        $earners = $result['commission_by_person'];
        $this->assertEquals(50.00, $earners['L11']['total_commission']); // Gen 1
        $this->assertEquals(50.00, $earners['L10']['total_commission']); // Gen 2
        $this->assertEquals(50.00, $earners['L9']['total_commission']);  // Gen 3
        $this->assertEquals(50.00, $earners['L8']['total_commission']);  // Gen 4
        $this->assertEquals(50.00, $earners['L7']['total_commission']);  // Gen 5

        // Beyond generation 5 are not paid
        $this->assertEquals(0.00, $earners['L6']['total_commission']);
        $this->assertEquals(0.00, $earners['L5']['total_commission']);
        $this->assertEquals(0.00, $earners['L1']['total_commission']);

        // Verify tree summary depth
        $this->assertEquals(12, $result['tree_summary']['total_nodes']);
        $this->assertEquals(12, $result['tree_summary']['max_depth']);
    }

    /**
     * 9. Test Decimal Sales
     * Sale = ₹249.75, Rate = 4.0%
     * ₹249.75 × 4% = ₹9.99
     *
     * Sale = ₹125.50, Rate = 10.0%
     * ₹125.50 × 10% = ₹12.55
     */
    public function test_decimal_sale_amounts(): void
    {
        $edges = [
            ['parent' => 'Parent', 'child' => 'Child', 'rate' => 4.0],
        ];

        $sales = [
            ['salesperson' => 'Child', 'amount' => 249.75],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        $this->assertEquals(249.75, $result['total_personal_sales']);
        $this->assertEquals(9.99, $result['total_commission_generated']);
        $this->assertEquals(9.99, $result['commission_by_person']['Parent']['total_commission']);
    }

    /**
     * 10. Test Decimal Rates
     * Fractional rates like 2.75%, 3.125%, 0.5%
     * Sale = ₹10,000 × 2.75% = ₹275.00
     * Sale = ₹1,000 × 3.125% = ₹31.25
     * Sale = ₹500 × 0.5% = ₹2.50
     */
    public function test_decimal_override_rates(): void
    {
        $edges = [
            ['parent' => 'Top', 'child' => 'Middle', 'rate' => 2.75],
            ['parent' => 'Middle', 'child' => 'Agent', 'rate' => 3.125],
        ];

        $sales = [
            ['salesperson' => 'Agent', 'amount' => 1000.0],
        ];

        $result = $this->calculator->compute(null, $edges, $sales, 5);

        // Agent sale = ₹1,000
        // Middle earns: ₹1,000 × 3.125% = ₹31.25 (Gen 1)
        // Top earns:    ₹1,000 × 2.75%  = ₹27.50 (Gen 2)
        // Total = 31.25 + 27.50 = ₹58.75
        $this->assertEquals(1000.00, $result['total_personal_sales']);
        $this->assertEquals(58.75, $result['total_commission_generated']);
        $this->assertEquals(31.25, $result['commission_by_person']['Middle']['total_commission']);
        $this->assertEquals(27.50, $result['commission_by_person']['Top']['total_commission']);
    }
}
