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
                <span class="text-slate-900 font-semibold">{{ isset($duplicateModel) ? 'Duplicate Model' : 'Model Builder' }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ isset($duplicateModel) ? 'Duplicate Commission Model' : 'Create Commission Model' }}
                </h2>
                @if(isset($duplicateModel))
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        Based on #{{ $duplicateModel->id }} ({{ $duplicateModel->name }})
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Select your commission calculation engine type below, then configure the hierarchical model parameters.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
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

    <!-- ========================================================================= -->
    <!-- STEP 1: MODEL TYPE SELECTION -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Step 1</span>
                <h3 class="text-base font-bold text-slate-900 mt-1">Select Commission Model Type</h3>
                <p class="text-xs text-slate-500">The user must select one model type before entering model-specific data.</p>
            </div>
            <div id="model-type-badge-container">
                <span id="badge-weakest" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span>Weakest Link (Model 1) Active</span>
                </span>
                <span id="badge-override" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hidden">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Level / Generation Override (Model 2) Active</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
            <!-- Model 1 Card -->
            <label 
                id="card-weakest" 
                for="model_type_weakest" 
                class="cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-indigo-600 bg-indigo-50/30 shadow-sm ring-1 ring-indigo-500/20"
            >
                <input 
                    type="radio" 
                    name="model_type_selector" 
                    id="model_type_weakest" 
                    value="weakest_link" 
                    class="sr-only" 
                    checked
                    onchange="switchModelType('weakest_link')"
                >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-600/30 font-mono">
                            M1
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Weakest Link</div>
                            <div class="text-xs text-slate-500">Side-by-side branch comparison & bottleneck cap</div>
                        </div>
                    </div>
                    <span id="check-weakest" class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-sm">✓</span>
                </div>
                <div class="mt-4 pt-3 border-t border-indigo-100 text-xs text-slate-600 leading-relaxed">
                    Compares main vs. side sales across linear levels. The minimum salesperson acts as the bottleneck for overall payout.
                </div>
            </label>

            <!-- Model 2 Card -->
            <label 
                id="card-override" 
                for="model_type_override" 
                class="cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-slate-200 hover:border-slate-300 bg-white"
            >
                <input 
                    type="radio" 
                    name="model_type_selector" 
                    id="model_type_override" 
                    value="generation_override" 
                    class="sr-only"
                    onchange="switchModelType('generation_override')"
                >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-emerald-600/30 font-mono">
                            M2
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">Level / Generation Override</div>
                            <div class="text-xs text-slate-500">Hierarchical tree override & multi-upline propagation</div>
                        </div>
                    </div>
                    <span id="check-override" class="w-5 h-5 rounded-full border border-slate-300 text-transparent flex items-center justify-center text-xs font-bold">✓</span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-xs text-slate-600 leading-relaxed">
                    Independent overrides per edge with unreduced base sale amounts. Stops at configurable maximum generations.
                </div>
            </label>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- INTERFACE 1: WEAKEST LINK MODEL (MODEL 1) -->
    <!-- ========================================================================= -->
    <div id="interface-model-1" class="space-y-8">
        <form id="commission-model-form" action="{{ route('commission-models.store') }}" method="POST" class="space-y-8">
            @csrf
            <input type="hidden" name="model_type" value="weakest_link">

            <!-- SECTION 1: Model Specifications -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Section 2</span>
                        <h3 class="text-base font-semibold text-slate-900 mt-1">Weakest Link Parameters</h3>
                    </div>
                    <div class="text-xs text-slate-500 hidden sm:block">
                        Configure baseline targets & level count
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
                                value="{{ old('name', isset($duplicateModel) ? 'Copy of ' . $duplicateModel->name : '10 Level Test') }}"
                                placeholder="e.g. 10 Level Test"
                                class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
                                required
                            >
                            <p class="text-[11px] text-slate-400 mt-1">Unique title to identify this commission structure.</p>
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
                                    value="{{ old('commission_rate', isset($duplicateModel) ? number_format($duplicateModel->commission_rate, 2, '.', '') : '5.00') }}"
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
                            <p class="text-[11px] text-slate-400 mt-1">Positive integer (e.g. 10). Re-generates rows dynamically.</p>
                        </div>
                    </div>

                    <!-- Preset Buttons -->
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-slate-500 mr-2">Quick Presets:</span>
                        <button type="button" onclick="loadDefaultTenLevelPreset()" class="px-3 py-1.5 text-xs font-medium rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            Default 10-Level Test
                        </button>
                        <button type="button" onclick="loadEqualPerformancePreset()" class="px-3 py-1.5 text-xs font-medium rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            Equal Performance
                        </button>
                        <button type="button" onclick="loadSteepDropPreset()" class="px-3 py-1.5 text-xs font-medium rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                            Steep Drop (Bottleneck at Level 10)
                        </button>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Levels Hierarchy Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Section 3</span>
                        <h3 class="text-base font-semibold text-slate-900 mt-1">Sales Hierarchy Structure</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="level-counter-text" class="text-xs font-semibold text-slate-500">10 Levels Defined</span>
                    </div>
                </div>

                <div id="no-levels-alert" class="p-8 text-center hidden">
                    <p class="text-sm text-slate-500 font-medium">No levels currently generated.</p>
                    <p class="text-xs text-slate-400 mt-1">Click "Generate Levels" above to build the hierarchical sales table.</p>
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
                            <!-- Dynamically generated rows via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Form Action Footer with Calculate Model and Save Model buttons -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        <span class="font-medium text-slate-700">Weakest Link Rule:</span> Calculates <code>MIN(main child, side salesperson)</code> upward from Level <span id="footer-bottom-level">10</span> to Level 1.
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                        <!-- Calculate Model Button -->
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
                            <span id="calc-btn-text">Calculate Model</span>
                        </button>

                        <!-- Save Model Button -->
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            <span id="save-btn-text">Save Model 1</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Live Calculation Results Area (Model 1) -->
        <div id="results-wrapper" class="space-y-6 hidden">
            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Sales -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sales Volume</span>
                    <div id="res-total-sales" class="text-2xl font-bold font-mono text-slate-900 mt-2">₹0.00</div>
                    <p class="text-[11px] text-slate-400 mt-1">Sum of all main & side sales</p>
                </div>

                <!-- Potential vs Final -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Potential Commission</span>
                    <div id="res-potential-commission" class="text-2xl font-bold font-mono text-indigo-600 mt-2">₹0.00</div>
                    <p class="text-[11px] text-slate-400 mt-1">If unconstrained by bottleneck</p>
                </div>

                <!-- Final Bottleneck Commission -->
                <div class="bg-white rounded-xl border-2 border-emerald-500 p-6 shadow-sm bg-emerald-50/20">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Final Commission (Weakest Link)</span>
                    <div id="res-final-commission" class="text-2xl font-bold font-mono text-emerald-700 mt-2">₹0.00</div>
                    <p id="res-weakest-note" class="text-[11px] text-emerald-800 font-medium mt-1">Determined by weakest link</p>
                </div>
            </div>

            <!-- Detailed Level Calculation Breakdown -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Step-by-Step Level Calculations</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Evaluation of each tier's selected minimum and resulting leader commission.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-600 font-semibold uppercase tracking-wider">
                                <th class="py-3 px-3 w-14 text-center">Level</th>
                                <th class="py-3 px-3 w-20 text-center">Rate %</th>
                                <th class="py-3 px-4">Main Person</th>
                                <th class="py-3 px-4">Main Sales</th>
                                <th class="py-3 px-4">Main Commission</th>
                                <th class="py-3 px-4">Side Person</th>
                                <th class="py-3 px-4">Side Sales</th>
                                <th class="py-3 px-4">Side Commission</th>
                                <th class="py-3 px-4 bg-indigo-50/60 text-indigo-950 font-bold">Selected Minimum</th>
                                <th class="py-3 px-4 bg-emerald-50/60 text-emerald-950 font-bold">Leader Commission</th>
                            </tr>
                        </thead>
                        <tbody id="detailed-calc-tbody" class="divide-y divide-slate-100 text-slate-700">
                            <!-- Populated from server JSON response -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- INTERFACE 2: LEVEL / GENERATION OVERRIDE MODEL (MODEL 2) -->
    <!-- ========================================================================= -->
    <div id="interface-model-2" class="space-y-8 hidden">
        <form id="override-model-form" action="{{ route('override-models.store') }}" method="POST" class="space-y-8">
            @csrf
            <input type="hidden" name="model_type" value="generation_override">

            <!-- SECTION 1: Model 2 Settings -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">Section 2</span>
                        <h3 class="text-base font-semibold text-slate-900 mt-1">MODEL 2 Settings</h3>
                    </div>
                    <div class="text-xs text-slate-500">
                        General override model configuration
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Model Name -->
                    <div class="md:col-span-2">
                        <label for="override_name" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Model Name <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="override_name" 
                            name="name" 
                            value="{{ old('name', 'Level Override Example') }}"
                            placeholder="e.g. Level Override Example"
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                            required
                        >
                        <p class="text-[11px] text-slate-400 mt-1">Example: Level Override Example</p>
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
                        <label for="override_max_generations" class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Maximum Generations <span class="text-rose-500">*</span></span>
                            <span class="text-[10px] font-normal text-slate-400">Default: 5</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                id="override_max_generations" 
                                name="max_generations" 
                                value="{{ old('max_generations', 5) }}"
                                min="1" 
                                max="100" 
                                class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800 font-semibold"
                                required
                            >
                            <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-medium pointer-events-none">gens</span>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-1.5 space-y-0.5">
                            <p class="font-medium text-emerald-700">Paid through generation <span id="m1-hint-paid-gen">5</span>; Generation <span id="m1-hint-excl-gen">6</span>+ excluded by model limit.</p>
                            <p class="text-slate-400 text-[10px]">Applies independently to every sale. Does not limit tree depth (trees may have 20+ levels).</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-3">
                        <label for="override_description" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Description / Business Notes (Optional)
                        </label>
                        <textarea 
                            id="override_description" 
                            name="description" 
                            rows="2"
                            placeholder="e.g. Tree structure with custom overrides: E1 (200), F (400), C (300)"
                            class="w-full text-sm rounded-lg border border-slate-200 px-3.5 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 text-slate-800"
                        >{{ old('description', '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Tree Builder & Hierarchy Visualizer -->
            <div class="space-y-4">
                @include('components.model2-tree-builder')
            </div>

                <!-- Form Action Footer -->
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        <span class="font-medium text-slate-700">Model 2 Rule:</span> Every sale propagates independently. The base amount remains constant and stops after the maximum generation limit.
                    </div>
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                        <button 
                            type="button" 
                            id="btn-recalculate-override"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-all"
                        >
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            <span>Calculate Overrides</span>
                        </button>

                        <button 
                            type="submit" 
                            id="btn-submit-override"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all"
                        >
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                            <span>Save Model 2</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Live Calculation Results Area (Model 2) -->
        <div id="override-results-wrapper" class="space-y-6 hidden">
            @include('components.model2-results')
        </div>
    </div>
</div>

<!-- Scripts for Dynamic Switching & Engine Calculations -->
<script>
    /**
     * Switch Model Type dynamically between Weakest Link and Level / Generation Override.
     */
    function switchModelType(type) {
        const interfaceM1 = document.getElementById('interface-model-1');
        const interfaceM2 = document.getElementById('interface-model-2');
        const badgeWeakest = document.getElementById('badge-weakest');
        const badgeOverride = document.getElementById('badge-override');
        const cardWeakest = document.getElementById('card-weakest');
        const cardOverride = document.getElementById('card-override');
        const checkWeakest = document.getElementById('check-weakest');
        const checkOverride = document.getElementById('check-override');
        const radioWeakest = document.getElementById('model_type_weakest');
        const radioOverride = document.getElementById('model_type_override');

        if (type === 'generation_override') {
            // Activate Model 2
            interfaceM1.classList.add('hidden');
            interfaceM2.classList.remove('hidden');

            badgeWeakest.classList.add('hidden');
            badgeOverride.classList.remove('hidden');

            cardWeakest.className = 'cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-slate-200 hover:border-slate-300 bg-white';
            cardOverride.className = 'cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-emerald-600 bg-emerald-50/30 shadow-sm ring-1 ring-emerald-500/20';

            checkWeakest.className = 'w-5 h-5 rounded-full border border-slate-300 text-transparent flex items-center justify-center text-xs font-bold';
            checkOverride.className = 'w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold shadow-sm';

            radioOverride.checked = true;

            // Trigger Model 2 calculation preview if rows exist
            if (document.querySelectorAll('#edges-tbody tr').length === 0) {
                loadSpecificationTreePreset();
            } else {
                recalculateOverrideModel();
            }

            // Sync URL query without reloading
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.set('type', 'generation_override');
                window.history.replaceState(null, '', url);
            }
        } else {
            // Activate Model 1 (Weakest Link)
            interfaceM2.classList.add('hidden');
            interfaceM1.classList.remove('hidden');

            badgeOverride.classList.add('hidden');
            badgeWeakest.classList.remove('hidden');

            cardOverride.className = 'cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-slate-200 hover:border-slate-300 bg-white';
            cardWeakest.className = 'cursor-pointer relative rounded-xl border-2 p-5 transition-all flex flex-col justify-between border-indigo-600 bg-indigo-50/30 shadow-sm ring-1 ring-indigo-500/20';

            checkOverride.className = 'w-5 h-5 rounded-full border border-slate-300 text-transparent flex items-center justify-center text-xs font-bold';
            checkWeakest.className = 'w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shadow-sm';

            radioWeakest.checked = true;

            // Trigger Model 1 calculation preview if rows exist
            triggerCalculateModel();

            // Sync URL query without reloading
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.set('type', 'weakest_link');
                window.history.replaceState(null, '', url);
            }
        }
    }

    // =========================================================================
    // MODEL 1 (WEAKEST LINK) JAVASCRIPT LOGIC
    // =========================================================================
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

    function generateLevels(overrideData = null) {
        const numInput = document.getElementById('number_of_levels');
        let count = parseInt(numInput.value, 10);

        if (isNaN(count) || count < 1) {
            alert('Please enter a valid positive integer for Number of Levels.');
            numInput.focus();
            return;
        }

        if (count > 100) {
            alert('Maximum supported levels is 100.');
            numInput.value = 100;
            count = 100;
        }

        const tbody = document.getElementById('levels-tbody');
        const container = document.getElementById('levels-table-container');
        const alertBox = document.getElementById('no-levels-alert');
        const counterText = document.getElementById('level-counter-text');
        const footerBottom = document.getElementById('footer-bottom-level');
        const defaultRate = document.getElementById('commission_rate') ? document.getElementById('commission_rate').value : '5.00';

        const existingData = overrideData || collectCurrentValues();
        tbody.innerHTML = '';

        for (let i = 1; i <= count; i++) {
            const defaultMain = getLetter(i - 1);
            const defaultSide = 'S' + i;
            
            const prev = existingData[i] || {};
            const rateVal = prev.commission_rate !== undefined && prev.commission_rate !== '' ? prev.commission_rate : defaultRate;
            const mainPersonVal = prev.main_person !== undefined ? prev.main_person : defaultMain;
            const mainSalesVal = prev.main_sales !== undefined ? prev.main_sales : '';
            const sidePersonVal = prev.side_person !== undefined ? prev.side_person : defaultSide;
            const sideSalesVal = prev.side_sales !== undefined ? prev.side_sales : '';

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

        container.classList.remove('hidden');
        alertBox.classList.add('hidden');
        if (counterText) counterText.textContent = `${count} Levels Defined`;
        if (footerBottom) footerBottom.textContent = count;

        attachModel1AutoCalculate();
    }

    function loadDefaultTenLevelPreset() {
        document.getElementById('name').value = '10 Level Test';
        document.getElementById('commission_rate').value = '5.00';
        document.getElementById('number_of_levels').value = 10;

        const presetData = {
            1: { commission_rate: '10.00', main_person: 'A', main_sales: '1000', side_person: 'S1', side_sales: '1000' },
            2: { commission_rate: '8.00',  main_person: 'B', main_sales: '900',  side_person: 'S2', side_sales: '900' },
            3: { commission_rate: '7.00',  main_person: 'C', main_sales: '800',  side_person: 'S3', side_sales: '800' },
            4: { commission_rate: '6.00',  main_person: 'D', main_sales: '700',  side_person: 'S4', side_sales: '700' },
            5: { commission_rate: '5.00',  main_person: 'E', main_sales: '600',  side_person: 'S5', side_sales: '600' },
            6: { commission_rate: '5.00',  main_person: 'F', main_sales: '500',  side_person: 'S6', side_sales: '500' },
            7: { commission_rate: '4.00',  main_person: 'G', main_sales: '400',  side_person: 'S7', side_sales: '400' },
            8: { commission_rate: '3.00',  main_person: 'H', main_sales: '300',  side_person: 'S8', side_sales: '300' },
            9: { commission_rate: '2.50',  main_person: 'I', main_sales: '200',  side_person: 'S9', side_sales: '250' },
            10: { commission_rate: '2.00', main_person: 'J', main_sales: '40',   side_person: 'S10', side_sales: '200' }
        };

        generateLevels(presetData);
        triggerCalculateModel();
    }

    function loadEqualPerformancePreset() {
        document.getElementById('name').value = 'Equal Performance Model';
        document.getElementById('commission_rate').value = '5.00';
        document.getElementById('number_of_levels').value = 5;

        const presetData = {
            1: { commission_rate: '5.00', main_person: 'A', main_sales: '1000', side_person: 'S1', side_sales: '1000' },
            2: { commission_rate: '5.00', main_person: 'B', main_sales: '1000', side_person: 'S2', side_sales: '1000' },
            3: { commission_rate: '5.00', main_person: 'C', main_sales: '1000', side_person: 'S3', side_sales: '1000' },
            4: { commission_rate: '5.00', main_person: 'D', main_sales: '1000', side_person: 'S4', side_sales: '1000' },
            5: { commission_rate: '5.00', main_person: 'E', main_sales: '1000', side_person: 'S5', side_sales: '1000' }
        };

        generateLevels(presetData);
        triggerCalculateModel();
    }

    function loadSteepDropPreset() {
        document.getElementById('name').value = 'Steep Drop Bottleneck Test';
        document.getElementById('commission_rate').value = '10.00';
        document.getElementById('number_of_levels').value = 4;

        const presetData = {
            1: { commission_rate: '12.00', main_person: 'Top',   main_sales: '5000', side_person: 'S1', side_sales: '5000' },
            2: { commission_rate: '10.00', main_person: 'Mid1',  main_sales: '4000', side_person: 'S2', side_sales: '4000' },
            3: { commission_rate: '8.00',  main_person: 'Mid2',  main_sales: '3000', side_person: 'S3', side_sales: '3000' },
            4: { commission_rate: '5.00',  main_person: 'Bottle', main_sales: '50',   side_person: 'S4', side_sales: '5000' }
        };

        generateLevels(presetData);
        triggerCalculateModel();
    }

    function formatCurrency(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function triggerCalculateModel() {
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
            if (status === 200 && body.success) {
                renderModel1Results(body.data);
            }
        })
        .catch(err => {
            console.error('Calculation error:', err);
        });
    }

    function renderModel1Results(data) {
        document.getElementById('results-wrapper').classList.remove('hidden');
        document.getElementById('res-total-sales').textContent = formatCurrency(data.total_sales);
        document.getElementById('res-potential-commission').textContent = formatCurrency(data.total_potential_commission);
        document.getElementById('res-final-commission').textContent = formatCurrency(data.final_commission);
        document.getElementById('res-weakest-note').textContent = `Bottleneck by ${data.weakest_person} (Sales: ${formatCurrency(data.weakest_sales)})`;

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
    }

    let model1DebounceTimer = null;
    function attachModel1AutoCalculate() {
        const inputs = document.querySelectorAll('#levels-tbody input, #commission_rate');
        inputs.forEach(input => {
            input.removeEventListener('input', onModel1Input);
            input.addEventListener('input', onModel1Input);
        });
    }

    function onModel1Input() {
        clearTimeout(model1DebounceTimer);
        model1DebounceTimer = setTimeout(triggerCalculateModel, 350);
    }

    // =========================================================================
    // MODEL 2 (LEVEL / GENERATION OVERRIDE) JAVASCRIPT LOGIC
    // =========================================================================
    let edgeCounter = 0;
    let saleCounter = 0;

    function addEdgeRow(parent = '', child = '', rate = 5.0) {
        edgeCounter++;
        const tbody = document.getElementById('edges-tbody');
        const tr = document.createElement('tr');
        tr.id = `edge-row-${edgeCounter}`;
        tr.className = 'hover:bg-slate-50/80 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-2.5 text-center font-mono text-slate-400 edge-idx">${edgeCounter}</td>
            <td class="px-4 py-2.5">
                <input 
                    type="text" 
                    name="edges[${edgeCounter - 1}][parent]" 
                    value="${parent}"
                    placeholder="e.g. A"
                    class="w-full text-xs font-semibold rounded border border-slate-200 px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 edge-input"
                    required
                >
            </td>
            <td class="px-4 py-2.5 text-center text-slate-400 font-bold">→</td>
            <td class="px-4 py-2.5">
                <input 
                    type="text" 
                    name="edges[${edgeCounter - 1}][child]" 
                    value="${child}"
                    placeholder="e.g. B"
                    class="w-full text-xs font-semibold rounded border border-slate-200 px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 edge-input"
                    required
                >
            </td>
            <td class="px-4 py-2.5">
                <div class="relative">
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="100" 
                        name="edges[${edgeCounter - 1}][rate]" 
                        value="${rate}"
                        placeholder="5.0"
                        class="w-full text-xs font-mono rounded border border-slate-200 pl-2.5 pr-6 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 edge-input"
                        required
                    >
                    <span class="absolute right-2 top-1.5 text-xs text-slate-400 pointer-events-none">%</span>
                </div>
            </td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" onclick="removeEdgeRow(${edgeCounter})" class="text-slate-400 hover:text-rose-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        reindexEdges();
        attachModel2AutoCalculate();
    }

    function removeEdgeRow(id) {
        const row = document.getElementById(`edge-row-${id}`);
        if (row) {
            row.remove();
            reindexEdges();
            recalculateOverrideModel();
        }
    }

    function reindexEdges() {
        const rows = document.querySelectorAll('#edges-tbody tr');
        rows.forEach((tr, idx) => {
            const idxCell = tr.querySelector('.edge-idx');
            if (idxCell) idxCell.textContent = idx + 1;
            tr.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/edges\[\d+\]/, `edges[${idx}]`));
                }
            });
        });
    }

    function addSaleRow(salesperson = '', amount = 200.0) {
        saleCounter++;
        const tbody = document.getElementById('sales-tbody');
        const tr = document.createElement('tr');
        tr.id = `sale-row-${saleCounter}`;
        tr.className = 'hover:bg-slate-50/80 transition-colors';
        tr.innerHTML = `
            <td class="px-4 py-2.5 text-center font-mono text-slate-400 sale-idx">${saleCounter}</td>
            <td class="px-4 py-2.5">
                <input 
                    type="text" 
                    name="sales[${saleCounter - 1}][salesperson]" 
                    value="${salesperson}"
                    placeholder="e.g. E1"
                    class="w-full text-xs font-semibold rounded border border-slate-200 px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 sale-input"
                    required
                >
            </td>
            <td class="px-4 py-2.5">
                <div class="relative">
                    <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-semibold">₹</span>
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        name="sales[${saleCounter - 1}][amount]" 
                        value="${amount}"
                        placeholder="0.00"
                        class="w-full text-xs font-mono rounded border border-slate-200 pl-6 pr-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 sale-input"
                        required
                    >
                </div>
            </td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" onclick="removeSaleRow(${saleCounter})" class="text-slate-400 hover:text-rose-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        reindexSales();
        attachModel2AutoCalculate();
    }

    function removeSaleRow(id) {
        const row = document.getElementById(`sale-row-${id}`);
        if (row) {
            row.remove();
            reindexSales();
            recalculateOverrideModel();
        }
    }

    function reindexSales() {
        const rows = document.querySelectorAll('#sales-tbody tr');
        rows.forEach((tr, idx) => {
            const idxCell = tr.querySelector('.sale-idx');
            if (idxCell) idxCell.textContent = idx + 1;
            tr.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/sales\[\d+\]/, `sales[${idx}]`));
                }
            });
        });
    }

    function loadSpecificationTreePreset() {
        document.getElementById('override_name').value = 'Level Override Example';
        document.getElementById('override_max_generations').value = 5;
        document.getElementById('override_description').value = 'Example specification tree: A -> B (2%), A -> C (5%), B -> E (3%), B -> F (5%), E -> E1 (5%). Sales: C (300), F (400), E1 (200).';

        if (window.TreeBuilder) {
            window.TreeBuilder.loadSpecificationPreset();
        }
    }

    function loadChainTreePreset() {
        document.getElementById('override_name').value = '10-Level Chain Override (Max 5 Depth)';
        document.getElementById('override_max_generations').value = 5;
        document.getElementById('override_description').value = 'Linear chain: J -> I -> H -> G -> F -> E -> D -> C -> B -> A. Sale at J ($1,000). Max 5 generations paid.';

        if (window.TreeBuilder) {
            window.TreeBuilder.loadChainPreset();
        }
    }

    function recalculateOverrideModel() {
        const form = document.getElementById('override-model-form');
        if (!form) return;

        // Ensure hidden inputs are updated from TreeBuilder state
        if (window.TreeBuilder && typeof window.TreeBuilder.validate === 'function') {
            window.TreeBuilder.validate();
        }

        const formData = new FormData(form);

        fetch("{{ route('override-models.calculate') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                renderModel2Results(body.data);
            }
        })
        .catch(err => {
            console.error('Model 2 calculate error:', err);
        });
    }
    window.recalculateOverrideModel = recalculateOverrideModel;

    function renderModel2Results(data) {
        const wrapper = document.getElementById('override-results-wrapper');
        if (wrapper) wrapper.classList.remove('hidden');

        if (typeof window.renderModel2ResultsView === 'function') {
            window.renderModel2ResultsView(data);
        }
    }

    function updateModel2Hints() {
        const input = document.getElementById('override_max_generations');
        const val = parseInt(input?.value, 10) || 5;
        const paidSpan = document.getElementById('m1-hint-paid-gen');
        const exclSpan = document.getElementById('m1-hint-excl-gen');
        if (paidSpan) paidSpan.textContent = val;
        if (exclSpan) exclSpan.textContent = val + 1;
    }

    let model2DebounceTimer = null;
    function onModel2Input() {
        updateModel2Hints();
        clearTimeout(model2DebounceTimer);
        model2DebounceTimer = setTimeout(recalculateOverrideModel, 350);
    }

    // =========================================================================
    // INITIALIZATION ON DOM READY
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function () {
        // Model 1 button bindings
        const btnCalc = document.getElementById('btn-calculate');
        if (btnCalc) btnCalc.addEventListener('click', triggerCalculateModel);

        const btnRecalcOverride = document.getElementById('btn-recalculate-override');
        if (btnRecalcOverride) btnRecalcOverride.addEventListener('click', recalculateOverrideModel);

        const overrideMaxGenInput = document.getElementById('override_max_generations');
        if (overrideMaxGenInput) {
            overrideMaxGenInput.addEventListener('input', onModel2Input);
            updateModel2Hints();
        }

        // Model 2 Form Validation Hook
        const formOverride = document.getElementById('override-model-form');
        if (formOverride) {
            formOverride.addEventListener('submit', function (e) {
                if (window.TreeBuilder && !window.TreeBuilder.validate()) {
                    e.preventDefault();
                    document.getElementById('tree-validation-alert')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            });
        }

        // Initialize Model 1 rows
        generateLevels();

        // Check if URL query asks for generation_override
        const urlParams = new URLSearchParams(window.location.search);
        const requestedType = urlParams.get('type') || (window.location.hash.includes('override') ? 'generation_override' : 'weakest_link');

        if (requestedType === 'generation_override' || requestedType === 'override') {
            switchModelType('generation_override');
        } else {
            switchModelType('weakest_link');
        }
    });
</script>
@endsection
