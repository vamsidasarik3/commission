<?php

namespace Tests\Unit;

use App\Services\CommissionCalculator;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private CommissionCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CommissionCalculator;
    }

    /**
     * Test 1 Level setup: Leader A, Main B, Side S1.
     */
    public function test_single_level_calculation(): void
    {
        $rate = 5.0; // 5%
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'main_sales' => 100.00,
                'side_person' => 'S1',
                'side_sales' => 200.00,
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals(1, $result['number_of_levels']);
        $this->assertEquals(300.00, $result['total_sales']);
        $this->assertEquals(15.00, $result['total_potential_commission']);
        // B commission = 5.00, S1 commission = 10.00
        // min(5.00, 10.00) = 5.00
        $this->assertEquals(5.00, $result['final_commission']);
        $this->assertEquals('B', $result['weakest_person']);
        $this->assertEquals(100.00, $result['weakest_sales']);
        $this->assertEquals(5.00, $result['weakest_commission']);

        $this->assertCount(1, $result['levels']);
        $this->assertEquals(5.00, $result['levels'][0]['main_commission']);
        $this->assertEquals(10.00, $result['levels'][0]['side_commission']);
        $this->assertEquals(5.00, $result['levels'][0]['selected_minimum']);
        $this->assertEquals(5.00, $result['levels'][0]['resulting_leader_commission']);
    }

    /**
     * Test 2 Levels setup:
     * A
     * ├── S1
     * └── B
     *     ├── S2
     *     └── C
     */
    public function test_two_levels_calculation(): void
    {
        $rate = 10.0; // 10%
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'side_person' => 'S1',
                'side_sales' => 500.00, // S1 comm = 50.00
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 150.00, // C comm = 15.00
                'side_person' => 'S2',
                'side_sales' => 300.00, // S2 comm = 30.00
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        // Level 2: min(C: 15.00, S2: 30.00) => Leader B = 15.00
        // Level 1: min(B: 15.00, S1: 50.00) => Leader A = 15.00
        $this->assertEquals(2, $result['number_of_levels']);
        $this->assertEquals(950.00, $result['total_sales']);
        $this->assertEquals(15.00, $result['final_commission']);
        $this->assertEquals('C', $result['weakest_person']);
        $this->assertEquals(150.00, $result['weakest_sales']);
        $this->assertEquals(15.00, $result['weakest_commission']);

        $this->assertEquals(15.00, $result['levels'][1]['resulting_leader_commission']);
        $this->assertEquals(15.00, $result['levels'][0]['main_commission']);
        $this->assertEquals(15.00, $result['levels'][0]['selected_minimum']);
    }

    /**
     * Test exact user example with 9-10 levels (A..J):
     * J sales = 40, rate = 5%, J commission = 2.
     * All side commissions = 10 (sales 200).
     * Final commission for A = 2.
     */
    public function test_ten_levels_user_example(): void
    {
        $rate = 5.0; // 5%
        // Build 9-level chain where J is leaf at the bottom
        $levels = $this->calculator->buildChain(
            numberOfLevels: 9,
            sideSales: 200.00, // 200 * 5% = 10.00 for each S1..S9
            bottomMainSales: 40.00 // 40 * 5% = 2.00 for J
        );

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals(9, $result['number_of_levels']);
        // Final commission for A must be exactly 2.00
        $this->assertEquals(2.00, $result['final_commission']);
        $this->assertEquals('J', $result['weakest_person']);
        $this->assertEquals(40.00, $result['weakest_sales']);
        $this->assertEquals(2.00, $result['weakest_commission']);

        // Check each level upward: min is 2.00 at every level
        foreach ($result['levels'] as $lvl) {
            $this->assertEquals(2.00, $lvl['selected_minimum']);
            $this->assertEquals(2.00, $lvl['resulting_leader_commission']);
            $this->assertEquals(10.00, $lvl['side_commission']);
        }

        // Total sales = 9 * 200 + 40 = 1840.00
        $this->assertEquals(1840.00, $result['total_sales']);
    }

    /**
     * Test 20 levels.
     */
    public function test_twenty_levels_calculation(): void
    {
        $rate = 8.0;
        // 20 levels, side sales = 500, bottom sales = 75
        $levels = $this->calculator->buildChain(
            numberOfLevels: 20,
            sideSales: 500.00,
            bottomMainSales: 75.00
        );

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals(20, $result['number_of_levels']);
        // Bottom commission = 75 * 0.08 = 6.00
        // Side commission = 500 * 0.08 = 40.00
        $this->assertEquals(6.00, $result['final_commission']);
        $this->assertEquals(6.00, $result['weakest_commission']);
        $this->assertEquals(75.00, $result['weakest_sales']);
        $this->assertCount(20, $result['levels']);
    }

    /**
     * Test 50 and 100 levels to verify arbitrary depth support.
     */
    public function test_arbitrary_depth_fifty_and_hundred_levels(): void
    {
        $rate = 6.0;

        // 50 levels
        $levels50 = $this->calculator->buildChain(50, 1000.00, 250.00);
        $res50 = $this->calculator->calculate($rate, $levels50);
        $this->assertEquals(50, $res50['number_of_levels']);
        $this->assertEquals(15.00, $res50['final_commission']); // 250 * 0.06 = 15.00

        // 100 levels
        $levels100 = $this->calculator->buildChain(100, 500.00, 100.00);
        $res100 = $this->calculator->calculate($rate, $levels100);
        $this->assertEquals(100, $res100['number_of_levels']);
        $this->assertEquals(6.00, $res100['final_commission']); // 100 * 0.06 = 6.00
    }

    /**
     * Test zero sales scenario.
     */
    public function test_zero_sales(): void
    {
        $rate = 10.0;
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'side_person' => 'S1',
                'side_sales' => 1000.00,
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 0.00, // zero sales on bottom person
                'side_person' => 'S2',
                'side_sales' => 500.00,
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        // C has 0 sales => 0 commission => MIN bottleneck pulls entire chain to 0
        $this->assertEquals(0.00, $result['final_commission']);
        $this->assertEquals('C', $result['weakest_person']);
        $this->assertEquals(0.00, $result['weakest_sales']);
        $this->assertEquals(0.00, $result['weakest_commission']);
        $this->assertEquals(1500.00, $result['total_sales']);
    }

    /**
     * Test equal commissions across all salespeople.
     */
    public function test_equal_commissions(): void
    {
        $rate = 5.0;
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'side_person' => 'S1',
                'side_sales' => 200.00,
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 200.00,
                'side_person' => 'S2',
                'side_sales' => 200.00,
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        // All have 200 sales => 10.00 commission
        $this->assertEquals(10.00, $result['final_commission']);
        $this->assertEquals(10.00, $result['weakest_commission']);
        $this->assertEquals(200.00, $result['weakest_sales']);
        $this->assertEquals(10.00, $result['levels'][0]['selected_minimum']);
        $this->assertEquals(10.00, $result['levels'][1]['selected_minimum']);
    }

    /**
     * Test decimal sales with cents precision.
     */
    public function test_decimal_sales(): void
    {
        $rate = 7.5; // 7.5%
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'side_person' => 'S1',
                'side_sales' => 1234.56, // 1234.56 * 0.075 = 92.592 => 92.59
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 456.78, // 456.78 * 0.075 = 34.2585 => 34.26
                'side_person' => 'S2',
                'side_sales' => 987.65, // 987.65 * 0.075 = 74.07375 => 74.07
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals(2678.99, $result['total_sales']);
        $this->assertEquals(34.26, $result['final_commission']);
        $this->assertEquals('C', $result['weakest_person']);
        $this->assertEquals(456.78, $result['weakest_sales']);
        $this->assertEquals(34.26, $result['weakest_commission']);
    }

    /**
     * Test decimal commission rates (e.g. 3.3333% or 8.456%).
     */
    public function test_decimal_commission_rates(): void
    {
        $rate = 3.3333;
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'main_sales' => 3000.00, // 3000 * 0.033333 = 100.00
                'side_person' => 'S1',
                'side_sales' => 6000.00, // 6000 * 0.033333 = 200.00
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals(3.3333, $result['commission_rate']);
        $this->assertEquals(100.00, $result['final_commission']);
        $this->assertEquals('B', $result['weakest_person']);
    }

    /**
     * Test weakest bottom-level salesperson.
     */
    public function test_weakest_bottom_level_salesperson(): void
    {
        $rate = 10.0;
        // 5 levels where sides have 500 sales each (50 comm), bottom main has 50 sales (5 comm)
        $levels = $this->calculator->buildChain(5, 500.00, 50.00);

        $result = $this->calculator->calculate($rate, $levels);

        $this->assertEquals('F', $result['weakest_person']);
        $this->assertEquals(50.00, $result['weakest_sales']);
        $this->assertEquals(5.00, $result['weakest_commission']);
        $this->assertEquals(5.00, $result['final_commission']);
    }

    /**
     * Test weakest side salesperson (side salesperson is the bottleneck).
     */
    public function test_weakest_side_salesperson(): void
    {
        $rate = 10.0;
        // 4 levels:
        // S1 = 500 (50), S2 = 500 (50), S3 = 30 (3), S4 = 500 (50)
        // Bottom Main = 1000 (100)
        $sideSales = [500.00, 500.00, 30.00, 500.00];
        $levels = $this->calculator->buildChain(4, $sideSales, 1000.00);

        $result = $this->calculator->calculate($rate, $levels);

        // S3 has sales = 30, comm = 3.00, which is the lowest in the whole system
        $this->assertEquals('S3', $result['weakest_person']);
        $this->assertEquals(30.00, $result['weakest_sales']);
        $this->assertEquals(3.00, $result['weakest_commission']);
        // Final commission must bottleneck at 3.00
        $this->assertEquals(3.00, $result['final_commission']);
    }

    /**
     * Test dynamically changing the weakest person.
     */
    public function test_changing_the_weakest_person(): void
    {
        $rate = 5.0;

        // Run 1: S2 is the weakest link
        $levelsRun1 = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 1000.00],
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 20.00], // 20 * 5% = 1.00
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'main_sales' => 500.00, 'side_person' => 'S3', 'side_sales' => 800.00],
        ];

        $res1 = $this->calculator->calculate($rate, $levelsRun1);
        $this->assertEquals('S2', $res1['weakest_person']);
        $this->assertEquals(1.00, $res1['final_commission']);

        // Run 2: S2 improves to 2000.00, now S1 drops to 10.00 (comm = 0.50)
        $levelsRun2 = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 10.00], // 10 * 5% = 0.50
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 2000.00],
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'main_sales' => 500.00, 'side_person' => 'S3', 'side_sales' => 800.00],
        ];

        $res2 = $this->calculator->calculate($rate, $levelsRun2);
        $this->assertEquals('S1', $res2['weakest_person']);
        $this->assertEquals(0.50, $res2['final_commission']);

        // Run 3: S1 and S2 improve, but bottom D drops to 5.00 (comm = 0.25)
        $levelsRun3 = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 1000.00],
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 2000.00],
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'main_sales' => 5.00, 'side_person' => 'S3', 'side_sales' => 800.00],
        ];

        $res3 = $this->calculator->calculate($rate, $levelsRun3);
        $this->assertEquals('D', $res3['weakest_person']);
        $this->assertEquals(0.25, $res3['final_commission']);
    }

    /**
     * Test verification of retained keys per level.
     */
    public function test_retained_level_keys_structure(): void
    {
        $rate = 10.0;
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'side_person' => 'S1',
                'side_sales' => 100.00,
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 50.00,
                'side_person' => 'S2',
                'side_sales' => 80.00,
            ],
        ];

        $result = $this->calculator->calculate($rate, $levels);

        foreach ($result['levels'] as $lvl) {
            $this->assertArrayHasKey('main_child', $lvl);
            $this->assertArrayHasKey('main_commission', $lvl);
            $this->assertArrayHasKey('side_person', $lvl);
            $this->assertArrayHasKey('side_commission', $lvl);
            $this->assertArrayHasKey('selected_minimum', $lvl);
            $this->assertArrayHasKey('resulting_leader_commission', $lvl);
        }
    }
}
