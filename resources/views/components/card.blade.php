@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden']) }}>
    @if ($title || $actions)
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <div>
                @if ($title)
                    <h3 class="text-base font-semibold text-slate-900 tracking-tight">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>
</div>
