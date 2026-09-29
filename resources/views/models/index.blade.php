@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Commission Models</span>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">Saved Commission Models</h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Browse, inspect historical calculation snapshots, edit, duplicate, or manage stored Weakest Link commission models.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('commission-models.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                + Model 1 (Weakest Link)
            </a>
            <a href="{{ route('override-models.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                + Model 2 (Override Model)
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Tabs by Model Type -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('commission-models.index') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ empty($type) ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            All Models ({{ $counts['all'] ?? $models->total() }})
        </a>
        <a href="{{ route('commission-models.index', ['type' => 'weakest_link']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $type === 'weakest_link' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
            Model 1: Weakest Link ({{ $counts['weakest_link'] ?? 0 }})
        </a>
        <a href="{{ route('commission-models.index', ['type' => 'generation_override']) }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $type === 'generation_override' || $type === 'override' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700' }}">
            Model 2: Generation Override ({{ $counts['generation_override'] ?? 0 }})
        </a>
    </div>

    <!-- Success Flash Notification -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3 shadow-sm animate-fade-in">
            <div class="flex items-center gap-2.5 text-sm font-semibold">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <span class="text-xs text-emerald-600 font-mono hidden sm:inline-block">Database Synced</span>
        </div>
    @endif

    <!-- Error Flash Notification -->
    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-1">
            <div class="flex items-center gap-2 font-semibold text-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>An error occurred:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-6">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Saved Models Table Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Database Models Catalog</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Historical snapshots preserved exactly as evaluated by the respective commission calculation engines.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                <span>{{ $models->total() }} Saved Models</span>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 w-16 text-center">ID</th>
                        <th class="py-3 px-5">Model Name</th>
                        <th class="py-3 px-4 text-center">Model Type</th>
                        <th class="py-3 px-4 text-center">Maximum Generations</th>
                        <th class="py-3 px-4 text-right">Total Sales</th>
                        <th class="py-3 px-4 text-right text-emerald-800">Total Commission</th>
                        <th class="py-3 px-4 text-center">Created At</th>
                        <th class="py-3 px-5 text-right w-64">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($models as $m)
                        @php
                            $showRoute = $m->isOverrideModel() ? route('override-models.show', $m) : route('commission-models.show', $m);
                            $editRoute = $m->isOverrideModel() ? route('override-models.edit', $m) : route('commission-models.edit', $m);
                            $dupRoute = $m->isOverrideModel() ? route('override-models.duplicate', $m) : route('commission-models.duplicate', $m);
                            $destroyRoute = $m->isOverrideModel() ? route('override-models.destroy', $m) : route('commission-models.destroy', $m);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <!-- 1. ID -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    #{{ $m->id }}
                                </span>
                            </td>

                            <!-- 2. Model Name -->
                            <td class="py-3.5 px-5">
                                <a href="{{ $showRoute }}" class="font-bold text-slate-900 group-hover:text-emerald-600 transition-colors text-sm">
                                    {{ $m->name }}
                                </a>
                                @if($m->isOverrideModel())
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $m->relationships->count() }} relationships • {{ $m->overrideSales->count() }} sales
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-400">
                                        <span>Bottleneck:</span>
                                        <span class="font-semibold text-rose-600">{{ $m->weakest_person }}</span>
                                        <span>(₹{{ number_format($m->weakest_commission, 2) }})</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 3. Model Type -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($m->isOverrideModel())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Level / Generation Override
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        Weakest Link
                                    </span>
                                @endif
                            </td>

                            <!-- 4. Maximum Generations -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($m->isOverrideModel())
                                    <span class="font-mono font-bold text-slate-800 bg-amber-50 border border-amber-200 text-amber-900 px-2.5 py-1 rounded-lg text-xs">
                                        {{ $m->max_generations ?? 5 }}
                                    </span>
                                @else
                                    <span class="text-slate-400 font-mono text-xs" title="Not applicable for Weakest Link">—</span>
                                @endif
                            </td>

                            <!-- 5. Total Sales -->
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900 whitespace-nowrap">
                                ₹{{ number_format($m->total_sales, 2) }}
                            </td>

                            <!-- 6. Total Commission -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="font-mono font-bold text-emerald-600 text-sm">
                                    ₹{{ number_format($m->final_commission, 2) }}
                                </div>
                                <span class="text-[10px] text-slate-400 block font-normal">
                                    {{ $m->isOverrideModel() ? 'Total Overrides' : 'Leader Payout' }}
                                </span>
                            </td>

                            <!-- 7. Created At -->
                            <td class="py-3.5 px-4 text-center text-slate-500 whitespace-nowrap">
                                <div class="font-medium text-slate-700">{{ $m->created_at->format('M d, Y H:i') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $m->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- 8. Actions -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a 
                                        href="{{ $showRoute }}" 
                                        title="{{ $m->isOverrideModel() ? 'Open Model 2 tree, calculation summary and commission ledger' : 'Open existing Model 1 results' }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold {{ $m->isOverrideModel() ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200' }} transition-colors shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>View</span>
                                    </a>

                                    <a 
                                        href="{{ $editRoute }}" 
                                        title="Edit model configuration"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <a 
                                        href="{{ $dupRoute }}" 
                                        title="Duplicate model"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors shadow-2xs"
                                    >
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                        <span>Copy</span>
                                    </a>

                                    <form action="{{ $destroyRoute }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this model?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            title="Delete this model"
                                            class="inline-flex items-center p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 border border-transparent hover:border-rose-200 transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="font-semibold text-slate-700 text-sm">No saved commission models found</div>
                                    <p class="text-xs text-slate-400">
                                        Create a Model 1 (Weakest Link) or Model 2 (Generation Override) to see historical calculations here.
                                    </p>
                                    <div class="flex items-center justify-center gap-2 pt-1">
                                        <a href="{{ route('commission-models.create') }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition-colors">
                                            Create Model 1
                                        </a>
                                        <a href="{{ route('override-models.create') }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition-colors">
                                            Create Model 2
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($models->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $models->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Interactive Confirmation Modal for Delete -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs hidden">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-xl border border-slate-200 overflow-hidden transform transition-all">
        <div class="p-6">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">Delete Commission Model?</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Are you sure you want to permanently delete <strong id="delete-modal-model-name" class="text-slate-800"></strong>? This will remove all associated level configurations and calculation snapshots from the database. This action cannot be undone.
            </p>
        </div>
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
            <button 
                type="button" 
                onclick="closeDeleteModal()" 
                class="px-4 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors"
            >
                Cancel
            </button>
            <button 
                type="button" 
                id="delete-modal-confirm-btn"
                class="px-4 py-2 text-xs font-semibold rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-sm transition-colors"
            >
                Yes, Delete Model
            </button>
        </div>
    </div>
</div>

<script>
    let activeDeleteForm = null;

    function confirmDelete(id, name) {
        // Find the event originating form
        const event = window.event;
        const form = event.target.closest('form');
        activeDeleteForm = form;

        // Open modal
        const modal = document.getElementById('delete-modal');
        const nameElem = document.getElementById('delete-modal-model-name');
        if (modal && nameElem) {
            nameElem.textContent = `"${name}" (#${id})`;
            modal.classList.remove('hidden');

            const confirmBtn = document.getElementById('delete-modal-confirm-btn');
            confirmBtn.onclick = function() {
                if (activeDeleteForm) {
                    activeDeleteForm.submit();
                }
            };

            return false; // prevent normal form submit until modal confirms
        }

        // Fallback native confirm if modal element not found
        return confirm(`Are you sure you want to permanently delete commission model "${name}"? This action cannot be undone.`);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
        activeDeleteForm = null;
    }
</script>
@endsection
