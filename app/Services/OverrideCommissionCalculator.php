<?php

namespace App\Services;

use App\Models\CommissionModel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OverrideCommissionCalculator
{
    /**
     * Calculate individual commission from sales amount and rate.
     * Formula: original_sale_amount * applicable_rate / 100
     */
    public function calculateIndividualCommission(float|int|string $saleAmount, float|int|string $rate): float
    {
        $saleAmount = (float) $saleAmount;
        $rate = (float) $rate;

        return round(($saleAmount * $rate) / 100, 2);
    }

    /**
     * Compute Level / Generation Override Commission across hierarchical sales network.
     *
     * Core Concept & Rules:
     * - A sale made by a salesperson generates commissions for multiple people above that salesperson.
     * - NO weakest-link comparison. NO MIN operation. NO sibling comparison.
     * - One sale can pay multiple uplines independently.
     * - Each upline has its own override percentage defined on the parent-child relationship/edge.
     * - The same original sale amount is used as the commission base as it moves upward (NOT reduced).
     * - Commission propagation stops after a configurable maximum number of generations (default: 5).
     * - The fifth generation is eligible; the sixth generation is not.
     *
     * @param  array  $edges  Array of parent-child relationships [['parent' => 'A', 'child' => 'B', 'rate' => 2.0], ...]
     * @param  array  $sales  Array of personal sales [['salesperson' => 'E1', 'amount' => 200.0], ...]
     * @param  int  $maxGenerations  Maximum eligible generation depth (default 5)
     * @return array Structured calculation results
    /**
     * Compute Level / Generation Override Commission.
     * Accepts a CommissionModel and/or explicit tree structure, parent-child relationships, personal sales, and max generations.
     */
    public function compute(
        ?CommissionModel $model = null,
        array $edges = [],
        array $sales = [],
        int $maxGenerations = 5
    ): array {
        if ($model !== null && empty($edges) && empty($sales)) {
            return $this->calculateFromModel($model);
        }

        return $this->calculate($edges, $sales, $maxGenerations, $model);
    }

    public function calculate(array $edges, array $sales, int $maxGenerations = 5, ?CommissionModel $model = null): array
    {
        if ($maxGenerations < 1) {
            throw new InvalidArgumentException('Maximum generations must be at least 1.');
        }

        // 1. Normalize and validate edges
        $normalizedEdges = $this->normalizeEdges($edges);

        // 2. Validate tree structure (detect cycles and single-parent constraints)
        $this->validateTreeStructure($normalizedEdges);

        // 3. Normalize sales data
        $normalizedSales = $this->normalizeSales($sales);

        // 4. Build upline lookup maps
        // parentOf[child] = ['parent' => parent, 'rate' => rate]
        $parentOf = [];
        $allPeople = [];

        foreach ($normalizedEdges as $edge) {
            $p = $edge['parent'];
            $c = $edge['child'];
            $r = (float) $edge['rate'];

            $parentOf[$c] = [
                'parent' => $p,
                'rate' => $r,
            ];

            $allPeople[$p] = true;
            $allPeople[$c] = true;
        }

        foreach ($normalizedSales as $sale) {
            $allPeople[$sale['salesperson']] = true;
        }

        // 5. Initialize earnings tracker for every person
        $earningsByPerson = [];
        foreach (array_keys($allPeople) as $personName) {
            $earningsByPerson[$personName] = [
                'name' => $personName,
                'personal_sales' => 0.00,
                'override_commission' => 0.00,
                'total_earnings' => 0.00,
                'sales_count' => 0,
                'commissions_received_count' => 0,
                'commissions_received' => [],
            ];
        }

        // Record personal sales
        $totalSales = 0.00;
        foreach ($normalizedSales as $sale) {
            $seller = $sale['salesperson'];
            $amt = (float) $sale['amount'];
            $totalSales += $amt;

            if (isset($earningsByPerson[$seller])) {
                $earningsByPerson[$seller]['personal_sales'] += $amt;
                $earningsByPerson[$seller]['sales_count']++;
            }
        }

        // 6. Process each sale independently and propagate commission up the upline chain
        $allCommissionRecords = [];
        $salesBreakdown = [];
        $totalCommissionPaid = 0.00;

        foreach ($normalizedSales as $saleIndex => $sale) {
            $seller = $sale['salesperson'];
            $originalSaleAmount = (float) $sale['amount'];

            $saleCommissions = [];
            $totalForThisSale = 0.00;

            $currentNode = $seller;
            $currentGeneration = 1;

            // Travel upward through uplines
            while (isset($parentOf[$currentNode])) {
                $uplineInfo = $parentOf[$currentNode];
                $parent = $uplineInfo['parent'];
                $rate = (float) $uplineInfo['rate'];

                $isEligible = ($currentGeneration <= $maxGenerations);
                $commissionAmount = $isEligible
                    ? $this->calculateIndividualCommission($originalSaleAmount, $rate)
                    : 0.00;

                $record = [
                    'sale_index' => $saleIndex,
                    'seller' => $seller,
                    'earner' => $parent,
                    'recipient' => $parent,
                    'original_sale_amount' => round($originalSaleAmount, 2),
                    'generation' => $currentGeneration,
                    'rate' => $rate,
                    'commission' => $commissionAmount,
                    'commission_amount' => $commissionAmount,
                    'is_eligible' => $isEligible,
                    'status' => $isEligible ? 'paid' : 'NOT PAID',
                    'child' => $currentNode,
                    'edge' => "{$parent} → {$currentNode}",
                ];

                $saleCommissions[] = $record;
                $allCommissionRecords[] = $record;

                if ($isEligible) {
                    $totalForThisSale += $commissionAmount;
                    $totalCommissionPaid += $commissionAmount;

                    // Credit upline recipient
                    if (isset($earningsByPerson[$parent])) {
                        $earningsByPerson[$parent]['override_commission'] += $commissionAmount;
                        $earningsByPerson[$parent]['total_earnings'] += $commissionAmount;
                        $earningsByPerson[$parent]['commissions_received_count']++;
                        $earningsByPerson[$parent]['commissions_received'][] = $record;
                    }
                }

                // Advance to parent for next generation
                $currentNode = $parent;
                $currentGeneration++;
            }

            $salesBreakdown[] = [
                'sale_index' => $saleIndex,
                'seller' => $seller,
                'amount' => round($originalSaleAmount, 2),
                'original_sale_amount' => round($originalSaleAmount, 2),
                'total_commission_paid' => round($totalForThisSale, 2),
                'uplines_count' => count($saleCommissions),
                'commissions' => $saleCommissions,
            ];
        }

        // Round summary earnings
        foreach ($earningsByPerson as &$pData) {
            $pData['personal_sales'] = round($pData['personal_sales'], 2);
            $pData['override_commission'] = round($pData['override_commission'], 2);
            $pData['total_earnings'] = round($pData['total_earnings'], 2);
        }
        unset($pData);

        // Sort earnings by override_commission descending, then personal sales descending
        uasort($earningsByPerson, function ($a, $b) {
            if ($b['override_commission'] != $a['override_commission']) {
                return $b['override_commission'] <=> $a['override_commission'];
            }

            return $b['personal_sales'] <=> $a['personal_sales'];
        });

        // Build hierarchical tree nodes representation for UI visualization
        $treeHierarchy = $this->buildTreeHierarchy($normalizedEdges, $earningsByPerson);

        // 7. Build generation_breakdown
        $generationBreakdown = [];
        for ($g = 1; $g <= $maxGenerations; $g++) {
            $generationBreakdown[$g] = [
                'generation' => $g,
                'total_commission' => 0.00,
                'count' => 0,
                'earners' => [],
                'payouts' => [],
            ];
        }

        foreach ($allCommissionRecords as $rec) {
            if ($rec['is_eligible']) {
                $gen = (int) $rec['generation'];
                if (! isset($generationBreakdown[$gen])) {
                    $generationBreakdown[$gen] = [
                        'generation' => $gen,
                        'total_commission' => 0.00,
                        'count' => 0,
                        'earners' => [],
                        'payouts' => [],
                    ];
                }
                $generationBreakdown[$gen]['total_commission'] = round($generationBreakdown[$gen]['total_commission'] + $rec['commission'], 2);
                $generationBreakdown[$gen]['count']++;
                if (! in_array($rec['earner'], $generationBreakdown[$gen]['earners'], true)) {
                    $generationBreakdown[$gen]['earners'][] = $rec['earner'];
                }
                $generationBreakdown[$gen]['payouts'][] = [
                    'seller' => $rec['seller'],
                    'earner' => $rec['earner'],
                    'original_sale_amount' => $rec['original_sale_amount'],
                    'rate' => $rec['rate'],
                    'commission' => $rec['commission'],
                ];
            }
        }

        // 8. Build tree_summary
        $childrenMap = [];
        foreach ($normalizedEdges as $edge) {
            $childrenMap[$edge['parent']][] = $edge['child'];
        }

        $roots = [];
        foreach (array_keys($allPeople) as $person) {
            if (! isset($parentOf[$person])) {
                $roots[] = $person;
            }
        }

        $maxTreeDepth = 0;
        $calcDepth = function ($node, $currDepth) use (&$calcDepth, &$childrenMap, &$maxTreeDepth) {
            if ($currDepth > $maxTreeDepth) {
                $maxTreeDepth = $currDepth;
            }
            foreach ($childrenMap[$node] ?? [] as $child) {
                $calcDepth($child, $currDepth + 1);
            }
        };
        foreach ($roots as $root) {
            $calcDepth($root, 1);
        }

        $treeSummary = [
            'total_nodes' => count($allPeople),
            'total_edges' => count($normalizedEdges),
            'root_nodes' => $roots,
            'max_depth' => $maxTreeDepth,
            'nodes' => array_keys($allPeople),
            'hierarchy' => $treeHierarchy,
        ];

        // 9. Build commission_by_person
        $commissionByPerson = [];
        foreach ($earningsByPerson as $person => $data) {
            $commissionByPerson[$person] = [
                'name' => $person,
                'personal_sales' => $data['personal_sales'],
                'total_commission' => $data['override_commission'],
                'total_earnings' => $data['total_earnings'],
                'sales_count' => $data['sales_count'],
                'commissions_received_count' => $data['commissions_received_count'],
                'breakdown' => array_map(fn ($item) => [
                    'seller' => $item['seller'],
                    'earner' => $item['earner'] ?? $item['recipient'],
                    'original_sale_amount' => $item['original_sale_amount'],
                    'generation' => $item['generation'],
                    'rate' => $item['rate'],
                    'commission' => $item['commission'],
                ], $data['commissions_received']),
            ];
        }

        // 10. Build commission_by_sale
        $commissionBySale = [];
        foreach ($salesBreakdown as $sb) {
            $commissionBySale[] = [
                'sale_index' => $sb['sale_index'],
                'seller' => $sb['seller'],
                'original_sale_amount' => $sb['amount'],
                'amount' => $sb['amount'],
                'total_commission_paid' => $sb['total_commission_paid'],
                'uplines_count' => $sb['uplines_count'],
                'paid_through_generation' => min($maxGenerations, $sb['uplines_count']),
                'cutoff_generation' => $maxGenerations,
                'has_excluded_uplines' => $sb['uplines_count'] > $maxGenerations,
                'commissions' => array_map(fn ($c) => [
                    'seller' => $c['seller'],
                    'earner' => $c['earner'],
                    'recipient' => $c['recipient'],
                    'generation' => $c['generation'],
                    'original_sale_amount' => $c['original_sale_amount'],
                    'rate' => $c['rate'],
                    'commission' => $c['commission'],
                    'commission_amount' => $c['commission_amount'],
                    'is_eligible' => $c['is_eligible'],
                    'status' => $c['status'],
                ], $sb['commissions']),
            ];
        }

        return [
            'model_type' => 'generation_override',
            'max_generations' => $maxGenerations,

            // Core requested return keys
            'total_personal_sales' => round($totalSales, 2),
            'total_commission_generated' => round($totalCommissionPaid, 2),
            'number_of_sales' => count($normalizedSales),
            'number_of_commission_entries' => count(array_filter($allCommissionRecords, fn ($r) => $r['is_eligible'])),
            'commission_by_person' => $commissionByPerson,
            'commission_by_sale' => $commissionBySale,
            'commission_ledger' => $allCommissionRecords,
            'generation_breakdown' => $generationBreakdown,
            'tree_summary' => $treeSummary,

            // Backward compatibility keys
            'total_sales' => round($totalSales, 2),
            'total_commission' => round($totalCommissionPaid, 2),
            'final_commission' => round($totalCommissionPaid, 2),
            'edges' => $normalizedEdges,
            'sales' => $normalizedSales,
            'commissions' => $allCommissionRecords,
            'sales_breakdown' => $salesBreakdown,
            'earnings_by_person' => $earningsByPerson,
            'tree_hierarchy' => $treeHierarchy,
            'all_people' => array_keys($allPeople),
            'people_count' => count($allPeople),
            'edges_count' => count($normalizedEdges),
            'sales_count' => count($normalizedSales),
        ];
    }

    /**
     * Normalize edge inputs.
     */
    protected function normalizeEdges(array $edges): array
    {
        $normalized = [];

        foreach ($edges as $edge) {
            $parent = trim((string) ($edge['parent'] ?? ($edge['parent_name'] ?? '')));
            $child = trim((string) ($edge['child'] ?? ($edge['child_name'] ?? '')));
            $rate = (float) ($edge['rate'] ?? ($edge['applicable_rate'] ?? 0));

            if ($parent === '' || $child === '') {
                continue;
            }

            if ($parent === $child) {
                throw new InvalidArgumentException("A person cannot be their own parent: '{$parent}'.");
            }

            if ($rate < 0 || $rate > 100) {
                throw new InvalidArgumentException("Override rate for '{$parent} → {$child}' must be between 0% and 100%. Received: {$rate}%.");
            }

            $normalized[] = [
                'parent' => $parent,
                'child' => $child,
                'rate' => $rate,
            ];
        }

        if (empty($normalized)) {
            throw new InvalidArgumentException('At least one parent-child relationship edge is required.');
        }

        return $normalized;
    }

    /**
     * Validate tree structure: single parent rule and no cycles.
     */
    protected function validateTreeStructure(array $edges): void
    {
        $parentMap = [];

        foreach ($edges as $edge) {
            $child = $edge['child'];
            $parent = $edge['parent'];

            if (isset($parentMap[$child]) && $parentMap[$child] !== $parent) {
                throw new InvalidArgumentException("Invalid tree: Person '{$child}' has multiple distinct parents ('{$parentMap[$child]}' and '{$parent}'). In a hierarchical tree, each person must have at most one direct parent.");
            }

            $parentMap[$child] = $parent;
        }

        // Cycle detection via DFS / path tracking
        foreach (array_keys($parentMap) as $child) {
            $visited = [];
            $curr = $child;

            while (isset($parentMap[$curr])) {
                $visited[$curr] = true;
                $curr = $parentMap[$curr];

                if (isset($visited[$curr])) {
                    throw new InvalidArgumentException("Cycle detected in network hierarchy involving '{$curr}'. A salesperson cannot be an upline of themselves.");
                }
            }
        }
    }

    /**
     * Normalize sales inputs.
     */
    protected function normalizeSales(array $sales): array
    {
        $normalized = [];

        foreach ($sales as $sale) {
            $person = trim((string) ($sale['salesperson'] ?? ($sale['salesperson_name'] ?? ($sale['person'] ?? ''))));
            $amount = (float) ($sale['amount'] ?? ($sale['sales'] ?? 0));

            if ($person === '') {
                continue;
            }

            if ($amount < 0) {
                throw new InvalidArgumentException("Sale amount for '{$person}' cannot be negative.");
            }

            $normalized[] = [
                'salesperson' => $person,
                'amount' => round($amount, 2),
            ];
        }

        if (empty($normalized)) {
            throw new InvalidArgumentException('At least one personal sale is required to calculate commissions.');
        }

        return $normalized;
    }

    /**
     * Build nested tree hierarchy for graph visualization.
     */
    protected function buildTreeHierarchy(array $edges, array $earnings): array
    {
        $childrenMap = [];
        $parentsMap = [];
        $edgeRateMap = [];

        foreach ($edges as $edge) {
            $p = $edge['parent'];
            $c = $edge['child'];
            $childrenMap[$p][] = $c;
            $parentsMap[$c] = $p;
            $edgeRateMap["{$p}→{$c}"] = $edge['rate'];
        }

        // Find root nodes (nodes that have no parent)
        $allNodes = array_unique(array_merge(array_keys($childrenMap), array_keys($parentsMap)));
        $roots = array_values(array_filter($allNodes, fn ($node) => ! isset($parentsMap[$node])));

        // Recursive tree builder
        $buildSubtree = function ($nodeName, $depth = 1) use (&$buildSubtree, &$childrenMap, &$edgeRateMap, &$earnings, &$parentsMap): array {
            $children = $childrenMap[$nodeName] ?? [];
            $childTrees = [];

            foreach ($children as $cName) {
                $rate = $edgeRateMap["{$nodeName}→{$cName}"] ?? 0.0;
                $childSub = $buildSubtree($cName, $depth + 1);
                $childSub['rate_from_parent'] = $rate;
                $childTrees[] = $childSub;
            }

            return [
                'name' => $nodeName,
                'depth' => $depth,
                'parent' => $parentsMap[$nodeName] ?? null,
                'rate_from_parent' => 0.0,
                'is_leaf' => empty($childTrees),
                'personal_sales' => $earnings[$nodeName]['personal_sales'] ?? 0.0,
                'override_commission' => $earnings[$nodeName]['override_commission'] ?? 0.0,
                'total_earnings' => $earnings[$nodeName]['total_earnings'] ?? 0.0,
                'children' => $childTrees,
            ];
        };

        $tree = [];
        foreach ($roots as $rootName) {
            $tree[] = $buildSubtree($rootName, 1);
        }

        return $tree;
    }

    /**
     * Calculate and sync directly with a CommissionModel instance.
     */
    public function calculateFromModel(CommissionModel $model): array
    {
        // If normalized edges exist, use them
        if ($model->edges()->exists()) {
            $edges = [];
            foreach ($model->edges()->with(['parentNode', 'childNode'])->get() as $edge) {
                if ($edge->parentNode && $edge->childNode) {
                    $edges[] = [
                        'parent' => $edge->parentNode->name,
                        'child' => $edge->childNode->name,
                        'rate' => (float) $edge->override_rate,
                    ];
                }
            }

            $sales = [];
            foreach ($model->nodes()->where('personal_sale', '>', 0)->get() as $node) {
                $sales[] = [
                    'salesperson' => $node->name,
                    'amount' => (float) $node->personal_sale,
                ];
            }

            return $this->calculate($edges, $sales, $model->max_generations ?? 5);
        }

        $edges = [];
        foreach ($model->relationships as $rel) {
            $edges[] = [
                'parent' => $rel->parent_name,
                'child' => $rel->child_name,
                'rate' => (float) $rel->rate,
            ];
        }

        $sales = [];
        foreach ($model->overrideSales as $sale) {
            $sales[] = [
                'salesperson' => $sale->salesperson_name,
                'amount' => (float) $sale->amount,
            ];
        }

        return $this->calculate($edges, $sales, $model->max_generations ?? 5);
    }

    /**
     * Calculate and persist results into CommissionModel and its associated records
     * inside a database transaction. If any part fails, the entire transaction is rolled back.
     *
     * Persists:
     * 1. Commission model (including calculation results snapshot & summary totals)
     * 2. All nodes (in model_nodes with personal sales and hierarchy)
     * 3. All parent-child relationships (in model_edges and model_nodes.parent_id)
     * 4. All override percentages (in model_edges.override_rate)
     * 5. All personal sales (in model_nodes.personal_sale and override_sales)
     * 6. Maximum generations (in commission_models.max_generations)
     * 7. Calculation results (in commission_models.calculation_results)
     * 8. Commission ledger (in commission_ledger with Model ID, Sale Node, Earner Node, Generation, Original Sale Amount, Rate Applied, Commission Amount, is_eligible, status)
     */
    public function persistModel(
        CommissionModel $model,
        array $edges,
        array $sales,
        int $maxGenerations = 5
    ): CommissionModel {
        return DB::transaction(function () use ($model, $edges, $sales, $maxGenerations) {
            $results = $this->calculate($edges, $sales, $maxGenerations);

            // 1. Save Commission model + calculation results + maximum generations + totals
            $model->update([
                'model_type' => 'generation_override',
                'max_generations' => $maxGenerations,
                'total_sales' => $results['total_personal_sales'],
                'total_potential_commission' => $results['total_commission_generated'],
                'final_commission' => $results['total_commission_generated'],
                'number_of_levels' => $results['tree_summary']['max_depth'] ?? count($results['tree_hierarchy']),
                'calculation_results' => $results,
            ]);

            // 2. Sync legacy tables for backward compatibility
            $model->relationships()->delete();
            foreach ($results['edges'] as $edge) {
                $model->relationships()->create([
                    'parent_name' => $edge['parent'],
                    'child_name' => $edge['child'],
                    'rate' => $edge['rate'],
                ]);
            }

            $model->overrideSales()->delete();
            foreach ($results['sales'] as $sale) {
                $model->overrideSales()->create([
                    'salesperson_name' => $sale['salesperson'],
                    'amount' => $sale['amount'],
                ]);
            }

            $model->overrideCommissions()->delete();
            foreach ($results['commission_ledger'] as $comm) {
                $model->overrideCommissions()->create([
                    'seller_name' => $comm['seller'],
                    'sale_amount' => $comm['original_sale_amount'],
                    'recipient_name' => $comm['earner'] ?? $comm['recipient'],
                    'child_name' => $comm['child'] ?? '',
                    'generation' => $comm['generation'],
                    'rate' => $comm['rate'],
                    'commission_amount' => $comm['commission'],
                    'is_eligible' => $comm['is_eligible'],
                ]);
            }

            // 3. Sync normalized model_nodes, model_edges, and commission_ledger
            // Delete existing records in child-to-parent order to respect foreign keys
            $model->ledger()->delete();
            $model->edges()->delete();
            $model->nodes()->delete();

            // Personal sales map
            $salesMap = [];
            foreach ($results['sales'] as $sale) {
                $salesMap[$sale['salesperson']] = (float) $sale['amount'];
            }

            // Relationship lookups
            $parentLookup = [];
            $hasChildren = [];
            foreach ($results['edges'] as $edge) {
                $parentLookup[$edge['child']] = $edge['parent'];
                $hasChildren[$edge['parent']] = true;
            }

            // Create all nodes first with parent_id = null
            $nodeInstances = [];
            $allPeople = $results['all_people'] ?? [];
            foreach ($allPeople as $personName) {
                $personalSale = $salesMap[$personName] ?? 0.00;
                $nodeType = ! isset($parentLookup[$personName])
                    ? 'root'
                    : (! isset($hasChildren[$personName]) ? 'salesperson' : 'member');

                $nodeInstances[$personName] = $model->nodes()->create([
                    'name' => $personName,
                    'parent_id' => null,
                    'node_type' => $nodeType,
                    'personal_sale' => $personalSale,
                ]);
            }

            // Set parent_id on child nodes & create model_edges
            foreach ($results['edges'] as $edge) {
                $parentNode = $nodeInstances[$edge['parent']] ?? null;
                $childNode = $nodeInstances[$edge['child']] ?? null;

                if ($parentNode && $childNode) {
                    $childNode->update(['parent_id' => $parentNode->id]);

                    $model->edges()->create([
                        'parent_node_id' => $parentNode->id,
                        'child_node_id' => $childNode->id,
                        'override_rate' => $edge['rate'],
                    ]);
                }
            }

            // Create auditable commission ledger entries preserving:
            // Model ID, Sale Node, Earner Node, Generation, Original Sale Amount, Rate Applied, Commission Amount, is_eligible, status
            foreach ($results['commission_ledger'] as $comm) {
                $saleNode = $nodeInstances[$comm['seller']] ?? null;
                $earnerNode = $nodeInstances[$comm['earner'] ?? $comm['recipient']] ?? null;

                if ($saleNode && $earnerNode) {
                    $model->ledger()->create([
                        'sale_node_id' => $saleNode->id,
                        'earner_node_id' => $earnerNode->id,
                        'generation' => (int) $comm['generation'],
                        'sale_amount' => $comm['original_sale_amount'],
                        'rate_applied' => $comm['rate'],
                        'commission_amount' => $comm['commission'],
                        'is_eligible' => (bool) $comm['is_eligible'],
                        'status' => $comm['status'] ?? ($comm['is_eligible'] ? 'paid' : 'NOT PAID'),
                    ]);
                }
            }

            return $model->fresh([
                'nodes',
                'edges',
                'ledger.saleNode',
                'ledger.earnerNode',
                'relationships',
                'overrideSales',
                'overrideCommissions',
            ]);
        });
    }

    /**
     * Load historical calculation results for an existing commission model
     * without recalculating, preserving exact historical audit integrity.
     * Historical models do not change when calculation rules change later.
     */
    public function loadHistoricalResults(CommissionModel $model): array
    {
        // 1. If calculation_results JSON snapshot was saved on the model, return it directly
        if (! empty($model->calculation_results) && is_array($model->calculation_results)) {
            $results = $model->calculation_results;
            $results['is_historical'] = true;

            return $results;
        }

        // 2. Otherwise reconstruct results from saved ledger and nodes without recalculating
        $model->loadMissing([
            'nodes',
            'edges.parentNode',
            'edges.childNode',
            'ledger.saleNode',
            'ledger.earnerNode',
        ]);

        $ledgerEntries = $model->ledger;
        $nodes = $model->nodes;
        $maxGen = $model->max_generations ?? 5;

        // Build commission_ledger array
        $allCommissionRecords = [];
        $salesGrouped = [];

        foreach ($ledgerEntries as $entry) {
            $sellerName = $entry->saleNode?->name ?? 'Unknown';
            $earnerName = $entry->earnerNode?->name ?? 'Unknown';
            $isEligible = (bool) $entry->is_eligible;
            $status = $entry->status;

            $record = [
                'seller' => $sellerName,
                'earner' => $earnerName,
                'recipient' => $earnerName,
                'original_sale_amount' => (float) $entry->sale_amount,
                'generation' => (int) $entry->generation,
                'rate' => (float) $entry->rate_applied,
                'commission' => (float) $entry->commission_amount,
                'commission_amount' => (float) $entry->commission_amount,
                'is_eligible' => $isEligible,
                'status' => $status,
                'edge' => "{$earnerName} → {$sellerName}",
            ];

            $allCommissionRecords[] = $record;
            $salesGrouped[$sellerName][] = $record;
        }

        // Build commission_by_person from nodes and ledger
        $commissionByPerson = [];
        foreach ($nodes as $node) {
            $pName = $node->name;
            $pSales = (float) $node->personal_sale;
            $earnedRecords = $ledgerEntries->where('earner_node_id', $node->id)->where('is_eligible', true);
            $totalCommission = $earnedRecords->sum('commission_amount');

            $commissionByPerson[$pName] = [
                'name' => $pName,
                'personal_sales' => $pSales,
                'total_commission' => (float) $totalCommission,
                'total_earnings' => (float) $totalCommission,
                'sales_count' => $pSales > 0 ? 1 : 0,
                'commissions_received_count' => $earnedRecords->count(),
                'breakdown' => $earnedRecords->map(fn ($r) => [
                    'seller' => $r->saleNode?->name ?? '',
                    'earner' => $pName,
                    'original_sale_amount' => (float) $r->sale_amount,
                    'generation' => (int) $r->generation,
                    'rate' => (float) $r->rate_applied,
                    'commission' => (float) $r->commission_amount,
                ])->values()->all(),
            ];
        }

        // Build commission_by_sale from grouped sales
        $commissionBySale = [];
        $saleIndex = 0;
        foreach ($salesGrouped as $seller => $comms) {
            $saleAmt = $comms[0]['original_sale_amount'] ?? 0.00;
            $eligibleComms = array_filter($comms, fn ($c) => $c['is_eligible']);
            $totalCommPaid = array_sum(array_column($eligibleComms, 'commission'));

            $commissionBySale[] = [
                'sale_index' => $saleIndex++,
                'seller' => $seller,
                'original_sale_amount' => $saleAmt,
                'amount' => $saleAmt,
                'total_commission_paid' => (float) $totalCommPaid,
                'uplines_count' => count($comms),
                'paid_through_generation' => min($maxGen, count($comms)),
                'cutoff_generation' => $maxGen,
                'has_excluded_uplines' => count($comms) > $maxGen,
                'commissions' => $comms,
            ];
        }

        // Build generation breakdown
        $generationBreakdown = [];
        for ($g = 1; $g <= $maxGen; $g++) {
            $generationBreakdown[$g] = [
                'generation' => $g,
                'total_commission' => 0.00,
                'count' => 0,
                'earners' => [],
                'payouts' => [],
            ];
        }
        foreach ($allCommissionRecords as $rec) {
            if ($rec['is_eligible']) {
                $g = (int) $rec['generation'];
                if (isset($generationBreakdown[$g])) {
                    $generationBreakdown[$g]['total_commission'] += $rec['commission'];
                    $generationBreakdown[$g]['count']++;
                    if (! in_array($rec['earner'], $generationBreakdown[$g]['earners'], true)) {
                        $generationBreakdown[$g]['earners'][] = $rec['earner'];
                    }
                    $generationBreakdown[$g]['payouts'][] = [
                        'seller' => $rec['seller'],
                        'earner' => $rec['earner'],
                        'original_sale_amount' => $rec['original_sale_amount'],
                        'rate' => $rec['rate'],
                        'commission' => $rec['commission'],
                    ];
                }
            }
        }

        $edges = $model->edges->map(fn ($e) => [
            'parent' => $e->parentNode?->name ?? '',
            'child' => $e->childNode?->name ?? '',
            'rate' => (float) $e->override_rate,
        ])->all();

        $sales = $nodes->where('personal_sale', '>', 0)->map(fn ($n) => [
            'salesperson' => $n->name,
            'amount' => (float) $n->personal_sale,
        ])->values()->all();

        $treeHierarchy = $this->buildTreeHierarchy($edges, $commissionByPerson);

        return [
            'model_type' => 'generation_override',
            'max_generations' => $maxGen,
            'total_personal_sales' => (float) $model->total_sales,
            'total_commission_generated' => (float) $model->final_commission,
            'number_of_sales' => count($commissionBySale),
            'number_of_commission_entries' => count(array_filter($allCommissionRecords, fn ($r) => $r['is_eligible'])),
            'commission_by_person' => $commissionByPerson,
            'commission_by_sale' => $commissionBySale,
            'commission_ledger' => $allCommissionRecords,
            'generation_breakdown' => $generationBreakdown,
            'edges' => $edges,
            'sales' => $sales,
            'tree_hierarchy' => $treeHierarchy,
            'tree_summary' => [
                'total_nodes' => $nodes->count(),
                'total_edges' => $model->edges->count(),
                'max_depth' => (int) $model->number_of_levels,
                'nodes' => $nodes->pluck('name')->all(),
                'hierarchy' => $treeHierarchy,
            ],
            'is_historical' => true,
        ];
    }

    /**
     * Build diagram data for the Hierarchy Diagram & Commission Flow visual component.
     */
    public function buildDiagramData(CommissionModel $model, array $results): array
    {
        $edgesList = $results['edges'] ?? [];
        $personList = $results['commission_by_person'] ?? [];
        $ledger = collect($results['commission_ledger'] ?? []);
        $maxGen = (int) ($results['max_generations'] ?? $model->max_generations ?? 5);

        // Map parent-child relationships
        $childrenOf = [];
        $parentOf = [];

        foreach ($edgesList as $edge) {
            $p = $edge['parent'];
            $c = $edge['child'];
            $childrenOf[$p][] = $c;
            $parentOf[$c] = $p;
        }

        // Helper to get all descendants of a person
        $getDescendants = function (string $person) use (&$getDescendants, &$childrenOf): array {
            $desc = [];
            foreach ($childrenOf[$person] ?? [] as $child) {
                $desc[] = $child;
                $desc = array_merge($desc, $getDescendants($child));
            }

            return $desc;
        };

        // Collect all distinct people
        $allPeople = [];
        foreach ($personList as $pName => $pData) {
            $allPeople[$pName] = true;
        }
        foreach ($edgesList as $edge) {
            $allPeople[$edge['parent']] = true;
            $allPeople[$edge['child']] = true;
        }

        $nodes = [];
        foreach (array_keys($allPeople) as $pName) {
            $parent = $parentOf[$pName] ?? null;
            $isLeaf = empty($childrenOf[$pName]);
            $pData = $personList[$pName] ?? [];
            $sales = (float) ($pData['personal_sales'] ?? 0);
            $comm = (float) ($pData['total_commission'] ?? 0);

            $role = $parent === null
                ? 'Top Leader / Root'
                : ($isLeaf ? 'Salesperson / Leaf' : 'Upline Leader');

            $nodes[$pName] = [
                'name' => $pName,
                'parent' => $parent,
                'sales' => $sales,
                'commission' => $comm,
                'is_leaf' => $isLeaf,
                'role' => $role,
            ];
        }

        // Build edges with override rate and total override commission paid along this branch
        $edges = [];
        foreach ($edgesList as $edge) {
            $p = $edge['parent'];
            $c = $edge['child'];
            $rate = (float) $edge['rate'];

            // Branch people: child + child's downlines
            $branchPeople = array_merge([$c], $getDescendants($c));

            $branchCommission = $ledger->filter(function ($row) use ($p, $branchPeople) {
                return ($row['earner'] ?? '') === $p
                    && in_array($row['seller'] ?? '', $branchPeople)
                    && ! empty($row['is_eligible']);
            })->sum('commission_amount');

            $edges[] = [
                'from' => $p,
                'to' => $c,
                'pct' => $rate,
                'commAmt' => round((float) $branchCommission, 2),
                'branch' => 'override',
            ];
        }

        $roots = array_filter($nodes, fn ($n) => empty($n['parent']));
        $topLeader = ! empty($roots) ? array_key_first($roots) : (array_key_first($nodes) ?? 'A');
        $topLeaderComm = $nodes[$topLeader]['commission'] ?? 0;

        return [
            'model_type' => 'generation_override',
            'model_id' => $model->id,
            'model_name' => $model->name,
            'theme' => 'emerald',
            'title' => 'Hierarchy Diagram & Commission Flow',
            'badge_text' => 'Model 2 · Generation Override',
            'subtitle' => 'Visual tree diagram illustrating custom override rates per edge, unreduced sale base, and upward multi-generation commission propagation.',
            'payout_label' => 'Total Network Override:',
            'total_payout' => (float) ($results['total_commission_generated'] ?? $model->final_commission),
            'top_leader' => $topLeader,
            'top_leader_commission' => (float) $topLeaderComm,
            'max_generations' => $maxGen,
            'nodes' => array_values($nodes),
            'edges' => $edges,
        ];
    }
}
