@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-violet-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-violet-600 transition-colors">Saved Models</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">{{ $model->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $model->name }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200">#{{ $model->id }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-100 text-violet-800 border border-violet-200">Model 3 · Unilevel MLM</span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Saved on {{ $model->created_at->format('M d, Y \a\t H:i') }} •
                Max Depth: <strong class="text-slate-700">{{ $model->max_generations ?? 10 }} levels</strong> •
                Distributors: <strong class="text-slate-700">{{ $results['node_count'] ?? 0 }}</strong>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('unilevel-models.edit', $model) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <a href="{{ route('unilevel-models.duplicate', $model) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Duplicate
            </a>
            <form action="{{ route('unilevel-models.destroy', $model) }}" method="POST" onsubmit="return confirm('Delete \'{{ addslashes($model->name) }}\'? This cannot be undone.');" class="inline">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">Saved Models</a>
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 text-sm font-semibold">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Summary Stats ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $totalSales  = $results['total_personal_sales'] ?? 0;
        $totalComm   = $results['total_commission_generated'] ?? 0;
        $nodeCount   = $results['node_count'] ?? 0;
        $saleCount   = $results['sale_count'] ?? 0;
        $maxDepth    = $results['max_depth'] ?? ($model->max_generations ?? 10);
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Total Personal Sales</div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono mt-1">₹{{ number_format($totalSales, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">Across {{ $saleCount }} recorded sale(s)</div>
        </div>
        <div class="bg-white rounded-2xl border border-violet-200 shadow-sm p-5">
            <div class="text-[10px] font-semibold text-violet-600 uppercase tracking-wider">Total Commission Generated</div>
            <div class="text-2xl font-extrabold text-violet-900 font-mono mt-1">₹{{ number_format($totalComm, 2) }}</div>
            <div class="text-xs text-slate-500 mt-1">All uplines combined</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Distributors in Network</div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono mt-1">{{ $nodeCount }}</div>
            <div class="text-xs text-slate-500 mt-1">Unique nodes in hierarchy</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Max Depth Configured</div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono mt-1">{{ $maxDepth }}</div>
            <div class="text-xs text-slate-500 mt-1">Levels paid commission</div>
        </div>
    </div>

    {{-- ── Rate Schedule ── --}}
    @if(!empty($results['rate_schedule']))
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Generation Rate Schedule Applied</h3>
            <p class="text-xs text-slate-500 mt-0.5">Rate paid to uplines at each generational depth. This is illustrative only.</p>
        </div>
        <div class="p-6 flex flex-wrap gap-3">
            @foreach($results['rate_schedule'] as $depth => $rate)
            <div class="flex flex-col items-center px-4 py-3 bg-violet-50 border border-violet-200 rounded-xl min-w-[80px]">
                <span class="text-[10px] font-semibold text-violet-600 uppercase">Gen {{ $depth }}</span>
                <span class="text-xl font-extrabold text-violet-800 font-mono mt-1">{{ $rate }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Commission by Sale Breakdown ── --}}
    @if(!empty($results['commission_by_sale']))
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Commission Breakdown by Sale</h3>
            <p class="text-xs text-slate-500 mt-0.5">Each sale and the commission it generated for uplines.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Seller</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Sale Amount</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Commission Paid</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-center">Uplines Paid</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-center">Eff. Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($results['commission_by_sale'] as $sale)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 font-mono font-semibold text-slate-800 text-xs">{{ $sale['seller'] }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">₹{{ number_format($sale['amount'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-violet-700">₹{{ number_format($sale['total_commission_paid'], 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-violet-100 text-violet-700">{{ $sale['uplines_paid'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-slate-600">
                            @if($sale['amount'] > 0)
                                {{ number_format(($sale['total_commission_paid'] / $sale['amount']) * 100, 2) }}%
                            @else—@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-violet-50 border-t-2 border-violet-200">
                        <td colspan="2" class="px-4 py-3 font-bold text-violet-900">Totals</td>
                        <td class="px-4 py-3 text-right font-extrabold font-mono text-slate-800">₹{{ number_format($totalSales, 2) }}</td>
                        <td class="px-4 py-3 text-right font-extrabold font-mono text-violet-800">₹{{ number_format($totalComm, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Earnings by Person ── --}}
    @if(!empty($results['earnings_by_person']))
    @php
    $earners = collect($results['earnings_by_person'])
        ->filter(fn($p) => $p['override_commission'] > 0)
        ->sortByDesc('override_commission')
        ->values();
    @endphp
    @if($earners->count() > 0)
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Earnings by Distributor</h3>
            <p class="text-xs text-slate-500 mt-0.5">All distributors who earned override commissions, sorted by total earned.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Distributor</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Personal Sales</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Override Commission</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Total Earnings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($earners as $person)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 font-mono font-bold text-slate-800 text-xs">{{ $person['name'] }}</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">₹{{ number_format($person['personal_sales'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-violet-700">₹{{ number_format($person['override_commission'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-emerald-700">₹{{ number_format($person['total_earnings'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @endif

    {{-- ── Full Ledger ── --}}
    @if(!empty($results['ledger']))
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Full Commission Ledger</h3>
            <p class="text-xs text-slate-500 mt-0.5">Every individual commission payout, with generation, rate, and amount.</p>
        </div>
        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-xs">
                <thead class="sticky top-0">
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-3 py-2 font-semibold text-slate-600">Seller</th>
                        <th class="px-3 py-2 font-semibold text-slate-600">Earner (Upline)</th>
                        <th class="px-3 py-2 font-semibold text-slate-600 text-center">Gen</th>
                        <th class="px-3 py-2 font-semibold text-slate-600 text-right">Sale Amount</th>
                        <th class="px-3 py-2 font-semibold text-slate-600 text-center">Rate</th>
                        <th class="px-3 py-2 font-semibold text-slate-600 text-right">Commission</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($results['ledger'] as $entry)
                    @if($entry['is_eligible'])
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-3 py-2 font-mono text-slate-600">{{ $entry['seller'] }}</td>
                        <td class="px-3 py-2 font-mono font-semibold text-violet-800">{{ $entry['earner'] }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-1.5 py-0.5 rounded bg-violet-100 text-violet-700 font-bold">{{ $entry['generation'] }}</span>
                        </td>
                        <td class="px-3 py-2 text-right font-mono text-slate-600">₹{{ number_format($entry['sale_amount'], 2) }}</td>
                        <td class="px-3 py-2 text-center font-semibold text-slate-700">{{ $entry['rate'] }}%</td>
                        <td class="px-3 py-2 text-right font-mono font-bold text-emerald-700">₹{{ number_format($entry['commission_amount'], 2) }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
