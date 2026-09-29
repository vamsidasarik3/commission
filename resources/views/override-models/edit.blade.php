@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header Breadcrumbs & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-emerald-600 transition-colors">Saved Models</a>
                <span>/</span>
                <a href="{{ route('override-models.show', $model) }}" class="hover:text-emerald-600 transition-colors">{{ $model->name }}</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Edit</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    Edit {{ $model->name }}
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Model 2: Override Engine
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Update relationship override rates, personal sales data, or max eligible generations.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('override-models.show', $model) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                View Model
            </a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                Saved Models
            </a>
        </div>
    </div>

    <!-- Server & AJAX Error Alert -->
    <div id="ajax-error-alert" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2 hidden">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="ajax-error-title">Validation Error</span>
        </div>
        <ul id="ajax-error-list" class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5"></ul>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2">
            <div class="flex items-center gap-2 font-semibold text-sm">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Please fix the following validation errors:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="override-model-form" action="{{ route('override-models.update', $model) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: Model Parameters -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 1</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Model Parameters</h3>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Model Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Model Name <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name', $model->name) }}"
                        class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                        required
                    >
                </div>

                <!-- Default Override Rate -->
                <div>
                    <label for="model2_default_override_rate" class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Default Override Rate (%)</span>
                        <span class="text-[10px] font-normal text-slate-400">Default: 5%</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="model2_default_override_rate" 
                            name="default_override_rate" 
                            value="5.00"
                            step="0.01"
                            min="0" 
                            max="100" 
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800 font-semibold"
                        >
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-medium pointer-events-none">%</span>
                    </div>
                </div>

                <!-- Max Generations -->
                <div>
                    <label for="max_generations" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Max Eligible Generations <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="max_generations" 
                            name="max_generations" 
                            value="{{ old('max_generations', $maxGenerations) }}"
                            min="1" 
                            max="50" 
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800 font-semibold"
                            required
                        >
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-medium pointer-events-none">gens</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="md:col-span-3">
                    <label for="description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Description / Business Notes (Optional)
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="2"
                        class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                    >{{ old('description', $model->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Hierarchy Relationships & Edge Rates -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 2</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Parent → Child Relationships & Override Rates</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-add-edge" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Relationship
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 font-semibold w-12 text-center">#</th>
                                <th class="px-4 py-3 font-semibold">Parent (Upline)</th>
                                <th class="px-4 py-3 font-semibold text-center w-12">→</th>
                                <th class="px-4 py-3 font-semibold">Child (Direct Downline)</th>
                                <th class="px-4 py-3 font-semibold w-48">Override Rate (%)</th>
                                <th class="px-4 py-3 font-semibold w-24 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="edges-tbody" class="divide-y divide-slate-100">
                            <!-- Dynamic Edges Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Personal Sales Input -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 3</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Personal Sales Data</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-add-sale" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Sale
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 font-semibold w-12 text-center">#</th>
                                <th class="px-4 py-3 font-semibold">Salesperson Identifier</th>
                                <th class="px-4 py-3 font-semibold">Personal Sale Amount (₹)</th>
                                <th class="px-4 py-3 font-semibold w-24 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="sales-tbody" class="divide-y divide-slate-100">
                            <!-- Dynamic Sales Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Live Dynamic Preview & Payout Engine -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <h3 class="text-base font-semibold text-slate-900">Recalculation Preview</h3>
                </div>
                <button type="button" id="btn-recalculate" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    Recalculate
                </button>
            </div>

            <div class="p-6 space-y-6">
                <!-- Summary KPI Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Sales Base</div>
                        <div id="kpi-total-sales" class="text-xl font-bold text-slate-900 mt-1">₹0.00</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                        <div class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Total Override Commission</div>
                        <div id="kpi-total-commission" class="text-xl font-bold text-emerald-800 mt-1">₹0.00</div>
                    </div>
                    <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                        <div class="text-[11px] font-semibold text-indigo-700 uppercase tracking-wider">Effective Override Rate</div>
                        <div id="kpi-effective-rate" class="text-xl font-bold text-indigo-800 mt-1">0.0%</div>
                    </div>
                    <div class="p-4 rounded-xl bg-amber-50 border border-amber-100">
                        <div class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">Generations Cap</div>
                        <div id="kpi-max-gen" class="text-xl font-bold text-amber-800 mt-1">{{ $maxGenerations }} Gens</div>
                    </div>
                </div>

                <!-- Earnings by Person Table -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">Earnings Summary by Person</h4>
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left text-xs divide-y divide-slate-100">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="px-4 py-2.5 font-semibold">Person</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Personal Sales</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Commissions Received</th>
                                    <th class="px-4 py-2.5 font-semibold text-right">Total Earnings</th>
                                    <th class="px-4 py-2.5 font-semibold text-center">Payouts Count</th>
                                </tr>
                            </thead>
                            <tbody id="earnings-tbody" class="divide-y divide-slate-100">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Submission Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('override-models.show', $model) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                Cancel
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" id="btn-save-model" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Update & Recalculate Model
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const edgesTbody = document.getElementById('edges-tbody');
    const salesTbody = document.getElementById('sales-tbody');
    const maxGenInput = document.getElementById('max_generations');
    const btnAddEdge = document.getElementById('btn-add-edge');
    const btnAddSale = document.getElementById('btn-add-sale');
    const btnRecalc = document.getElementById('btn-recalculate');
    const form = document.getElementById('override-model-form');

    let defaultEdges = @json($editEdges);
    let defaultSales = @json($editSales);

    function renderEdges(edges) {
        edgesTbody.innerHTML = '';
        edges.forEach((edge, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/80 transition-colors';
            tr.innerHTML = `
                <td class="px-4 py-2.5 text-center text-slate-400 font-mono text-[11px]">${idx + 1}</td>
                <td class="px-4 py-2.5">
                    <input type="text" name="edges[${idx}][parent]" value="${edge.parent}" placeholder="Parent" 
                        class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
                </td>
                <td class="px-4 py-2.5 text-center text-slate-400 font-bold">→</td>
                <td class="px-4 py-2.5">
                    <input type="text" name="edges[${idx}][child]" value="${edge.child}" placeholder="Child" 
                        class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
                </td>
                <td class="px-4 py-2.5">
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="edges[${idx}][rate]" value="${edge.rate}" 
                            class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white text-right pr-6" required>
                        <span class="absolute right-2 top-1.5 text-xs text-slate-400">%</span>
                    </div>
                </td>
                <td class="px-4 py-2.5 text-center">
                    <button type="button" class="btn-remove-edge p-1 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Delete relationship">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </td>
            `;
            edgesTbody.appendChild(tr);
        });
        bindEdgeEvents();
    }

    function renderSales(sales) {
        salesTbody.innerHTML = '';
        sales.forEach((sale, idx) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/80 transition-colors';
            tr.innerHTML = `
                <td class="px-4 py-2.5 text-center text-slate-400 font-mono text-[11px]">${idx + 1}</td>
                <td class="px-4 py-2.5">
                    <input type="text" name="sales[${idx}][salesperson]" value="${sale.salesperson}" placeholder="Salesperson Name" 
                        class="sale-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
                </td>
                <td class="px-4 py-2.5">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-medium">₹</span>
                        <input type="number" step="0.01" min="0" name="sales[${idx}][amount]" value="${sale.amount}" 
                            class="sale-input w-full text-xs font-semibold pl-6 pr-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white text-right" required>
                    </div>
                </td>
                <td class="px-4 py-2.5 text-center">
                    <button type="button" class="btn-remove-sale p-1 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Delete sale">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </td>
            `;
            salesTbody.appendChild(tr);
        });
        bindSaleEvents();
    }

    function bindEdgeEvents() {
        document.querySelectorAll('.btn-remove-edge').forEach(btn => {
            btn.onclick = function () {
                const tr = btn.closest('tr');
                if (edgesTbody.children.length > 1) {
                    tr.remove();
                    reindexEdges();
                    scheduleRecalculate();
                } else {
                    alert('At least one relationship is required.');
                }
            };
        });
        document.querySelectorAll('.edge-input').forEach(input => {
            input.oninput = scheduleRecalculate;
        });
    }

    function bindSaleEvents() {
        document.querySelectorAll('.btn-remove-sale').forEach(btn => {
            btn.onclick = function () {
                const tr = btn.closest('tr');
                if (salesTbody.children.length > 1) {
                    tr.remove();
                    reindexSales();
                    scheduleRecalculate();
                } else {
                    alert('At least one sale is required.');
                }
            };
        });
        document.querySelectorAll('.sale-input').forEach(input => {
            input.oninput = scheduleRecalculate;
        });
    }

    function reindexEdges() {
        Array.from(edgesTbody.children).forEach((tr, i) => {
            tr.children[0].innerText = i + 1;
            tr.querySelector('input[name*="[parent]"]').name = `edges[${i}][parent]`;
            tr.querySelector('input[name*="[child]"]').name = `edges[${i}][child]`;
            tr.querySelector('input[name*="[rate]"]').name = `edges[${i}][rate]`;
        });
    }

    function reindexSales() {
        Array.from(salesTbody.children).forEach((tr, i) => {
            tr.children[0].innerText = i + 1;
            tr.querySelector('input[name*="[salesperson]"]').name = `sales[${i}][salesperson]`;
            tr.querySelector('input[name*="[amount]"]').name = `sales[${i}][amount]`;
        });
    }

    btnAddEdge.onclick = function () {
        const nextIdx = edgesTbody.children.length;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/80 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-2.5 text-center text-slate-400 font-mono text-[11px]">${nextIdx + 1}</td>
            <td class="px-4 py-2.5">
                <input type="text" name="edges[${nextIdx}][parent]" placeholder="Parent" class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
            </td>
            <td class="px-4 py-2.5 text-center text-slate-400 font-bold">→</td>
            <td class="px-4 py-2.5">
                <input type="text" name="edges[${nextIdx}][child]" placeholder="Child" class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
            </td>
            <td class="px-4 py-2.5">
                <div class="relative">
                    <input type="number" step="0.01" min="0" max="100" name="edges[${nextIdx}][rate]" value="5.0" class="edge-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white text-right pr-6" required>
                    <span class="absolute right-2 top-1.5 text-xs text-slate-400">%</span>
                </div>
            </td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" class="btn-remove-edge p-1 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Delete relationship">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </td>
        `;
        edgesTbody.appendChild(tr);
        bindEdgeEvents();
    };

    btnAddSale.onclick = function () {
        const nextIdx = salesTbody.children.length;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50/80 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-2.5 text-center text-slate-400 font-mono text-[11px]">${nextIdx + 1}</td>
            <td class="px-4 py-2.5">
                <input type="text" name="sales[${nextIdx}][salesperson]" placeholder="Salesperson" class="sale-input w-full text-xs font-semibold px-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white uppercase" required>
            </td>
            <td class="px-4 py-2.5">
                <div class="relative">
                    <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-medium">₹</span>
                    <input type="number" step="0.01" min="0" name="sales[${nextIdx}][amount]" value="100.00" class="sale-input w-full text-xs font-semibold pl-6 pr-2.5 py-1.5 rounded border border-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500 bg-white text-right" required>
                </div>
            </td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" class="btn-remove-sale p-1 text-slate-400 hover:text-rose-600 rounded transition-colors" title="Delete sale">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </td>
        `;
        salesTbody.appendChild(tr);
        bindSaleEvents();
    };

    let debounceTimer = null;
    function scheduleRecalculate() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(calculateLive, 300);
    }

    maxGenInput.oninput = scheduleRecalculate;
    btnRecalc.onclick = calculateLive;

    function calculateLive() {
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            max_generations: formData.get('max_generations'),
            edges: [],
            sales: []
        };

        Array.from(edgesTbody.children).forEach(tr => {
            const parent = tr.querySelector('input[name*="[parent]"]')?.value.trim();
            const child = tr.querySelector('input[name*="[child]"]')?.value.trim();
            const rate = tr.querySelector('input[name*="[rate]"]')?.value;
            if (parent && child) {
                data.edges.push({ parent, child, rate });
            }
        });

        Array.from(salesTbody.children).forEach(tr => {
            const salesperson = tr.querySelector('input[name*="[salesperson]"]')?.value.trim();
            const amount = tr.querySelector('input[name*="[amount]"]')?.value;
            if (salesperson) {
                data.sales.push({ salesperson, amount });
            }
        });

        const alertBox = document.getElementById('ajax-error-alert');
        const alertList = document.getElementById('ajax-error-list');

        fetch("{{ route('override-models.calculate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alertBox.classList.remove('hidden');
                alertList.innerHTML = '';
                const errs = res.errors || [res.message];
                errs.forEach(e => {
                    const li = document.createElement('li');
                    li.innerText = e;
                    alertList.appendChild(li);
                });
                return;
            }

            alertBox.classList.add('hidden');
            renderCalculationData(res.data);
        })
        .catch(err => {
            console.error('Calculation error:', err);
        });
    }

    function renderCalculationData(data) {
        document.getElementById('kpi-total-sales').innerText = '₹' + parseFloat(data.total_sales).toFixed(2);
        document.getElementById('kpi-total-commission').innerText = '₹' + parseFloat(data.total_commission).toFixed(2);
        const effRate = data.total_sales > 0 ? ((data.total_commission / data.total_sales) * 100).toFixed(2) : '0.00';
        document.getElementById('kpi-effective-rate').innerText = effRate + '%';
        document.getElementById('kpi-max-gen').innerText = data.max_generations + ' Gens';

        const earningsTbody = document.getElementById('earnings-tbody');
        earningsTbody.innerHTML = '';
        const earnings = Object.values(data.earnings_by_person);

        if (earnings.length === 0) {
            earningsTbody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-slate-400">No participants found.</td></tr>';
        } else {
            earnings.forEach(p => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/80 transition-colors';
                tr.innerHTML = `
                    <td class="px-4 py-2.5 font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">
                            ${p.name.substring(0, 2)}
                        </span>
                        ${p.name}
                    </td>
                    <td class="px-4 py-2.5 text-right font-medium text-slate-600">₹${parseFloat(p.personal_sales).toFixed(2)}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-emerald-700">₹${parseFloat(p.override_commission).toFixed(2)}</td>
                    <td class="px-4 py-2.5 text-right font-bold text-slate-900">₹${parseFloat(p.total_earnings).toFixed(2)}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold ${p.commissions_received_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500'}">
                            ${p.commissions_received_count} override(s)
                        </span>
                    </td>
                `;
                earningsTbody.appendChild(tr);
            });
        }
    }

    renderEdges(defaultEdges);
    renderSales(defaultSales);
    calculateLive();
});
</script>
@endsection
