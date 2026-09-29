@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-violet-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-violet-600 transition-colors">Saved Models</a>
                <span>/</span>
                <a href="{{ route('unilevel-models.show', $model) }}" class="hover:text-violet-600 transition-colors">{{ $model->name }}</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Edit</span>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Edit Unilevel Model</h2>
            <p class="text-sm text-slate-500 mt-0.5">Update node hierarchy, sales records, or rate schedule. Results will be recalculated and saved.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('unilevel-models.show', $model) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">View Model</a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">Saved Models</a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
        <div class="font-semibold text-sm mb-2">Please fix the errors:</div>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @php
    $savedNodes = [];
    $savedSales = [];
    if (!empty($results['commission_by_sale'])) {
        foreach ($results['commission_by_sale'] as $s) {
            $savedSales[] = ['distributor' => $s['seller'], 'amount' => $s['amount']];
        }
    }
    if (!empty($results['hierarchy_tree'])) {
        $flattenTree = function($tree, $parent = null) use (&$flattenTree) {
            $out = [];
            foreach ($tree as $node) {
                $out[] = ['name' => $node['name'], 'parent' => $parent ?? ''];
                if (!empty($node['children'])) {
                    foreach ($flattenTree($node['children'], $node['name']) as $child) {
                        $out[] = $child;
                    }
                }
            }
            return $out;
        };
        $savedNodes = $flattenTree($results['hierarchy_tree']);
    }
    $savedRates = $results['rate_schedule'] ?? $defaultRates;
    @endphp

    <form action="{{ route('unilevel-models.update', $model) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')

        {{-- Model Details --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 1</span>
                <h3 class="text-base font-semibold text-slate-900 mt-1">Model Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Model Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $model->name) }}"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Max Depth <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_depth" value="{{ old('max_depth', $model->max_generations ?? 10) }}" min="1" max="20"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">{{ old('description', $model->description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Rate Schedule --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 2</span>
                <h3 class="text-base font-semibold text-slate-900 mt-1">Generation Rate Schedule</h3>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-5 gap-3">
                @foreach($defaultRates as $depth => $defaultRate)
                @php $currentRate = old("rate_schedule.{$depth}", $savedRates[$depth] ?? $defaultRate); @endphp
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gen {{ $depth }} Rate %</label>
                    <input type="number" name="rate_schedule[{{ $depth }}]" step="0.01" min="0" max="100" value="{{ $currentRate }}"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Nodes --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 3</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Distributor Nodes</h3>
                </div>
                <button type="button" id="add-node-btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Node
                </button>
            </div>
            <div class="p-6 space-y-2" id="nodes-container"></div>
        </div>

        {{-- Sales --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 4</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Personal Sales</h3>
                </div>
                <button type="button" id="add-sale-btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Sale
                </button>
            </div>
            <div class="p-6 space-y-2" id="sales-container"></div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('unilevel-models.show', $model) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 shadow-sm shadow-violet-600/25 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save & Recalculate
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    let nodeCount = 0, saleCount = 0;

    function addNodeRow(name = '', parent = '') {
        const idx = nodeCount++;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <div class="flex-1 grid grid-cols-2 gap-2">
                <input type="text" name="nodes[${idx}][name]" value="${name}" placeholder="Distributor Name"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                <input type="text" name="nodes[${idx}][parent]" value="${parent}" placeholder="Sponsor (blank = root)"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        document.getElementById('nodes-container').appendChild(row);
    }

    function addSaleRow(distributor = '', amount = 10000) {
        const idx = saleCount++;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <div class="flex-1 grid grid-cols-2 gap-2">
                <input type="text" name="sales[${idx}][distributor]" value="${distributor}" placeholder="Distributor Name"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                <input type="number" name="sales[${idx}][amount]" value="${amount}" min="0" step="0.01" placeholder="Amount (₹)"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        document.getElementById('sales-container').appendChild(row);
    }

    document.getElementById('add-node-btn').addEventListener('click', () => addNodeRow());
    document.getElementById('add-sale-btn').addEventListener('click', () => addSaleRow());

    // Populate from saved data
    @foreach($savedNodes as $node)
    addNodeRow(@json($node['name']), @json($node['parent']));
    @endforeach

    @foreach($savedSales as $sale)
    addSaleRow(@json($sale['distributor']), {{ $sale['amount'] }});
    @endforeach

    // Fallback: at least one row of each
    if (!document.querySelector('[name^="nodes["]')) addNodeRow();
    if (!document.querySelector('[name^="sales["]')) addSaleRow();
})();
</script>
@endsection
