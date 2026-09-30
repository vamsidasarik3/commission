@props([
    'treeData' => [],
    'uniqueId' => null,
])

@php
    $uniqueId = $uniqueId ?? 'hdf-' . uniqid();
    $theme = $treeData['theme'] ?? 'indigo';
    $modelType = $treeData['model_type'] ?? 'weakest_link';
    $title = $treeData['title'] ?? 'Hierarchy Diagram & Commission Flow';
    $subtitle = $treeData['subtitle'] ?? 'Visual branch diagram illustrating organizational hierarchy, sales performance, and upward commission propagation.';
    $badgeText = $treeData['badge_text'] ?? 'Interactive Graphic';
    $totalPayout = (float) ($treeData['total_payout'] ?? 0);
    $payoutLabel = $treeData['payout_label'] ?? 'Total Commission Payout:';
    $topLeader = $treeData['top_leader'] ?? 'A';
    $topLeaderComm = (float) ($treeData['top_leader_commission'] ?? 0);
    $modelName = $treeData['model_name'] ?? 'Commission Model';
    $modelId = $treeData['model_id'] ?? 'model';
    $maxGen = $treeData['max_generations'] ?? 10;

    // Theme color palettes
    $themeClasses = [
        'indigo' => [
            'icon_bg' => 'bg-indigo-600 shadow-indigo-600/30',
            'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'accent' => '#4f46e5',
            'ring' => 'focus:ring-indigo-500',
            'payout_text' => 'text-indigo-900',
            'payout_amount' => 'text-indigo-700',
        ],
        'emerald' => [
            'icon_bg' => 'bg-emerald-600 shadow-emerald-600/30',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'accent' => '#059669',
            'ring' => 'focus:ring-emerald-500',
            'payout_text' => 'text-emerald-900',
            'payout_amount' => 'text-emerald-700',
        ],
        'violet' => [
            'icon_bg' => 'bg-violet-600 shadow-violet-600/30',
            'badge' => 'bg-violet-100 text-violet-800 border-violet-200',
            'accent' => '#7c3aed',
            'ring' => 'focus:ring-violet-500',
            'payout_text' => 'text-violet-900',
            'payout_amount' => 'text-violet-700',
        ],
    ];

    $palette = $themeClasses[$theme] ?? $themeClasses['indigo'];
@endphp

<!-- ========================================================================= -->
<!-- HIERARCHY DIAGRAM & COMMISSION FLOW (COMPONENT) -->
<!-- ========================================================================= -->
<div id="{{ $uniqueId }}-container" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-tree-data='@json($treeData)'>
    <!-- Header & Action Controls -->
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl {{ $palette['icon_bg'] }} text-white flex items-center justify-center font-bold shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span>{!! $title !!}</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $palette['badge'] }} border uppercase tracking-wider">
                        {{ $badgeText }}
                    </span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $subtitle }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Search Person / Node Input -->
            <div class="relative">
                <input 
                    type="text" 
                    id="{{ $uniqueId }}-search-input" 
                    placeholder="Search person..." 
                    class="w-36 sm:w-44 pl-7 pr-2.5 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 {{ $palette['ring'] }} focus:border-transparent bg-white placeholder-slate-400 font-mono shadow-2xs transition-all"
                >
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Zoom & Pan Controls -->
            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-0.5 shadow-2xs">
                <button type="button" id="{{ $uniqueId }}-btn-zoom-in" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors" title="Zoom In (+)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <button type="button" id="{{ $uniqueId }}-btn-zoom-out" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors" title="Zoom Out (-)">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <button type="button" id="{{ $uniqueId }}-btn-zoom-reset" class="px-2 py-1 hover:bg-slate-100 rounded-lg text-[11px] font-mono font-bold text-slate-700 transition-colors" title="Reset Zoom (1:1)">
                    1:1
                </button>
            </div>

            <!-- Export Tree as PNG Image Button -->
            <button 
                type="button" 
                id="{{ $uniqueId }}-btn-export-image" 
                onclick="exportHierarchyDiagramPng('{{ $uniqueId }}', '{{ addslashes($modelName) }}')" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition-all"
                title="Export the interactive hierarchy diagram as an image file (PNG)"
            >
                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Tree (PNG)</span>
            </button>
        </div>
    </div>

    <!-- Legend & Commission Summary Bar -->
    <div class="px-6 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between gap-4 text-xs font-sans text-slate-600">
        <div class="flex flex-wrap items-center gap-4 sm:gap-6">
            @if($theme === 'violet')
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#f5f3ff] border-2 border-[#7c3aed]"></div>
                    <span class="font-semibold text-slate-800">Root Distributor</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#faf5ff] border-2 border-[#8b5cf6]"></div>
                    <span class="font-semibold text-slate-700">Upline Leader</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#edfaf4] border-2 border-[#56a97a]"></div>
                    <span class="font-semibold text-slate-700">Retail Distributor (Leaf)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="px-2 py-0.5 rounded bg-white border border-slate-300 text-[11px] font-bold text-slate-700">10% • ₹2,400</div>
                    <span class="text-slate-500">Rate applied & Branch Commission</span>
                </div>
            @elseif($theme === 'emerald')
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#ecfdf5] border-2 border-[#059669]"></div>
                    <span class="font-semibold text-emerald-950">Leader / Upline Node</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#f0fdf4] border-2 border-[#10b981]"></div>
                    <span class="font-semibold text-emerald-900">Salesperson / Leaf</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="px-2 py-0.5 rounded bg-white border border-slate-300 text-[11px] font-bold text-slate-700">5% • ₹50</div>
                    <span class="text-slate-500">Override % & Branch Commission</span>
                </div>
                <div class="flex items-center gap-1.5 px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-semibold">
                    <span>Limit: {{ $maxGen }} Generations</span>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#fff0ee] border-2 border-[#c0705a]"></div>
                    <span class="font-semibold text-[#7c2d12]">Leader / Internal Node</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#edfaf4] border-2 border-[#56a97a]"></div>
                    <span class="font-semibold text-[#14532d]">Salesperson / Leaf Node</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-5 h-4 rounded bg-[#fff1f2] border-2 border-dashed border-[#e11d48]"></div>
                    <span class="font-bold text-[#e11d48]">★ Weakest Link Bottleneck</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="px-2 py-0.5 rounded bg-white border border-slate-300 text-[11px] font-bold text-slate-700">5% • ₹50</div>
                    <span class="text-slate-500">Rate applied & Branch Commission</span>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3 font-semibold text-slate-800">
            @if($topLeaderComm > 0)
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="text-slate-500">Top Leader ({{ $topLeader }}):</span>
                    <span class="font-mono font-bold text-emerald-700">₹{{ number_format($topLeaderComm, 2) }}</span>
                </div>
            @endif
            <div class="flex items-center gap-1.5 text-xs">
                <span class="text-slate-500">{{ $payoutLabel }}</span>
                <span class="font-mono font-extrabold text-sm {{ $palette['payout_amount'] }}">₹{{ number_format($totalPayout, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Active Node Flow Notice (Injected on Node Click) -->
    <div id="{{ $uniqueId }}-flow-toast" class="hidden px-6 py-2.5 bg-slate-900 text-white text-xs flex flex-wrap items-center justify-between gap-3 font-mono transition-all">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            <span id="{{ $uniqueId }}-flow-text" class="text-slate-200">Select any node to highlight upward commission flow.</span>
        </div>
        <button type="button" onclick="resetDiagramHighlight('{{ $uniqueId }}')" class="text-slate-400 hover:text-white underline text-[11px]">
            Reset Highlight
        </button>
    </div>

    <!-- SVG Canvas Scrollable Viewport -->
    <div id="{{ $uniqueId }}-wrapper" class="overflow-auto bg-white select-none" style="width: 100%; max-height: 680px; position: relative;">
        <div id="{{ $uniqueId }}-canvas-box" style="padding: 32px 28px 40px 28px; display: inline-block; min-width: 100%;">
            <svg id="{{ $uniqueId }}-svg" style="display: block; overflow: visible; transition: transform 0.15s ease;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <!-- Marker for directional commission flow arrow -->
                    <marker id="{{ $uniqueId }}-arrow" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                        <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="#94a3b8" />
                    </marker>
                    <marker id="{{ $uniqueId }}-arrow-highlight" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                        <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="{{ $palette['accent'] }}" />
                    </marker>
                </defs>
                <g id="{{ $uniqueId }}-viewport-group">
                    <g id="{{ $uniqueId }}-edges-group"></g>
                    <g id="{{ $uniqueId }}-nodes-group"></g>
                </g>
            </svg>
        </div>
    </div>
</div>

<script>
(function() {
    var containerId = '{{ $uniqueId }}';
    var container = document.getElementById(containerId + '-container');
    if (!container) return;

    var rawData = container.getAttribute('data-tree-data');
    var treeData = {};
    try {
        treeData = JSON.parse(rawData);
    } catch(e) {
        console.error('Invalid tree data JSON:', e);
        return;
    }

    var theme = treeData.theme || 'indigo';
    var modelType = treeData.model_type || 'weakest_link';
    var inputNodes = treeData.nodes || [];
    var inputEdges = treeData.edges || [];
    var weakest = treeData.weakest_person || '';
    var topLeader = treeData.top_leader || (inputNodes.length > 0 ? inputNodes[0].name : 'A');

    var nodes = {}; // keyed by name
    var edges = []; // array of edges

    // 1. Populate node map
    inputNodes.forEach(function(n) {
        nodes[n.name] = {
            name: n.name,
            parent: n.parent || null,
            children: [],
            sales: parseFloat(n.sales) || 0,
            commission: parseFloat(n.commission) || 0,
            level: parseInt(n.level) || 1,
            role: n.role || (n.parent ? (n.is_leaf ? 'Salesperson' : 'Upline Leader') : 'Top Leader'),
            is_leaf: (n.is_leaf === true || n.is_leaf === 1 || n.is_leaf === 'true'),
            is_weakest: (n.name === weakest || n.is_weakest === true)
        };
    });

    // Populate edges and construct children links
    inputEdges.forEach(function(e) {
        if (!nodes[e.from]) {
            nodes[e.from] = { name: e.from, parent: null, children: [], sales: 0, commission: 0, level: 1, is_leaf: false };
        }
        if (!nodes[e.to]) {
            nodes[e.to] = { name: e.to, parent: e.from, children: [], sales: 0, commission: 0, level: 2, is_leaf: true };
        }

        nodes[e.to].parent = e.from;
        if (nodes[e.from].children.indexOf(e.to) === -1) {
            nodes[e.from].children.push(e.to);
            nodes[e.from].is_leaf = false;
        }

        edges.push({
            id: e.from + '-' + e.to,
            from: e.from,
            to: e.to,
            pct: parseFloat(e.pct) || 0,
            commAmt: parseFloat(e.commAmt) || 0,
            branch: e.branch || 'downline',
            tooltip: e.tooltip || ''
        });
    });

    // Back-connect any parents declared in nodes if not already in children
    Object.values(nodes).forEach(function(n) {
        if (n.parent && nodes[n.parent]) {
            if (nodes[n.parent].children.indexOf(n.name) === -1) {
                nodes[n.parent].children.push(n.name);
                nodes[n.parent].is_leaf = false;
            }
        }
    });

    // 2. Identify Roots
    var roots = [];
    Object.values(nodes).forEach(function(n) {
        if (!n.parent || !nodes[n.parent]) {
            roots.push(n.name);
        }
    });

    if (roots.length === 0 && Object.keys(nodes).length > 0) {
        roots = [Object.keys(nodes)[0]];
    }

    // 3. Tree Layout Engine
    var NW = 152;
    var NH = 74;
    var HGAP = 36;
    var VGAP = 94;
    var PAD = 40;

    function assignDepths(name, depth, visited) {
        if (!visited) visited = {};
        if (visited[name]) return;
        visited[name] = true;
        var n = nodes[name];
        if (!n) return;
        n._depth = depth;
        (n.children || []).forEach(function(c) {
            assignDepths(c, depth + 1, visited);
        });
    }

    var xCounter = [0];

    function computeX(name, visited) {
        if (!visited) visited = {};
        if (visited[name]) return;
        visited[name] = true;
        var n = nodes[name];
        if (!n) return;
        var children = n.children || [];
        if (children.length === 0) {
            n._x = xCounter[0];
            xCounter[0] += NW + HGAP;
        } else {
            children.forEach(function(c) {
                computeX(c, visited);
            });
            var first = nodes[children[0]];
            var last = nodes[children[children.length - 1]];
            var midX = (first._x + last._x) / 2;
            n._x = Math.max(midX, xCounter[0] - (NW + HGAP) * children.length / 2);
        }
    }

    roots.forEach(function(r) {
        assignDepths(r, 0, {});
        computeX(r, {});
    });

    var allNodeList = Object.values(nodes);
    if (allNodeList.length === 0) return;

    var minX = Math.min.apply(null, allNodeList.map(function(n) { return n._x || 0; }));
    var maxX = Math.max.apply(null, allNodeList.map(function(n) { return (n._x || 0) + NW; }));
    var maxDepth = Math.max.apply(null, allNodeList.map(function(n) { return n._depth || 0; }));

    var svgW = Math.max(860, (maxX - minX) + PAD * 2);
    var svgH = (maxDepth + 1) * (NH + VGAP) + PAD * 2 - VGAP + 30;

    allNodeList.forEach(function(n) {
        n._x = (n._x - minX) + PAD;
    });

    var svg = document.getElementById(containerId + '-svg');
    if (!svg) return;

    svg.setAttribute('width', svgW);
    svg.setAttribute('height', svgH);
    svg.setAttribute('viewBox', '0 0 ' + svgW + ' ' + svgH);

    var edgesGroup = document.getElementById(containerId + '-edges-group');
    var nodesGroup = document.getElementById(containerId + '-nodes-group');

    function fmt(val) {
        var num = parseFloat(val) || 0;
        return '\u20B9' + num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function fmtRate(rate) {
        var num = parseFloat(rate) || 0;
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(1)) + '%';
    }

    // 4. Render Edges with Pills
    edges.forEach(function(edge) {
        var pNode = nodes[edge.from];
        var cNode = nodes[edge.to];
        if (!pNode || !cNode) return;

        var x1 = pNode._x + NW / 2;
        var y1 = pNode._depth * (NH + VGAP) + PAD + NH;
        var x2 = cNode._x + NW / 2;
        var y2 = cNode._depth * (NH + VGAP) + PAD;

        var edgeG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        edgeG.setAttribute('id', containerId + '-edge-' + edge.from + '-' + edge.to);
        edgeG.setAttribute('class', 'hierarchy-edge');

        // Connecting Line
        var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        line.setAttribute('x1', x1);
        line.setAttribute('y1', y1);
        line.setAttribute('x2', x2);
        line.setAttribute('y2', y2);
        line.setAttribute('stroke', '#cbd5e1');
        line.setAttribute('stroke-width', '1.8');
        line.setAttribute('stroke-linecap', 'round');
        edgeG.appendChild(line);

        // Rate & Commission Midpoint Pill
        var mx = (x1 + x2) / 2;
        var my = (y1 + y2) / 2;
        var pillW = 76;
        var pillH = 34;

        var pillG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pillG.setAttribute('transform', 'translate(' + mx + ',' + my + ')');
        pillG.style.cursor = 'help';

        var pillRect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        pillRect.setAttribute('x', -pillW / 2);
        pillRect.setAttribute('y', -pillH / 2);
        pillRect.setAttribute('width', pillW);
        pillRect.setAttribute('height', pillH);
        pillRect.setAttribute('rx', 7);
        pillRect.setAttribute('ry', 7);
        pillRect.setAttribute('fill', '#ffffff');
        pillRect.setAttribute('stroke', '#cbd5e1');
        pillRect.setAttribute('stroke-width', '1.2');
        pillRect.setAttribute('filter', 'drop-shadow(0 1px 2px rgba(0,0,0,0.06))');
        pillG.appendChild(pillRect);

        // Rate Text
        var txtRate = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        txtRate.setAttribute('x', 0);
        txtRate.setAttribute('y', -3);
        txtRate.setAttribute('text-anchor', 'middle');
        txtRate.setAttribute('font-family', 'Inter, system-ui, sans-serif');
        txtRate.setAttribute('font-size', '10.5');
        txtRate.setAttribute('font-weight', '700');
        txtRate.setAttribute('fill', theme === 'violet' ? '#6d28d9' : (theme === 'emerald' ? '#065f46' : '#475569'));
        txtRate.textContent = fmtRate(edge.pct);
        pillG.appendChild(txtRate);

        // Commission Amount Text
        var txtComm = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        txtComm.setAttribute('x', 0);
        txtComm.setAttribute('y', 11);
        txtComm.setAttribute('text-anchor', 'middle');
        txtComm.setAttribute('font-family', 'Inter, system-ui, sans-serif');
        txtComm.setAttribute('font-size', '10');
        txtComm.setAttribute('font-weight', '800');
        txtComm.setAttribute('fill', '#059669');
        txtComm.textContent = fmt(edge.commAmt);
        pillG.appendChild(txtComm);

        // Edge Tooltip
        var edgeTitle = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        edgeTitle.textContent = 'Connection: ' + edge.from + ' \u2192 ' + edge.to + 
            '\nOverride Rate: ' + fmtRate(edge.pct) + 
            '\nBranch Commission Paid Upward: ' + fmt(edge.commAmt);
        pillG.appendChild(edgeTitle);

        edgeG.appendChild(pillG);
        edgesGroup.appendChild(edgeG);
    });

    // 5. Render Nodes
    allNodeList.forEach(function(node) {
        var nx = node._x;
        var ny = node._depth * (NH + VGAP) + PAD;
        var isLeaf = node.is_leaf;
        var isWeakest = node.is_weakest;
        var isRoot = (!node.parent);

        // Styling based on role & theme
        var fillColor, strokeColor, strokeWidth, nameColor;
        if (isWeakest) {
            fillColor = '#fff1f2';
            strokeColor = '#e11d48';
            strokeWidth = 2.5;
            nameColor = '#9f1239';
        } else if (isRoot) {
            if (theme === 'violet') {
                fillColor = '#f5f3ff';
                strokeColor = '#7c3aed';
                nameColor = '#5b21b6';
            } else if (theme === 'emerald') {
                fillColor = '#ecfdf5';
                strokeColor = '#059669';
                nameColor = '#065f46';
            } else {
                fillColor = '#eef2ff';
                strokeColor = '#4f46e5';
                nameColor = '#312e81';
            }
            strokeWidth = 2.2;
        } else if (isLeaf) {
            fillColor = '#f0fdf4';
            strokeColor = '#10b981';
            strokeWidth = 1.8;
            nameColor = '#047857';
        } else {
            // Internal leader node
            if (theme === 'violet') {
                fillColor = '#faf5ff';
                strokeColor = '#8b5cf6';
                nameColor = '#6d28d9';
            } else if (theme === 'emerald') {
                fillColor = '#f0fdf4';
                strokeColor = '#34d399';
                nameColor = '#065f46';
            } else {
                fillColor = '#fff0ee';
                strokeColor = '#c0705a';
                nameColor = '#7c2d12';
            }
            strokeWidth = 1.8;
        }

        var nodeG = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        nodeG.setAttribute('id', containerId + '-node-' + node.name);
        nodeG.setAttribute('class', 'hierarchy-node cursor-pointer');
        nodeG.style.cursor = 'pointer';

        // Background Card Rect
        var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        rect.setAttribute('x', nx);
        rect.setAttribute('y', ny);
        rect.setAttribute('width', NW);
        rect.setAttribute('height', NH);
        rect.setAttribute('rx', 10);
        rect.setAttribute('ry', 10);
        rect.setAttribute('fill', fillColor);
        rect.setAttribute('stroke', strokeColor);
        rect.setAttribute('stroke-width', strokeWidth);
        if (isWeakest) {
            rect.setAttribute('stroke-dasharray', '5,3');
        }
        nodeG.appendChild(rect);

        // Person Name Text
        var nameText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        nameText.setAttribute('x', nx + NW / 2);
        nameText.setAttribute('y', ny + (isWeakest ? 20 : (node.sales > 0 ? 22 : 28)));
        nameText.setAttribute('text-anchor', 'middle');
        nameText.setAttribute('font-family', 'Inter, system-ui, sans-serif');
        nameText.setAttribute('font-weight', '800');
        nameText.setAttribute('font-size', '13.5');
        nameText.setAttribute('fill', nameColor);
        nameText.textContent = node.name;
        nodeG.appendChild(nameText);

        // Personal Sales Text
        if (node.sales > 0) {
            var saleText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            saleText.setAttribute('x', nx + NW / 2);
            saleText.setAttribute('y', ny + 38);
            saleText.setAttribute('text-anchor', 'middle');
            saleText.setAttribute('font-family', 'Inter, system-ui, sans-serif');
            saleText.setAttribute('font-size', '10.5');
            saleText.setAttribute('font-weight', '600');
            saleText.setAttribute('fill', '#475569');
            saleText.textContent = 'Sale: ' + fmt(node.sales);
            nodeG.appendChild(saleText);
        }

        // Commission Earned Text
        var commText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        commText.setAttribute('x', nx + NW / 2);
        commText.setAttribute('y', ny + (node.sales > 0 ? 56 : 50));
        commText.setAttribute('text-anchor', 'middle');
        commText.setAttribute('font-family', 'Inter, system-ui, sans-serif');
        commText.setAttribute('font-size', '11.5');
        commText.setAttribute('font-weight', '800');
        commText.setAttribute('fill', node.commission > 0 ? '#059669' : '#94a3b8');
        commText.textContent = (isLeaf ? 'Comm: ' : 'Earns: ') + fmt(node.commission);
        nodeG.appendChild(commText);

        // Weakest link badge text
        if (isWeakest) {
            var badge = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            badge.setAttribute('x', nx + NW / 2);
            badge.setAttribute('y', ny + NH - 5);
            badge.setAttribute('text-anchor', 'middle');
            badge.setAttribute('font-family', 'Inter, system-ui, sans-serif');
            badge.setAttribute('font-size', '8.5');
            badge.setAttribute('font-weight', '800');
            badge.setAttribute('fill', '#e11d48');
            badge.textContent = '\u2605 WEAKEST LINK';
            nodeG.appendChild(badge);
        }

        // Detailed hover tooltip
        var titleElem = document.createElementNS('http://www.w3.org/2000/svg', 'title');
        var tip = [node.name + ' (' + node.role + ')'];
        if (node.sales > 0) tip.push('Personal Sales: ' + fmt(node.sales));
        tip.push('Commission Received: ' + fmt(node.commission));
        if (node.parent) tip.push('Direct Sponsor / Parent: ' + node.parent);
        if (node.children && node.children.length > 0) tip.push('Direct Recruits / Downlines: ' + node.children.join(', '));
        if (isWeakest) tip.push('\u2605 Bottleneck level limiting upstream commission');
        titleElem.textContent = tip.join('\n');
        nodeG.appendChild(titleElem);

        // Interactive Click to Highlight Commission Flow Path
        nodeG.addEventListener('click', function() {
            highlightCommissionPath(containerId, node.name, nodes, edges, fmt);
        });

        nodesGroup.appendChild(nodeG);
    });

    // 6. Center SVG canvas if narrower than wrapper
    var canvasWrapper = document.getElementById(containerId + '-wrapper');
    if (canvasWrapper && svgW < canvasWrapper.clientWidth) {
        svg.style.margin = '0 auto';
    }

    // 7. Zoom & Pan Handlers
    var zoomLevel = 1.0;
    var viewportGroup = document.getElementById(containerId + '-viewport-group');

    function applyZoom() {
        if (viewportGroup) {
            viewportGroup.setAttribute('transform', 'scale(' + zoomLevel + ')');
            svg.setAttribute('width', (svgW * zoomLevel));
            svg.setAttribute('height', (svgH * zoomLevel));
        }
    }

    var btnZoomIn = document.getElementById(containerId + '-btn-zoom-in');
    var btnZoomOut = document.getElementById(containerId + '-btn-zoom-out');
    var btnZoomReset = document.getElementById(containerId + '-btn-zoom-reset');

    if (btnZoomIn) {
        btnZoomIn.addEventListener('click', function() {
            zoomLevel = Math.min(2.5, zoomLevel + 0.15);
            applyZoom();
        });
    }
    if (btnZoomOut) {
        btnZoomOut.addEventListener('click', function() {
            zoomLevel = Math.max(0.4, zoomLevel - 0.15);
            applyZoom();
        });
    }
    if (btnZoomReset) {
        btnZoomReset.addEventListener('click', function() {
            zoomLevel = 1.0;
            applyZoom();
        });
    }

    // 8. Search / Highlight Input
    var searchInput = document.getElementById(containerId + '-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            var query = (e.target.value || '').trim().toLowerCase();
            if (!query) {
                resetDiagramHighlight(containerId);
                return;
            }
            var match = Object.keys(nodes).find(function(name) {
                return name.toLowerCase().indexOf(query) !== -1;
            });
            if (match) {
                highlightCommissionPath(containerId, match, nodes, edges, fmt);
            }
        });
    }
})();

// Function to highlight upline commission flow path
function highlightCommissionPath(containerId, personName, nodes, edges, fmt) {
    resetDiagramHighlight(containerId);

    var toast = document.getElementById(containerId + '-flow-toast');
    var toastText = document.getElementById(containerId + '-flow-text');

    var current = personName;
    var uplines = [];
    var pathEdges = [];

    while (current && nodes[current]) {
        var nodeEl = document.getElementById(containerId + '-node-' + current);
        if (nodeEl) {
            var rect = nodeEl.querySelector('rect');
            if (rect) {
                rect.setAttribute('stroke', '#4f46e5');
                rect.setAttribute('stroke-width', '3');
                rect.setAttribute('filter', 'drop-shadow(0 0 8px rgba(79, 70, 229, 0.4))');
            }
        }

        var parentName = nodes[current].parent;
        if (parentName) {
            uplines.push(parentName);
            var edgeEl = document.getElementById(containerId + '-edge-' + parentName + '-' + current);
            if (edgeEl) {
                var line = edgeEl.querySelector('line');
                if (line) {
                    line.setAttribute('stroke', '#4f46e5');
                    line.setAttribute('stroke-width', '3.5');
                }
            }
        }
        current = parentName;
    }

    if (toast && toastText) {
        toast.classList.remove('hidden');
        var p = nodes[personName];
        var saleStr = p && p.sales > 0 ? ('Personal Sale: ' + fmt(p.sales)) : 'Distributor';
        toastText.textContent = '\u2191 Commission Flow for [' + personName + ']: ' + saleStr + ' \u2192 Propagating through ' + (uplines.length > 0 ? uplines.join(' \u2192 ') : 'Root Leader');
    }
}

// Function to reset all highlights
function resetDiagramHighlight(containerId) {
    var toast = document.getElementById(containerId + '-flow-toast');
    if (toast) toast.classList.add('hidden');

    var container = document.getElementById(containerId + '-container');
    if (!container) return;

    var rects = container.querySelectorAll('.hierarchy-node rect');
    rects.forEach(function(r) {
        r.removeAttribute('filter');
    });

    var lines = container.querySelectorAll('.hierarchy-edge line');
    lines.forEach(function(l) {
        l.setAttribute('stroke', '#cbd5e1');
        l.setAttribute('stroke-width', '1.8');
    });
}

// Export Tree as PNG Image Function
function exportHierarchyDiagramPng(containerId, modelName) {
    var svg = document.getElementById(containerId + '-svg');
    if (!svg) return;

    var btn = document.getElementById(containerId + '-btn-export-image');
    var originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin text-slate-700 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Generating PNG...</span>';
    }

    var serializer = new XMLSerializer();
    var source = serializer.serializeToString(svg);
    if (!source.match(/^<svg[^>]+xmlns="http:\/\/www\.w3\.org\/2000\/svg"/)) {
        source = source.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
    }

    var svgBlob = new Blob([source], { type: 'image/svg+xml;charset=utf-8' });
    var url = URL.createObjectURL(svgBlob);

    var img = new Image();
    img.onload = function() {
        var w = parseFloat(svg.getAttribute('width')) || 1200;
        var h = parseFloat(svg.getAttribute('height')) || 800;
        var canvas = document.createElement('canvas');
        canvas.width = (w + 40) * 2;
        canvas.height = (h + 40) * 2;
        var ctx = canvas.getContext('2d');
        ctx.scale(2, 2);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, w + 40, h + 40);
        ctx.drawImage(img, 20, 20);
        URL.revokeObjectURL(url);

        try {
            var pngUrl = canvas.toDataURL('image/png');
            var downloadLink = document.createElement('a');
            var cleanName = (modelName || 'commission_model').toLowerCase().replace(/[^a-z0-9_-]/g, '_');
            downloadLink.download = cleanName + '_tree.png';
            downloadLink.href = pngUrl;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        } catch (e) {
            var downloadLink = document.createElement('a');
            downloadLink.download = 'commission_tree.svg';
            downloadLink.href = url;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
        if (btn) btn.innerHTML = originalText;
    };

    img.onerror = function() {
        var downloadLink = document.createElement('a');
        downloadLink.download = 'commission_tree.svg';
        downloadLink.href = url;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
        if (btn) btn.innerHTML = originalText;
    };

    img.src = url;
}
</script>
