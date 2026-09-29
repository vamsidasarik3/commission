<?php

namespace App\Services;

use App\Models\CommissionModel;
use InvalidArgumentException;

class UniLevelCommissionCalculator
{
    /**
     * Default rate schedule per depth level (Level 1 = direct parent).
     * Illustrative only — not any real company's compensation plan.
     *
     * @var array<int, float>
     */
    public const DEFAULT_RATE_SCHEDULE = [
        1  => 10.0,
        2  => 5.0,
        3  => 4.0,
        4  => 3.0,
        5  => 2.0,
        6  => 1.0,
        7  => 1.0,
        8  => 0.5,
        9  => 0.5,
        10 => 0.5,
    ];

    /**
     * Calculate individual commission from a sale amount and rate.
     */
    public function calculateIndividualCommission(float $saleAmount, float $rate): float
    {
        return round(($saleAmount * $rate) / 100, 2);
    }

    /**
     * Core calculation engine for the Unilevel / Generation-Based MLM model.
     *
     * Rules:
     * - Each node has exactly one parent (single-upline).
     * - A sale made by any node triggers independent commission payments to uplines.
     * - Each upline receives the rate defined for its generational depth from the seller.
     * - The full original sale amount is used as the base at every generation (not reduced).
     * - Propagation stops at maxDepth or when there is no more upline.
     *
     * @param  array  $nodes    [['name' => 'A', 'parent' => null], ['name' => 'B', 'parent' => 'A'], ...]
     * @param  array  $sales    [['distributor' => 'B1', 'amount' => 10000.0], ...]
     * @param  array  $rateSchedule  [1 => 10.0, 2 => 5.0, ...] (depth => %)
     * @param  int    $maxDepth  Maximum generations to pay commission to (default 10)
     */
    public function calculate(
        array $nodes,
        array $sales,
        array $rateSchedule = [],
        int $maxDepth = 10
    ): array {
        if (empty($nodes)) {
            throw new InvalidArgumentException('At least one node (distributor) is required.');
        }
        if (empty($sales)) {
            throw new InvalidArgumentException('At least one sale record is required.');
        }
        if ($maxDepth < 1) {
            throw new InvalidArgumentException('Max depth must be at least 1.');
        }

        if (empty($rateSchedule)) {
            $rateSchedule = self::DEFAULT_RATE_SCHEDULE;
        }

        // 1. Build parent-lookup map and all-people registry
        $parentOf = [];   // name -> parent_name
        $allPeople = [];  // name -> true

        foreach ($nodes as $node) {
            $name   = trim($node['name']);
            $parent = isset($node['parent']) && $node['parent'] !== '' ? trim($node['parent']) : null;

            $parentOf[$name] = $parent;
            $allPeople[$name] = true;
            if ($parent !== null) {
                $allPeople[$parent] = true;
            }
        }

        // 2. Initialize earnings tracker
        $earnings = [];
        foreach (array_keys($allPeople) as $person) {
            $earnings[$person] = [
                'name'                 => $person,
                'personal_sales'       => 0.0,
                'override_commission'  => 0.0,
                'total_earnings'       => 0.0,
                'commissions_received' => [],
                'depth_in_tree'        => $this->computeDepth($person, $parentOf),
            ];
        }

        // 3. Record personal sales
        $totalPersonalSales = 0.0;
        foreach ($sales as $sale) {
            $distributor = trim($sale['distributor']);
            $amount      = (float) $sale['amount'];
            $totalPersonalSales += $amount;

            if (isset($earnings[$distributor])) {
                $earnings[$distributor]['personal_sales'] += $amount;
            }
        }

        // 4. For each sale, walk up the chain and pay commissions
        $ledger            = [];
        $totalCommission   = 0.0;
        $commissionBySale  = [];

        foreach ($sales as $saleIndex => $sale) {
            $seller        = trim($sale['distributor']);
            $originalAmount = (float) $sale['amount'];
            $saleCommissions = [];
            $saleTotal       = 0.0;

            $currentNode = $seller;
            $depth       = 1;

            while (($depth <= $maxDepth) && isset($parentOf[$currentNode]) && $parentOf[$currentNode] !== null) {
                $upline = $parentOf[$currentNode];
                $rate   = (float) ($rateSchedule[$depth] ?? 0.0);

                $commissionAmount = $this->calculateIndividualCommission($originalAmount, $rate);

                $record = [
                    'sale_index'          => $saleIndex,
                    'seller'              => $seller,
                    'earner'              => $upline,
                    'generation'          => $depth,
                    'sale_amount'         => round($originalAmount, 2),
                    'rate'                => $rate,
                    'commission_amount'   => $commissionAmount,
                    'is_eligible'         => $rate > 0,
                ];

                $saleCommissions[] = $record;
                $ledger[]          = $record;

                if ($rate > 0) {
                    $saleTotal     += $commissionAmount;
                    $totalCommission += $commissionAmount;

                    if (isset($earnings[$upline])) {
                        $earnings[$upline]['override_commission'] += $commissionAmount;
                        $earnings[$upline]['total_earnings']      += $commissionAmount;
                        $earnings[$upline]['commissions_received'][] = $record;
                    }
                }

                $currentNode = $upline;
                $depth++;
            }

            $commissionBySale[] = [
                'sale_index'           => $saleIndex,
                'seller'               => $seller,
                'amount'               => round($originalAmount, 2),
                'total_commission_paid' => round($saleTotal, 2),
                'uplines_paid'         => count($saleCommissions),
                'commissions'          => $saleCommissions,
            ];
        }

        // 5. Round summaries
        foreach ($earnings as &$pData) {
            $pData['personal_sales']      = round($pData['personal_sales'], 2);
            $pData['override_commission'] = round($pData['override_commission'], 2);
            $pData['total_earnings']      = round($pData['total_earnings'], 2);
        }
        unset($pData);

        // 6. Build hierarchy tree for display
        $hierarchyTree = $this->buildHierarchyTree($parentOf, $earnings, $rateSchedule);

        return [
            'total_personal_sales'     => round($totalPersonalSales, 2),
            'total_commission_generated' => round($totalCommission, 2),
            'max_depth'                => $maxDepth,
            'rate_schedule'            => $rateSchedule,
            'earnings_by_person'       => $earnings,
            'commission_by_sale'       => $commissionBySale,
            'ledger'                   => $ledger,
            'hierarchy_tree'           => $hierarchyTree,
            'node_count'               => count($allPeople),
            'sale_count'               => count($sales),
        ];
    }

    /**
     * Compute depth from root for a given node.
     *
     * @param  array<string, string|null>  $parentOf
     */
    protected function computeDepth(string $node, array $parentOf): int
    {
        $depth = 1;
        $current = $node;
        while (isset($parentOf[$current]) && $parentOf[$current] !== null) {
            $current = $parentOf[$current];
            $depth++;
            if ($depth > 100) {
                break; // cycle guard
            }
        }

        return $depth;
    }

    /**
     * Build a nested hierarchy tree structure for rendering.
     */
    protected function buildHierarchyTree(array $parentOf, array $earnings, array $rateSchedule): array
    {
        // Find all children for each node
        $childrenOf = [];
        foreach ($parentOf as $node => $parent) {
            if ($parent !== null) {
                $childrenOf[$parent][] = $node;
            }
        }

        // Find roots (nodes with no parent)
        $roots = [];
        foreach ($parentOf as $node => $parent) {
            if ($parent === null) {
                $roots[] = $node;
            }
        }

        $buildNode = null;
        $buildNode = function (string $name, int $level) use (&$buildNode, &$childrenOf, &$earnings, &$rateSchedule): array {
            $children = [];
            if (isset($childrenOf[$name])) {
                sort($childrenOf[$name]);
                foreach ($childrenOf[$name] as $child) {
                    $children[] = $buildNode($child, $level + 1);
                }
            }

            return [
                'name'                => $name,
                'level'               => $level,
                'personal_sales'      => $earnings[$name]['personal_sales'] ?? 0.0,
                'override_commission' => $earnings[$name]['override_commission'] ?? 0.0,
                'rate_as_upline'      => (float) ($rateSchedule[$level] ?? 0.0),
                'children'            => $children,
                'child_count'         => count($children),
            ];
        };

        $tree = [];
        foreach ($roots as $root) {
            $tree[] = $buildNode($root, 1);
        }

        return $tree;
    }

    /**
     * Persist a Unilevel Commission Model along with its nodes, edges and ledger.
     */
    public function persistModel(
        CommissionModel $model,
        array $nodes,
        array $sales,
        array $rateSchedule,
        int $maxDepth
    ): CommissionModel {
        $results = $this->calculate($nodes, $sales, $rateSchedule, $maxDepth);

        $model->update([
            'number_of_levels'         => $maxDepth,
            'total_sales'              => $results['total_personal_sales'],
            'total_potential_commission' => $results['total_commission_generated'],
            'final_commission'         => $results['total_commission_generated'],
            'calculation_results'      => $results,
        ]);

        return $model->fresh();
    }

    /**
     * Load and recalculate from a saved CommissionModel using its stored calculation_results.
     */
    public function loadFromModel(CommissionModel $model): array
    {
        if (! empty($model->calculation_results)) {
            return $model->calculation_results;
        }

        return [
            'total_personal_sales'       => (float) $model->total_sales,
            'total_commission_generated' => (float) $model->final_commission,
            'max_depth'                  => $model->max_generations ?? 10,
            'rate_schedule'              => self::DEFAULT_RATE_SCHEDULE,
            'earnings_by_person'         => [],
            'commission_by_sale'         => [],
            'ledger'                     => [],
            'hierarchy_tree'             => [],
            'node_count'                 => 0,
            'sale_count'                 => 0,
        ];
    }
}
