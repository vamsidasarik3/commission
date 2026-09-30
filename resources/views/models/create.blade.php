@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    {{-- ── Header & Breadcrumbs ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-indigo-600 transition-colors">Saved Models</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">{{ isset($duplicateModel) ? 'Duplicate Model 1' : 'Model 1 Builder' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isset($duplicateModel) ? 'Duplicate Weakest Link Model' : 'Weakest Link Commission Model' }}
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                    Model 1 Engine
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Linear sales hierarchy with side-by-side branch comparison. Each level evaluates <code class="text-indigo-600 font-mono text-xs">MIN(main, side)</code> to determine upward commission propagation.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white shadow-sm shadow-indigo-600/20">
                Model 1 (Weakest Link)
            </span>
            <a href="{{ route('override-models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                Model 2 (Override) &rarr;
            </a>
            <a href="{{ route('unilevel-models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-violet-50 text-violet-700 hover:bg-violet-100 border border-violet-200 transition-colors">
                Model 3 (Unilevel) &rarr;
            </a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                Saved Models
            </a>
        </div>
    </div>

    {{-- ── Invariants Banner ── --}}
    <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950 via-slate-900 to-indigo-900 text-white shadow-md flex flex-col lg:flex-row lg:items-center justify-between gap-5">
        <div class="space-y-1.5 max-w-3xl">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-500 text-white uppercase tracking-wider">Model 1 Invariants</span>
                <span class="text-xs text-indigo-200">MIN(Main Child, Side Salesperson) &bull; Bottleneck Constraint</span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed">
                At every level, the leader's commission is restricted to the minimum between their main child branch and their direct side salesperson: <code class="text-indigo-300 font-mono">leader = min(main, side)</code>. Each salesperson can have their own commission percentage. The weakest link bottlenecks the entire chain up to Top Leader A.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2" id="banner-preset-buttons">
            <button type="button" data-strategy-btn="progressive" onclick="selectPresetStrategy('progressive')" class="px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white border border-indigo-400/40 shadow transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                Progressive Ladder (3%–15%)
            </button>
            <button type="button" data-strategy-btn="regressive" onclick="selectPresetStrategy('regressive')" class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                Margin Decay (15%–2.5%)
            </button>
            <button type="button" data-strategy-btn="dynamic" onclick="selectPresetStrategy('dynamic')" class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Dynamic Non-Fixed Rates
            </button>
            <button type="button" data-strategy-btn="default" onclick="selectPresetStrategy('default')" class="px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Default 10-Level
            </button>
        </div>
    </div>

    {{-- ── Strategy Presets Checkable Cards ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Interactive Presets</span>
                <h3 class="text-base font-bold text-slate-900 mt-1">Choose Strategy / Preset to Create Model</h3>
                <p class="text-xs text-slate-500 mt-0.5">Click any strategy card to check and configure its complete 10-level hierarchy and rates.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">10-Level Architectures</span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" id="strategy-cards-container">
                {{-- Card 1: Progressive Ladder --}}
                <div 
                    id="card-strategy-progressive"
                    onclick="selectPresetStrategy('progressive')"
                    class="strategy-card relative rounded-xl border-2 border-indigo-600 bg-indigo-50/30 p-5 cursor-pointer transition-all hover:shadow-md flex flex-col justify-between group"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 uppercase tracking-wider">
                                Escalating Rates
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span id="badge-check-progressive" class="check-badge inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    Checked
                                </span>
                                <input type="radio" name="strategy_preset_radio" id="radio-progressive" value="progressive" checked class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                Progressive Ladder (3%–15%)
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Commission rates scale progressively from 3% at top to 15% at deep leaf level.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-white border border-slate-200/80 space-y-1 text-xs font-mono">
                            <div class="flex justify-between text-slate-600">
                                <span>Rate Escalation:</span>
                                <span class="font-bold text-indigo-600">3.00% &rarr; 15.00%</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Depth:</span>
                                <span class="font-semibold text-slate-800">10 Levels</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Leaf Sales:</span>
                                <span class="font-semibold text-slate-800">₹300.00 @ 15%</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-slate-200/60 flex flex-col gap-2">
                        <button 
                            type="button" 
                            onclick="event.stopPropagation(); selectPresetStrategy('progressive', true)" 
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition-colors"
                        >
                            <span>Create with Progressive</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Card 2: Margin Decay --}}
                <div 
                    id="card-strategy-regressive"
                    onclick="selectPresetStrategy('regressive')"
                    class="strategy-card relative rounded-xl border border-slate-200 bg-white p-5 cursor-pointer transition-all hover:border-slate-300 hover:shadow-md flex flex-col justify-between group"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
                                Margin Decay
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span id="badge-check-regressive" class="check-badge hidden items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    Checked
                                </span>
                                <input type="radio" name="strategy_preset_radio" id="radio-regressive" value="regressive" class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                Margin Decay (15%–2.5%)
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                High upfront commission for upper tiers tapering down to 2.5% for deeper branches.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-1 text-xs font-mono">
                            <div class="flex justify-between text-slate-600">
                                <span>Rate Decay:</span>
                                <span class="font-bold text-amber-700">15.00% &rarr; 2.50%</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Depth:</span>
                                <span class="font-semibold text-slate-800">10 Levels</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Top Side Sales:</span>
                                <span class="font-semibold text-slate-800">₹500.00 @ 15%</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-slate-200/60 flex flex-col gap-2">
                        <button 
                            type="button" 
                            onclick="event.stopPropagation(); selectPresetStrategy('regressive', true)" 
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition-colors"
                        >
                            <span>Create with Margin Decay</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Card 3: Dynamic Non-Fixed Rates --}}
                <div 
                    id="card-strategy-dynamic"
                    onclick="selectPresetStrategy('dynamic')"
                    class="strategy-card relative rounded-xl border border-slate-200 bg-white p-5 cursor-pointer transition-all hover:border-slate-300 hover:shadow-md flex flex-col justify-between group"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">
                                Rep Customized
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span id="badge-check-dynamic" class="check-badge hidden items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    Checked
                                </span>
                                <input type="radio" name="strategy_preset_radio" id="radio-dynamic" value="dynamic" class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                Dynamic Non-Fixed Rates
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Completely independent, rep-specific commission percentages across all 10 tiers.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-1 text-xs font-mono">
                            <div class="flex justify-between text-slate-600">
                                <span>Rate Variance:</span>
                                <span class="font-bold text-purple-700">3.50% – 12.00%</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Depth:</span>
                                <span class="font-semibold text-slate-800">10 Levels</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Custom Rates:</span>
                                <span class="font-semibold text-slate-800">10 Distinct Rates</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-slate-200/60 flex flex-col gap-2">
                        <button 
                            type="button" 
                            onclick="event.stopPropagation(); selectPresetStrategy('dynamic', true)" 
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition-colors"
                        >
                            <span>Create with Dynamic Rates</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Card 4: Default 10-Level Baseline --}}
                <div 
                    id="card-strategy-default"
                    onclick="selectPresetStrategy('default')"
                    class="strategy-card relative rounded-xl border border-slate-200 bg-white p-5 cursor-pointer transition-all hover:border-slate-300 hover:shadow-md flex flex-col justify-between group"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase tracking-wider">
                                Standard Baseline
                            </span>
                            <div class="flex items-center gap-1.5">
                                <span id="badge-check-default" class="check-badge hidden items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-indigo-600 text-white shadow-xs">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    Checked
                                </span>
                                <input type="radio" name="strategy_preset_radio" id="radio-default" value="default" class="w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                Default 10-Level Baseline
                            </h4>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Standard 10-tier linear hierarchy with 5% fallback rate and balanced side branch sales.
                            </p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-1 text-xs font-mono">
                            <div class="flex justify-between text-slate-600">
                                <span>Default Rate:</span>
                                <span class="font-bold text-slate-800">5.00% Baseline</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Depth:</span>
                                <span class="font-semibold text-slate-800">10 Levels</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Structure:</span>
                                <span class="font-semibold text-slate-800">B through K + S1-S10</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-2 border-t border-slate-200/60 flex flex-col gap-2">
                        <button 
                            type="button" 
                            onclick="event.stopPropagation(); selectPresetStrategy('default', true)" 
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-900 text-white shadow-sm transition-colors"
                        >
                            <span>Create with Baseline</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Error Alerts ── --}}
    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2">
            <div class="flex items-center gap-2 font-semibold text-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Please correct the errors below:
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div id="ajax-error-alert" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2 hidden">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="ajax-error-title">Validation Error</span>
        </div>
        <ul id="ajax-error-list" class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5"></ul>
    </div>

    {{-- ── Primary Builder Form ── --}}
    <form id="commission-model-form" action="{{ route('commission-models.store') }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="model_type" value="weakest_link">

        {{-- Section 1: Model Details --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Section 1</span>
                    <h3 class="text-base font-bold text-slate-900 mt-1">Model Specifications</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Specify model title, fallback rate, and level depth (minimum 10 levels).</p>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Model Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            value="{{ old('name', isset($duplicateModel) ? 'Copy of ' . $duplicateModel->name : 'Model 1: Progressive Escalation Ladder') }}"
                            placeholder="e.g. 10-Level Progressive Escalation"
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
                            required
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Unique title to identify this commission structure.</p>
                    </div>

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
                                value="{{ old('commission_rate', isset($duplicateModel) ? number_format($duplicateModel->commission_rate, 2, '.', '') : '5.00') }}"
                                placeholder="5.00"
                                class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 pr-9"
                                required
                            >
                            <span class="absolute right-3.5 top-2.5 text-sm font-semibold text-slate-400">%</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Fallback rate. Each level can have its own different rate.</p>
                    </div>

                    <div>
                        <label for="number_of_levels" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Number of Levels <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="number" 
                                id="number_of_levels" 
                                name="number_of_levels" 
                                min="1" 
                                max="100" 
                                value="{{ old('number_of_levels', isset($duplicateModel) ? $duplicateModel->number_of_levels : 10) }}"
                                class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
                                required
                            >
                            <button 
                                type="button" 
                                id="btn-generate-levels"
                                onclick="generateLevels()"
                                class="px-4 py-2.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors whitespace-nowrap"
                            >
                                Generate
                            </button>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Number of hierarchical levels (e.g. 10).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Sales Hierarchy Structure --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Section 2</span>
                    <h3 class="text-base font-bold text-slate-900 mt-1">Sales Hierarchy Table</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Customize individual commission rates (%), main person sales, and side salesperson sales per level.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="level-counter-text" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        10 Levels Defined
                    </span>
                </div>
            </div>

            <div id="no-levels-alert" class="p-8 text-center hidden">
                <p class="text-sm text-slate-500 font-medium">No levels currently generated.</p>
                <p class="text-xs text-slate-400 mt-1">Click "Generate" above to build the sales table.</p>
            </div>

            <div id="levels-table-container" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 font-semibold uppercase tracking-wider">
                            <th class="py-3 px-4 w-16 text-center">Level</th>
                            <th class="py-3 px-3 w-32 text-center">Rate (%)</th>
                            <th class="py-3 px-5">Main Person</th>
                            <th class="py-3 px-5">Main Sales (₹)</th>
                            <th class="py-3 px-5">Side Person</th>
                            <th class="py-3 px-5">Side Sales (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="levels-tbody" class="divide-y divide-slate-100 text-slate-700 font-sans">
                        {{-- Populated by JavaScript --}}
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500">
                    <span class="font-medium text-slate-700">Weakest Link Rule:</span> Calculates <code class="bg-slate-200/60 px-1 py-0.5 rounded text-indigo-700">min(main, side)</code> upward from bottom level to Leader A.
                </div>
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <button 
                        type="button" 
                        id="btn-calculate"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 text-xs font-semibold rounded-lg bg-indigo-100 text-indigo-800 hover:bg-indigo-200 border border-indigo-200 transition-colors shadow-sm"
                    >
                        <svg id="calc-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span>Preview Live Calculation</span>
                    </button>

                    <button 
                        type="submit" 
                        id="btn-submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-all"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <span>Calculate & Save Model</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Section 3: Live Calculation Results & Audit Table --}}
        <div id="results-wrapper" class="space-y-6">
            {{-- KPI Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sales Volume</span>
                    <div id="res-total-sales" class="text-2xl font-bold font-mono text-slate-900 mt-2">₹0.00</div>
                    <p class="text-[11px] text-slate-400 mt-1">Sum of all main & side sales</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Potential Commission</span>
                    <div id="res-potential-commission" class="text-2xl font-bold font-mono text-indigo-600 mt-2">₹0.00</div>
                    <p class="text-[11px] text-slate-400 mt-1">Sum of unconstrained payouts</p>
                </div>

                <div class="bg-white rounded-2xl border-2 border-emerald-500 p-6 shadow-sm bg-emerald-50/20">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Final Leader Commission</span>
                    <div id="res-final-commission" class="text-2xl font-bold font-mono text-emerald-700 mt-2">₹0.00</div>
                    <p id="res-weakest-note" class="text-[11px] text-emerald-800 font-semibold mt-1">Bottlenecked by Weakest Link</p>
                </div>
            </div>

            {{-- Step-by-Step Level Breakdown --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <h4 class="text-sm font-bold text-slate-900">Step-by-Step Level Calculation Audit</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Evaluation of each tier's selected minimum and resulting leader commission.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider">
                                <th class="py-3 px-3 w-14 text-center">Level</th>
                                <th class="py-3 px-3 w-20 text-center">Rate %</th>
                                <th class="py-3 px-4">Main Person</th>
                                <th class="py-3 px-4">Main Sales</th>
                                <th class="py-3 px-4">Main Comm</th>
                                <th class="py-3 px-4">Side Person</th>
                                <th class="py-3 px-4">Side Sales</th>
                                <th class="py-3 px-4">Side Comm</th>
                                <th class="py-3 px-4 bg-indigo-50/60 text-indigo-950 font-bold">Selected Minimum</th>
                                <th class="py-3 px-4 bg-emerald-50/60 text-emerald-950 font-bold">Leader Comm</th>
                            </tr>
                        </thead>
                        <tbody id="detailed-calc-tbody" class="divide-y divide-slate-100 text-slate-700 font-sans">
                            {{-- Populated from calculation response --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Live Interactive Hierarchy Diagram & Commission Flow Preview --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="live-hierarchy-diagram-card">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm shadow-indigo-600/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span>Hierarchy Diagram & Commission Flow Preview</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase tracking-wider">
                                    Live Preview
                                </span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Visual branch tree illustrating pairwise MIN evaluations and bottleneck propagation.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            id="btn-export-svg" 
                            onclick="exportLiveTreeImage()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition-all"
                        >
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download Tree (PNG)</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4 text-xs">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded bg-amber-100 border border-amber-500 inline-block"></span>
                            <span class="text-slate-600 font-medium">Leader / Chain Node</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded bg-emerald-100 border border-emerald-500 inline-block"></span>
                            <span class="text-slate-600 font-medium">Salesperson / Leaf</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded bg-rose-100 border-2 border-dashed border-rose-500 inline-block"></span>
                            <span class="text-rose-700 font-bold">★ Weakest Link Bottleneck</span>
                        </div>
                    </div>
                    <div class="text-slate-500 font-mono text-[11px]">
                        Scroll or drag horizontally to view full 10-level hierarchy
                    </div>
                </div>

                <div id="live-tree-wrapper" class="overflow-x-auto p-6 bg-white flex justify-center">
                    <svg id="live-tree-svg" class="overflow-visible" style="min-height: 520px;"></svg>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- ── Client-Side Controller Script ── --}}
<script>
(function() {
    'use strict';

    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');

    function getLetter(index) {
        if (index < letters.length) {
            return letters[index];
        }
        return 'Person_' + (index + 1);
    }

    function formatCurrency(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Pre-configured 10-level strategies with full valid numerical amounts ──
    const PRESETS = {
        progressive: {
            key: 'progressive',
            name: 'Model 1: Progressive Escalation Ladder',
            commission_rate: '5.00',
            levels_count: 10,
            data: {
                1:  { commission_rate: '3.00',  main_person: 'B', main_sales: '0.00', side_person: 'S1',  side_sales: '2000.00' },
                2:  { commission_rate: '4.00',  main_person: 'C', main_sales: '0.00', side_person: 'S2',  side_sales: '1800.00' },
                3:  { commission_rate: '5.00',  main_person: 'D', main_sales: '0.00', side_person: 'S3',  side_sales: '1500.00' },
                4:  { commission_rate: '6.00',  main_person: 'E', main_sales: '0.00', side_person: 'S4',  side_sales: '1400.00' },
                5:  { commission_rate: '7.00',  main_person: 'F', main_sales: '0.00', side_person: 'S5',  side_sales: '1200.00' },
                6:  { commission_rate: '8.00',  main_person: 'G', main_sales: '0.00', side_person: 'S6',  side_sales: '1100.00' },
                7:  { commission_rate: '9.00',  main_person: 'H', main_sales: '0.00', side_person: 'S7',  side_sales: '1000.00' },
                8:  { commission_rate: '10.00', main_person: 'I', main_sales: '0.00', side_person: 'S8',  side_sales: '900.00' },
                9:  { commission_rate: '12.00', main_person: 'J', main_sales: '0.00', side_person: 'S9',  side_sales: '800.00' },
                10: { commission_rate: '15.00', main_person: 'K', main_sales: '300.00', side_person: 'S10', side_sales: '700.00' }
            }
        },
        regressive: {
            key: 'regressive',
            name: 'Model 1: Regressive Margin Decay Plan',
            commission_rate: '8.00',
            levels_count: 10,
            data: {
                1:  { commission_rate: '15.00', main_person: 'B', main_sales: '0.00', side_person: 'S1',  side_sales: '500.00' },
                2:  { commission_rate: '12.50', main_person: 'C', main_sales: '0.00', side_person: 'S2',  side_sales: '600.00' },
                3:  { commission_rate: '10.00', main_person: 'D', main_sales: '0.00', side_person: 'S3',  side_sales: '800.00' },
                4:  { commission_rate: '8.50',  main_person: 'E', main_sales: '0.00', side_person: 'S4',  side_sales: '900.00' },
                5:  { commission_rate: '7.00',  main_person: 'F', main_sales: '0.00', side_person: 'S5',  side_sales: '1000.00' },
                6:  { commission_rate: '6.00',  main_person: 'G', main_sales: '0.00', side_person: 'S6',  side_sales: '1200.00' },
                7:  { commission_rate: '5.00',  main_person: 'H', main_sales: '0.00', side_person: 'S7',  side_sales: '1400.00' },
                8:  { commission_rate: '4.00',  main_person: 'I', main_sales: '0.00', side_person: 'S8',  side_sales: '1600.00' },
                9:  { commission_rate: '3.00',  main_person: 'J', main_sales: '0.00', side_person: 'S9',  side_sales: '1000.00' },
                10: { commission_rate: '2.50',  main_person: 'K', main_sales: '2500.00', side_person: 'S10', side_sales: '2000.00' }
            }
        },
        dynamic: {
            key: 'dynamic',
            name: 'Model 1: Dynamic Rep-Specific Rates',
            commission_rate: '7.00',
            levels_count: 10,
            data: {
                1:  { commission_rate: '5.50',  main_person: 'B', main_sales: '0.00', side_person: 'S1',  side_sales: '1500.00' },
                2:  { commission_rate: '8.00',  main_person: 'C', main_sales: '0.00', side_person: 'S2',  side_sales: '1200.00' },
                3:  { commission_rate: '4.50',  main_person: 'D', main_sales: '0.00', side_person: 'S3',  side_sales: '2000.00' },
                4:  { commission_rate: '11.00', main_person: 'E', main_sales: '0.00', side_person: 'S4',  side_sales: '800.00' },
                5:  { commission_rate: '6.50',  main_person: 'F', main_sales: '0.00', side_person: 'S5',  side_sales: '1400.00' },
                6:  { commission_rate: '9.50',  main_person: 'G', main_sales: '0.00', side_person: 'S6',  side_sales: '1000.00' },
                7:  { commission_rate: '3.50',  main_person: 'H', main_sales: '0.00', side_person: 'S7',  side_sales: '2500.00' },
                8:  { commission_rate: '12.00', main_person: 'I', main_sales: '0.00', side_person: 'S8',  side_sales: '750.00' },
                9:  { commission_rate: '7.50',  main_person: 'J', main_sales: '0.00', side_person: 'S9',  side_sales: '1100.00' },
                10: { commission_rate: '10.00', main_person: 'K', main_sales: '500.00', side_person: 'S10', side_sales: '900.00' }
            }
        },
        default: {
            key: 'default',
            name: '10-Level Progressive Escalation Model',
            commission_rate: '5.00',
            levels_count: 10,
            data: {
                1:  { commission_rate: '3.00',  main_person: 'B', main_sales: '0.00', side_person: 'S1',  side_sales: '2000.00' },
                2:  { commission_rate: '4.00',  main_person: 'C', main_sales: '0.00', side_person: 'S2',  side_sales: '1800.00' },
                3:  { commission_rate: '5.00',  main_person: 'D', main_sales: '0.00', side_person: 'S3',  side_sales: '1500.00' },
                4:  { commission_rate: '6.00',  main_person: 'E', main_sales: '0.00', side_person: 'S4',  side_sales: '1400.00' },
                5:  { commission_rate: '7.00',  main_person: 'F', main_sales: '0.00', side_person: 'S5',  side_sales: '1200.00' },
                6:  { commission_rate: '8.00',  main_person: 'G', main_sales: '0.00', side_person: 'S6',  side_sales: '1100.00' },
                7:  { commission_rate: '9.00',  main_person: 'H', main_sales: '0.00', side_person: 'S7',  side_sales: '1000.00' },
                8:  { commission_rate: '10.00', main_person: 'I', main_sales: '0.00', side_person: 'S8',  side_sales: '900.00' },
                9:  { commission_rate: '12.00', main_person: 'J', main_sales: '0.00', side_person: 'S9',  side_sales: '800.00' },
                10: { commission_rate: '15.00', main_person: 'K', main_sales: '300.00', side_person: 'S10', side_sales: '700.00' }
            }
        }
    };

    /**
     * Activate a selected preset strategy, update UI cards and banner buttons,
     * populate table with guaranteed valid numeric values, and trigger live calculation.
     */
    window.selectPresetStrategy = function(strategyKey, autoSubmit = false) {
        const preset = PRESETS[strategyKey] || PRESETS.progressive;

        // 1. Update radio check state
        const radio = document.getElementById('radio-' + strategyKey);
        if (radio) {
            radio.checked = true;
        }

        // 2. Update visual card active states
        document.querySelectorAll('.strategy-card').forEach(card => {
            card.classList.remove('border-indigo-600', 'bg-indigo-50/30', 'ring-2', 'ring-indigo-500/20');
            card.classList.add('border-slate-200', 'bg-white');
        });
        document.querySelectorAll('.check-badge').forEach(badge => {
            badge.classList.remove('inline-flex');
            badge.classList.add('hidden');
        });

        const activeCard = document.getElementById('card-strategy-' + strategyKey);
        if (activeCard) {
            activeCard.classList.remove('border-slate-200', 'bg-white');
            activeCard.classList.add('border-indigo-600', 'bg-indigo-50/30', 'ring-2', 'ring-indigo-500/20');
        }
        const activeBadge = document.getElementById('badge-check-' + strategyKey);
        if (activeBadge) {
            activeBadge.classList.remove('hidden');
            activeBadge.classList.add('inline-flex');
        }

        // 3. Update top banner button highlights
        document.querySelectorAll('[data-strategy-btn]').forEach(btn => {
            const btnKey = btn.getAttribute('data-strategy-btn');
            if (btnKey === strategyKey) {
                btn.className = 'px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white border border-indigo-400/40 shadow transition-all flex items-center gap-1.5';
            } else {
                btn.className = 'px-3 py-2 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5';
            }
        });

        // 4. Update form top-level inputs
        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.value = preset.name;
        }
        const rateInput = document.getElementById('commission_rate');
        if (rateInput) {
            rateInput.value = preset.commission_rate;
        }
        const levelsInput = document.getElementById('number_of_levels');
        if (levelsInput) {
            levelsInput.value = preset.levels_count;
        }

        // 5. Generate rows with full valid data
        generateLevels(preset.data);

        // 6. Clear any previous error alerts
        const errAlert = document.getElementById('ajax-error-alert');
        if (errAlert) {
            errAlert.classList.add('hidden');
        }

        // 7. Trigger server calculation engine
        triggerCalculateModel(function() {
            if (autoSubmit) {
                const form = document.getElementById('commission-model-form');
                if (form) {
                    form.submit();
                }
            }
        });
    };

    // Backward-compatible global trigger functions
    window.loadDefaultTenLevelPreset = function() { selectPresetStrategy('default'); };
    window.loadProgressivePreset    = function() { selectPresetStrategy('progressive'); };
    window.loadRegressivePreset     = function() { selectPresetStrategy('regressive'); };
    window.loadDynamicPreset        = function() { selectPresetStrategy('dynamic'); };

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
                    main_sales: mainSalesInput ? mainSalesInput.value : '0.00',
                    side_person: sidePersonInput ? sidePersonInput.value : '',
                    side_sales: sideSalesInput ? sideSalesInput.value : '1000.00'
                };
            }
        });
        return data;
    }

    window.generateLevels = function(overrideData = null) {
        const numInput = document.getElementById('number_of_levels');
        let count = parseInt(numInput.value, 10);

        if (isNaN(count) || count < 1) {
            count = 10;
            numInput.value = 10;
        }

        if (count > 100) {
            count = 100;
            numInput.value = 100;
        }

        const tbody = document.getElementById('levels-tbody');
        const counterText = document.getElementById('level-counter-text');
        const defaultRate = document.getElementById('commission_rate') ? document.getElementById('commission_rate').value : '5.00';

        const existingData = overrideData || collectCurrentValues();
        tbody.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            const defaultMain = getLetter(i);
            const defaultSide = 'S' + i;

            const prev = existingData[i] || {};
            const rateVal = (prev.commission_rate !== undefined && prev.commission_rate !== '') ? prev.commission_rate : defaultRate;
            const mainPersonVal = (prev.main_person !== undefined && prev.main_person !== '') ? prev.main_person : defaultMain;
            
            // Guaranteed numeric fallback for main_sales: intermediate derive from child (0.00), leaf bottom child has positive sales
            const mainSalesVal = (prev.main_sales !== undefined && prev.main_sales !== '' && prev.main_sales !== null) 
                ? prev.main_sales 
                : (i === count ? '300.00' : '0.00');

            const sidePersonVal = (prev.side_person !== undefined && prev.side_person !== '') ? prev.side_person : defaultSide;
            const sideSalesVal = (prev.side_sales !== undefined && prev.side_sales !== '' && prev.side_sales !== null) 
                ? prev.side_sales 
                : '1000.00';

            const tr = document.createElement('tr');
            tr.setAttribute('data-level', i);
            tr.className = 'hover:bg-slate-50/80 transition-colors';

            tr.innerHTML = `
                <td class="py-3 px-4 text-center font-bold text-slate-500 bg-slate-50/50">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 font-mono text-[11px]">
                        ${i}
                    </span>
                    <input type="hidden" name="levels[${i - 1}][level]" value="${i}">
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
                        placeholder="${defaultMain}"
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
                            placeholder="0.00"
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
                        placeholder="${defaultSide}"
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
                            placeholder="0.00"
                            class="w-full text-xs font-mono rounded-lg border border-slate-200 pl-6 pr-3 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" 
                            required
                        >
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        }

        if (counterText) {
            counterText.textContent = `${count} Levels Defined`;
        }

        attachAutoCalculate();
    };

    function attachAutoCalculate() {
        const inputs = document.querySelectorAll('#levels-tbody input, #commission_rate');
        inputs.forEach(input => {
            input.removeEventListener('input', onInputChange);
            input.addEventListener('input', onInputChange);
        });
    }

    let debounceTimer = null;
    function onInputChange() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { triggerCalculateModel(); }, 300);
    }

    window.triggerCalculateModel = function(onSuccessCallback = null) {
        const form = document.getElementById('commission-model-form');
        if (!form) return;

        const formData = new FormData(form);

        fetch("{{ route('commission-models.calculate') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            const errAlert = document.getElementById('ajax-error-alert');
            const errList = document.getElementById('ajax-error-list');

            if (status === 200 && body.success) {
                if (errAlert) errAlert.classList.add('hidden');
                renderModel1Results(body.data);
                if (typeof onSuccessCallback === 'function') {
                    onSuccessCallback(body.data);
                }
            } else {
                if (errAlert && errList) {
                    errList.innerHTML = '';
                    (body.errors || [body.message || 'Validation failed']).forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errList.appendChild(li);
                    });
                    errAlert.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            console.error('Calculation error:', err);
        });
    };

    function renderModel1Results(data) {
        const resultsWrapper = document.getElementById('results-wrapper');
        if (resultsWrapper) resultsWrapper.classList.remove('hidden');

        document.getElementById('res-total-sales').textContent = formatCurrency(data.total_sales);
        document.getElementById('res-potential-commission').textContent = formatCurrency(data.total_potential_commission);
        document.getElementById('res-final-commission').textContent = formatCurrency(data.final_commission);
        document.getElementById('res-weakest-note').textContent = `Bottlenecked by ${data.weakest_person} (Sales: ${formatCurrency(data.weakest_sales)} @ ${data.weakest_commission ? formatCurrency(data.weakest_commission) : ''})`;

        const tbody = document.getElementById('detailed-calc-tbody');
        tbody.innerHTML = '';

        (data.levels || []).forEach(lvl => {
            const tr = document.createElement('tr');
            const lvlRate = lvl.commission_rate !== undefined && lvl.commission_rate !== null ? parseFloat(lvl.commission_rate).toFixed(2) : parseFloat(data.commission_rate || 5).toFixed(2);
            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center font-bold font-mono bg-slate-50">${lvl.level}</td>
                <td class="py-2.5 px-3 text-center font-mono font-semibold text-indigo-700 bg-indigo-50/20">${lvlRate}%</td>
                <td class="py-2.5 px-4 font-semibold text-slate-900">${lvl.main_person}</td>
                <td class="py-2.5 px-4 font-mono">${formatCurrency(lvl.main_sales)}</td>
                <td class="py-2.5 px-4 font-mono text-slate-600">${formatCurrency(lvl.main_commission)}</td>
                <td class="py-2.5 px-4 font-semibold text-slate-900">${lvl.side_person}</td>
                <td class="py-2.5 px-4 font-mono">${formatCurrency(lvl.side_sales)}</td>
                <td class="py-2.5 px-4 font-mono text-slate-600">${formatCurrency(lvl.side_commission)}</td>
                <td class="py-2.5 px-4 font-mono font-bold bg-indigo-50/40 text-indigo-900">${formatCurrency(lvl.selected_minimum)}</td>
                <td class="py-2.5 px-4 font-mono font-bold bg-emerald-50/40 text-emerald-900">${formatCurrency(lvl.leader_commission)}</td>
            `;
            tbody.appendChild(tr);
        });

        // Render dynamic SVG tree hierarchy
        renderLiveSvgTree(data);
    }

    /**
     * Render the Weakest Link Hierarchy Diagram & Commission Flow SVG
     */
    function renderLiveSvgTree(treeData) {
        const svg = document.getElementById('live-tree-svg');
        if (!svg) return;
        svg.innerHTML = '';

        const levels = treeData.levels || [];
        const rate = parseFloat(treeData.commission_rate) || 5;
        const weakest = treeData.weakest_person || '';
        const topLeader = treeData.top_leader || 'A';

        const nodes = {};
        const edges = [];

        function getOrCreate(name) {
            if (!nodes[name]) {
                nodes[name] = { 
                    name: name, 
                    children: [], 
                    parent: null, 
                    is_leaf: true, 
                    sales: 0, 
                    commission: 0,
                    is_weakest: false
                };
            }
            return nodes[name];
        }

        const rootNode = getOrCreate(topLeader);
        rootNode.is_leaf = false;
        rootNode.commission = parseFloat(treeData.final_commission) || 0;
        rootNode.is_weakest = (topLeader === weakest);

        let currentLeader = topLeader;
        levels.forEach(function(lvl, idx) {
            const sideName = lvl.side_person;
            const mainName = lvl.main_person;
            const sideSales = parseFloat(lvl.side_sales) || 0;
            const sideComm = parseFloat(lvl.side_commission) || 0;
            const mainSales = parseFloat(lvl.main_sales) || 0;
            const mainComm = parseFloat(lvl.main_commission) || 0;
            const isLast = (idx === levels.length - 1);

            const sideNode = getOrCreate(sideName);
            sideNode.is_leaf = true;
            sideNode.sales = sideSales;
            sideNode.commission = sideComm;
            sideNode.parent = currentLeader;
            sideNode.is_weakest = (sideName === weakest);

            const mainNode = getOrCreate(mainName);
            mainNode.parent = currentLeader;
            mainNode.is_weakest = (mainName === weakest);

            if (isLast) {
                mainNode.is_leaf = true;
                mainNode.sales = mainSales;
                mainNode.commission = mainComm;
            } else {
                const nextLvl = levels[idx + 1];
                mainNode.is_leaf = false;
                mainNode.sales = mainSales;
                mainNode.commission = parseFloat(nextLvl ? nextLvl.leader_commission : mainComm) || mainComm;
            }

            const leaderNode = nodes[currentLeader];
            leaderNode.is_leaf = false;
            leaderNode.children.push(sideName);
            leaderNode.children.push(mainName);

            const lvlRate = lvl.commission_rate !== undefined && lvl.commission_rate !== null ? parseFloat(lvl.commission_rate) : rate;

            edges.push({
                from: currentLeader,
                to: sideName,
                pct: lvlRate,
                commAmt: sideComm,
                branch: 'side'
            });

            edges.push({
                from: currentLeader,
                to: mainName,
                pct: lvlRate,
                commAmt: mainComm,
                branch: 'main'
            });

            currentLeader = mainName;
        });

        const NW = 140;
        const NH = 68;
        const HGAP = 36;
        const VGAP = 90;

        const xCounter = [0];

        function computeX(name, visited) {
            if (!visited) visited = {};
            if (visited[name]) return;
            visited[name] = true;
            const n = nodes[name];
            if (!n) return;
            const children = n.children;
            if (!children || children.length === 0) {
                n._x = xCounter[0];
                xCounter[0] += NW + HGAP;
            } else {
                children.forEach(function(c) { computeX(c, visited); });
                const first = nodes[children[0]];
                const last = nodes[children[children.length-1]];
                n._x = (first._x + last._x) / 2;
            }
        }

        function assignDepths(name, depth, visited) {
            if (!visited) visited = {};
            if (visited[name]) return;
            visited[name] = true;
            const n = nodes[name];
            if (!n) return;
            n._depth = depth;
            (n.children || []).forEach(function(c) { assignDepths(c, depth + 1, visited); });
        }

        assignDepths(topLeader, 0);
        computeX(topLeader);

        let minX = Infinity, maxX = -Infinity, maxDepth = 0;
        Object.keys(nodes).forEach(function(k) {
            const n = nodes[k];
            if (n._x !== undefined) {
                if (n._x < minX) minX = n._x;
                if (n._x + NW > maxX) maxX = n._x + NW;
                if (n._depth > maxDepth) maxDepth = n._depth;
            }
        });

        const PAD = 30;
        const svgW = Math.max(maxX - minX + PAD * 2, 700);
        const svgH = (maxDepth + 1) * (NH + VGAP) + PAD * 2;

        svg.setAttribute('width', svgW);
        svg.setAttribute('height', svgH);
        svg.setAttribute('viewBox', `0 0 ${svgW} ${svgH}`);

        const shiftX = PAD - minX;
        Object.keys(nodes).forEach(function(k) {
            const n = nodes[k];
            if (n._x !== undefined) {
                n._px = n._x + shiftX;
                n._py = PAD + n._depth * (NH + VGAP);
            }
        });

        // Draw edges with branch labels
        edges.forEach(function(e) {
            const fn = nodes[e.from];
            const tn = nodes[e.to];
            if (!fn || !tn || fn._px === undefined || tn._px === undefined) return;

            const x1 = fn._px + NW / 2;
            const y1 = fn._py + NH;
            const x2 = tn._px + NW / 2;
            const y2 = tn._py;
            const midY = (y1 + y2) / 2;

            const d = `M ${x1} ${y1} C ${x1} ${midY}, ${x2} ${midY}, ${x2} ${y2}`;

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', '#cbd5e1');
            path.setAttribute('stroke-width', '2');
            svg.appendChild(path);

            const lx = (x1 + x2) / 2;
            const ly = midY;
            const pillW = 76;
            const pillH = 18;

            const pillRect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            pillRect.setAttribute('x', lx - pillW / 2);
            pillRect.setAttribute('y', ly - pillH / 2);
            pillRect.setAttribute('width', pillW);
            pillRect.setAttribute('height', pillH);
            pillRect.setAttribute('rx', '4');
            pillRect.setAttribute('fill', '#ffffff');
            pillRect.setAttribute('stroke', '#cbd5e1');
            pillRect.setAttribute('stroke-width', '1');
            svg.appendChild(pillRect);

            const pillTxt = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            pillTxt.setAttribute('x', lx);
            pillTxt.setAttribute('y', ly + 4);
            pillTxt.setAttribute('text-anchor', 'middle');
            pillTxt.setAttribute('font-size', '9.5');
            pillTxt.setAttribute('font-family', 'ui-monospace, monospace');
            pillTxt.setAttribute('font-weight', '600');
            pillTxt.setAttribute('fill', '#475569');
            pillTxt.textContent = `${e.pct}% • ₹${Math.round(e.commAmt)}`;
            svg.appendChild(pillTxt);
        });

        // Draw nodes
        Object.keys(nodes).forEach(function(k) {
            const n = nodes[k];
            if (n._px === undefined) return;

            const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.setAttribute('transform', `translate(${n._px}, ${n._py})`);
            g.setAttribute('cursor', 'pointer');

            let fill = '#fff0ee';
            let stroke = '#c0705a';
            let strokeWidth = '1.5';
            let strokeDash = '';

            if (n.is_weakest) {
                fill = '#fff1f2';
                stroke = '#e11d48';
                strokeWidth = '2.5';
                strokeDash = '5,3';
            } else if (n.is_leaf) {
                fill = '#edfaf4';
                stroke = '#56a97a';
            }

            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('width', NW);
            rect.setAttribute('height', NH);
            rect.setAttribute('rx', '8');
            rect.setAttribute('fill', fill);
            rect.setAttribute('stroke', stroke);
            rect.setAttribute('stroke-width', strokeWidth);
            if (strokeDash) rect.setAttribute('stroke-dasharray', strokeDash);
            g.appendChild(rect);

            // Title
            const title = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            title.setAttribute('x', '12');
            title.setAttribute('y', '22');
            title.setAttribute('font-size', '12');
            title.setAttribute('font-weight', '700');
            title.setAttribute('font-family', 'sans-serif');
            title.setAttribute('fill', n.is_weakest ? '#e11d48' : '#0f172a');
            title.textContent = (n.is_weakest ? '★ ' : '') + n.name;
            g.appendChild(title);

            // Role Badge
            const role = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            role.setAttribute('x', NW - 10);
            role.setAttribute('y', '21');
            role.setAttribute('text-anchor', 'end');
            role.setAttribute('font-size', '9');
            role.setAttribute('font-weight', '600');
            role.setAttribute('font-family', 'sans-serif');
            role.setAttribute('fill', n.is_leaf ? '#15803d' : '#9a3412');
            role.textContent = n.is_leaf ? 'Direct Rep' : 'Leader Tier';
            g.appendChild(role);

            // Sales / Commission info
            const line1 = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            line1.setAttribute('x', '12');
            line1.setAttribute('y', '42');
            line1.setAttribute('font-size', '10.5');
            line1.setAttribute('font-family', 'ui-monospace, monospace');
            line1.setAttribute('fill', '#475569');
            line1.textContent = n.sales > 0 ? `Sales: ₹${Math.round(n.sales)}` : `Branch Derived`;
            g.appendChild(line1);

            const line2 = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            line2.setAttribute('x', '12');
            line2.setAttribute('y', '57');
            line2.setAttribute('font-size', '11');
            line2.setAttribute('font-family', 'ui-monospace, monospace');
            line2.setAttribute('font-weight', '700');
            line2.setAttribute('fill', n.is_weakest ? '#be123c' : '#047857');
            line2.textContent = `Comm: ₹${n.commission.toFixed(2)}`;
            g.appendChild(line2);

            svg.appendChild(g);
        });
    }

    /**
     * Export the live hierarchy SVG tree as high-DPI PNG image
     */
    window.exportLiveTreeImage = function() {
        const svg = document.getElementById('live-tree-svg');
        if (!svg) return;

        const serializer = new XMLSerializer();
        let svgString = serializer.serializeToString(svg);

        const svgW = parseFloat(svg.getAttribute('width')) || 800;
        const svgH = parseFloat(svg.getAttribute('height')) || 600;

        const img = new Image();
        const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
        const URL = window.URL || window.webkitURL || window;
        const blobURL = URL.createObjectURL(svgBlob);

        img.onload = function() {
            const scale = 2;
            const canvas = document.createElement('canvas');
            canvas.width = svgW * scale;
            canvas.height = svgH * scale;
            const ctx = canvas.getContext('2d');
            ctx.scale(scale, scale);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, svgW, svgH);
            ctx.drawImage(img, 0, 0);

            const pngURL = canvas.toDataURL('image/png');
            const dl = document.createElement('a');
            dl.download = 'model1-hierarchy-commission-flow.png';
            dl.href = pngURL;
            document.body.appendChild(dl);
            dl.click();
            document.body.removeChild(dl);
            URL.revokeObjectURL(blobURL);
        };
        img.src = blobURL;
    };

    // DOM Ready Initialization
    document.addEventListener('DOMContentLoaded', function () {
        const btnCalc = document.getElementById('btn-calculate');
        if (btnCalc) {
            btnCalc.addEventListener('click', function() { triggerCalculateModel(); });
        }

        // Initialize with Progressive Ladder preset by default
        window.selectPresetStrategy('progressive');
    });
})();
</script>
@endsection
