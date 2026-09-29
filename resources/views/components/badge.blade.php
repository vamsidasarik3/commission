@props(['status'])

@php
    $classes = match (strtolower(trim($status))) {
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-600/20',
        'draft' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-600/20',
        'under review', 'review' => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-600/20',
        'archived' => 'bg-slate-100 text-slate-600 border-slate-200 ring-slate-600/20',
        'optimal' => 'bg-teal-50 text-teal-700 border-teal-200 ring-teal-600/20',
        'configured' => 'bg-indigo-50 text-indigo-700 border-indigo-200 ring-indigo-600/20',
        'tracked' => 'bg-violet-50 text-violet-700 border-violet-200 ring-violet-600/20',
        default => 'bg-gray-50 text-gray-700 border-gray-200 ring-gray-600/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border ring-1 ring-inset $classes"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ match(strtolower(trim($status))) {
        'active' => 'bg-emerald-500',
        'draft' => 'bg-amber-500',
        'under review', 'review' => 'bg-blue-500',
        'archived' => 'bg-slate-400',
        'optimal' => 'bg-teal-500',
        'configured' => 'bg-indigo-500',
        'tracked' => 'bg-violet-500',
        default => 'bg-gray-400',
    } }}"></span>
    {{ $status }}
</span>
