{{--
    Model 2 Recursive Tree Node Card Component
    Displays each node as a card with:
    - Node name
    - Personal sale for leaf nodes
    - Override % for parent-child relationships
--}}
@php
    $isLeaf = $node['is_leaf'] ?? (empty($node['children']));
    $pSale = $node['personal_sales'] ?? 0;
    $rate = $node['rate_from_parent'] ?? 0;
    $hasParent = !empty($node['parent']);
    $children = $node['children'] ?? [];
@endphp

<div class="relative pl-6 md:pl-8 border-l-2 {{ $isLeaf ? 'border-emerald-400' : 'border-slate-300' }} my-3 space-y-3">
    <!-- Horizontal Branch Connector Line -->
    <div class="absolute -left-[2px] top-6 w-6 md:w-8 h-0.5 {{ $isLeaf ? 'bg-emerald-400' : 'bg-slate-300' }}"></div>

    <!-- Node Card -->
    <div class="p-4 rounded-xl border transition-all duration-200 {{ $isLeaf ? 'bg-emerald-50/70 border-emerald-300 shadow-xs hover:border-emerald-400' : 'bg-white border-slate-200 shadow-xs hover:border-slate-300' }} max-w-lg">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <!-- Node Name and Avatar -->
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs {{ $isLeaf ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-800 text-white shadow-xs' }}">
                    {{ substr($node['name'], 0, 3) }}
                </span>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-slate-900 font-mono">{{ $node['name'] }}</span>
                        @if($isLeaf)
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                Leaf Node
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                Depth {{ $node['depth'] ?? 1 }}
                            </span>
                        @endif
                    </div>

                    <!-- Relationship Override % display -->
                    @if($hasParent)
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5 font-mono">
                            <span class="text-slate-400">{{ $node['parent'] }}</span>
                            <span class="text-emerald-500 font-bold">→</span>
                            <span class="text-slate-700 font-bold">{{ $node['name'] }}</span>
                            <span class="px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 font-bold border border-indigo-200 text-[10px]">
                                {{ $rate == intval($rate) ? number_format($rate, 0) : number_format($rate, 1) }}% Override
                            </span>
                        </div>
                    @else
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            Root Leader (Network Head)
                        </div>
                    @endif
                </div>
            </div>

            <!-- Leaf Personal Sale or Earnings Display -->
            <div class="text-right">
                @if($isLeaf)
                    <div class="text-[10px] uppercase font-bold tracking-wider text-emerald-800">Personal Sale</div>
                    <div class="font-mono text-base font-extrabold text-emerald-700">
                        ₹{{ $pSale == intval($pSale) ? number_format($pSale, 0) : number_format($pSale, 2) }} sale
                    </div>
                @else
                    @if($pSale > 0)
                        <div class="text-[10px] text-slate-400">Personal Sale</div>
                        <div class="font-mono text-xs font-bold text-slate-700">
                            ₹{{ $pSale == intval($pSale) ? number_format($pSale, 0) : number_format($pSale, 2) }}
                        </div>
                    @endif
                    @if(($node['override_commission'] ?? 0) > 0)
                        <div class="text-[10px] text-emerald-700 font-medium">Earned Overrides: ₹{{ number_format($node['override_commission'], 0) }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Recursive Children Branches -->
    @if(!empty($children))
        <div class="space-y-2 pt-1">
            @foreach($children as $child)
                @include('components.model2-tree-node', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
