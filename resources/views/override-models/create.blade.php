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
                <span class="text-slate-900 font-semibold">{{ isset($duplicateModel) ? 'Duplicate Override Model' : 'Model 2 Builder' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isset($duplicateModel) ? 'Duplicate Level / Generation Override Model' : 'Level / Generation Override Commission Model' }}
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Model 2 Engine
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Every sale propagates upward through its independent upline hierarchy with custom override rates per edge, capped at a configurable maximum generation depth.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('commission-models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">
                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Switch to Model 1 (Weakest Link)
            </a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Saved Models
            </a>
        </div>
    </div>

    <!-- Model Comparison Banner -->
    <div class="p-4 rounded-xl bg-gradient-to-r from-emerald-900 to-slate-900 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500 text-white uppercase tracking-wider">Model 2 Invariants</span>
                <span class="text-xs text-emerald-200">No Weakest-Link • No MIN • Multi-Upline Overrides</span>
            </div>
            <p class="text-xs text-slate-300 max-w-3xl">
                Base sale amount remains constant as it travels upward (never reduced). Each parent-child relationship maintains its own configurable override percentage. Propagation halts after the configured max generation limit.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-load-preset-user-tree" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white border border-emerald-400/40 shadow transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Load Specification Example Tree
            </button>
            <button type="button" id="btn-load-preset-chain" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-colors flex items-center gap-1.5">
                Deep Chain (J = ₹1000, Max 5 Limit)
            </button>
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

    <form id="override-model-form" action="{{ route('override-models.store') }}" method="POST" class="space-y-8">
        @csrf

        <!-- SECTION 1: Model Parameters -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 1</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Model Parameters</h3>
                </div>
                <div class="text-xs text-slate-500">
                    Step 1 of 3: General Configuration
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
                        value="{{ old('name', isset($duplicateModel) ? 'Copy of ' . $duplicateModel->name : 'Generation Override Test Model') }}"
                        placeholder="e.g. Q4 Regional Generation Override Model"
                        class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                        required
                    >
                    <p class="text-[11px] text-slate-400 mt-1">Descriptive title for this override commission configuration.</p>
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
                    <p class="text-[11px] text-slate-400 mt-1">Default for new nodes. Can be customized per node below.</p>
                </div>

                <!-- Maximum Generations -->
                <div>
                    <label for="max_generations" class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Maximum Generations <span class="text-rose-500">*</span></span>
                        <span class="text-[10px] font-normal text-slate-400">Default: 5</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            id="max_generations" 
                            name="max_generations" 
                            value="{{ old('max_generations', $duplicateMaxGenerations ?? 5) }}"
                            min="1" 
                            max="50" 
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800 font-semibold"
                            required
                        >
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-medium pointer-events-none">gens</span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1.5 space-y-0.5">
                        <p class="font-medium text-emerald-700">Paid through generation <span id="hint-paid-gen">5</span>; Generation <span id="hint-excl-gen">6</span>+ excluded by model limit.</p>
                        <p class="text-slate-400 text-[10px]">Applies independently to every sale. Does not limit tree depth (trees may have 20+ levels).</p>
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
                        placeholder="e.g. Tree structure with custom overrides: E1 (200), F (400), C (300)"
                        class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                    >{{ old('description', isset($duplicateModel) ? $duplicateModel->description : '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Tree Builder & Hierarchy Visualizer -->
        <div class="space-y-4">
            @include('components.model2-tree-builder')
        </div>

        <!-- SECTION 3: Live Results (SUMMARY, COMMISSION BY PERSON, COMMISSION LEDGER) -->
        <div class="space-y-6">
            @include('components.model2-results')
        </div>

        <!-- Form Submission Actions -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
            <a href="{{ route('commission-models.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition-colors">
                Cancel
            </a>
            <div class="flex items-center gap-3">
                <button type="submit" id="btn-save-model" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                    Save Model 2 to Database
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const maxGenInput = document.getElementById('max_generations');
    const btnRecalc = document.getElementById('btn-recalculate');
    const btnLoadUserTree = document.getElementById('btn-load-preset-user-tree');
    const btnLoadChain = document.getElementById('btn-load-preset-chain');
    const form = document.getElementById('override-model-form');

    if (btnLoadUserTree) {
        btnLoadUserTree.onclick = function () {
            if (maxGenInput) maxGenInput.value = 5;
            const nameInput = document.getElementById('name');
            if (nameInput) nameInput.value = 'Level Override Example';
            if (window.TreeBuilder) {
                window.TreeBuilder.loadSpecificationPreset();
            }
        };
    }

    if (btnLoadChain) {
        btnLoadChain.onclick = function () {
            if (maxGenInput) maxGenInput.value = 5;
            const nameInput = document.getElementById('name');
            if (nameInput) nameInput.value = '10-Level Chain (Max 5 Generation Cutoff)';
            if (window.TreeBuilder) {
                window.TreeBuilder.loadChainPreset();
            }
        };
    }

    function updateMaxGenHints() {
        const val = parseInt(maxGenInput?.value, 10) || 5;
        const paidSpan = document.getElementById('hint-paid-gen');
        const exclSpan = document.getElementById('hint-excl-gen');
        if (paidSpan) paidSpan.textContent = val;
        if (exclSpan) exclSpan.textContent = val + 1;
    }

    let debounceTimer = null;
    function scheduleRecalculate() {
        updateMaxGenHints();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(calculateLive, 300);
    }

    if (maxGenInput) {
        maxGenInput.oninput = scheduleRecalculate;
        updateMaxGenHints();
    }
    if (btnRecalc) btnRecalc.onclick = calculateLive;

    function calculateLive() {
        if (window.TreeBuilder && typeof window.TreeBuilder.validate === 'function') {
            window.TreeBuilder.validate();
        }

        const formData = new FormData(form);
        const alertBox = document.getElementById('ajax-error-alert');
        const alertList = document.getElementById('ajax-error-list');

        fetch("{{ route('override-models.calculate') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                if (alertBox && alertList) {
                    alertBox.classList.remove('hidden');
                    alertList.innerHTML = '';
                    const errs = res.errors || [res.message];
                    errs.forEach(e => {
                        const li = document.createElement('li');
                        li.innerText = e;
                        alertList.appendChild(li);
                    });
                }
                return;
            }

            if (alertBox) alertBox.classList.add('hidden');
            renderCalculationData(res.data);
        })
        .catch(err => {
            console.error('Calculation error:', err);
        });
    }

    window.recalculateOverrideModel = calculateLive;

    if (form) {
        form.addEventListener('submit', function (e) {
            if (window.TreeBuilder && !window.TreeBuilder.validate()) {
                e.preventDefault();
                document.getElementById('tree-validation-alert')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        });
    }

    // Initial calculation trigger
    function renderCalculationData(data) {
        if (typeof window.renderModel2ResultsView === 'function') {
            window.renderModel2ResultsView(data);
        }
    }

    // Initial calculation trigger
    calculateLive();
});
</script>
@endsection
