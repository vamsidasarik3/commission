<?php

namespace Tests\Feature;

use App\Models\CommissionModel;
use App\Services\CommissionCalculator;
use App\Services\OverrideCommissionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Model1RegressionAndSeparationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test exact Model 1 manual regression scenario:
     * Commission rate = 5%
     *
     * J = ₹40 (J commission = ₹2)
     * S9 = ₹200 (S9 commission = ₹10)
     * S8 = ₹300 (S8 commission = ₹15)
     * S7 = ₹400 (S7 commission = ₹20)
     * S6 = ₹500 (S6 commission = ₹25)
     * S5 = ₹600 (S5 commission = ₹30)
     * S4 = ₹700 (S4 commission = ₹35)
     * S3 = ₹800 (S3 commission = ₹40)
     * S2 = ₹900 (S2 commission = ₹45)
     * S1 = ₹1000 (S1 commission = ₹50)
     *
     * Weakest-link calculation must produce:
     * I = ₹2
     * H = ₹2
     * G = ₹2
     * F = ₹2
     * E = ₹2
     * D = ₹2
     * C = ₹2
     * B = ₹2
     * A = ₹2
     */
    public function test_model_1_exact_manual_regression(): void
    {
        $calculator = app(CommissionCalculator::class);

        $rate = 5.0; // 5%

        // 9 levels from A down to I, with bottom leaf J
        $levels = [
            [
                'level' => 1,
                'leader' => 'A',
                'main_person' => 'B',
                'main_sales' => 0.0,
                'side_person' => 'S1',
                'side_sales' => 1000.0,
            ],
            [
                'level' => 2,
                'leader' => 'B',
                'main_person' => 'C',
                'main_sales' => 0.0,
                'side_person' => 'S2',
                'side_sales' => 900.0,
            ],
            [
                'level' => 3,
                'leader' => 'C',
                'main_person' => 'D',
                'main_sales' => 0.0,
                'side_person' => 'S3',
                'side_sales' => 800.0,
            ],
            [
                'level' => 4,
                'leader' => 'D',
                'main_person' => 'E',
                'main_sales' => 0.0,
                'side_person' => 'S4',
                'side_sales' => 700.0,
            ],
            [
                'level' => 5,
                'leader' => 'E',
                'main_person' => 'F',
                'main_sales' => 0.0,
                'side_person' => 'S5',
                'side_sales' => 600.0,
            ],
            [
                'level' => 6,
                'leader' => 'F',
                'main_person' => 'G',
                'main_sales' => 0.0,
                'side_person' => 'S6',
                'side_sales' => 500.0,
            ],
            [
                'level' => 7,
                'leader' => 'G',
                'main_person' => 'H',
                'main_sales' => 0.0,
                'side_person' => 'S7',
                'side_sales' => 400.0,
            ],
            [
                'level' => 8,
                'leader' => 'H',
                'main_person' => 'I',
                'main_sales' => 0.0,
                'side_person' => 'S8',
                'side_sales' => 300.0,
            ],
            [
                'level' => 9,
                'leader' => 'I',
                'main_person' => 'J',
                'main_sales' => 40.0, // bottom salesperson
                'side_person' => 'S9',
                'side_sales' => 200.0,
            ],
        ];

        $results = $calculator->calculate($rate, $levels);

        // Verification of bottom salesperson J
        $this->assertEquals('J', $results['weakest_person']);
        $this->assertEquals(40.00, $results['weakest_sales']);
        $this->assertEquals(2.00, $results['weakest_commission']); // 40 * 5% = ₹2

        // Verify that every single leader produces exactly ₹2:
        // Level 9: Leader I = ₹2
        $lvl9 = collect($results['levels'])->firstWhere('level', 9);
        $this->assertEquals('I', $lvl9['leader']);
        $this->assertEquals(2.00, $lvl9['resulting_leader_commission']); // I = ₹2

        // Level 8: Leader H = ₹2
        $lvl8 = collect($results['levels'])->firstWhere('level', 8);
        $this->assertEquals('H', $lvl8['leader']);
        $this->assertEquals(2.00, $lvl8['resulting_leader_commission']); // H = ₹2

        // Level 7: Leader G = ₹2
        $lvl7 = collect($results['levels'])->firstWhere('level', 7);
        $this->assertEquals('G', $lvl7['leader']);
        $this->assertEquals(2.00, $lvl7['resulting_leader_commission']); // G = ₹2

        // Level 6: Leader F = ₹2
        $lvl6 = collect($results['levels'])->firstWhere('level', 6);
        $this->assertEquals('F', $lvl6['leader']);
        $this->assertEquals(2.00, $lvl6['resulting_leader_commission']); // F = ₹2

        // Level 5: Leader E = ₹2
        $lvl5 = collect($results['levels'])->firstWhere('level', 5);
        $this->assertEquals('E', $lvl5['leader']);
        $this->assertEquals(2.00, $lvl5['resulting_leader_commission']); // E = ₹2

        // Level 4: Leader D = ₹2
        $lvl4 = collect($results['levels'])->firstWhere('level', 4);
        $this->assertEquals('D', $lvl4['leader']);
        $this->assertEquals(2.00, $lvl4['resulting_leader_commission']); // D = ₹2

        // Level 3: Leader C = ₹2
        $lvl3 = collect($results['levels'])->firstWhere('level', 3);
        $this->assertEquals('C', $lvl3['leader']);
        $this->assertEquals(2.00, $lvl3['resulting_leader_commission']); // C = ₹2

        // Level 2: Leader B = ₹2
        $lvl2 = collect($results['levels'])->firstWhere('level', 2);
        $this->assertEquals('B', $lvl2['leader']);
        $this->assertEquals(2.00, $lvl2['resulting_leader_commission']); // B = ₹2

        // Level 1: Leader A = ₹2
        $lvl1 = collect($results['levels'])->firstWhere('level', 1);
        $this->assertEquals('A', $lvl1['leader']);
        $this->assertEquals(2.00, $lvl1['resulting_leader_commission']); // A = ₹2

        // Final Leader A commission is ₹2
        $this->assertEquals(2.00, $results['final_commission']);

        // Test through HTTP calculate endpoint as well
        $response = $this->postJson(route('commission-models.calculate'), [
            'commission_rate' => 5.0,
            'number_of_levels' => 9,
            'levels' => $levels,
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(2.00, $data['final_commission']);
        $this->assertEquals('J', $data['weakest_person']);
        $this->assertEquals(2.00, $data['weakest_commission']);
    }

    /**
     * Test architectural separation:
     * Model 2 must never invoke the Weakest Link MIN calculation.
     * Model 1 must never invoke the Level Override calculation.
     */
    public function test_architectural_separation_between_model1_and_model2(): void
    {
        // 1. Spying on CommissionCalculator during Model 2 execution
        $mockM1 = $this->spy(CommissionCalculator::class);

        $m2Calculator = app(OverrideCommissionCalculator::class);
        $m2Result = $m2Calculator->calculate(
            edges: [
                ['parent' => 'A', 'child' => 'B', 'rate' => 2.0],
                ['parent' => 'B', 'child' => 'E1', 'rate' => 5.0],
            ],
            sales: [
                ['salesperson' => 'E1', 'amount' => 200.0],
            ],
            maxGenerations: 5
        );

        // Model 2 calculated additive overrides (E1 gives B: 10, A: 4; total = 14)
        $this->assertEquals(14.00, $m2Result['total_commission_generated']);
        // CommissionCalculator was NEVER invoked during Model 2
        $mockM1->shouldNotHaveReceived('calculate');

        // 2. Spying on OverrideCommissionCalculator during Model 1 execution
        $mockM2 = $this->spy(OverrideCommissionCalculator::class);

        $m1Calculator = new CommissionCalculator;
        $m1Result = $m1Calculator->calculate(
            commissionRate: 5.0,
            levels: [
                [
                    'level' => 1,
                    'leader' => 'A',
                    'main_person' => 'B',
                    'main_sales' => 100.0,
                    'side_person' => 'S1',
                    'side_sales' => 200.0,
                ],
            ]
        );

        // Model 1 used MIN comparison (min(5, 10) = 5)
        $this->assertEquals(5.00, $m1Result['final_commission']);
        // OverrideCommissionCalculator was NEVER invoked during Model 1
        $mockM2->shouldNotHaveReceived('calculate');
    }

    /**
     * Test Saved Models page supports multiple model types:
     * Displays ID, Model Name, Model Type, Maximum Generations, Total Sales, Total Commission, Created At, Actions.
     * Model Type displays "Weakest Link" or "Level / Generation Override".
     * Model 1 opens existing Model 1 results.
     * Model 2 opens Model 2 results.
     */
    public function test_saved_models_page_displays_both_model_types_accurately(): void
    {
        // Create Model 1
        $m1 = CommissionModel::create([
            'name' => 'Q3 Weakest Link Benchmark',
            'model_type' => 'weakest_link',
            'commission_rate' => 5.0,
            'number_of_levels' => 3,
            'total_sales' => 1500.0,
            'final_commission' => 25.0,
            'weakest_person' => 'Branch_C',
            'weakest_sales' => 500.0,
            'weakest_commission' => 25.0,
        ]);

        // Create Model 2
        $m2 = CommissionModel::create([
            'name' => 'Q4 Override Network Model',
            'model_type' => 'generation_override',
            'max_generations' => 5,
            'total_sales' => 2400.0,
            'final_commission' => 180.0,
        ]);

        $response = $this->get(route('commission-models.index'));

        $response->assertStatus(200);

        // Verify Table Headers
        $response->assertSee('ID');
        $response->assertSee('Model Name');
        $response->assertSee('Model Type');
        $response->assertSee('Maximum Generations');
        $response->assertSee('Total Sales');
        $response->assertSee('Total Commission');
        $response->assertSee('Created At');
        $response->assertSee('Actions');

        // Verify Model Type values
        $response->assertSee('Weakest Link');
        $response->assertSee('Level / Generation Override');

        // Verify Maximum Generations display
        $response->assertSee('5'); // for Model 2
        $response->assertSee('—'); // for Model 1

        // Verify Model 1 links to Model 1 results
        $response->assertSee(route('commission-models.show', $m1));

        // Verify Model 2 links to Model 2 results
        $response->assertSee(route('override-models.show', $m2));
    }
}
