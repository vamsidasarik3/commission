@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <!-- Top Welcome & Context Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Weakest Link Commission Model</h2>
            <p class="text-sm text-slate-500 mt-1">
                Configure, manage, and benchmark sales commission rules governed by team bottleneck thresholds and floor constraints.
            </p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Schema
            </button>
            <a href="{{ route('commission-models.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Model
            </a>
        </div>
    </div>

    <!-- Section 1: Statistics -->
    <section id="statistics">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Statistics Overview</h3>
            <span class="text-xs text-slate-400">Real-time metrics placeholder</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($statistics as $stat)
                <x-stat-card 
                    :title="$stat['title']" 
                    :value="$stat['value']" 
                    :change="$stat['change']" 
                    :trend="$stat['trend']" 
                    :icon="$stat['icon']" 
                    :badge="$stat['badge']" 
                />
            @endforeach
        </div>
    </section>

    <!-- Main Grid: Left (Saved Models & Recent Models) / Right (Create Commission Model & Logic Info) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Primary Data (Span 2) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Section 2: Saved Models -->
            <section id="saved-models">
                <x-card title="Saved Models" subtitle="Active and configured weakest link commission plans">
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('commission-models.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-100 transition-colors inline-flex items-center gap-1">
                                <span>Browse Catalog</span>
                                <span>&rarr;</span>
                            </a>
                        </div>
                    </x-slot:actions>

                    <!-- Models Table -->
                    <div class="overflow-x-auto -mx-6 -my-6">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200/80 bg-slate-50/75 text-slate-500 font-semibold uppercase tracking-wider">
                                    <th class="py-3 px-6">Model Name & Category</th>
                                    <th class="py-3 px-4">Bottleneck Rule</th>
                                    <th class="py-3 px-4">Tiers</th>
                                    <th class="py-3 px-4">Base Rate</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($savedModels as $model)
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="py-3.5 px-6">
                                            <div class="font-semibold text-slate-900 text-sm">{{ $model['name'] }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $model['category'] }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 max-w-xs">
                                            <div class="text-slate-800 truncate" title="{{ $model['bottleneck_rule'] }}">
                                                {{ $model['bottleneck_rule'] }}
                                            </div>
                                            <div class="text-[11px] text-rose-600 font-mono mt-0.5">{{ $model['floor_penalty'] }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono font-medium text-slate-900">
                                            {{ $model['tier_count'] }} tiers
                                        </td>
                                        <td class="py-3.5 px-4 font-mono font-semibold text-indigo-600">
                                            {{ $model['base_commission'] }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <x-badge :status="$model['status']" />
                                        </td>
                                        <td class="py-3.5 px-6 text-right whitespace-nowrap space-x-1">
                                            <button type="button" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-md hover:bg-slate-100 transition-colors" title="View Model">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <button type="button" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-md hover:bg-slate-100 transition-colors" title="Edit Configuration">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </section>

            <!-- Section 3: Recent Models Activity -->
            <section id="recent-models">
                <x-card title="Recent Models" subtitle="Latest model versions and threshold calibrations">
                    <div class="space-y-4">
                        @foreach ($recentModels as $recent)
                            <div class="flex items-center justify-between p-3.5 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 mt-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-900 text-xs">{{ $recent['name'] }}</span>
                                            <span class="font-mono text-[10px] text-slate-400 bg-slate-200/60 px-1.5 py-0.5 rounded">{{ $recent['code'] }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Weakest Link Metric: <strong class="text-slate-700 font-medium">{{ $recent['weakest_link_kpi'] }}</strong> ({{ $recent['threshold'] }})
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <x-badge :status="$recent['status']" />
                                    <p class="text-[11px] text-slate-400 mt-1">{{ $recent['updated_at'] }} &bull; {{ $recent['created_by'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </section>
        </div>

        <!-- Right Column: Create Commission Model & Theory Card (Span 1) -->
        <div class="space-y-8">
            
            <!-- Section 4: Create Commission Model Action Card -->
            <section id="create-model">
                <x-card title="Commission Model Builder" subtitle="Define hierarchical levels and calculate Weakest Link bottlenecks">
                    <div class="space-y-4 text-xs text-slate-600">
                        <p>
                            Configure custom multi-level sales structures. Dynamically generate levels from 1 to 100 tiers, assign main chain and side salesperson sales, and compute leader commissions using the authoritative server-side calculation engine.
                        </p>
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80 space-y-1.5 font-mono text-[11px]">
                            <div class="flex justify-between text-slate-500">
                                <span>Default Template:</span>
                                <span class="font-semibold text-slate-700">10-Level Hierarchy</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Engine Comparison:</span>
                                <span class="font-semibold text-indigo-600">MIN(Child, Side)</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Calculation:</span>
                                <span class="font-semibold text-emerald-600">Bottom-up to Root</span>
                            </div>
                        </div>
                        <div class="pt-2">
                            <a href="{{ route('models.create') }}" class="w-full py-2.5 px-4 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Launch Model Builder
                            </a>
                        </div>
                    </div>
                </x-card>
            </section>

            <!-- Weakest Link Philosophy Summary -->
            <div class="p-5 rounded-xl border border-indigo-100 bg-indigo-50/50 text-indigo-950 space-y-2.5">
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-md bg-indigo-600 text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900">Weakest Link Principle</h4>
                </div>
                <p class="text-xs text-indigo-800/90 leading-relaxed">
                    Under the <strong>Weakest Link Commission Model</strong>, overall team and individual commissions are conditioned upon eliminating performance bottlenecks. If the lowest metric drops below the defined floor, scaling dampeners activate.
                </p>
                <div class="pt-1 text-[11px] text-indigo-700 font-medium">
                    &bull; Prevents isolated over-achievement with team failures<br>
                    &bull; Incentivizes cross-enablement and peer support
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
