@props([
    'title',
    'value',
    'change' => null,
    'trend' => 'up',
    'icon' => 'models',
    'badge' => null,
])

<div class="bg-white rounded-xl border border-slate-200/90 shadow-sm p-5 hover:border-slate-300 transition-colors">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $title }}</p>
            <h4 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">{{ $value }}</h4>
        </div>
        <div class="w-10 h-10 rounded-lg flex items-center justify-center 
            {{ match($icon) {
                'models' => 'bg-blue-50 text-blue-600',
                'tiers' => 'bg-indigo-50 text-indigo-600',
                'teams' => 'bg-violet-50 text-violet-600',
                'bottleneck' => 'bg-amber-50 text-amber-600',
                default => 'bg-slate-100 text-slate-600',
            } }}">
            @if ($icon === 'models')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            @elseif ($icon === 'tiers')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M3 10h18M3 16h18M3 20h18" />
                </svg>
            @elseif ($icon === 'teams')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            @elseif ($icon === 'bottleneck')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            @endif
        </div>
    </div>
    
    <div class="mt-3.5 flex items-center justify-between text-xs">
        <span class="text-slate-500 flex items-center gap-1">
            @if ($trend === 'up')
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            @elseif ($trend === 'down')
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" />
                </svg>
            @endif
            <span>{{ $change }}</span>
        </span>
        @if ($badge)
            <x-badge :status="$badge" />
        @endif
    </div>
</div>
