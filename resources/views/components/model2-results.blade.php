{{-- 
    Model 2 Results Component:
    Renders:
    1. SUMMARY (Total Personal Sales, Total Commission Generated, Number of Sales, Number of Commission Entries, Maximum Generations, Generation Limit Rule)
    2. COMMISSION BY PERSON (Person, Personal Sales, Commission Earned, Number of Override Commissions)
    3. VISUAL COMMISSION FLOW (Per-sale vertical cards with downward arrows, constant original sale base, upline rates, eligible 'paid' vs excluded 'NOT PAID')
    4. HIERARCHICAL COMMISSION TREE (Tree hierarchy cards, leaf personal sales, relationship override rates, ASCII tree)
    5. COMMISSION LEDGER (Sale Person, Sale Amount, Earner, Generation, Rate, Commission, Status - unmerged per sale, generated from original sale amount)
--}}

@php
    $hasResults = isset($results) && is_array($results);
    $summary = $hasResults ? $results : [];
    $totalSales = $summary['total_personal_sales'] ?? ($summary['total_sales'] ?? 0);
    $totalComm = $summary['total_commission_generated'] ?? ($summary['total_commission'] ?? 0);
    $numSales = $summary['number_of_sales'] ?? ($summary['sales_count'] ?? count($summary['sales'] ?? []));
    $numEntries = $summary['number_of_commission_entries'] ?? count(array_filter($summary['commissions'] ?? ($summary['commission_ledger'] ?? []), fn($c) => !isset($c['is_eligible']) || $c['is_eligible']));
    $maxGen = $summary['max_generations'] ?? 5;
    $personList = $summary['commission_by_person'] ?? ($summary['earnings_by_person'] ?? []);
    $salesList = $summary['commission_by_sale'] ?? ($summary['sales_breakdown'] ?? []);
    $treeHierarchy = $summary['tree_hierarchy'] ?? ($summary['tree_summary']['hierarchy'] ?? ($summary['hierarchy'] ?? []));

    // Dynamic ASCII Tree Generator Closure
    $renderAsciiTree = function($nodes, $prefix = '') use (&$renderAsciiTree) {
        $out = '';
        $count = count($nodes);
        foreach ($nodes as $i => $n) {
            $isLast = ($i === $count - 1);
            $connector = $isLast ? '└── ' : '├── ';
            $childPrefix = $prefix . ($isLast ? '    ' : '│   ');
            $rateStr = !empty($n['rate_from_parent']) ? ' [' . ($n['rate_from_parent'] == intval($n['rate_from_parent']) ? number_format($n['rate_from_parent'], 0) : number_format($n['rate_from_parent'], 1)) . '%]' : '';
            $isLeaf = !empty($n['is_leaf']) || empty($n['children']);
            $saleVal = $n['personal_sales'] ?? 0;
            $saleStr = ($isLeaf && $saleVal > 0)
                ? ' • ₹' . ($saleVal == intval($saleVal) ? number_format($saleVal, 0) : number_format($saleVal, 2)) . ' sale'
                : '';
            $out .= $prefix . $connector . $n['name'] . $rateStr . $saleStr . "\n";
            if (!empty($n['children'])) {
                $out .= $renderAsciiTree($n['children'], $childPrefix);
            }
        }
        return $out;
    };

    $asciiTreeOutput = '';
    foreach ($treeHierarchy as $rootNode) {
        $asciiTreeOutput .= $rootNode['name'] . "\n" . $renderAsciiTree($rootNode['children'] ?? [], '');
    }

    $diagramData = $treeData ?? ($summary['tree_data'] ?? null);
    if (empty($diagramData) && (!empty($summary['edges']) || !empty($summary['tree_hierarchy']))) {
        $dummyModel = new \App\Models\CommissionModel([
            'id' => $summary['model_id'] ?? 0,
            'name' => $summary['model_name'] ?? 'Generation Override',
            'model_type' => 'generation_override',
            'max_generations' => $maxGen,
            'final_commission' => $totalComm,
        ]);
        $diagramData = app(\App\Services\OverrideCommissionCalculator::class)->buildDiagramData($dummyModel, $summary);
    }
@endphp

<div id="model2-results-container" class="space-y-8">
    <!-- ========================================================================= -->
    <!-- 1. SUMMARY -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">SUMMARY</h3>
            </div>
            <span class="text-xs font-medium text-slate-500">
                Model 2 • Level / Generation Override
            </span>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Total Personal Sales -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                        Total Personal Sales
                    </span>
                    <div id="res-total-personal-sales" class="text-2xl font-bold font-mono text-slate-900 mt-1.5">
                        ₹{{ $totalSales == intval($totalSales) ? number_format($totalSales, 0) : number_format($totalSales, 2) }}
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Sum of original sales</span>
                </div>

                <!-- Total Commission Generated -->
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">
                        Total Commission Generated
                    </span>
                    <div id="res-total-commission-generated" class="text-2xl font-bold font-mono text-emerald-700 mt-1.5">
                        ₹{{ $totalComm == intval($totalComm) ? number_format($totalComm, 0) : number_format($totalComm, 2) }}
                    </div>
                    <span class="text-[11px] text-emerald-700/80 mt-1 block">Total overrides paid</span>
                </div>

                <!-- Number of Sales -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">
                        Number of Sales
                    </span>
                    <div id="res-number-of-sales" class="text-2xl font-bold font-mono text-slate-800 mt-1.5">
                        {{ $numSales }}
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Sales transactions</span>
                </div>

                <!-- Number of Commission Entries -->
                <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-200">
                    <span class="text-[11px] font-bold text-indigo-800 uppercase tracking-wider block">
                        Number of Commission Entries
                    </span>
                    <div id="res-number-of-commission-entries" class="text-2xl font-bold font-mono text-indigo-700 mt-1.5">
                        {{ $numEntries }}
                    </div>
                    <span class="text-[11px] text-indigo-700/80 mt-1 block">Eligible payouts</span>
                </div>

                <!-- Maximum Generations -->
                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider block">
                            Maximum Generations
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-200 text-amber-900 font-mono">Limit</span>
                    </div>
                    <div id="res-maximum-generations" class="text-2xl font-bold font-mono text-amber-800 mt-1.5">
                        {{ $maxGen }}
                    </div>
                    <span class="text-[11px] text-amber-800 font-semibold mt-1 block">
                        Paid through generation {{ $maxGen }}
                    </span>
                    <span class="text-[10px] text-amber-700/80 mt-0.5 block">
                        Generation {{ $maxGen + 1 }}+ excluded by model limit
                    </span>
                </div>
            </div>

            <!-- Generation Limit Rule Callout Banner -->
            <div id="res-gen-limit-banner" class="mt-4 p-4 rounded-xl bg-amber-50/80 border border-amber-200 text-xs text-amber-950 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-start sm:items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-600 text-white font-mono font-bold flex items-center justify-center text-xs flex-shrink-0 shadow-xs mt-0.5 sm:mt-0">
                        {{ $maxGen }}
                    </span>
                    <div>
                        <div class="font-bold text-amber-900 flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200 font-semibold text-[11px]">
                                Paid through generation {{ $maxGen }}
                            </span>
                            <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 border border-rose-200 font-semibold text-[11px]">
                                Generation {{ $maxGen + 1 }}+ excluded by model limit
                            </span>
                        </div>
                        <div class="text-[11px] text-amber-800/90 mt-1">
                            <strong>Rule:</strong> The generation limit applies independently to every sale. Do not interpret max_generations as maximum tree depth. A tree can contain 20 levels while only {{ $maxGen }} uplines receive commission for each sale.
                        </div>
                    </div>
                </div>
                <div class="text-[10px] font-mono font-bold text-amber-800/80 bg-white/70 px-3 py-1.5 rounded-lg border border-amber-200 flex-shrink-0">
                    Per-Sale Limit: {{ $maxGen }} Generations
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 2. HIERARCHY DIAGRAM & COMMISSION FLOW -->
    <!-- ========================================================================= -->
    @if(!empty($diagramData))
        <x-hierarchy-diagram-flow :treeData="$diagramData" />
    @else
        <x-visual-tree-viewer :treeData="$summary" />
    @endif

    <!-- ========================================================================= -->
    <!-- 3. COMMISSION BY PERSON -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">COMMISSION BY PERSON</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Summary of personal volume, total commissions earned, and number of override commissions received.</p>
            </div>
            <span id="res-person-count-badge" class="text-xs font-mono text-slate-500 font-semibold">
                {{ count($personList) }} Persons
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-5">Person</th>
                        <th class="py-3 px-5 text-right">Personal Sales</th>
                        <th class="py-3 px-5 text-right font-bold text-emerald-800">Commission Earned</th>
                        <th class="py-3 px-5 text-center">Number of Override Commissions</th>
                    </tr>
                </thead>
                <tbody id="res-commission-by-person-tbody" class="divide-y divide-slate-100">
                    @forelse($personList as $p)
                        @php
                            $pSale = $p['personal_sales'] ?? 0;
                            $pComm = $p['total_commission'] ?? ($p['override_commission'] ?? 0);
                            $pCount = $p['commissions_received_count'] ?? (isset($p['breakdown']) ? count($p['breakdown']) : 0);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-5 font-bold text-slate-900 flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold font-mono">
                                    {{ substr($p['name'], 0, 2) }}
                                </span>
                                <span>{{ $p['name'] }}</span>
                            </td>
                            <td class="py-3 px-5 text-right font-mono text-slate-700 font-medium">
                                ₹{{ $pSale == intval($pSale) ? number_format($pSale, 0) : number_format($pSale, 2) }}
                            </td>
                            <td class="py-3 px-5 text-right font-mono font-bold text-emerald-700 text-sm">
                                ₹{{ $pComm == intval($pComm) ? number_format($pComm, 0) : number_format($pComm, 2) }}
                            </td>
                            <td class="py-3 px-5 text-center font-mono">
                                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pCount > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $pCount }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400 italic">
                                Click "Calculate Overrides" to view earnings by person.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 3. VISUAL COMMISSION FLOW -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">VISUAL COMMISSION FLOW</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Step-by-step override propagation showing constant original sale base and per-sale generation limits.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                    Paid through generation {{ $maxGen }}
                </span>
                <span class="px-2.5 py-1 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold">
                    Generation {{ $maxGen + 1 }}+ excluded by model limit
                </span>
            </div>
        </div>

        <!-- Explanatory Anti-Pattern & Limit Callout -->
        <div class="p-6 bg-slate-50/50 border-b border-slate-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 text-xs">
                    <div class="flex items-center gap-2 font-bold text-emerald-900 mb-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Correct Calculation Logic (Same Original Sale Base Used Everywhere)</span>
                    </div>
                    <p class="text-emerald-800 text-[11px] leading-relaxed">
                        Every upline calculates its override directly from the <strong>original personal sale amount</strong>:
                        <code class="px-1.5 py-0.5 rounded bg-emerald-100/90 font-mono font-bold text-emerald-900 ml-1">commission = original_sale × rate / 100</code>
                    </p>
                </div>
                <div class="p-4 rounded-xl bg-rose-50/80 border border-rose-200 text-xs">
                    <div class="flex items-center gap-2 font-bold text-rose-900 mb-1">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Independent Per-Sale Limit</span>
                    </div>
                    <p class="text-rose-800 text-[11px] leading-relaxed">
                        Calculation is <strong>NOT</strong> compounded (<code class="px-1 py-0.5 rounded bg-rose-100 font-mono text-[10px]">₹200 → ₹10 → ₹6 → ₹4 (Incorrect)</code>).
                        Uplines within generation {{ $maxGen }} are <strong>paid</strong>; Generation {{ $maxGen + 1 }}+ are <strong>NOT PAID</strong>.
                    </p>
                </div>
            </div>
        </div>

        <div id="res-commission-flow-container" class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            @forelse($salesList as $sIdx => $sale)
                @php
                    $seller = $sale['seller'] ?? '';
                    $saleAmt = $sale['amount'] ?? ($sale['original_sale_amount'] ?? 0);
                    $commissions = $sale['commissions'] ?? [];
                    $fmtAmt = $saleAmt == intval($saleAmt) ? number_format($saleAmt, 0) : number_format($saleAmt, 2);
                    $hasExcluded = count(array_filter($commissions, fn($c) => isset($c['is_eligible']) && !$c['is_eligible'])) > 0;
                @endphp
                <div class="border border-slate-200 rounded-2xl bg-white shadow-xs overflow-hidden flex flex-col justify-between">
                    <!-- Flow Header -->
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-mono font-bold text-xs flex items-center justify-center">
                                #{{ $sIdx + 1 }}
                            </span>
                            <span class="font-bold text-slate-800 text-xs">Sale: <strong class="text-emerald-700 font-mono">{{ $seller }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Paid through generation {{ min($maxGen, count($commissions)) }}
                            </span>
                            @if($hasExcluded)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Gen {{ $maxGen + 1 }}+ excluded
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Flow Body -->
                    <div class="p-5 flex-1 space-y-3">
                        <!-- Constant Base Highlight Box -->
                        <div class="p-3.5 rounded-xl bg-slate-900 text-white shadow-sm space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Original Sale: ₹{{ $fmtAmt }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">Constant Base</span>
                            </div>
                            <div class="space-y-1.5 text-xs font-mono text-slate-200">
                                @forelse($commissions as $comm)
                                    @php
                                        $cEarner = $comm['earner'] ?? ($comm['recipient'] ?? '');
                                        $cRate = $comm['rate'] ?? 0;
                                        $cGen = $comm['generation'] ?? 1;
                                        $cVal = $comm['commission'] ?? ($comm['commission_amount'] ?? 0);
                                        $isEligible = !isset($comm['is_eligible']) || $comm['is_eligible'];
                                        $cRateFmt = $cRate == intval($cRate) ? number_format($cRate, 0) : number_format($cRate, 1);
                                        $cValFmt = $cVal == intval($cVal) ? number_format($cVal, 0) : number_format($cVal, 2);
                                    @endphp
                                    <div class="flex items-center justify-between py-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold {{ $isEligible ? 'text-emerald-300' : 'text-slate-400 line-through' }}">
                                                {{ $cEarner }}: {{ $cRateFmt }}% = ₹{{ $cValFmt }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-normal">
                                                ({{ $cEarner }} = Generation {{ $cGen }})
                                            </span>
                                        </div>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $isEligible ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800' }}">
                                            {{ $isEligible ? 'paid' : 'NOT PAID' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-slate-500 italic text-[11px]">No uplines for this node.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Step Cards with Downward Arrows (↓) -->
                        <div class="pt-2 flex flex-col items-center space-y-2">
                            <!-- Origin Leaf Card -->
                            <div class="w-full p-3 rounded-xl border border-emerald-300 bg-emerald-50/90 text-center shadow-xs">
                                <div class="font-mono font-bold text-sm text-slate-900">{{ $seller }}</div>
                                <div class="font-mono text-xs font-bold text-emerald-700 mt-0.5">₹{{ $fmtAmt }} sale</div>
                                <span class="text-[10px] text-emerald-800 font-semibold uppercase tracking-wider block mt-0.5">Origin Seller</span>
                            </div>

                            @foreach($commissions as $comm)
                                @php
                                    $cEarner = $comm['earner'] ?? ($comm['recipient'] ?? '');
                                    $cGen = $comm['generation'] ?? 1;
                                    $cRate = $comm['rate'] ?? 0;
                                    $cVal = $comm['commission'] ?? ($comm['commission_amount'] ?? 0);
                                    $isEligible = !isset($comm['is_eligible']) || $comm['is_eligible'];
                                    $cRateFmt = $cRate == intval($cRate) ? number_format($cRate, 0) : number_format($cRate, 1);
                                    $cValFmt = $cVal == intval($cVal) ? number_format($cVal, 0) : number_format($cVal, 2);
                                @endphp

                                <!-- Generation Limit Cutoff Divider if transitioning beyond max generations -->
                                @if($cGen == $maxGen + 1)
                                    <div class="w-full my-2 p-2 rounded-xl bg-rose-50 border border-rose-200 text-center">
                                        <div class="text-[11px] font-bold text-rose-900 flex items-center justify-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Generation {{ $maxGen + 1 }}+ excluded by model limit</span>
                                        </div>
                                        <div class="text-[10px] text-rose-700 mt-0.5">
                                            Paid through generation {{ $maxGen }} • Maximum {{ $maxGen }} override tiers reached
                                        </div>
                                    </div>
                                @endif

                                <!-- Downward Arrow Connector -->
                                <div class="flex flex-col items-center py-0.5">
                                    <span class="text-slate-400 font-bold text-lg leading-none select-none">↓</span>
                                    <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest mt-0.5">Passes Upward</span>
                                </div>

                                <!-- Upline Card -->
                                <div class="w-full p-3 rounded-xl border text-center shadow-xs transition-colors {{ $isEligible ? 'border-slate-200 bg-white hover:border-slate-300' : 'border-dashed border-rose-200 bg-rose-50/40 text-slate-400' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-semibold {{ $isEligible ? 'text-slate-500' : 'text-rose-500 font-bold' }} font-mono">
                                            Gen {{ $cGen }}
                                        </span>
                                        <div class="flex items-center gap-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                {{ $cRateFmt }}%
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $isEligible ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' }}">
                                                {{ $isEligible ? 'paid' : 'NOT PAID' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="font-mono font-bold text-sm {{ $isEligible ? 'text-slate-900' : 'text-slate-600' }}">{{ $cEarner }}</div>
                                    
                                    @if($isEligible)
                                        <div class="font-mono text-sm font-extrabold text-emerald-700 mt-1">₹{{ $cValFmt }}</div>
                                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                            ₹{{ $fmtAmt }} × {{ $cRateFmt }}%
                                        </div>
                                    @else
                                        <div class="font-mono text-sm font-bold text-slate-400 line-through mt-1">₹0</div>
                                        <div class="text-[10px] text-rose-600 font-semibold mt-0.5">
                                            Generation {{ $cGen }}+ excluded by model limit
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-8 text-center text-slate-400 italic">
                    Click "Calculate Overrides" to view the visual commission-flow diagrams.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 4. HIERARCHICAL COMMISSION TREE -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">HIERARCHICAL COMMISSION TREE</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Network structure displaying each node as a card, leaf personal sales, and relationship override rates.
                </p>
            </div>
            <span class="text-xs text-slate-500 font-medium">
                Leaf Nodes Highlighted in Emerald
            </span>
        </div>

        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Card Tree View -->
            <div class="lg:col-span-7">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Node Cards & Relationships</span>
                </div>
                <div id="res-tree-cards-container" class="space-y-4">
                    @forelse($treeHierarchy as $rootNode)
                        @include('components.model2-tree-node', ['node' => $rootNode])
                    @empty
                        <div class="py-8 text-center text-slate-400 italic">
                            Click "Calculate Overrides" to render hierarchical tree cards.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: ASCII Tree Structure -->
            <div class="lg:col-span-5 flex flex-col">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span>Tree Hierarchy Diagram</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Hierarchy Map</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-900 text-slate-200 font-mono text-xs overflow-x-auto shadow-inner border border-slate-800 flex-1">
                    <pre id="res-tree-ascii-container" class="leading-relaxed font-mono whitespace-pre">{{ $asciiTreeOutput !== '' ? $asciiTreeOutput : "A\n├── B [2%]\n│   ├── E [3%]\n│   │   └── E1 [5%] • ₹200 sale\n│   └── F [5%] • ₹400 sale\n└── C [5%] • ₹300 sale" }}</pre>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Shows all parent-child links with their override rates and leaf personal sales.
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 5. COMMISSION LEDGER -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">COMMISSION LEDGER</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Individual commission calculations per sale transaction. Different sales are never merged.
                </p>
            </div>
            <div class="flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium">
                <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>Calculated from <strong>Original Sale Amount</strong></span>
            </div>
        </div>

        <!-- Ledger Description Banner -->
        <div class="px-6 py-3 bg-slate-50/50 border-b border-slate-100 text-[11px] text-slate-600 flex items-center gap-2">
            <span class="font-bold text-slate-700">Notice:</span>
            <span>All override commissions are generated directly from the <strong>original sale amount</strong> as it moves upward. The sale amount is never reduced. Each personal sale produces independent commission rows.</span>
        </div>

        <div id="res-commission-ledger-container" class="p-6 space-y-6">
            @forelse($salesList as $saleIndex => $sale)
                @php
                    $seller = $sale['seller'] ?? '';
                    $saleAmt = $sale['amount'] ?? ($sale['original_sale_amount'] ?? 0);
                    $commissions = $sale['commissions'] ?? [];
                    $hasExcludedInSale = count(array_filter($commissions, fn($c) => isset($c['is_eligible']) && !$c['is_eligible'])) > 0;
                @endphp
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs bg-white">
                    <!-- Sale Header (Unmerged distinct block) -->
                    <div class="px-4 py-2.5 bg-slate-100/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-mono font-bold text-[10px] uppercase">
                                Sale #{{ $saleIndex + 1 }}
                            </span>
                            <span class="font-bold text-slate-900">Sale Person: <span class="font-mono text-emerald-800 font-bold">{{ $seller }}</span></span>
                            <span class="text-slate-400">•</span>
                            <span class="font-bold text-slate-700">Sale Amount: <span class="font-mono text-slate-900 font-bold">₹{{ $saleAmt == intval($saleAmt) ? number_format($saleAmt, 0) : number_format($saleAmt, 2) }}</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Paid through generation {{ min($maxGen, count($commissions)) }}
                            </span>
                            @if($hasExcludedInSale)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Generation {{ $maxGen + 1 }}+ excluded by model limit
                                </span>
                            @endif
                            <span class="text-slate-500 font-mono">
                                {{ count($commissions) }} upline override(s)
                            </span>
                        </div>
                    </div>

                    <!-- Ledger Rows for this specific sale -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/60 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                    <th class="py-2.5 px-4">Sale Person</th>
                                    <th class="py-2.5 px-4">Sale Amount</th>
                                    <th class="py-2.5 px-4 font-bold text-slate-700">Earner</th>
                                    <th class="py-2.5 px-4 text-center">Generation</th>
                                    <th class="py-2.5 px-4 text-right">Rate</th>
                                    <th class="py-2.5 px-4 text-right font-bold text-emerald-800">Commission</th>
                                    <th class="py-2.5 px-4 text-center font-bold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($commissions as $comm)
                                    @php
                                        $cEarner = $comm['earner'] ?? ($comm['recipient'] ?? '');
                                        $cGen = $comm['generation'] ?? 1;
                                        $cRate = $comm['rate'] ?? 0;
                                        $cVal = $comm['commission'] ?? ($comm['commission_amount'] ?? 0);
                                        $isEligible = !isset($comm['is_eligible']) || $comm['is_eligible'];
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors {{ !$isEligible ? 'opacity-50 bg-rose-50/30' : '' }}">
                                        <td class="py-2.5 px-4 font-mono font-bold text-slate-800">{{ $seller }}</td>
                                        <td class="py-2.5 px-4 font-mono text-slate-600">
                                            ₹{{ $saleAmt == intval($saleAmt) ? number_format($saleAmt, 0) : number_format($saleAmt, 2) }}
                                        </td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900 font-mono text-xs flex items-center gap-1.5">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 border border-slate-200 text-slate-700 inline-flex items-center justify-center text-[10px]">
                                                {{ substr($cEarner, 0, 2) }}
                                            </span>
                                            <span>{{ $cEarner }}</span>
                                        </td>
                                        <td class="py-2.5 px-4 text-center font-mono font-bold text-slate-700">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                Gen {{ $cGen }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono font-medium text-slate-700">
                                            {{ $cRate == intval($cRate) ? number_format($cRate, 0) : number_format($cRate, 1) }}%
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-mono font-bold text-xs {{ $isEligible ? 'text-emerald-700' : 'text-slate-400 line-through' }}">
                                            ₹{{ $cVal == intval($cVal) ? number_format($cVal, 0) : number_format($cVal, 2) }}
                                        </td>
                                        <td class="py-2.5 px-4 text-center">
                                            @if($isEligible)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">
                                                    paid
                                                </span>
                                            @else
                                                <div class="inline-flex flex-col items-center">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 uppercase">
                                                        NOT PAID
                                                    </span>
                                                    <span class="text-[9px] text-rose-600 font-medium mt-0.5">
                                                        Generation {{ $cGen }}+ excluded by model limit
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-3 px-4 text-center text-slate-400 italic">
                                            No upline earners for this node.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Card Footer Rule Indicator -->
                    <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-600 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-700">Generation Limit Rule:</span>
                            <span class="font-semibold text-emerald-700">Paid through generation {{ $maxGen }}</span>
                            <span class="text-slate-400">•</span>
                            <span class="font-semibold text-rose-600">Generation {{ $maxGen + 1 }}+ excluded by model limit</span>
                        </div>
                        <span class="text-[10px] text-slate-400">The generation limit applies independently to every sale</span>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-slate-400 italic">
                    Click "Calculate Overrides" to generate the auditable commission ledger.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Client-Side Results Renderer (Shared JavaScript) -->
<!-- ========================================================================= -->
<script>
window.renderModel2ResultsView = function (data) {
    if (!data) return;

    // Helper currency formatter
    function fmtRupee(val) {
        const num = parseFloat(val) || 0;
        if (num % 1 === 0) {
            return '₹' + num.toLocaleString('en-IN');
        }
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtRate(val) {
        const num = parseFloat(val) || 0;
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(1)) + '%';
    }

    // 1. UPDATE SUMMARY
    const totalSales = data.total_personal_sales || data.total_sales || 0;
    const totalComm = data.total_commission_generated || data.total_commission || 0;
    const numSales = data.number_of_sales || (data.sales ? data.sales.length : (data.sales_breakdown ? data.sales_breakdown.length : 0));
    const allComms = data.commission_ledger || data.commissions || [];
    const numEntries = data.number_of_commission_entries || allComms.filter(c => c.is_eligible !== false).length;
    const maxGen = data.max_generations || 5;

    const elSales = document.getElementById('res-total-personal-sales');
    const elComm = document.getElementById('res-total-commission-generated');
    const elNumSales = document.getElementById('res-number-of-sales');
    const elNumEntries = document.getElementById('res-number-of-commission-entries');
    const elMaxGen = document.getElementById('res-maximum-generations');

    if (elSales) elSales.textContent = fmtRupee(totalSales);
    if (elComm) elComm.textContent = fmtRupee(totalComm);
    if (elNumSales) elNumSales.textContent = numSales;
    if (elNumEntries) elNumEntries.textContent = numEntries;
    if (elMaxGen) elMaxGen.textContent = maxGen;

    // 2. UPDATE DYNAMIC SVG VISUAL TREE VIEWER
    if (typeof window.updateVisualTreeViewer === 'function') {
        window.updateVisualTreeViewer(data);
    }

    // 3. UPDATE COMMISSION BY PERSON
    const personTbody = document.getElementById('res-commission-by-person-tbody');
    const personBadge = document.getElementById('res-person-count-badge');
    const personMap = data.commission_by_person || data.earnings_by_person || {};
    const persons = Object.values(personMap);

    if (personBadge) {
        personBadge.textContent = `${persons.length} Persons`;
    }

    if (personTbody) {
        personTbody.innerHTML = '';
        if (persons.length === 0) {
            personTbody.innerHTML = `<tr><td colspan="4" class="py-6 text-center text-slate-400 italic">No participants found.</td></tr>`;
        } else {
            persons.forEach(p => {
                const pSale = p.personal_sales || 0;
                const pComm = p.total_commission !== undefined ? p.total_commission : (p.override_commission || 0);
                const pCount = p.commissions_received_count !== undefined ? p.commissions_received_count : (p.breakdown ? p.breakdown.length : 0);

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/80 transition-colors';
                tr.innerHTML = `
                    <td class="py-3 px-5 font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold font-mono">
                            ${p.name.substring(0, 2)}
                        </span>
                        <span>${p.name}</span>
                    </td>
                    <td class="py-3 px-5 text-right font-mono text-slate-700 font-medium">
                        ${fmtRupee(pSale)}
                    </td>
                    <td class="py-3 px-5 text-right font-mono font-bold text-emerald-700 text-sm">
                        ${fmtRupee(pComm)}
                    </td>
                    <td class="py-3 px-5 text-center font-mono">
                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-bold ${pCount > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-400'}">
                            ${pCount}
                        </span>
                    </td>
                `;
                personTbody.appendChild(tr);
            });
        }
    }

    // 3. UPDATE VISUAL COMMISSION FLOW DIAGRAMS
    const flowContainer = document.getElementById('res-commission-flow-container');
    const salesList = data.commission_by_sale || data.sales_breakdown || [];
    if (flowContainer) {
        flowContainer.innerHTML = '';
        if (salesList.length === 0) {
            flowContainer.innerHTML = '<div class="col-span-3 py-8 text-center text-slate-400 italic">No sales processed.</div>';
        } else {
            salesList.forEach((sale, sIdx) => {
                const seller = sale.seller || '';
                const saleAmt = sale.amount || (sale.original_sale_amount || 0);
                const comms = sale.commissions || [];
                const fmtAmt = fmtRupee(saleAmt);
                const hasExcluded = comms.some(c => c.is_eligible === false);

                let summaryLines = '';
                let stepCards = '';

                comms.forEach(c => {
                    const earner = c.earner || (c.recipient || '');
                    const rate = c.rate || 0;
                    const commVal = c.commission !== undefined ? c.commission : (c.commission_amount || 0);
                    const gen = c.generation || 1;
                    const isEligible = c.is_eligible !== false;

                    summaryLines += `
                        <div class="flex items-center justify-between py-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-bold ${isEligible ? 'text-emerald-300' : 'text-slate-400 line-through'}">
                                    ${earner}: ${fmtRate(rate)} = ${fmtRupee(commVal)}
                                </span>
                                <span class="text-[10px] text-slate-400 font-normal">
                                    (${earner} = Generation ${gen})
                                </span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold ${isEligible ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-rose-950 text-rose-300 border border-rose-800'}">
                                ${isEligible ? 'paid' : 'NOT PAID'}
                            </span>
                        </div>
                    `;

                    // Generation Cutoff Barrier if crossing maxGen
                    if (gen === maxGen + 1) {
                        stepCards += `
                            <div class="w-full my-2 p-2 rounded-xl bg-rose-50 border border-rose-200 text-center">
                                <div class="text-[11px] font-bold text-rose-900 flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span>Generation ${maxGen + 1}+ excluded by model limit</span>
                                </div>
                                <div class="text-[10px] text-rose-700 mt-0.5">
                                    Paid through generation ${maxGen} • Maximum ${maxGen} override tiers reached
                                </div>
                            </div>
                        `;
                    }

                    stepCards += `
                        <div class="flex flex-col items-center py-0.5">
                            <span class="text-slate-400 font-bold text-lg leading-none select-none">↓</span>
                            <span class="text-[9px] font-mono text-slate-400 uppercase tracking-widest mt-0.5">Passes Upward</span>
                        </div>
                        <div class="w-full p-3 rounded-xl border text-center shadow-xs transition-colors ${isEligible ? 'border-slate-200 bg-white hover:border-slate-300' : 'border-dashed border-rose-200 bg-rose-50/40 text-slate-400'}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-semibold ${isEligible ? 'text-slate-500' : 'text-rose-500 font-bold'} font-mono">Gen ${gen}</span>
                                <div class="flex items-center gap-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        ${fmtRate(rate)}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold ${isEligible ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200'}">
                                        ${isEligible ? 'paid' : 'NOT PAID'}
                                    </span>
                                </div>
                            </div>
                            <div class="font-mono font-bold text-sm ${isEligible ? 'text-slate-900' : 'text-slate-600'}">${earner}</div>
                            ${isEligible ? `
                                <div class="font-mono text-sm font-extrabold text-emerald-700 mt-1">${fmtRupee(commVal)}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    ${fmtAmt} × ${fmtRate(rate)}
                                </div>
                            ` : `
                                <div class="font-mono text-sm font-bold text-slate-400 line-through mt-1">₹0</div>
                                <div class="text-[10px] text-rose-600 font-semibold mt-0.5">
                                    Generation ${gen}+ excluded by model limit
                                </div>
                            `}
                        </div>
                    `;
                });

                const col = document.createElement('div');
                col.className = 'border border-slate-200 rounded-2xl bg-white shadow-xs overflow-hidden flex flex-col justify-between';
                col.innerHTML = `
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-mono font-bold text-xs flex items-center justify-center">
                                #${sIdx + 1}
                            </span>
                            <span class="font-bold text-slate-800 text-xs">Sale: <strong class="text-emerald-700 font-mono">${seller}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Paid through generation ${Math.min(maxGen, comms.length)}
                            </span>
                            ${hasExcluded ? `
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Gen ${maxGen + 1}+ excluded
                                </span>
                            ` : ''}
                        </div>
                    </div>
                    <div class="p-5 flex-1 space-y-3">
                        <div class="p-3.5 rounded-xl bg-slate-900 text-white shadow-sm space-y-2">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Original Sale: ${fmtAmt}</span>
                                <span class="text-[10px] text-slate-400 font-mono">Paid through gen ${maxGen}</span>
                            </div>
                            <div class="space-y-1.5 text-xs font-mono text-slate-200">
                                ${summaryLines || '<div class="text-slate-500 italic text-[11px]">No uplines for this node.</div>'}
                            </div>
                        </div>
                        <div class="pt-2 flex flex-col items-center space-y-2">
                            <div class="w-full p-3 rounded-xl border border-emerald-300 bg-emerald-50/90 text-center shadow-xs">
                                <div class="font-mono font-bold text-sm text-slate-900">${seller}</div>
                                <div class="font-mono text-xs font-bold text-emerald-700 mt-0.5">${fmtAmt} sale</div>
                                <span class="text-[10px] text-emerald-800 font-semibold uppercase tracking-wider block mt-0.5">Origin Seller</span>
                            </div>
                            ${stepCards}
                        </div>
                    </div>
                `;
                flowContainer.appendChild(col);
            });
        }
    }

    // 4. UPDATE HIERARCHICAL COMMISSION TREE (Cards + ASCII Diagram)
    const treeCardsContainer = document.getElementById('res-tree-cards-container');
    const treeAsciiContainer = document.getElementById('res-tree-ascii-container');
    const treeHierarchy = data.tree_hierarchy || (data.tree_summary ? data.tree_summary.hierarchy : (data.hierarchy || []));

    // Dynamic ASCII Tree Generator
    function generateAsciiTree(nodes, prefix = '') {
        let out = '';
        nodes.forEach((n, i) => {
            const isLast = (i === nodes.length - 1);
            const connector = isLast ? '└── ' : '├── ';
            const childPrefix = prefix + (isLast ? '    ' : '│   ');
            let line = prefix + connector + n.name;
            if (n.rate_from_parent) {
                line += ` [${fmtRate(n.rate_from_parent)}]`;
            }
            const isLeaf = n.is_leaf || (!n.children || n.children.length === 0);
            const pSale = n.personal_sales || 0;
            if (isLeaf && pSale > 0) {
                line += ` • ${fmtRupee(pSale)} sale`;
            }
            out += line + '\n';
            if (n.children && n.children.length > 0) {
                out += generateAsciiTree(n.children, childPrefix);
            }
        });
        return out;
    }

    if (treeAsciiContainer) {
        let asciiStr = '';
        treeHierarchy.forEach(root => {
            asciiStr += root.name + '\n' + generateAsciiTree(root.children || [], '');
        });
        treeAsciiContainer.textContent = asciiStr || "A\n├── B [2%]\n│   ├── E [3%]\n│   │   └── E1 [5%] • ₹200 sale\n│   └── F [5%] • ₹400 sale\n└── C [5%] • ₹300 sale";
    }

    // Card Tree DOM Node Generator
    function buildCardNodeDom(n) {
        const isLeaf = n.is_leaf || (!n.children || n.children.length === 0);
        const pSale = n.personal_sales || 0;
        const rate = n.rate_from_parent || 0;
        const hasParent = !!n.parent;

        const wrapper = document.createElement('div');
        wrapper.className = `relative pl-6 md:pl-8 border-l-2 ${isLeaf ? 'border-emerald-400' : 'border-slate-300'} my-3 space-y-3`;

        const connector = document.createElement('div');
        connector.className = `absolute -left-[2px] top-6 w-6 md:w-8 h-0.5 ${isLeaf ? 'bg-emerald-400' : 'bg-slate-300'}`;
        wrapper.appendChild(connector);

        const card = document.createElement('div');
        card.className = `p-4 rounded-xl border transition-all duration-200 ${isLeaf ? 'bg-emerald-50/70 border-emerald-300 shadow-xs' : 'bg-white border-slate-200 shadow-xs'} max-w-lg`;

        card.innerHTML = `
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs ${isLeaf ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-white'}">
                        ${n.name.substring(0, 3)}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-sm text-slate-900 font-mono">${n.name}</span>
                            ${isLeaf ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">Leaf Node</span>' : `<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Depth ${n.depth || 1}</span>`}
                        </div>
                        ${hasParent ? `
                            <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5 font-mono">
                                <span class="text-slate-400">${n.parent}</span>
                                <span class="text-emerald-500 font-bold">→</span>
                                <span class="text-slate-700 font-bold">${n.name}</span>
                                <span class="px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 text-[10px]">
                                    ${fmtRate(rate)} Override
                                </span>
                            </div>
                        ` : '<div class="text-[11px] text-slate-400 mt-0.5">Root Leader (Network Head)</div>'}
                    </div>
                </div>
                <div class="text-right">
                    ${isLeaf ? `
                        <div class="text-[10px] uppercase font-bold tracking-wider text-emerald-800">Personal Sale</div>
                        <div class="font-mono text-base font-extrabold text-emerald-700">${fmtRupee(pSale)} sale</div>
                    ` : `
                        ${pSale > 0 ? `<div class="text-[10px] text-slate-400">Personal Sale</div><div class="font-mono text-xs font-bold text-slate-700">${fmtRupee(pSale)}</div>` : ''}
                        ${(n.override_commission || 0) > 0 ? `<div class="text-[10px] text-emerald-700 font-medium">Earned: ${fmtRupee(n.override_commission)}</div>` : ''}
                    `}
                </div>
            </div>
        `;
        wrapper.appendChild(card);

        if (n.children && n.children.length > 0) {
            const childrenContainer = document.createElement('div');
            childrenContainer.className = 'space-y-2 pt-1';
            n.children.forEach(c => {
                childrenContainer.appendChild(buildCardNodeDom(c));
            });
            wrapper.appendChild(childrenContainer);
        }

        return wrapper;
    }

    if (treeCardsContainer) {
        treeCardsContainer.innerHTML = '';
        if (treeHierarchy.length === 0) {
            treeCardsContainer.innerHTML = '<div class="py-8 text-center text-slate-400 italic">No network hierarchy nodes found.</div>';
        } else {
            treeHierarchy.forEach(root => {
                treeCardsContainer.appendChild(buildCardNodeDom(root));
            });
        }
    }

    // 5. UPDATE COMMISSION LEDGER (Unmerged distinct sales entries with status)
    const ledgerContainer = document.getElementById('res-commission-ledger-container');
    if (ledgerContainer) {
        ledgerContainer.innerHTML = '';

        if (salesList.length === 0) {
            ledgerContainer.innerHTML = `<div class="py-8 text-center text-slate-400 italic">No sales processed.</div>`;
        } else {
            salesList.forEach((sale, sIdx) => {
                const seller = sale.seller || '';
                const saleAmt = sale.amount || (sale.original_sale_amount || 0);
                const comms = sale.commissions || [];
                const hasExcluded = comms.some(c => c.is_eligible === false);

                const card = document.createElement('div');
                card.className = 'border border-slate-200 rounded-xl overflow-hidden shadow-xs bg-white';

                let rowsHtml = '';
                if (comms.length === 0) {
                    rowsHtml = `<tr><td colspan="7" class="py-3 px-4 text-center text-slate-400 italic">No upline earners for this node.</td></tr>`;
                } else {
                    comms.forEach(c => {
                        const earner = c.earner || (c.recipient || '');
                        const gen = c.generation || 1;
                        const rate = c.rate || 0;
                        const commVal = c.commission !== undefined ? c.commission : (c.commission_amount || 0);
                        const isEligible = c.is_eligible !== false;

                        rowsHtml += `
                            <tr class="hover:bg-slate-50/70 transition-colors ${!isEligible ? 'opacity-50 bg-rose-50/30' : ''}">
                                <td class="py-2.5 px-4 font-mono font-bold text-slate-800">${seller}</td>
                                <td class="py-2.5 px-4 font-mono text-slate-600">${fmtRupee(saleAmt)}</td>
                                <td class="py-2.5 px-4 font-bold text-slate-900 font-mono text-xs flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-slate-100 border border-slate-200 text-slate-700 inline-flex items-center justify-center text-[10px]">
                                        ${earner.substring(0, 2)}
                                    </span>
                                    <span>${earner}</span>
                                </td>
                                <td class="py-2.5 px-4 text-center font-mono font-bold text-slate-700">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        Gen ${gen}
                                    </span>
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-medium text-slate-700">
                                    ${fmtRate(rate)}
                                </td>
                                <td class="py-2.5 px-4 text-right font-mono font-bold text-xs ${isEligible ? 'text-emerald-700' : 'text-slate-400 line-through'}">
                                    ${fmtRupee(commVal)}
                                </td>
                                <td class="py-2.5 px-4 text-center">
                                    ${isEligible ? `
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">
                                            paid
                                        </span>
                                    ` : `
                                        <div class="inline-flex flex-col items-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 uppercase">
                                                NOT PAID
                                            </span>
                                            <span class="text-[9px] text-rose-600 font-medium mt-0.5">
                                                Generation ${gen}+ excluded by model limit
                                            </span>
                                        </div>
                                    `}
                                </td>
                            </tr>
                        `;
                    });
                }

                card.innerHTML = `
                    <div class="px-4 py-2.5 bg-slate-100/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-mono font-bold text-[10px] uppercase">
                                Sale #${sIdx + 1}
                            </span>
                            <span class="font-bold text-slate-900">Sale Person: <span class="font-mono text-emerald-800 font-bold">${seller}</span></span>
                            <span class="text-slate-400">•</span>
                            <span class="font-bold text-slate-700">Sale Amount: <span class="font-mono text-slate-900 font-bold">${fmtRupee(saleAmt)}</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Paid through generation ${Math.min(maxGen, comms.length)}
                            </span>
                            ${hasExcluded ? `
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    Generation ${maxGen + 1}+ excluded by model limit
                                </span>
                            ` : ''}
                            <span class="text-slate-500 font-mono">
                                ${comms.length} upline override(s)
                            </span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/60 text-slate-500 text-[11px] uppercase tracking-wider font-semibold">
                                    <th class="py-2.5 px-4">Sale Person</th>
                                    <th class="py-2.5 px-4">Sale Amount</th>
                                    <th class="py-2.5 px-4 font-bold text-slate-700">Earner</th>
                                    <th class="py-2.5 px-4 text-center">Generation</th>
                                    <th class="py-2.5 px-4 text-right">Rate</th>
                                    <th class="py-2.5 px-4 text-right font-bold text-emerald-800">Commission</th>
                                    <th class="py-2.5 px-4 text-center font-bold text-slate-700">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                ${rowsHtml}
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-600 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-700">Generation Limit Rule:</span>
                            <span class="font-semibold text-emerald-700">Paid through generation ${maxGen}</span>
                            <span class="text-slate-400">•</span>
                            <span class="font-semibold text-rose-600">Generation ${maxGen + 1}+ excluded by model limit</span>
                        </div>
                        <span class="text-[10px] text-slate-400">The generation limit applies independently to every sale</span>
                    </div>
                `;

                ledgerContainer.appendChild(card);
            });
        }
    }
};
</script>
