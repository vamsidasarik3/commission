@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-8">
    <!-- Header Breadcrumbs & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-indigo-600 transition-colors">Saved Models</a>
                <span>/</span>
                <a href="{{ route('commission-models.show', $model) }}" class="hover:text-indigo-600 transition-colors">{{ $model->name }}</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Edit</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Edit Commission Model</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    #{{ $model->id }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Update level sales or parameters. Changes will be recalculated and saved in the database within a transaction.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('commission-models.show', $model) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View Stored Model
            </a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                Saved Models
            </a>
        </div>
    </div>

    <!-- Server-Side Validation Errors Alert -->
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

    <!-- AJAX Live Validation & Server Error Alert -->
    <div id="ajax-error-alert" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2 hidden">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="ajax-error-title">Validation or Engine Error</span>
        </div>
        <ul id="ajax-error-list" class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5">
        </ul>
    </div>

    <form id="commission-model-form" action="{{ route('commission-models.update', $model) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: Model Specifications -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Section 1</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Model Parameters</h3>
                </div>
                <div class="text-xs text-slate-500 hidden sm:block">
                    Update baseline targets
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Model Name -->
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Model Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', $model->name) }}"
                            placeholder="e.g. 10 Level Test"
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
                            required
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Title to identify this commission structure.</p>
                    </div>

                    <!-- Commission Rate -->
                    <div>
                        <label for="commission_rate" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Default Commission Rate (%) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                id="commission_rate" 
                                name="commission_rate" 
                                step="0.01" 
                                min="0" 
                                max="100" 
                                value="{{ old('commission_rate', number_format($model->commission_rate, 2, '.', '')) }}"
                                placeholder="5.00"
                                class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 pr-9"
                                required
                            >
                            <span class="absolute right-3.5 top-2.5 text-sm font-semibold text-slate-400">%</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Default rate for levels. Each level below can be dynamically customized.</p>
                    </div>

                    <!-- Number of Levels -->
                    <div>
                        <label for="number_of_levels" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Number of Levels <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="number_of_levels" 
                            name="number_of_levels" 
                            min="1" 
                            max="100" 
                            value="{{ old('number_of_levels', $model->number_of_levels) }}"
                            placeholder="10"
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
                            required
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Positive integer (Supports 1 up to 100).</p>
                    </div>
                </div>

                <!-- Generate Levels Button & Quick Preset Pills -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="font-medium">Quick Presets:</span>
                        <button type="button" onclick="applyPreset(1)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">1 Level</button>
                        <button type="button" onclick="applyPreset(2)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">2 Levels</button>
                        <button type="button" onclick="applyPreset(10)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">10 Levels</button>
                        <button type="button" onclick="applyPreset(20)" class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">20 Levels</button>
                    </div>

                    <button 
                        type="button" 
                        id="btn-generate-levels"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-all focus:ring-2 focus:ring-indigo-500/30"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Update Level Rows</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Dynamic Levels Table -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 2</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Hierarchical Level Configuration</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Each level defines the main child branch and the lateral side salesperson branch.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span id="level-counter-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span id="level-counter-text">{{ $model->number_of_levels }} Levels Loaded</span>
                    </span>
                </div>
            </div>

            <!-- Client-Side Alert Banner if no rows generated -->
            <div id="no-levels-alert" class="p-8 text-center text-slate-500 hidden">
                <p class="text-sm font-medium text-slate-700">No Levels Generated Yet</p>
            </div>

            <!-- Levels Table Container -->
            <div id="levels-table-container" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4 w-16 text-center">Level</th>
                            <th class="py-3 px-3 w-28 text-center">Rate (%)</th>
                            <th class="py-3 px-5">Main Person</th>
                            <th class="py-3 px-5">Main Sales (₹)</th>
                            <th class="py-3 px-5">Side Person</th>
                            <th class="py-3 px-5">Side Sales (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="levels-tbody" class="divide-y divide-slate-100 text-slate-700">
                        <!-- Populated by JavaScript from $editLevels -->
                    </tbody>
                </table>
            </div>

            <!-- Action Footer -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    <span class="font-medium text-slate-700">Weakest Link Rule:</span> The server engine calculates <code>MIN(main child, side salesperson)</code> upward from Level <span id="footer-bottom-level">{{ $model->number_of_levels }}</span> to Level 1.
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <!-- Calculate Model Button (Preview) -->
                    <button 
                        type="button" 
                        id="btn-calculate"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-all focus:ring-2 focus:ring-indigo-500/30"
                    >
                        <svg id="calc-spinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg id="calc-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span id="calc-btn-text">Preview Recalculation</span>
                    </button>

                    <!-- Update Model Button -->
                    <button 
                        type="submit" 
                        id="btn-submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all focus:ring-2 focus:ring-emerald-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg id="save-spinner" class="w-4 h-4 hidden animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg id="save-icon" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span id="save-btn-text">Update Model</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- SECTION 3: Live Recalculation Preview Section -->
    <div id="calculation-results-section" class="hidden space-y-8 transition-all duration-300">
        <!-- Spotlight Bottleneck Summary Card -->
        <div class="rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/50 p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Updated Bottleneck Identified
                        </span>
                        <span class="text-xs text-slate-500 font-medium">Server Engine Evaluation</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">
                        Final Top-Level Commission: <span id="res-final-commission-banner" class="text-indigo-600 font-mono text-xl">₹0.00</span>
                    </h3>
                    <p class="text-xs text-slate-600 max-w-2xl leading-relaxed">
                        Under the Weakest Link Rule, the leader's commission is determined by the bottom-up formula: 
                        <code class="bg-amber-100/70 text-amber-900 px-1 py-0.5 rounded font-mono text-[11px]">MIN(main child commission, side salesperson commission)</code>.
                        The performance ceiling for the entire hierarchy is bounded by <strong id="res-weakest-person-name" class="text-slate-900 font-bold">Person ?</strong> with sales of <strong id="res-weakest-sales-banner" class="text-slate-900 font-mono">₹0.00</strong>.
                    </p>
                </div>

                <div class="bg-white p-4 rounded-xl border border-amber-200/80 shadow-sm flex items-center gap-4 min-w-[240px]">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center font-bold text-xl font-mono">
                        <span id="res-weakest-initial">?</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Weakest Person</span>
                        <span id="res-weakest-person" class="text-sm font-bold text-slate-900">Person ?</span>
                        <span id="res-weakest-comm-sub" class="block text-xs font-mono text-amber-700 font-semibold mt-0.5">₹0.00 Commission</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6 Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Sales</span>
                <span id="res-total-sales" class="text-lg font-bold font-mono text-slate-900 mt-1 block">₹0.00</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Potential</span>
                <span id="res-total-potential" class="text-lg font-bold font-mono text-slate-900 mt-1 block">₹0.00</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">Weakest Person</span>
                <span id="res-card-weakest-person" class="text-lg font-bold text-amber-700 mt-1 block">Person ?</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Weakest Sales</span>
                <span id="res-weakest-sales" class="text-lg font-bold font-mono text-slate-900 mt-1 block">₹0.00</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Weakest Commission</span>
                <span id="res-weakest-commission" class="text-lg font-bold font-mono text-amber-600 mt-1 block">₹0.00</span>
            </div>
            <div class="bg-indigo-50/70 p-4 rounded-xl border border-indigo-200 shadow-sm">
                <span class="text-[11px] font-semibold text-indigo-700 uppercase tracking-wider block">Final Commission</span>
                <span id="res-final-commission" class="text-lg font-bold font-mono text-indigo-700 mt-1 block">₹0.00</span>
            </div>
        </div>
    </div>
</div>

<script>
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

    function getLetter(index) {
        if (index < letters.length) {
            return letters[index];
        }
        return 'Person_' + (index + 1);
    }

    function collectCurrentValues() {
        const rows = document.querySelectorAll('#levels-tbody tr');
        const data = {};
        rows.forEach(tr => {
            const level = tr.getAttribute('data-level');
            if (level) {
                const rateInput = tr.querySelector('input[name$="[commission_rate]"]');
                const mainPersonInput = tr.querySelector('input[name$="[main_person]"]');
                const mainSalesInput = tr.querySelector('input[name$="[main_sales]"]');
                const sidePersonInput = tr.querySelector('input[name$="[side_person]"]');
                const sideSalesInput = tr.querySelector('input[name$="[side_sales]"]');

                data[level] = {
                    commission_rate: rateInput ? rateInput.value : '',
                    main_person: mainPersonInput ? mainPersonInput.value : '',
                    main_sales: mainSalesInput ? mainSalesInput.value : '',
                    side_person: sidePersonInput ? sidePersonInput.value : '',
                    side_sales: sideSalesInput ? sideSalesInput.value : ''
                };
            }
        });
        return data;
    }

    function generateLevels(initialData = null) {
        const input = document.getElementById('number_of_levels');
        let count = parseInt(input.value, 10);

        if (isNaN(count) || count < 1) {
            count = 1;
            input.value = 1;
        } else if (count > 100) {
            count = 100;
            input.value = 100;
        }

        const currentValues = initialData || collectCurrentValues();
        const tbody = document.getElementById('levels-tbody');
        const container = document.getElementById('levels-table-container');
        const alertBox = document.getElementById('no-levels-alert');
        const counterText = document.getElementById('level-counter-text');
        const footerBottom = document.getElementById('footer-bottom-level');
        const defaultRate = document.getElementById('commission_rate') ? document.getElementById('commission_rate').value : '5.00';

        tbody.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            const isBottom = (i === count);
            const defaultMain = getLetter(i - 1);
            const defaultSide = 'S' + i;

            const existing = currentValues[i] || {};
            const rateVal = existing.commission_rate !== undefined && existing.commission_rate !== '' ? existing.commission_rate : defaultRate;
            const mainPersonVal = existing.main_person !== undefined ? existing.main_person : defaultMain;
            const mainSalesVal = existing.main_sales !== undefined ? existing.main_sales : '1000.00';
            const sidePersonVal = existing.side_person !== undefined ? existing.side_person : defaultSide;
            const sideSalesVal = existing.side_sales !== undefined ? existing.side_sales : '1000.00';

            const tr = document.createElement('tr');
            tr.setAttribute('data-level', i);
            tr.className = `hover:bg-slate-50/70 transition-colors ${isBottom ? 'bg-amber-50/30' : ''}`;

            tr.innerHTML = `
                <td class="py-3 px-4 font-mono font-medium text-center">
                    <input type="hidden" name="levels[${i - 1}][level]" value="${i}">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-md ${isBottom ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'} text-xs font-semibold">
                        ${i}
                    </span>
                    ${isBottom ? '<span class="block text-[9px] text-amber-600 font-sans font-medium">Bottom</span>' : ''}
                </td>
                <td class="py-3 px-3 text-center">
                    <div class="relative flex items-center justify-center">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            max="100" 
                            name="levels[${i - 1}][commission_rate]" 
                            value="${rateVal}"
                            placeholder="${defaultRate}"
                            class="w-20 text-xs font-mono font-semibold text-center rounded-lg border border-slate-200 px-2 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-indigo-700" 
                            required
                        >
                        <span class="ml-1 text-[11px] text-slate-400 font-semibold">%</span>
                    </div>
                </td>
                <td class="py-3 px-5">
                    <input 
                        type="text" 
                        name="levels[${i - 1}][main_person]" 
                        value="${mainPersonVal}"
                        class="w-full text-xs font-medium rounded-lg border border-slate-200 px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                        required
                    >
                </td>
                <td class="py-3 px-5">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-semibold">₹</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="levels[${i - 1}][main_sales]" 
                            value="${mainSalesVal}"
                            class="w-full text-xs font-mono rounded-lg border border-slate-200 pl-6 pr-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                            required
                        >
                    </div>
                </td>
                <td class="py-3 px-5">
                    <input 
                        type="text" 
                        name="levels[${i - 1}][side_person]" 
                        value="${sidePersonVal}"
                        class="w-full text-xs font-medium rounded-lg border border-slate-200 px-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                        required
                    >
                </td>
                <td class="py-3 px-5">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-semibold">₹</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            name="levels[${i - 1}][side_sales]" 
                            value="${sideSalesVal}"
                            class="w-full text-xs font-mono rounded-lg border border-slate-200 pl-6 pr-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                            required
                        >
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        }

        container.classList.remove('hidden');
        alertBox.classList.add('hidden');
        counterText.textContent = `${count} Levels Configured`;
        footerBottom.textContent = count;
    }

    function applyPreset(levels) {
        document.getElementById('number_of_levels').value = levels;
        generateLevels();
    }

    function formatCurrency(amount) {
        if (amount === undefined || amount === null) return '₹0.00';
        const num = Number(amount);
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showAjaxError(title, errors) {
        const alert = document.getElementById('ajax-error-alert');
        const titleElem = document.getElementById('ajax-error-title');
        const list = document.getElementById('ajax-error-list');
        titleElem.textContent = title;
        list.innerHTML = '';
        (errors || []).forEach(err => {
            const li = document.createElement('li');
            li.textContent = err;
            list.appendChild(li);
        });
        alert.classList.remove('hidden');
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideAjaxError() {
        const alert = document.getElementById('ajax-error-alert');
        alert.classList.add('hidden');
    }

    async function calculateModel() {
        hideAjaxError();

        const rateInput = document.getElementById('commission_rate');
        const numLevelsInput = document.getElementById('number_of_levels');
        const nameInput = document.getElementById('name');

        const rate = parseFloat(rateInput.value);
        if (isNaN(rate) || rate < 0) {
            showAjaxError('Validation Error', ['Commission rate must be a valid non-negative number.']);
            return;
        }

        const numLevels = parseInt(numLevelsInput.value, 10);
        const rows = document.querySelectorAll('#levels-tbody tr');
        if (rows.length === 0) {
            showAjaxError('Validation Error', ['Please configure at least 1 level.']);
            return;
        }

        const levels = [];
        rows.forEach(tr => {
            const lvlVal = parseInt(tr.querySelector('input[name$="[level]"]').value, 10);
            const rateInput = tr.querySelector('input[name$="[commission_rate]"]');
            const commRate = rateInput && rateInput.value !== '' ? parseFloat(rateInput.value) : rate;
            const mainPerson = tr.querySelector('input[name$="[main_person]"]').value.trim();
            const mainSales = parseFloat(tr.querySelector('input[name$="[main_sales]"]').value) || 0;
            const sidePerson = tr.querySelector('input[name$="[side_person]"]').value.trim();
            const sideSales = parseFloat(tr.querySelector('input[name$="[side_sales]"]').value) || 0;

            levels.push({
                level: lvlVal,
                commission_rate: commRate,
                main_person: mainPerson,
                main_sales: mainSales,
                side_person: sidePerson,
                side_sales: sideSales,
            });
        });

        const btn = document.getElementById('btn-calculate');
        const spinner = document.getElementById('calc-spinner');
        const icon = document.getElementById('calc-icon');
        const btnText = document.getElementById('calc-btn-text');

        btn.disabled = true;
        spinner.classList.remove('hidden');
        icon.classList.add('hidden');
        btnText.textContent = 'Calculating in Engine...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('{{ route('commission-models.calculate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    name: nameInput.value || 'Commission Model',
                    commission_rate: rate,
                    number_of_levels: numLevels,
                    levels: levels,
                }),
            });

            const json = await response.json();
            if (!response.ok || !json.success) {
                showAjaxError('Calculation Error', json.errors || [json.message]);
                return;
            }

            const data = json.data;
            document.getElementById('res-total-sales').textContent = formatCurrency(data.total_sales);
            document.getElementById('res-total-potential').textContent = formatCurrency(data.total_potential_commission);
            document.getElementById('res-card-weakest-person').textContent = data.weakest_person;
            document.getElementById('res-weakest-person').textContent = data.weakest_person;
            document.getElementById('res-weakest-person-name').textContent = data.weakest_person;
            document.getElementById('res-weakest-sales').textContent = formatCurrency(data.weakest_sales);
            document.getElementById('res-weakest-sales-banner').textContent = formatCurrency(data.weakest_sales);
            document.getElementById('res-weakest-commission').textContent = formatCurrency(data.weakest_commission);
            document.getElementById('res-weakest-comm-sub').textContent = `${formatCurrency(data.weakest_commission)} Commission`;
            document.getElementById('res-final-commission').textContent = formatCurrency(data.final_commission);
            document.getElementById('res-final-commission-banner').textContent = formatCurrency(data.final_commission);
            document.getElementById('res-weakest-initial').textContent = (data.weakest_person || '?').charAt(0);

            document.getElementById('calculation-results-section').classList.remove('hidden');
        } catch (err) {
            showAjaxError('Network Error', ['Failed to communicate with calculation engine.']);
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            icon.classList.remove('hidden');
            btnText.textContent = 'Preview Recalculation';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btnGen = document.getElementById('btn-generate-levels');
        if (btnGen) {
            btnGen.addEventListener('click', () => generateLevels());
        }

        const btnCalc = document.getElementById('btn-calculate');
        if (btnCalc) {
            btnCalc.addEventListener('click', () => calculateModel());
        }

        // Initialize with existing saved levels
        const existingData = @json($editLevels);
        generateLevels(existingData);

        // Accidental double submission protection
        let isSaving = false;
        const form = document.getElementById('commission-model-form');
        form.addEventListener('submit', (e) => {
            if (isSaving) {
                e.preventDefault();
                return false;
            }
            isSaving = true;

            const btnSubmit = document.getElementById('btn-submit');
            const saveSpinner = document.getElementById('save-spinner');
            const saveIcon = document.getElementById('save-icon');
            const saveBtnText = document.getElementById('save-btn-text');
            const btnCalc = document.getElementById('btn-calculate');

            if (btnSubmit) btnSubmit.disabled = true;
            if (btnCalc) btnCalc.disabled = true;
            if (saveSpinner) saveSpinner.classList.remove('hidden');
            if (saveIcon) saveIcon.classList.add('hidden');
            if (saveBtnText) saveBtnText.textContent = 'Updating Model...';
        });
    });
</script>
@endsection
