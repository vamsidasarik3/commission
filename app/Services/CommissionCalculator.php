<?php

namespace App\Services;

use App\Models\CommissionLevel;
use App\Models\CommissionModel;
use InvalidArgumentException;

class CommissionCalculator
{
    /**
     * Calculate individual commission from sales and rate.
     * Formula: sales * commission_rate / 100
     */
    public function calculateIndividualCommission(float|int|string $sales, float|int|string $rate): float
    {
        $sales = (float) $sales;
        $rate = (float) $rate;

        return round(($sales * $rate) / 100, 2);
    }

    /**
     * Compute Weakest Link Commission across arbitrary levels.
     *
     * Each level may define its own `commission_rate`; if omitted, the global
     * $commissionRate is used as fallback (backwards-compatible).
     *
     * @param  float|int|string  $commissionRate  Global fallback percentage rate (e.g. 5.0 for 5%)
     * @param  array  $levels  Level definitions ordered or indexed by level
     * @param  string  $topLeader  Name of the root leader (default 'A')
     * @return array Structured calculation results
     */
    public function calculate(float|int|string $commissionRate, array $levels, string $topLeader = 'A'): array
    {
        $globalRate = (float) $commissionRate;
        if ($globalRate < 0) {
            throw new InvalidArgumentException('Commission rate cannot be negative.');
        }

        if (empty($levels)) {
            throw new InvalidArgumentException('Commission model must contain at least 1 level.');
        }

        // 1. Normalize and sort levels by level index ascending (Level 1 is top, Level N is bottom)
        $normalizedLevels = $this->normalizeLevels($levels, $topLeader);
        $totalLevels = count($normalizedLevels);

        // 2. Track all direct salespeople and their individual commissions
        $salespeople = [];
        $totalSales = 0.0;
        $totalPotentialCommission = 0.0;

        // Iterate through all levels bottom-up to register main and side salespeople
        for ($i = $totalLevels - 1; $i >= 0; $i--) {
            $lvl = $normalizedLevels[$i];
            $levelRate = isset($lvl['commission_rate']) && $lvl['commission_rate'] !== null
                ? (float) $lvl['commission_rate']
                : $globalRate;
            $mainPerson = $lvl['main_person'];
            $mainSales = (float) ($lvl['main_sales'] ?? 0.0);
            $sidePerson = $lvl['side_person'];
            $sideSales = (float) ($lvl['side_sales'] ?? 0.0);

            // Register side salesperson
            if (! isset($salespeople[$sidePerson])) {
                $sideCommission = $this->calculateIndividualCommission($sideSales, $levelRate);
                $salespeople[$sidePerson] = [
                    'name' => $sidePerson,
                    'sales' => $sideSales,
                    'commission' => $sideCommission,
                    'role' => 'side_salesperson',
                    'level' => $lvl['level'],
                ];
                $totalSales += $sideSales;
                $totalPotentialCommission += $sideCommission;
            }

            // Register main salesperson (for bottom level always; for intermediate levels only if positive personal sales were passed)
            if ($i === $totalLevels - 1 || $mainSales > 0) {
                if (! isset($salespeople[$mainPerson])) {
                    $mainCommission = $this->calculateIndividualCommission($mainSales, $levelRate);
                    $salespeople[$mainPerson] = [
                        'name' => $mainPerson,
                        'sales' => $mainSales,
                        'commission' => $mainCommission,
                        'role' => ($i === $totalLevels - 1) ? 'bottom_main_child' : 'main_chain_salesperson',
                        'level' => $lvl['level'],
                    ];
                    $totalSales += $mainSales;
                    $totalPotentialCommission += $mainCommission;
                }
            }
        }

        // 3. Determine the Weakest Person among direct salespeople
        $weakestPerson = null;
        $weakestSales = null;
        $weakestCommission = null;

        foreach ($salespeople as $personData) {
            if ($weakestCommission === null || $personData['commission'] < $weakestCommission) {
                $weakestPerson = $personData['name'];
                $weakestSales = $personData['sales'];
                $weakestCommission = $personData['commission'];
            }
        }

        // 4. Execute bottom-up calculation:
        // At every level, the leader's commission is determined by:
        // MIN(main child commission, side salesperson commission)
        $calculatedLevels = [];
        $nextMainCommission = null;

        for ($i = $totalLevels - 1; $i >= 0; $i--) {
            $currentLevel = $normalizedLevels[$i];
            $levelNumber = $currentLevel['level'];
            $levelRate = isset($currentLevel['commission_rate']) && $currentLevel['commission_rate'] !== null
                ? (float) $currentLevel['commission_rate']
                : $globalRate;
            $mainChild = $currentLevel['main_person'];
            $mainSales = (float) ($currentLevel['main_sales'] ?? 0.0);
            $sidePerson = $currentLevel['side_person'];
            $sideSales = (float) ($currentLevel['side_sales'] ?? 0.0);
            $sideCommission = $this->calculateIndividualCommission($sideSales, $levelRate);

            if ($i === $totalLevels - 1) {
                // Bottom level: main child commission comes from their own direct sales
                $mainCommission = $this->calculateIndividualCommission($mainSales, $levelRate);
            } else {
                // Intermediate level: incoming commission from level below
                $childBranchCommission = $nextMainCommission;
                if ($mainSales > 0) {
                    $directPersonalCommission = $this->calculateIndividualCommission($mainSales, $levelRate);
                    $mainCommission = min($childBranchCommission, $directPersonalCommission);
                } else {
                    $mainCommission = $childBranchCommission;
                }
            }

            // WEAKEST LINK COMPARISON RULE: MIN(main child commission, side salesperson commission)
            $selectedMinimum = min($mainCommission, $sideCommission);
            $resultingLeaderCommission = $selectedMinimum;

            // Store level calculation result
            $calculatedLevels[$i] = [
                'level' => $levelNumber,
                'commission_rate' => $levelRate,
                'leader' => $currentLevel['leader'],
                'main_child' => $mainChild,
                'main_person' => $mainChild,
                'main_sales' => $mainSales,
                'main_commission' => $mainCommission,
                'side_person' => $sidePerson,
                'side_sales' => $sideSales,
                'side_commission' => $sideCommission,
                'selected_minimum' => $selectedMinimum,
                'selected_commission' => $selectedMinimum,
                'resulting_leader_commission' => $resultingLeaderCommission,
                'leader_commission' => $resultingLeaderCommission,
                'weakest_at_level' => ($mainCommission <= $sideCommission) ? $mainChild : $sidePerson,
            ];

            // Propagate leader commission upward to become the main child commission of the parent level
            $nextMainCommission = $resultingLeaderCommission;
        }

        // Reorder levels array back to Level 1 -> Level N
        ksort($calculatedLevels);
        $levelByLevel = array_values($calculatedLevels);

        // Final commission for top leader A is Level 1 resulting leader commission
        $finalCommission = $levelByLevel[0]['resulting_leader_commission'];

        // Selected minimums at each level in order 1..N
        $selectedMinimums = array_map(fn ($lvl) => $lvl['selected_minimum'], $levelByLevel);

        return [
            'commission_rate' => $globalRate,
            'number_of_levels' => $totalLevels,
            'top_leader' => $topLeader,
            'total_sales' => round($totalSales, 2),
            'total_potential_commission' => round($totalPotentialCommission, 2),
            'weakest_person' => $weakestPerson,
            'weakest_sales' => round((float) $weakestSales, 2),
            'weakest_commission' => round((float) $weakestCommission, 2),
            'final_commission' => round($finalCommission, 2),
            'level_by_level_calculations' => $levelByLevel,
            'levels' => $levelByLevel,
            'each_salesperson_commission' => $salespeople,
            'selected_minimum_at_each_level' => $selectedMinimums,
            'selected_minimums' => $selectedMinimums,
        ];
    }

    /**
     * Calculate and sync directly with a CommissionModel instance.
     */
    public function calculateFromModel(CommissionModel $model): array
    {
        $levelsData = [];
        $dbLevels = $model->levels()->orderBy('level')->get();

        foreach ($dbLevels as $lvl) {
            $levelsData[] = [
                'id' => $lvl->id,
                'level' => $lvl->level,
                'commission_rate' => $lvl->commission_rate !== null ? (float) $lvl->commission_rate : null,
                'main_person' => $lvl->main_person,
                'main_sales' => (float) $lvl->main_sales,
                'side_person' => $lvl->side_person,
                'side_sales' => (float) $lvl->side_sales,
            ];
        }

        return $this->calculate($model->commission_rate, $levelsData, 'A');
    }

    /**
     * Calculate and persist results into CommissionModel and its CommissionLevel records.
     */
    public function calculateAndPersist(CommissionModel $model): CommissionModel
    {
        $results = $this->calculateFromModel($model);

        $model->update([
            'number_of_levels' => $results['number_of_levels'],
            'total_sales' => $results['total_sales'],
            'total_potential_commission' => $results['total_potential_commission'],
            'final_commission' => $results['final_commission'],
            'weakest_person' => $results['weakest_person'],
            'weakest_sales' => $results['weakest_sales'],
            'weakest_commission' => $results['weakest_commission'],
        ]);

        foreach ($results['levels'] as $calcLvl) {
            CommissionLevel::where('commission_model_id', $model->id)
                ->where('level', $calcLvl['level'])
                ->update([
                    'commission_rate' => $calcLvl['commission_rate'],
                    'main_person' => $calcLvl['main_person'],
                    'main_sales' => $calcLvl['main_sales'],
                    'main_commission' => $calcLvl['main_commission'],
                    'side_person' => $calcLvl['side_person'],
                    'side_sales' => $calcLvl['side_sales'],
                    'side_commission' => $calcLvl['side_commission'],
                    'selected_commission' => $calcLvl['selected_commission'],
                    'leader_commission' => $calcLvl['leader_commission'],
                ]);
        }

        return $model->fresh(['levels']);
    }

    /**
     * Helper to dynamically build arbitrary level chains (e.g. 1, 2, 5, 10, 20, 50, 100 levels).
     */
    public function buildChain(
        int $numberOfLevels,
        float|array $sideSales,
        float $bottomMainSales,
        string $topLeader = 'A'
    ): array {
        if ($numberOfLevels < 1) {
            throw new InvalidArgumentException('Number of levels must be at least 1.');
        }

        $levels = [];
        $letters = range('A', 'Z');

        for ($i = 1; $i <= $numberOfLevels; $i++) {
            $leaderIndex = $i - 1;
            $mainIndex = $i;

            $leaderName = $leaderIndex < count($letters) ? $letters[$leaderIndex] : "Leader_{$i}";
            $mainName = $mainIndex < count($letters) ? $letters[$mainIndex] : "Main_{$i}";
            $sideName = "S{$i}";

            $currentSideSales = is_array($sideSales)
                ? ($sideSales[$i - 1] ?? ($sideSales[$sideName] ?? 0.0))
                : (float) $sideSales;

            $levelData = [
                'level' => $i,
                'leader' => $leaderName,
                'main_person' => $mainName,
                'main_sales' => ($i === $numberOfLevels) ? $bottomMainSales : 0.0,
                'side_person' => $sideName,
                'side_sales' => (float) $currentSideSales,
            ];

            $levels[] = $levelData;
        }

        return $levels;
    }

    /**
     * Normalize incoming levels array.
     */
    protected function normalizeLevels(array $levels, string $topLeader): array
    {
        $normalized = [];
        $letters = range('A', 'Z');

        // Check if levels have a 'level' key or sequential array
        $isAssociativeWithLevelKey = isset(reset($levels)['level']);

        if ($isAssociativeWithLevelKey) {
            usort($levels, fn ($a, $b) => ($a['level'] ?? 0) <=> ($b['level'] ?? 0));
        }

        $count = count($levels);
        foreach (array_values($levels) as $idx => $lvl) {
            $levelNumber = $lvl['level'] ?? ($idx + 1);
            $leaderIndex = $idx;
            $mainIndex = $idx + 1;

            $defaultLeader = $idx === 0 ? $topLeader : ($leaderIndex < count($letters) ? $letters[$leaderIndex] : "Leader_{$levelNumber}");
            $defaultMain = ($mainIndex < count($letters)) ? $letters[$mainIndex] : "Main_{$levelNumber}";
            $defaultSide = "S{$levelNumber}";

            $normalized[] = [
                'level' => (int) $levelNumber,
                'commission_rate' => isset($lvl['commission_rate']) && $lvl['commission_rate'] !== null && $lvl['commission_rate'] !== '' ? (float) $lvl['commission_rate'] : null,
                'leader' => $lvl['leader'] ?? $defaultLeader,
                'main_person' => $lvl['main_person'] ?? ($lvl['main_child'] ?? $defaultMain),
                'main_sales' => isset($lvl['main_sales']) ? (float) $lvl['main_sales'] : 0.0,
                'side_person' => $lvl['side_person'] ?? $defaultSide,
                'side_sales' => isset($lvl['side_sales']) ? (float) $lvl['side_sales'] : 0.0,
            ];
        }

        return $normalized;
    }
}
