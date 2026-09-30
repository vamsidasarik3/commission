<?php

namespace Database\Seeders;

use App\Models\CommissionModel;
use App\Services\CommissionCalculator;
use App\Services\OverrideCommissionCalculator;
use App\Services\UniLevelCommissionCalculator;
use Illuminate\Database\Seeder;

class CommissionModelExamplesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weakestCalculator = app(CommissionCalculator::class);
        $overrideCalculator = app(OverrideCommissionCalculator::class);
        $unilevelCalculator = app(UniLevelCommissionCalculator::class);

        // =========================================================================
        // MODEL 1: WEAKEST LINK (3 EXAMPLES, MINIMUM 10 LEVELS, VARYING RATES)
        // =========================================================================

        // --- Model 1 Example 1: Progressive Escalation Ladder (10 Levels) ---
        $m1Ex1Levels = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 2000.00, 'commission_rate' => 3.0],
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 1800.00, 'commission_rate' => 4.0],
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'side_person' => 'S3', 'side_sales' => 1500.00, 'commission_rate' => 5.0],
            ['level' => 4, 'leader' => 'D', 'main_person' => 'E', 'side_person' => 'S4', 'side_sales' => 1400.00, 'commission_rate' => 6.0],
            ['level' => 5, 'leader' => 'E', 'main_person' => 'F', 'side_person' => 'S5', 'side_sales' => 1200.00, 'commission_rate' => 7.0],
            ['level' => 6, 'leader' => 'F', 'main_person' => 'G', 'side_person' => 'S6', 'side_sales' => 1100.00, 'commission_rate' => 8.0],
            ['level' => 7, 'leader' => 'G', 'main_person' => 'H', 'side_person' => 'S7', 'side_sales' => 1000.00, 'commission_rate' => 9.0],
            ['level' => 8, 'leader' => 'H', 'main_person' => 'I', 'side_person' => 'S8', 'side_sales' => 900.00, 'commission_rate' => 10.0],
            ['level' => 9, 'leader' => 'I', 'main_person' => 'J', 'side_person' => 'S9', 'side_sales' => 800.00, 'commission_rate' => 12.0],
            ['level' => 10, 'leader' => 'J', 'main_person' => 'K', 'main_sales' => 300.00, 'side_person' => 'S10', 'side_sales' => 700.00, 'commission_rate' => 15.0],
        ];
        $this->seedWeakestLinkModel(
            $weakestCalculator,
            'Model 1 - Ex 1: Progressive Escalation Ladder (10 Levels)',
            '10-level weakest link hierarchy with ascending commission rates per level (3% to 15%). Bottleneck occurs at bottom salesperson K.',
            5.0,
            $m1Ex1Levels
        );

        // --- Model 1 Example 2: Regressive Margin Decay Plan (10 Levels) ---
        $m1Ex2Levels = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 500.00, 'commission_rate' => 15.0],
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 600.00, 'commission_rate' => 12.5],
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'side_person' => 'S3', 'side_sales' => 800.00, 'commission_rate' => 10.0],
            ['level' => 4, 'leader' => 'D', 'main_person' => 'E', 'side_person' => 'S4', 'side_sales' => 900.00, 'commission_rate' => 8.5],
            ['level' => 5, 'leader' => 'E', 'main_person' => 'F', 'side_person' => 'S5', 'side_sales' => 1000.00, 'commission_rate' => 7.0],
            ['level' => 6, 'leader' => 'F', 'main_person' => 'G', 'side_person' => 'S6', 'side_sales' => 1200.00, 'commission_rate' => 6.0],
            ['level' => 7, 'leader' => 'G', 'main_person' => 'H', 'side_person' => 'S7', 'side_sales' => 1400.00, 'commission_rate' => 5.0],
            ['level' => 8, 'leader' => 'H', 'main_person' => 'I', 'side_person' => 'S8', 'side_sales' => 1600.00, 'commission_rate' => 4.0],
            ['level' => 9, 'leader' => 'I', 'main_person' => 'J', 'side_person' => 'S9', 'side_sales' => 1000.00, 'commission_rate' => 3.0],
            ['level' => 10, 'leader' => 'J', 'main_person' => 'K', 'main_sales' => 2500.00, 'side_person' => 'S10', 'side_sales' => 2000.00, 'commission_rate' => 2.5],
        ];
        $this->seedWeakestLinkModel(
            $weakestCalculator,
            'Model 1 - Ex 2: Regressive Margin Decay Plan (10 Levels)',
            '10-level weakest link hierarchy with descending rates from 15% at Level 1 to 2.5% at Level 10. Side salesperson S9 bottlenecks the chain.',
            8.0,
            $m1Ex2Levels
        );

        // --- Model 1 Example 3: Dynamic Rep-Specific Performance Model (10 Levels) ---
        $m1Ex3Levels = [
            ['level' => 1, 'leader' => 'A', 'main_person' => 'B', 'side_person' => 'S1', 'side_sales' => 1500.00, 'commission_rate' => 5.5],
            ['level' => 2, 'leader' => 'B', 'main_person' => 'C', 'side_person' => 'S2', 'side_sales' => 1200.00, 'commission_rate' => 8.0],
            ['level' => 3, 'leader' => 'C', 'main_person' => 'D', 'side_person' => 'S3', 'side_sales' => 2000.00, 'commission_rate' => 4.5],
            ['level' => 4, 'leader' => 'D', 'main_person' => 'E', 'side_person' => 'S4', 'side_sales' => 800.00, 'commission_rate' => 11.0],
            ['level' => 5, 'leader' => 'E', 'main_person' => 'F', 'side_person' => 'S5', 'side_sales' => 1400.00, 'commission_rate' => 6.5],
            ['level' => 6, 'leader' => 'F', 'main_person' => 'G', 'side_person' => 'S6', 'side_sales' => 1000.00, 'commission_rate' => 9.5],
            ['level' => 7, 'leader' => 'G', 'main_person' => 'H', 'side_person' => 'S7', 'side_sales' => 2500.00, 'commission_rate' => 3.5],
            ['level' => 8, 'leader' => 'H', 'main_person' => 'I', 'side_person' => 'S8', 'side_sales' => 750.00, 'commission_rate' => 12.0],
            ['level' => 9, 'leader' => 'I', 'main_person' => 'J', 'side_person' => 'S9', 'side_sales' => 1100.00, 'commission_rate' => 7.5],
            ['level' => 10, 'leader' => 'J', 'main_person' => 'K', 'main_sales' => 500.00, 'side_person' => 'S10', 'side_sales' => 900.00, 'commission_rate' => 10.0],
        ];
        $this->seedWeakestLinkModel(
            $weakestCalculator,
            'Model 1 - Ex 3: Dynamic Rep-Specific Performance Model (10 Levels)',
            '10-level model with non-fixed fluctuating commission percentages per level reflecting individual rep compensation agreements.',
            7.0,
            $m1Ex3Levels
        );

        // =========================================================================
        // MODEL 2: GENERATION OVERRIDE (3 EXAMPLES, MINIMUM 10 LEVELS, VARYING RATES)
        // =========================================================================

        // --- Model 2 Example 1: Corporate Enterprise Override Hierarchy (10 Levels) ---
        $m2Ex1Edges = [
            ['parent' => 'VP_National',       'child' => 'Reg_Dir_North',     'rate' => 8.5],
            ['parent' => 'Reg_Dir_North',     'child' => 'Area_Mgr_1',        'rate' => 7.0],
            ['parent' => 'Area_Mgr_1',        'child' => 'Dist_Lead_A',       'rate' => 6.0],
            ['parent' => 'Dist_Lead_A',       'child' => 'Senior_Partner_1',  'rate' => 5.0],
            ['parent' => 'Senior_Partner_1',  'child' => 'Team_Lead_Alpha',   'rate' => 4.0],
            ['parent' => 'Team_Lead_Alpha',   'child' => 'Senior_Exec_1',     'rate' => 3.5],
            ['parent' => 'Senior_Exec_1',     'child' => 'Account_Exec_1',    'rate' => 2.5],
            ['parent' => 'Account_Exec_1',    'child' => 'Field_Rep_1',       'rate' => 2.0],
            ['parent' => 'Field_Rep_1',       'child' => 'Junior_Associate_1', 'rate' => 1.5],
        ];
        $m2Ex1Sales = [
            ['salesperson' => 'Junior_Associate_1', 'amount' => 50000.00],
            ['salesperson' => 'Field_Rep_1',        'amount' => 30000.00],
            ['salesperson' => 'Account_Exec_1',     'amount' => 20000.00],
        ];
        $this->seedOverrideModel(
            $overrideCalculator,
            'Model 2 - Ex 1: Corporate Enterprise Override Hierarchy (10 Levels)',
            '10-tier corporate hierarchy where each reporting line has a customized override rate (1.5% to 8.5%). Sales at depth pay 9 uplines simultaneously.',
            $m2Ex1Edges,
            $m2Ex1Sales,
            10
        );

        // --- Model 2 Example 2: Executive Brokerage Network (10 Levels) ---
        $m2Ex2Edges = [
            ['parent' => 'CEO_Agency',          'child' => 'Executive_VP',      'rate' => 10.0],
            ['parent' => 'Executive_VP',        'child' => 'Managing_Director', 'rate' => 8.0],
            ['parent' => 'Managing_Director',   'child' => 'Senior_Director',   'rate' => 6.5],
            ['parent' => 'Senior_Director',     'child' => 'Regional_Head',     'rate' => 5.5],
            ['parent' => 'Regional_Head',       'child' => 'Territory_Manager', 'rate' => 4.5],
            ['parent' => 'Territory_Manager',   'child' => 'Branch_Supervisor', 'rate' => 3.8],
            ['parent' => 'Branch_Supervisor',   'child' => 'Unit_Leader',       'rate' => 3.0],
            ['parent' => 'Unit_Leader',         'child' => 'Senior_Consultant', 'rate' => 2.2],
            ['parent' => 'Senior_Consultant',   'child' => 'Broker_Agent_X',    'rate' => 1.8],
        ];
        $m2Ex2Sales = [
            ['salesperson' => 'Broker_Agent_X',    'amount' => 100000.00],
            ['salesperson' => 'Senior_Consultant', 'amount' => 40000.00],
            ['salesperson' => 'Unit_Leader',       'amount' => 25000.00],
        ];
        $this->seedOverrideModel(
            $overrideCalculator,
            'Model 2 - Ex 2: Executive Brokerage Network (10 Levels)',
            '10-level executive real estate and financial brokerage network with negotiated contract rates per tier (1.8% to 10.0%).',
            $m2Ex2Edges,
            $m2Ex2Sales,
            10
        );

        // --- Model 2 Example 3: Franchise Supply & Dealership Multi-Tier (10 Levels) ---
        $m2Ex3Edges = [
            ['parent' => 'Master_Franchisor', 'child' => 'Country_Licensee',  'rate' => 9.0],
            ['parent' => 'Country_Licensee',  'child' => 'State_Master',      'rate' => 7.5],
            ['parent' => 'State_Master',      'child' => 'Metro_Franchise',   'rate' => 6.0],
            ['parent' => 'Metro_Franchise',   'child' => 'District_Operator', 'rate' => 5.0],
            ['parent' => 'District_Operator', 'child' => 'City_Hub',          'rate' => 4.2],
            ['parent' => 'City_Hub',          'child' => 'Zone_Coordinator',  'rate' => 3.6],
            ['parent' => 'Zone_Coordinator',  'child' => 'Store_Manager',     'rate' => 2.8],
            ['parent' => 'Store_Manager',     'child' => 'Shift_Lead',        'rate' => 2.0],
            ['parent' => 'Shift_Lead',        'child' => 'Frontline_Cashier_1', 'rate' => 1.2],
        ];
        $m2Ex3Sales = [
            ['salesperson' => 'Frontline_Cashier_1', 'amount' => 80000.00],
            ['salesperson' => 'Shift_Lead',          'amount' => 35000.00],
            ['salesperson' => 'Store_Manager',       'amount' => 20000.00],
        ];
        $this->seedOverrideModel(
            $overrideCalculator,
            'Model 2 - Ex 3: Franchise Supply & Dealership Multi-Tier (10 Levels)',
            '10-tier retail franchise distribution model with variable royalty overrides from 1.2% store level to 9.0% master franchisor level.',
            $m2Ex3Edges,
            $m2Ex3Sales,
            10
        );

        // =========================================================================
        // MODEL 3: UNILEVEL MLM (3 EXAMPLES, MINIMUM 10 LEVELS, VARYING RATES)
        // =========================================================================

        // --- Model 3 Example 1: Retail Unilevel Distribution Plan (10 Levels) ---
        $m3Ex1Schedule = [
            1 => 12.0,
            2 => 8.0,
            3 => 6.0,
            4 => 5.0,
            5 => 4.0,
            6 => 3.0,
            7 => 2.0,
            8 => 1.5,
            9 => 1.0,
            10 => 0.5,
        ];
        $m3Ex1Nodes = [
            ['name' => 'Dist_A', 'parent' => null],
            ['name' => 'Dist_B', 'parent' => 'Dist_A'],
            ['name' => 'Dist_C', 'parent' => 'Dist_B'],
            ['name' => 'Dist_D', 'parent' => 'Dist_C'],
            ['name' => 'Dist_E', 'parent' => 'Dist_D'],
            ['name' => 'Dist_F', 'parent' => 'Dist_E'],
            ['name' => 'Dist_G', 'parent' => 'Dist_F'],
            ['name' => 'Dist_H', 'parent' => 'Dist_G'],
            ['name' => 'Dist_I', 'parent' => 'Dist_H'],
            ['name' => 'Dist_J', 'parent' => 'Dist_I'],
            ['name' => 'Dist_K', 'parent' => 'Dist_J'],
        ];
        $m3Ex1Sales = [
            ['distributor' => 'Dist_K', 'amount' => 20000.00],
            ['distributor' => 'Dist_I', 'amount' => 15000.00],
            ['distributor' => 'Dist_F', 'amount' => 10000.00],
        ];
        $this->seedUniLevelModel(
            $unilevelCalculator,
            'Model 3 - Ex 1: Retail Unilevel Distribution Plan (10 Levels)',
            '10-generation front-loaded unilevel distribution schedule (12% Gen 1 down to 0.5% Gen 10).',
            $m3Ex1Nodes,
            $m3Ex1Sales,
            $m3Ex1Schedule,
            10
        );

        // --- Model 3 Example 2: High-Growth Executive Builder Plan (10 Levels) ---
        $m3Ex2Schedule = [
            1 => 15.0,
            2 => 10.0,
            3 => 7.5,
            4 => 5.5,
            5 => 4.5,
            6 => 3.5,
            7 => 2.5,
            8 => 2.0,
            9 => 1.5,
            10 => 1.0,
        ];
        $m3Ex2Nodes = [
            ['name' => 'Apex_Leader', 'parent' => null],
            ['name' => 'Builder_L2',  'parent' => 'Apex_Leader'],
            ['name' => 'Builder_L3',  'parent' => 'Builder_L2'],
            ['name' => 'Builder_L4',  'parent' => 'Builder_L3'],
            ['name' => 'Builder_L5',  'parent' => 'Builder_L4'],
            ['name' => 'Builder_L6',  'parent' => 'Builder_L5'],
            ['name' => 'Builder_L7',  'parent' => 'Builder_L6'],
            ['name' => 'Builder_L8',  'parent' => 'Builder_L7'],
            ['name' => 'Builder_L9',  'parent' => 'Builder_L8'],
            ['name' => 'Builder_L10', 'parent' => 'Builder_L9'],
        ];
        $m3Ex2Sales = [
            ['distributor' => 'Builder_L10', 'amount' => 50000.00],
            ['distributor' => 'Builder_L7',  'amount' => 30000.00],
            ['distributor' => 'Builder_L4',  'amount' => 20000.00],
        ];
        $this->seedUniLevelModel(
            $unilevelCalculator,
            'Model 3 - Ex 2: High-Growth Executive Builder Plan (10 Levels)',
            '10-generation aggressive direct seller incentive plan with 15% direct parent bonus and payouts through generation 10.',
            $m3Ex2Nodes,
            $m3Ex2Sales,
            $m3Ex2Schedule,
            10
        );

        // --- Model 3 Example 3: Balanced Deep-Matrix Incentive Plan (10 Levels) ---
        $m3Ex3Schedule = [
            1 => 8.0,
            2 => 7.5,
            3 => 7.0,
            4 => 6.5,
            5 => 6.0,
            6 => 5.5,
            7 => 4.5,
            8 => 3.5,
            9 => 2.5,
            10 => 2.0,
        ];
        $m3Ex3Nodes = [
            ['name' => 'Global_Partner', 'parent' => null],
            ['name' => 'Matrix_L2',      'parent' => 'Global_Partner'],
            ['name' => 'Matrix_L3',      'parent' => 'Matrix_L2'],
            ['name' => 'Matrix_L4',      'parent' => 'Matrix_L3'],
            ['name' => 'Matrix_L5',      'parent' => 'Matrix_L4'],
            ['name' => 'Matrix_L6',      'parent' => 'Matrix_L5'],
            ['name' => 'Matrix_L7',      'parent' => 'Matrix_L6'],
            ['name' => 'Matrix_L8',      'parent' => 'Matrix_L7'],
            ['name' => 'Matrix_L9',      'parent' => 'Matrix_L8'],
            ['name' => 'Matrix_L10',     'parent' => 'Matrix_L9'],
        ];
        $m3Ex3Sales = [
            ['distributor' => 'Matrix_L10', 'amount' => 40000.00],
            ['distributor' => 'Matrix_L9',  'amount' => 30000.00],
            ['distributor' => 'Matrix_L6',  'amount' => 15000.00],
        ];
        $this->seedUniLevelModel(
            $unilevelCalculator,
            'Model 3 - Ex 3: Balanced Deep-Matrix Incentive Plan (10 Levels)',
            '10-generation balanced retention schedule rewarding leadership downlines evenly across all 10 depths (8.0% to 2.0%).',
            $m3Ex3Nodes,
            $m3Ex3Sales,
            $m3Ex3Schedule,
            10
        );
    }

    /**
     * Helper to persist a Model 1 (Weakest Link) example.
     */
    protected function seedWeakestLinkModel(
        CommissionCalculator $calculator,
        string $name,
        string $description,
        float $fallbackRate,
        array $levels
    ): void {
        $calcResult = $calculator->calculate($fallbackRate, $levels);

        // Delete existing model with this name if exists
        $existing = CommissionModel::where('name', $name)->first();
        if ($existing) {
            $existing->levels()->delete();
            $existing->delete();
        }

        $model = CommissionModel::create([
            'name' => $name,
            'model_type' => 'weakest_link',
            'description' => $description,
            'commission_rate' => $calcResult['commission_rate'],
            'number_of_levels' => $calcResult['number_of_levels'],
            'total_sales' => $calcResult['total_sales'],
            'total_potential_commission' => $calcResult['total_potential_commission'],
            'final_commission' => $calcResult['final_commission'],
            'weakest_person' => $calcResult['weakest_person'],
            'weakest_sales' => $calcResult['weakest_sales'],
            'weakest_commission' => $calcResult['weakest_commission'],
            'calculation_results' => $calcResult,
        ]);

        foreach ($calcResult['levels'] as $lvlData) {
            $model->levels()->create([
                'level' => $lvlData['level'],
                'commission_rate' => $lvlData['commission_rate'],
                'main_person' => $lvlData['main_person'],
                'main_sales' => $lvlData['main_sales'],
                'main_commission' => $lvlData['main_commission'],
                'side_person' => $lvlData['side_person'],
                'side_sales' => $lvlData['side_sales'],
                'side_commission' => $lvlData['side_commission'],
                'selected_commission' => $lvlData['selected_commission'],
                'leader_commission' => $lvlData['leader_commission'],
            ]);
        }
    }

    /**
     * Helper to persist a Model 2 (Generation Override) example.
     */
    protected function seedOverrideModel(
        OverrideCommissionCalculator $calculator,
        string $name,
        string $description,
        array $edges,
        array $sales,
        int $maxGenerations
    ): void {
        $existing = CommissionModel::where('name', $name)->first();
        if ($existing) {
            $existing->ledger()->delete();
            $existing->edges()->delete();
            $existing->nodes()->delete();
            $existing->relationships()->delete();
            $existing->overrideSales()->delete();
            $existing->overrideCommissions()->delete();
            $existing->delete();
        }

        $model = CommissionModel::create([
            'name' => $name,
            'model_type' => 'generation_override',
            'description' => $description,
            'commission_rate' => null,
            'max_generations' => $maxGenerations,
            'number_of_levels' => $maxGenerations,
        ]);

        $calculator->persistModel($model, $edges, $sales, $maxGenerations);
    }

    /**
     * Helper to persist a Model 3 (Unilevel MLM) example.
     */
    protected function seedUniLevelModel(
        UniLevelCommissionCalculator $calculator,
        string $name,
        string $description,
        array $nodes,
        array $sales,
        array $rateSchedule,
        int $maxDepth
    ): void {
        $existing = CommissionModel::where('name', $name)->first();
        if ($existing) {
            $existing->delete();
        }

        $model = CommissionModel::create([
            'name' => $name,
            'model_type' => 'unilevel',
            'description' => $description,
            'max_generations' => $maxDepth,
            'number_of_levels' => $maxDepth,
            'commission_rate' => null,
            'total_sales' => 0.00,
            'final_commission' => 0.00,
            'total_potential_commission' => 0.00,
        ]);

        $calculator->persistModel($model, $nodes, $sales, $rateSchedule, $maxDepth);
    }
}
