@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-violet-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('commission-models.index') }}" class="hover:text-violet-600 transition-colors">Saved Models</a>
                <span>/</span>
                <span class="text-slate-900 font-semibold">Model 3 Builder</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Unilevel / Generation-Based MLM Commission Model</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-violet-100 text-violet-800 border border-violet-200">Model 3 Engine</span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Each sale flows upward independently through the sponsor chain. Every upline earns a commission based on their generational depth from the seller — no weakest-link compression, no MIN comparison.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('commission-models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition-colors">Model 1 (Weakest Link)</a>
            <a href="{{ route('override-models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors">Model 2 (Override)</a>
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">Saved Models</a>
        </div>
    </div>

    {{-- ── Invariants Banner ── --}}
    <div class="p-4 rounded-xl bg-gradient-to-r from-violet-900 to-slate-900 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-violet-500 text-white uppercase tracking-wider">Model 3 — Unilevel MLM</span>
                <span class="text-xs text-violet-200">No MIN • No Compression • Generation-Depth Rate Schedule</span>
            </div>
            <p class="text-xs text-slate-300 max-w-3xl">
                A distributor may personally recruit unlimited members. Each recruit builds their own downline. When any distributor makes a sale, every ancestor in their upline chain earns an override commission — each at the percentage defined for their depth from the seller.
                <strong class="text-white">This is a generic illustrative model; percentages are not from any real company's plan.</strong>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-load-10level-preset" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-violet-600 hover:bg-violet-500 text-white border border-violet-400/40 shadow transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Load 10-Level Example
            </button>
        </div>
    </div>

    {{-- ── Reference Hierarchy Diagram ── --}}
    <div class="bg-white rounded-2xl border border-violet-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-violet-100 bg-violet-50/60 flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-violet-600 text-white flex items-center justify-center font-bold shadow-sm shadow-violet-600/30 text-sm">M3</span>
            <div>
                <h3 class="text-base font-bold text-slate-900">10-Level Unilevel Hierarchy — Reference Diagram</h3>
                <p class="text-xs text-slate-500">Every distributor has ≥3 direct recruits. Labels show the rate each node earns as an upline when someone below them sells.</p>
            </div>
        </div>
        <div class="p-6 overflow-x-auto">
            {{-- Rates legend --}}
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach([1=>'10%',2=>'5%',3=>'4%',4=>'3%',5=>'2%',6=>'1%',7=>'1%',8=>'0.5%',9=>'0.5%',10=>'0.5%'] as $d=>$r)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-violet-100 text-violet-800 border border-violet-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Level {{ $d }}: {{ $r }}
                </span>
                @endforeach
            </div>

            {{-- Visual tree (CSS-based for clarity) --}}
            <div class="text-xs font-mono leading-relaxed overflow-x-auto whitespace-pre bg-slate-50 rounded-xl p-5 border border-slate-200 text-slate-700" style="font-size:11px; line-height:1.7;">
[<span class="text-violet-700 font-bold">Level 1</span>] A ──── (Root Distributor · earns as upline: 10% from L2 · 5% from L3 · 4% from L4 ... 0.5% from L10)
│
├── [<span class="text-violet-600 font-bold">Level 2</span>] B ── (earns: 10% from L3 · 5% from L4 · 4% from L5 · ... · 0.5% from L10)
│   ├── [<span class="text-violet-500 font-bold">Level 3</span>] B1
│   │   ├── [<span class="text-indigo-500 font-bold">Level 4</span>] B1a
│   │   │   ├── [<span class="text-indigo-400 font-bold">Level 5</span>] B1a-1
│   │   │   │   ├── [<span class="text-blue-500 font-bold">Level 6</span>] B1a-1-i
│   │   │   │   │   ├── [<span class="text-blue-400 font-bold">Level 7</span>] B1a-1-i-α
│   │   │   │   │   │   ├── [<span class="text-cyan-500 font-bold">Level 8</span>] B1a-1-i-α-I
│   │   │   │   │   │   │   ├── [<span class="text-teal-500 font-bold">Level 9</span>] B1a-1-i-α-I-X
│   │   │   │   │   │   │   │   ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-X-p  (Sale: ₹10,000 → 9 uplines paid)
│   │   │   │   │   │   │   │   ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-X-q
│   │   │   │   │   │   │   │   └── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-X-r
│   │   │   │   │   │   │   ├── [<span class="text-teal-500 font-bold">Level 9</span>] B1a-1-i-α-I-Y
│   │   │   │   │   │   │   │   ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Y-p
│   │   │   │   │   │   │   │   ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Y-q
│   │   │   │   │   │   │   │   └── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Y-r
│   │   │   │   │   │   │   └── [<span class="text-teal-500 font-bold">Level 9</span>] B1a-1-i-α-I-Z
│   │   │   │   │   │   │       ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Z-p
│   │   │   │   │   │   │       ├── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Z-q
│   │   │   │   │   │   │       └── [<span class="text-green-600 font-bold">Level 10</span>] B1a-1-i-α-I-Z-r
│   │   │   │   │   │   ├── [<span class="text-cyan-500 font-bold">Level 8</span>] B1a-1-i-α-II  (3 L9 children, each with 3 L10 children)
│   │   │   │   │   │   └── [<span class="text-cyan-500 font-bold">Level 8</span>] B1a-1-i-α-III (3 L9 children, each with 3 L10 children)
│   │   │   │   │   ├── [<span class="text-blue-400 font-bold">Level 7</span>] B1a-1-i-β  (3 L8 children, each with 3 L9, each with 3 L10)
│   │   │   │   │   └── [<span class="text-blue-400 font-bold">Level 7</span>] B1a-1-i-γ  (3 L8 children, each with 3 L9, each with 3 L10)
│   │   │   │   ├── [<span class="text-blue-500 font-bold">Level 6</span>] B1a-1-ii  (3 L7, 3 L8, 3 L9, 3 L10 per branch)
│   │   │   │   └── [<span class="text-blue-500 font-bold">Level 6</span>] B1a-1-iii (3 L7, 3 L8, 3 L9, 3 L10 per branch)
│   │   │   ├── [<span class="text-indigo-400 font-bold">Level 5</span>] B1a-2  (3 L6, 3 L7, 3 L8, 3 L9, 3 L10 per branch)
│   │   │   └── [<span class="text-indigo-400 font-bold">Level 5</span>] B1a-3  (3 L6, 3 L7, 3 L8, 3 L9, 3 L10 per branch)
│   │   ├── [<span class="text-indigo-500 font-bold">Level 4</span>] B1b  (3 L5 → 3 L6 → 3 L7 → 3 L8 → 3 L9 → 3 L10)
│   │   └── [<span class="text-indigo-500 font-bold">Level 4</span>] B1c  (3 L5 → 3 L6 → 3 L7 → 3 L8 → 3 L9 → 3 L10)
│   ├── [<span class="text-violet-500 font-bold">Level 3</span>] B2  (3 L4 → 3 L5 → 3 L6 → 3 L7 → 3 L8 → 3 L9 → 3 L10)
│   └── [<span class="text-violet-500 font-bold">Level 3</span>] B3  (3 L4 → 3 L5 → 3 L6 → 3 L7 → 3 L8 → 3 L9 → 3 L10)
│
├── [<span class="text-violet-600 font-bold">Level 2</span>] C  (3 L3 branches, each expanding fully to L10)
│   ├── C1 → C1a → C1a-1 → ... → Level 10
│   ├── C2 → C2a → C2a-1 → ... → Level 10
│   └── C3 → C3a → C3a-1 → ... → Level 10
│
└── [<span class="text-violet-600 font-bold">Level 2</span>] D  (3 L3 branches, each expanding fully to L10)
    ├── D1 → D1a → D1a-1 → ... → Level 10
    ├── D2 → D2a → D2a-1 → ... → Level 10
    └── D3 → D3a → D3a-1 → ... → Level 10

<span class="text-slate-400">Every node at every level has ≥ 3 direct children. Total nodes (3-wide, 10-deep): 3^0 + 3^1 + ... + 3^9 = 29,524 distributors.</span>
            </div>
        </div>
    </div>

    {{-- ── Commission Flow Visualisation ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Commission Flow — Single ₹10,000 Sale at Level 10</h3>
            <p class="text-xs text-slate-500 mt-0.5">When <strong>B1a-1-i-α-I-X-p</strong> (Level 10) makes a ₹10,000 sale, commissions travel upward through 9 generations simultaneously and independently.</p>
        </div>
        <div class="p-6">
            {{-- Flow diagram --}}
            <div class="grid grid-cols-1 gap-0">
                @php
                $flowRows = [
                    ['gen'=>1, 'earner'=>'B1a-1-i-α-I-X',   'lvl'=>9,  'rate'=>10.0,  'comm'=>1000.00, 'color'=>'violet'],
                    ['gen'=>2, 'earner'=>'B1a-1-i-α-I',      'lvl'=>8,  'rate'=>5.0,   'comm'=>500.00,  'color'=>'indigo'],
                    ['gen'=>3, 'earner'=>'B1a-1-i-α',        'lvl'=>7,  'rate'=>4.0,   'comm'=>400.00,  'color'=>'blue'],
                    ['gen'=>4, 'earner'=>'B1a-1-i',          'lvl'=>6,  'rate'=>3.0,   'comm'=>300.00,  'color'=>'cyan'],
                    ['gen'=>5, 'earner'=>'B1a-1',            'lvl'=>5,  'rate'=>2.0,   'comm'=>200.00,  'color'=>'teal'],
                    ['gen'=>6, 'earner'=>'B1a',              'lvl'=>4,  'rate'=>1.0,   'comm'=>100.00,  'color'=>'green'],
                    ['gen'=>7, 'earner'=>'B1',               'lvl'=>3,  'rate'=>1.0,   'comm'=>100.00,  'color'=>'lime'],
                    ['gen'=>8, 'earner'=>'B',                'lvl'=>2,  'rate'=>0.5,   'comm'=>50.00,   'color'=>'yellow'],
                    ['gen'=>9, 'earner'=>'A',                'lvl'=>1,  'rate'=>0.5,   'comm'=>50.00,   'color'=>'orange'],
                ];
                $colorMap = [
                    'violet'=>['bg'=>'bg-violet-50','border'=>'border-violet-300','badge'=>'bg-violet-600','text'=>'text-violet-800','amount'=>'text-violet-700'],
                    'indigo'=>['bg'=>'bg-indigo-50','border'=>'border-indigo-300','badge'=>'bg-indigo-600','text'=>'text-indigo-800','amount'=>'text-indigo-700'],
                    'blue'  =>['bg'=>'bg-blue-50',  'border'=>'border-blue-300',  'badge'=>'bg-blue-600',  'text'=>'text-blue-800',  'amount'=>'text-blue-700'],
                    'cyan'  =>['bg'=>'bg-cyan-50',  'border'=>'border-cyan-300',  'badge'=>'bg-cyan-600',  'text'=>'text-cyan-800',  'amount'=>'text-cyan-700'],
                    'teal'  =>['bg'=>'bg-teal-50',  'border'=>'border-teal-300',  'badge'=>'bg-teal-600',  'text'=>'text-teal-800',  'amount'=>'text-teal-700'],
                    'green' =>['bg'=>'bg-green-50', 'border'=>'border-green-300', 'badge'=>'bg-green-600', 'text'=>'text-green-800', 'amount'=>'text-green-700'],
                    'lime'  =>['bg'=>'bg-lime-50',  'border'=>'border-lime-300',  'badge'=>'bg-lime-600',  'text'=>'text-lime-800',  'amount'=>'text-lime-700'],
                    'yellow'=>['bg'=>'bg-yellow-50','border'=>'border-yellow-300','badge'=>'bg-yellow-500','text'=>'text-yellow-800','amount'=>'text-yellow-700'],
                    'orange'=>['bg'=>'bg-orange-50','border'=>'border-orange-300','badge'=>'bg-orange-500','text'=>'text-orange-800','amount'=>'text-orange-700'],
                ];
                @endphp

                {{-- Seller row --}}
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-7 h-7 rounded-full bg-slate-800 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">S</div>
                    <div class="flex-1 bg-slate-800 text-white rounded-xl px-4 py-3 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Seller (Level 10)</span>
                            <p class="font-bold text-sm">B1a-1-i-α-I-X-p</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400">Sale Amount</span>
                            <p class="font-extrabold text-emerald-400 text-lg font-mono">₹10,000.00</p>
                        </div>
                    </div>
                </div>

                @foreach($flowRows as $i => $row)
                @php $c = $colorMap[$row['color']]; @endphp
                {{-- Arrow --}}
                <div class="flex items-center gap-3 py-0.5 ml-3.5">
                    <div class="w-px h-5 bg-slate-300 ml-[calc(0.875rem-0.5px)]"></div>
                    <svg class="w-3 h-3 text-slate-400 -ml-1.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    <span class="text-[10px] text-slate-400 font-medium">Generation {{ $row['gen'] }} upline</span>
                </div>
                <div class="flex items-center gap-3 mb-0.5">
                    <div class="w-7 h-7 rounded-full {{ $c['badge'] }} text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0">{{ $row['gen'] }}</div>
                    <div class="flex-1 {{ $c['bg'] }} border {{ $c['border'] }} rounded-xl px-4 py-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider {{ $c['text'] }}">Level {{ $row['lvl'] }} Upline — Gen {{ $row['gen'] }}</span>
                            <p class="font-bold text-sm text-slate-800">{{ $row['earner'] }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500" id="flow-formula-{{ $row['gen'] }}">₹10,000 × {{ $row['rate'] }}%</span>
                            <p class="font-extrabold {{ $c['amount'] }} text-base font-mono" id="flow-comm-{{ $row['gen'] }}">₹{{ number_format($row['comm'], 2) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Commission Calculation Table ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Commission Calculation Table — ₹10,000 Sale at Level 10</h3>
            <p class="text-xs text-slate-500 mt-0.5">All 9 uplines receive their commission from the <strong>same ₹10,000 sale amount</strong> — the base is never reduced.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Generation</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Upline Earner</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Tree Level</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Sale Base</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-center">Rate</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider text-right">Commission Earned</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php
                    $tableRows = [
                        [1,'B1a-1-i-α-I-X (Direct Parent)',9,10000,10.0,1000.0],
                        [2,'B1a-1-i-α-I (Grandparent)',8,10000,5.0,500.0],
                        [3,'B1a-1-i-α (Great-grandparent)',7,10000,4.0,400.0],
                        [4,'B1a-1-i',6,10000,3.0,300.0],
                        [5,'B1a-1',5,10000,2.0,200.0],
                        [6,'B1a',4,10000,1.0,100.0],
                        [7,'B1',3,10000,1.0,100.0],
                        [8,'B',2,10000,0.5,50.0],
                        [9,'A (Root)',1,10000,0.5,50.0],
                    ];
                    $total = collect($tableRows)->sum(fn($r)=>$r[5]);
                    @endphp
                    @foreach($tableRows as [$gen,$earner,$lvl,$base,$rate,$comm])
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-violet-100 text-violet-700 font-bold text-xs">{{ $gen }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-800 font-mono text-xs">{{ $earner }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">Level {{ $lvl }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-600">₹{{ number_format($base, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold text-violet-700" id="table-rate-{{ $gen }}">{{ $rate }}%</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-700" id="table-comm-{{ $gen }}">₹{{ number_format($comm, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-violet-50 border-t-2 border-violet-200">
                        <td colspan="5" class="px-4 py-4 font-bold text-violet-900 text-sm">Total Commission Generated from ONE ₹10,000 Sale</td>
                        <td class="px-4 py-4 text-right font-extrabold font-mono text-violet-700 text-lg" id="table-total-comm">₹{{ number_format($total, 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50 border-t border-slate-200">
                        <td colspan="5" class="px-4 py-2 text-xs text-slate-500">Effective total commission rate on a single sale</td>
                        <td class="px-4 py-2 text-right font-semibold text-slate-700 text-xs" id="table-eff-rate">{{ number_format(($total/10000)*100, 1) }}% of sale</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Model Comparison ── --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-base font-bold text-slate-900">Model Comparison</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Feature</th>
                        <th class="px-4 py-3 text-xs font-semibold text-indigo-600 uppercase tracking-wider">Model 1 · Weakest Link</th>
                        <th class="px-4 py-3 text-xs font-semibold text-emerald-600 uppercase tracking-wider">Model 2 · Override</th>
                        <th class="px-4 py-3 text-xs font-semibold text-violet-600 uppercase tracking-wider">Model 3 · Unilevel MLM</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $rows = [
                        ['Calculation Operator','MIN(weakest branch)','Sale × Edge Rate','Sale × Depth Rate'],
                        ['Rate Structure','Single flat rate for all','Per-edge configurable rate','Per-depth generation schedule'],
                        ['Low Performer Impact','Bottlenecks entire upline','Uplines unaffected','Uplines earn on all other sales'],
                        ['Sale Base Propagation','Reduced at each MIN step','Full original amount','Full original amount'],
                        ['Max Depth Concept','Number of chain levels','Max generation cap','Max generation depth (10)'],
                        ['Upline Count Per Sale','1 chain upward','Multi-upline per edge','1 direct sponsor chain upward'],
                        ['Branching Factor','Single main chain','Tree with any topology','Sponsor can recruit unlimited'],
                    ]; @endphp
                    @foreach($rows as [$feature,$m1,$m2,$m3])
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $feature }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m1 }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $m2 }}</td>
                        <td class="px-4 py-3 text-violet-700 font-medium">{{ $m3 }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── AJAX Error Alert ── --}}
    <div id="ajax-error-alert" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-2 hidden">
        <div class="flex items-center gap-2 font-semibold text-sm">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span id="ajax-error-title">Validation Error</span>
        </div>
        <ul id="ajax-error-list" class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5"></ul>
    </div>

    @if(isset($errors) && $errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
        <div class="font-semibold text-sm mb-2">Please fix the errors:</div>
        <ul class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Builder Form ── --}}
    <form id="unilevel-form" action="{{ route('unilevel-models.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Section 1: Model Details --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 1</span>
                <h3 class="text-base font-semibold text-slate-900 mt-1">Model Details</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Model Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="model-name" value="{{ old('name') }}"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                        placeholder="e.g. Q4 2026 Unilevel Example" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Max Depth (Levels) <span class="text-rose-500">*</span></label>
                    <input type="number" name="max_depth" id="max-depth" value="{{ old('max_depth', 10) }}" min="1" max="20"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors" required>
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Description (optional)</label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors"
                        placeholder="Brief description of this model...">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 2: Rate Schedule --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 2</span>
                <h3 class="text-base font-semibold text-slate-900 mt-1">Generation Rate Schedule</h3>
                <p class="text-xs text-slate-500 mt-0.5">Rate paid to an upline at each generation depth from the seller. Defaults are illustrative only.</p>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-5 gap-3">
                @foreach($defaultRates as $depth => $rate)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gen {{ $depth }} Rate %</label>
                    <input type="number" name="rate_schedule[{{ $depth }}]" step="0.01" min="0" max="100"
                        value="{{ old("rate_schedule.{$depth}", $rate) }}"
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-colors">
                </div>
                @endforeach
            </div>
        </div>

        {{-- Section 3: Distributor Nodes --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 3</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Distributor Nodes</h3>
                    <p class="text-xs text-slate-500">Define each distributor and their direct sponsor (parent). Root distributor has no parent.</p>
                </div>
                <button type="button" id="add-node-btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Node
                </button>
            </div>
            <div class="p-6 space-y-2" id="nodes-container">
                {{-- Pre-populated by JS --}}
            </div>
        </div>

        {{-- Section 4: Sales Records --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-violet-600 bg-violet-50 px-2 py-0.5 rounded border border-violet-100">Section 4</span>
                    <h3 class="text-base font-semibold text-slate-900 mt-1">Personal Sales</h3>
                    <p class="text-xs text-slate-500">Record each distributor's personal monthly eligible sales volume.</p>
                </div>
                <button type="button" id="add-sale-btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Sale
                </button>
            </div>
            <div class="p-6 space-y-2" id="sales-container">
                {{-- Pre-populated by JS --}}
            </div>
        </div>

        {{-- Live Results Panel --}}
        <div id="live-results-panel" class="bg-white rounded-xl border border-violet-200 shadow-sm overflow-hidden hidden">
            <div class="px-6 py-4 border-b border-violet-100 bg-violet-50/60 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-violet-500 animate-pulse"></span>
                <h3 class="text-base font-bold text-violet-900">Live Calculation Results</h3>
            </div>
            <div class="p-6" id="live-results-content"></div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex gap-2">
                <button type="button" id="btn-calculate-ajax" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg bg-violet-100 text-violet-800 hover:bg-violet-200 border border-violet-300 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M4 19h16a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Preview Calculation
                </button>
            </div>
            <button type="submit" id="btn-save" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold rounded-lg bg-violet-600 text-white hover:bg-violet-700 shadow-sm shadow-violet-600/25 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Calculate & Save Model
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';

    /* ── Default 10-level preset data ── */
    const PRESET_NODES = [
        {name:'A',     parent:''},
        {name:'B',     parent:'A'},
        {name:'C',     parent:'A'},
        {name:'D',     parent:'A'},
        {name:'B1',    parent:'B'},
        {name:'B2',    parent:'B'},
        {name:'B3',    parent:'B'},
        {name:'C1',    parent:'C'},
        {name:'C2',    parent:'C'},
        {name:'C3',    parent:'C'},
        {name:'D1',    parent:'D'},
        {name:'D2',    parent:'D'},
        {name:'D3',    parent:'D'},
        {name:'B1a',   parent:'B1'},
        {name:'B1b',   parent:'B1'},
        {name:'B1c',   parent:'B1'},
        {name:'B1a-1', parent:'B1a'},
        {name:'B1a-2', parent:'B1a'},
        {name:'B1a-3', parent:'B1a'},
        {name:'B1a-1-i',  parent:'B1a-1'},
        {name:'B1a-1-ii', parent:'B1a-1'},
        {name:'B1a-1-iii',parent:'B1a-1'},
        {name:'B1a-1-i-α',parent:'B1a-1-i'},
        {name:'B1a-1-i-β',parent:'B1a-1-i'},
        {name:'B1a-1-i-γ',parent:'B1a-1-i'},
        {name:'B1a-1-i-α-I',  parent:'B1a-1-i-α'},
        {name:'B1a-1-i-α-II', parent:'B1a-1-i-α'},
        {name:'B1a-1-i-α-III',parent:'B1a-1-i-α'},
        {name:'B1a-1-i-α-I-X',parent:'B1a-1-i-α-I'},
        {name:'B1a-1-i-α-I-Y',parent:'B1a-1-i-α-I'},
        {name:'B1a-1-i-α-I-Z',parent:'B1a-1-i-α-I'},
        {name:'B1a-1-i-α-I-X-p',parent:'B1a-1-i-α-I-X'},
        {name:'B1a-1-i-α-I-X-q',parent:'B1a-1-i-α-I-X'},
        {name:'B1a-1-i-α-I-X-r',parent:'B1a-1-i-α-I-X'},
    ];

    const PRESET_SALES = [
        {distributor:'B1a-1-i-α-I-X-p', amount:10000},
        {distributor:'B1a-1-i-α-I-X-q', amount:10000},
        {distributor:'B1a-1-i-α-I-X-r', amount:10000},
        {distributor:'B1a-1-i-α-I-Y',   amount:10000},
        {distributor:'B2',               amount:10000},
        {distributor:'C1',               amount:10000},
        {distributor:'D1',               amount:10000},
    ];

    let nodeCount = 0;
    let saleCount = 0;

    /* ── Render a node row ── */
    function addNodeRow(name = '', parent = '') {
        const idx = nodeCount++;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <div class="flex-1 grid grid-cols-2 gap-2">
                <input type="text" name="nodes[${idx}][name]" value="${name}"
                    placeholder="Distributor Name (e.g. B1)"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                <input type="text" name="nodes[${idx}][parent]" value="${parent}"
                    placeholder="Sponsor Name (blank = root)"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        document.getElementById('nodes-container').appendChild(row);
    }

    /* ── Render a sale row ── */
    function addSaleRow(distributor = '', amount = 10000) {
        const idx = saleCount++;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2';
        row.innerHTML = `
            <div class="flex-1 grid grid-cols-2 gap-2">
                <input type="text" name="sales[${idx}][distributor]" value="${distributor}"
                    placeholder="Distributor Name"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
                <input type="number" name="sales[${idx}][amount]" value="${amount}" min="0" step="0.01"
                    placeholder="Sale Amount (₹)"
                    class="px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500" required>
            </div>
            <button type="button" onclick="this.parentElement.remove()"
                class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>`;
        document.getElementById('sales-container').appendChild(row);
    }

    /* ── Load preset ── */
    function loadPreset() {
        document.getElementById('nodes-container').innerHTML = '';
        document.getElementById('sales-container').innerHTML = '';
        nodeCount = 0; saleCount = 0;
        PRESET_NODES.forEach(n => addNodeRow(n.name, n.parent));
        PRESET_SALES.forEach(s => addSaleRow(s.distributor, s.amount));
        document.getElementById('model-name').value = 'Q4 2026 — 10-Level Unilevel Example';
    }

    /* ── Collect form data for AJAX ── */
    function collectFormData() {
        const formData = new FormData(document.getElementById('unilevel-form'));
        const data = { nodes: [], sales: [], rate_schedule: {}, max_depth: 10 };
        data.max_depth = parseInt(document.getElementById('max-depth').value) || 10;

        const nodeMap = {};
        const saleMap = {};

        for (const [key, value] of formData.entries()) {
            const nodeMatch = key.match(/^nodes\[(\d+)\]\[(\w+)\]$/);
            if (nodeMatch) {
                const [, idx, field] = nodeMatch;
                if (!nodeMap[idx]) nodeMap[idx] = {};
                nodeMap[idx][field] = value;
            }
            const saleMatch = key.match(/^sales\[(\d+)\]\[(\w+)\]$/);
            if (saleMatch) {
                const [, idx, field] = saleMatch;
                if (!saleMap[idx]) saleMap[idx] = {};
                saleMap[idx][field] = field === 'amount' ? parseFloat(value) : value;
            }
            const rateMatch = key.match(/^rate_schedule\[(\d+)\]$/);
            if (rateMatch) data.rate_schedule[rateMatch[1]] = parseFloat(value);
        }

        data.nodes = Object.values(nodeMap).filter(n => n.name);
        data.sales = Object.values(saleMap).filter(s => s.distributor && s.amount > 0);
        return data;
    }

    /* ── AJAX preview ── */
    document.getElementById('btn-calculate-ajax').addEventListener('click', async () => {
        const data = collectFormData();
        const errorDiv = document.getElementById('ajax-error-alert');
        errorDiv.classList.add('hidden');

        try {
            const resp = await fetch('{{ route("unilevel-models.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            });
            const json = await resp.json();

            if (!json.success) {
                document.getElementById('ajax-error-list').innerHTML =
                    (json.errors || [json.message]).map(e => `<li>${e}</li>`).join('');
                errorDiv.classList.remove('hidden');
                return;
            }

            const r = json.data;
            const panel = document.getElementById('live-results-panel');
            const content = document.getElementById('live-results-content');

            const earners = Object.values(r.earnings_by_person || {})
                .filter(p => p.override_commission > 0)
                .sort((a, b) => b.override_commission - a.override_commission);

            content.innerHTML = `
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-violet-50 rounded-xl p-4 border border-violet-200 text-center">
                        <div class="text-[10px] font-semibold text-violet-600 uppercase tracking-wider">Total Sales</div>
                        <div class="text-2xl font-extrabold text-violet-900 font-mono mt-1">₹${r.total_personal_sales.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200 text-center">
                        <div class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider">Total Commission</div>
                        <div class="text-2xl font-extrabold text-emerald-800 font-mono mt-1">₹${r.total_commission_generated.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-center">
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Distributors</div>
                        <div class="text-2xl font-extrabold text-slate-800 font-mono mt-1">${r.node_count}</div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-center">
                        <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider">Sales Recorded</div>
                        <div class="text-2xl font-extrabold text-slate-800 font-mono mt-1">${r.sale_count}</div>
                    </div>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-3">Top Earners (Override Commission)</h4>
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs">
                        <thead><tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Distributor</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Personal Sales</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Override Commission</th>
                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Total Earnings</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            ${earners.slice(0,20).map(p => `
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-2 font-mono font-semibold text-slate-800">${p.name}</td>
                                    <td class="px-3 py-2 text-right font-mono text-slate-600">₹${p.personal_sales.toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                    <td class="px-3 py-2 text-right font-mono font-bold text-violet-700">₹${p.override_commission.toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                    <td class="px-3 py-2 text-right font-mono font-bold text-emerald-700">₹${p.total_earnings.toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>`;

            panel.classList.remove('hidden');
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });

        } catch (err) {
            document.getElementById('ajax-error-list').innerHTML = `<li>${err.message}</li>`;
            errorDiv.classList.remove('hidden');
        }
    });

    /* ── Dynamic Flow Diagram Updater ── */
    function updateDynamicFlowDiagram() {
        const saleAmount = 10000;
        let totalComm = 0;
        for (let gen = 1; gen <= 9; gen++) {
            const input = document.querySelector(`input[name="rate_schedule[${gen}]"]`);
            const rate = input ? (parseFloat(input.value) || 0) : 0;
            const comm = (saleAmount * rate) / 100;
            totalComm += comm;

            const flowFormula = document.getElementById(`flow-formula-${gen}`);
            if (flowFormula) flowFormula.textContent = `₹10,000 × ${rate}%`;

            const flowComm = document.getElementById(`flow-comm-${gen}`);
            if (flowComm) flowComm.textContent = `₹` + comm.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            const tableRate = document.getElementById(`table-rate-${gen}`);
            if (tableRate) tableRate.textContent = `${rate}%`;

            const tableComm = document.getElementById(`table-comm-${gen}`);
            if (tableComm) tableComm.textContent = `₹` + comm.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        const totalEl = document.getElementById('table-total-comm');
        if (totalEl) totalEl.textContent = `₹` + totalComm.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const effEl = document.getElementById('table-eff-rate');
        if (effEl) effEl.textContent = ((totalComm / saleAmount) * 100).toFixed(1) + `% of sale`;
    }

    /* ── Listen to all rate_schedule changes ── */
    document.querySelectorAll('input[name^="rate_schedule["]').forEach(input => {
        input.addEventListener('input', updateDynamicFlowDiagram);
    });

    /* ── Wire buttons ── */
    document.getElementById('add-node-btn').addEventListener('click', () => addNodeRow());
    document.getElementById('add-sale-btn').addEventListener('click', () => addSaleRow());
    document.getElementById('btn-load-10level-preset').addEventListener('click', () => {
        loadPreset();
        updateDynamicFlowDiagram();
    });

    /* ── Boot with preset ── */
    loadPreset();
    updateDynamicFlowDiagram();
})();
</script>
@endsection
