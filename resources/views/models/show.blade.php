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
                <span class="text-slate-900 font-semibold">{{ $model->name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">{{ $model->name }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    #{{ $model->id }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-0.5">
                Calculated on {{ $model->created_at->format('M d, Y H:i') }} &bull; Rate: <span class="font-semibold text-slate-800">{{ number_format($model->commission_rate, 2) }}%</span> &bull; Levels: <span class="font-semibold text-slate-800">{{ $model->number_of_levels }}</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Edit -->
            <a href="{{ route('commission-models.edit', $model) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>

            <!-- Duplicate -->
            <a href="{{ route('commission-models.duplicate', $model) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200 transition-colors">
                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
                Duplicate
            </a>

            <!-- Delete with confirmation -->
            <form 
                action="{{ route('commission-models.destroy', $model) }}" 
                method="POST" 
                class="inline-block" 
                onsubmit="return confirm('Are you sure you want to delete commission model \'{{ addslashes($model->name) }}\'? This cannot be undone.');"
            >
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition-colors">
                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
                </button>
            </form>

            <!-- All Saved Models -->
            <a href="{{ route('commission-models.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-sm transition-colors">
                Saved Models
            </a>
        </div>
    </div>

    <!-- Success Flash Message -->
    @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm font-semibold">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <span class="text-xs text-emerald-600 font-mono">Status: Saved to MySQL</span>
        </div>
    @endif

    <!-- WEAKEST LINK KEY FINDING BANNER -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white shadow-md border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                Weakest Link Bottleneck Identified
            </div>
            <h3 class="text-xl font-bold text-white tracking-tight">
                Weakest Salesperson: <span class="text-rose-400 underline decoration-rose-500/50 underline-offset-4">{{ $model->weakest_person }}</span>
            </h3>
            <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                Under the Weakest Link Rule, the final commission for root leader A is bounded by the lowest branch in the hierarchy. {{ $model->weakest_person }} generated <strong class="text-white">₹{{ number_format($model->weakest_sales, 2) }}</strong> in sales (individual commission: <strong class="text-white">₹{{ number_format($model->weakest_commission, 2) }}</strong>), which forms the bottleneck ceiling.
            </p>
        </div>
        <div class="bg-white/10 rounded-xl p-4 border border-white/10 text-center min-w-[200px] shrink-0">
            <span class="text-[11px] font-semibold text-slate-300 uppercase tracking-wider">Final Leader Commission</span>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1 font-mono tracking-tight">
                ₹{{ number_format($model->final_commission, 2) }}
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">MIN(main, side) Upward</span>
        </div>
    </div>

    <!-- Summary Statistics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card 
            title="Total Model Sales" 
            :value="'₹' . number_format($model->total_sales, 2)" 
            change="Across all {{ $model->number_of_levels }} levels" 
            trend="up" 
            icon="models" 
            badge="Audited" 
        />
        <x-stat-card 
            title="Total Potential Commission" 
            :value="'₹' . number_format($model->total_potential_commission, 2)" 
            :change="'At ' . number_format($model->commission_rate, 2) . '% rate'" 
            trend="neutral" 
            icon="tiers" 
            badge="Calculated" 
        />
        <x-stat-card 
            title="Final Leader Commission" 
            :value="'₹' . number_format($model->final_commission, 2)" 
            change="Governed by weakest link" 
            trend="up" 
            icon="bottleneck" 
            badge="Final" 
        />
        <x-stat-card 
            title="Weakest Salesperson Sales" 
            :value="'₹' . number_format($model->weakest_sales, 2)" 
            :change="'Person: ' . $model->weakest_person" 
            trend="down" 
            icon="teams" 
            badge="Bottleneck" 
        />
    </div>

    <!-- Visual Model Hierarchy & Graphical Diagram -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" id="hierarchy-card-container">
        <!-- Header & Action Controls -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span>Hierarchy Diagram & Commission Flow</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase tracking-wider">
                            Interactive Graphic
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Visual branch diagram illustrating pairwise MIN comparisons and upward bottleneck propagation.
                    </p>
                </div>
                  <div class="flex flex-wrap items-center gap-2">
                <!-- Export Tree as PNG Image Button -->
                <button 
                    type="button" 
                    id="btn-export-image" 
                    onclick="exportHierarchyImage()" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-xs transition-all"
                    title="Export the interactive tree diagram as an image file (PNG)"
                >
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download Tree (PNG)</span>
                </button>
            </div>
        </div>

        <!-- INTERACTIVE SVG TREE VIEW ONLY -->
        <div id="view-tree" class="overflow-hidden bg-white">
            @if(isset($treeData))
            <div id="wl-tree-wrapper" style="width:100%;overflow:auto;background:#fff;">
                <!-- Legend & Commission Summary -->
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;padding:16px 24px;border-bottom:1px solid #f1f5f9;font-family:'Inter',sans-serif;font-size:12px;color:#475569;background:#f8fafc;">
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:20px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:16px;border-radius:4px;background:#fff0ee;border:2px solid #c0705a;"></div>
                            <span style="font-weight:600;color:#7c2d12;">Leader / Internal Node</span>
                            <span style="color:#64748b;">(Earns Override Commission)</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:16px;border-radius:4px;background:#edfaf4;border:2px solid #56a97a;"></div>
                            <span style="font-weight:600;color:#14532d;">Salesperson / Leaf Node</span>
                            <span style="color:#64748b;">(Personal Sales & Direct Commission)</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:22px;height:16px;border-radius:4px;background:#fff1f2;border:2px dashed #e11d48;"></div>
                            <span style="font-weight:700;color:#e11d48;">★ Weakest Link Bottleneck</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="padding:2px 7px;border-radius:4px;background:#ffffff;border:1px solid #cbd5e1;font-size:11px;font-weight:700;color:#334155;">5% • ₹50</div>
                            <span style="color:#64748b;">Rate applied & Branch Commission</span>
                        </div>
                    </div>
                    <div style="font-weight:600;color:#0f172a;display:flex;align-items:center;gap:6px;">
                        <span>Top Leader Payout:</span>
                        <span style="color:#059669;font-size:14px;font-weight:800;font-family:monospace;">₹{{ number_format($model->final_commission, 2) }}</span>
                    </div>
                </div>

                <div id="wl-tree-canvas" style="padding:28px 24px 36px 24px;">
                    <svg id="wl-tree-svg" style="display:block;overflow:visible;" xmlns="http://www.w3.org/2000/svg"></svg>
                </div>
            </div>

            <script>
            (function() {
                var treeData = @json($treeData);
                var levels = treeData.levels || [];
                var rate = treeData.commission_rate || 5;
                var weakest = treeData.weakest_person || '';
                var topLeader = treeData.top_leader || 'A';

                var nodes = {}; // keyed by name
                var edges = []; // {from, to, pct, commAmt, branch}

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

                // Root leader (e.g. A)
                var rootNode = getOrCreate(topLeader);
                rootNode.is_leaf = false;
                rootNode.commission = parseFloat(treeData.final_commission) || 0;
                rootNode.is_weakest = (topLeader === weakest);

                var currentLeader = topLeader;
                levels.forEach(function(lvl, idx) {
                    var sideName = lvl.side_person;
                    var mainName = lvl.main_person;
                    var sideSales = parseFloat(lvl.side_sales) || 0;
                    var sideComm = parseFloat(lvl.side_commission) || 0;
                    var mainSales = parseFloat(lvl.main_sales) || 0;
                    var mainComm = parseFloat(lvl.main_commission) || 0;
                    var isLast = (idx === levels.length - 1);

                    // Side salesperson (leaf)
                    var sideNode = getOrCreate(sideName);
                    sideNode.is_leaf = true;
                    sideNode.sales = sideSales;
                    sideNode.commission = sideComm;
                    sideNode.parent = currentLeader;
                    sideNode.is_weakest = (sideName === weakest);

                    // Main salesperson / child node
                    var mainNode = getOrCreate(mainName);
                    mainNode.parent = currentLeader;
                    mainNode.is_weakest = (mainName === weakest);

                    if (isLast) {
                        // Very bottom node is a leaf with direct sales
                        mainNode.is_leaf = true;
                        mainNode.sales = mainSales;
                        mainNode.commission = mainComm;
                    } else {
                        // Internal leader of the next level
                        var nextLvl = levels[idx + 1];
                        mainNode.is_leaf = false;
                        mainNode.sales = mainSales;
                        mainNode.commission = parseFloat(nextLvl.leader_commission) || mainComm;
                    }

                    // Comparison info for hover tooltips
                    mainNode.main_commission = mainComm;
                    mainNode.side_commission = sideComm;
                    mainNode.selected_commission = parseFloat(lvl.selected_commission) || 0;
                    mainNode.leader_commission = parseFloat(lvl.leader_commission) || 0;

                    var leaderNode = nodes[currentLeader];
                    leaderNode.is_leaf = false;
                    leaderNode.children.push(sideName);
                    leaderNode.children.push(mainName);

                    var lvlRate = lvl.commission_rate !== undefined && lvl.commission_rate !== null ? parseFloat(lvl.commission_rate) : rate;

                    // Edge from leader to side person: rate applied and side commission amount
                    edges.push({
                        from: currentLeader,
                        to: sideName,
                        pct: lvlRate,
                        commAmt: sideComm,
                        branch: 'side'
                    });

                    // Edge from leader to main person: rate applied and main branch commission amount
                    edges.push({
                        from: currentLeader,
                        to: mainName,
                        pct: lvlRate,
                        commAmt: mainComm,
                        branch: 'main'
                    });

                    currentLeader = mainName;
                });

                // Layout configuration
                var NW = 148;
                var NH = 74;
                var HGAP = 40;
                var VGAP = 100;

                var xCounter = [0];

                function computeX(name, visited) {
                    if (!visited) visited = {};
                    if (visited[name]) return;
                    visited[name] = true;
                    var n = nodes[name];
                    if (!n) return;
                    var children = n.children;
                    if (!children || children.length === 0) {
                        n._x = xCounter[0];
                        xCounter[0] += NW + HGAP;
                    } else {
                        children.forEach(function(c) { computeX(c, visited); });
                        var first = nodes[children[0]];
                        var last = nodes[children[children.length-1]];
                        n._x = (first._x + last._x) / 2;
                    }
                }

                function assignDepths(name, depth, visited) {
                    if (!visited) visited = {};
                    if (visited[name]) return;
                    visited[name] = true;
                    var n = nodes[name];
                    if (!n) return;
                    n._depth = depth;
                    (n.children || []).forEach(function(c) { assignDepths(c, depth + 1, visited); });
                }

                assignDepths(topLeader, 0, {});
                computeX(topLeader, {});

                var allNodes = Object.values(nodes);
                var minX = Math.min.apply(null, allNodes.map(function(n){ return n._x || 0; }));
                var maxX = Math.max.apply(null, allNodes.map(function(n){ return (n._x || 0) + NW; }));
                var maxDepth = Math.max.apply(null, allNodes.map(function(n){ return n._depth || 0; }));

                var PAD = 40;
                var svgW = (maxX - minX) + PAD * 2;
                var svgH = (maxDepth + 1) * (NH + VGAP) + PAD * 2 - VGAP + 30;

                allNodes.forEach(function(n) { n._x = (n._x - minX) + PAD; });

                var svg = document.getElementById('wl-tree-svg');
                svg.setAttribute('width', svgW);
                svg.setAttribute('height', svgH);
                svg.setAttribute('viewBox', '0 0 ' + svgW + ' ' + svgH);

                function fmt(v) {
                    var n = parseFloat(v) || 0;
                    return '\u20B9' + n.toLocaleString('en-IN', {minimumFractionDigits: 0, maximumFractionDigits: 2});
                }

                function fmtRate(r) {
                    var n = parseFloat(r) || 0;
                    return (n % 1 === 0 ? n.toFixed(0) : n.toFixed(1)) + '%';
                }

                // 1. Draw Edges
                edges.forEach(function(edge) {
                    var pNode = nodes[edge.from];
                    var cNode = nodes[edge.to];
                    if (!pNode || !cNode) return;

                    var x1 = pNode._x + NW/2;
                    var y1 = pNode._depth * (NH + VGAP) + PAD + NH;
                    var x2 = cNode._x + NW/2;
                    var y2 = cNode._depth * (NH + VGAP) + PAD;

                    var line = document.createElementNS('http://www.w3.org/2000/svg','line');
                    line.setAttribute('x1', x1);
                    line.setAttribute('y1', y1);
                    line.setAttribute('x2', x2);
                    line.setAttribute('y2', y2);
                    line.setAttribute('stroke', '#94a3b8');
                    line.setAttribute('stroke-width', '1.6');
                    svg.appendChild(line);

                    // Midpoint for percentage and commission amount pill
                    var mx = (x1 + x2) / 2;
                    var my = (y1 + y2) / 2;
                    var dx = x2 - x1;
                    var dy = y2 - y1;
                    var len = Math.sqrt(dx*dx + dy*dy) || 1;
                    var ox = -dy/len * 20;
                    var oy = dx/len * 20;

                    var pillG = document.createElementNS('http://www.w3.org/2000/svg','g');
                    pillG.setAttribute('transform', 'translate(' + (mx + ox) + ',' + (my + oy) + ')');

                    var pillW = 72;
                    var pillH = 34;

                    var pillRect = document.createElementNS('http://www.w3.org/2000/svg','rect');
                    pillRect.setAttribute('x', -pillW/2);
                    pillRect.setAttribute('y', -pillH/2);
                    pillRect.setAttribute('width', pillW);
                    pillRect.setAttribute('height', pillH);
                    pillRect.setAttribute('rx', 6);
                    pillRect.setAttribute('ry', 6);
                    pillRect.setAttribute('fill', '#ffffff');
                    pillRect.setAttribute('stroke', '#cbd5e1');
                    pillRect.setAttribute('stroke-width', '1.2');
                    pillG.appendChild(pillRect);

                    // Rate label (e.g. 5%)
                    var txtRate = document.createElementNS('http://www.w3.org/2000/svg','text');
                    txtRate.setAttribute('x', 0);
                    txtRate.setAttribute('y', -3);
                    txtRate.setAttribute('text-anchor', 'middle');
                    txtRate.setAttribute('font-family', 'Inter,sans-serif');
                    txtRate.setAttribute('font-size', '10.5');
                    txtRate.setAttribute('font-weight', '700');
                    txtRate.setAttribute('fill', '#475569');
                    txtRate.textContent = fmtRate(edge.pct);
                    pillG.appendChild(txtRate);

                    // Commission amount (e.g. ₹50)
                    var txtComm = document.createElementNS('http://www.w3.org/2000/svg','text');
                    txtComm.setAttribute('x', 0);
                    txtComm.setAttribute('y', 11);
                    txtComm.setAttribute('text-anchor', 'middle');
                    txtComm.setAttribute('font-family', 'Inter,sans-serif');
                    txtComm.setAttribute('font-size', '10');
                    txtComm.setAttribute('font-weight', '800');
                    txtComm.setAttribute('fill', edge.branch === 'side' ? '#059669' : '#b45309');
                    txtComm.textContent = fmt(edge.commAmt);
                    pillG.appendChild(txtComm);

                    // Tooltip for edge
                    var edgeTitle = document.createElementNS('http://www.w3.org/2000/svg','title');
                    edgeTitle.textContent = (edge.branch === 'side' ? 'Side Salesperson Branch' : 'Main Downline Branch') + 
                        '\nRate: ' + fmtRate(edge.pct) + 
                        '\nBranch Commission: ' + fmt(edge.commAmt);
                    pillG.appendChild(edgeTitle);

                    svg.appendChild(pillG);
                });

                // 2. Draw Nodes
                allNodes.forEach(function(node) {
                    var nx = node._x;
                    var ny = node._depth * (NH + VGAP) + PAD;
                    var isLeaf = node.is_leaf;
                    var isWeakest = node.is_weakest;

                    var fillColor = isWeakest ? '#fff1f2' : (isLeaf ? '#edfaf4' : '#fff0ee');
                    var strokeColor = isWeakest ? '#e11d48' : (isLeaf ? '#56a97a' : '#c0705a');
                    var strokeWidth = isWeakest ? 2.5 : 1.8;

                    var g = document.createElementNS('http://www.w3.org/2000/svg','g');

                    var rect = document.createElementNS('http://www.w3.org/2000/svg','rect');
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
                    g.appendChild(rect);

                    // Name line
                    var displayName = (node.name === topLeader) ? ('Leader ' + node.name) : node.name;
                    var nameText = document.createElementNS('http://www.w3.org/2000/svg','text');
                    nameText.setAttribute('x', nx + NW/2);
                    nameText.setAttribute('text-anchor', 'middle');
                    nameText.setAttribute('font-family', 'Inter,sans-serif');
                    nameText.setAttribute('font-weight', '800');
                    nameText.setAttribute('fill', isLeaf ? '#14532d' : '#7c2d12');

                    if (isLeaf) {
                        nameText.setAttribute('y', ny + (isWeakest ? 18 : 22));
                        nameText.setAttribute('font-size', '13.5');
                        nameText.textContent = displayName;
                        g.appendChild(nameText);

                        // Personal sales line
                        var saleText = document.createElementNS('http://www.w3.org/2000/svg','text');
                        saleText.setAttribute('x', nx + NW/2);
                        saleText.setAttribute('y', ny + (isWeakest ? 33 : 38));
                        saleText.setAttribute('text-anchor', 'middle');
                        saleText.setAttribute('font-family', 'Inter,sans-serif');
                        saleText.setAttribute('font-size', '10.5');
                        saleText.setAttribute('font-weight', '500');
                        saleText.setAttribute('fill', '#475569');
                        saleText.textContent = 'Sale: ' + fmt(node.sales);
                        g.appendChild(saleText);

                        // Commission earned line
                        var commText = document.createElementNS('http://www.w3.org/2000/svg','text');
                        commText.setAttribute('x', nx + NW/2);
                        commText.setAttribute('y', ny + (isWeakest ? 47 : 56));
                        commText.setAttribute('text-anchor', 'middle');
                        commText.setAttribute('font-family', 'Inter,sans-serif');
                        commText.setAttribute('font-size', '11.5');
                        commText.setAttribute('font-weight', '800');
                        commText.setAttribute('fill', '#059669');
                        commText.textContent = 'Comm: ' + fmt(node.commission);
                        g.appendChild(commText);
                    } else {
                        // Internal leader node
                        nameText.setAttribute('y', ny + (isWeakest ? 24 : 30));
                        nameText.setAttribute('font-size', '14');
                        nameText.textContent = displayName;
                        g.appendChild(nameText);

                        // Commission earned line
                        var commText = document.createElementNS('http://www.w3.org/2000/svg','text');
                        commText.setAttribute('x', nx + NW/2);
                        commText.setAttribute('y', ny + (isWeakest ? 43 : 52));
                        commText.setAttribute('text-anchor', 'middle');
                        commText.setAttribute('font-family', 'Inter,sans-serif');
                        commText.setAttribute('font-size', '12');
                        commText.setAttribute('font-weight', '800');
                        commText.setAttribute('fill', '#9a3412');
                        commText.textContent = 'Earns: ' + fmt(node.commission);
                        g.appendChild(commText);
                    }

                    // Weakest link badge at bottom
                    if (isWeakest) {
                        var badge = document.createElementNS('http://www.w3.org/2000/svg','text');
                        badge.setAttribute('x', nx + NW/2);
                        badge.setAttribute('y', ny + NH - 6);
                        badge.setAttribute('text-anchor', 'middle');
                        badge.setAttribute('font-family', 'Inter,sans-serif');
                        badge.setAttribute('font-size', '8.5');
                        badge.setAttribute('font-weight', '800');
                        badge.setAttribute('fill', '#e11d48');
                        badge.textContent = '★ WEAKEST LINK';
                        g.appendChild(badge);
                    }

                    // Detailed hover tooltip
                    var title = document.createElementNS('http://www.w3.org/2000/svg','title');
                    var tipLines = [displayName];
                    if (isLeaf && node.sales > 0) tipLines.push('Personal Sales: ' + fmt(node.sales));
                    tipLines.push('Commission Received: ' + fmt(node.commission));
                    if (isWeakest) tipLines.push('★ WEAKEST LINK (Determines upstream bottleneck payout)');
                    if (!isLeaf && node.selected_commission !== undefined) {
                        tipLines.push('Pairwise Decision: MIN(Main ' + fmt(node.main_commission) + ', Side ' + fmt(node.side_commission) + ') = ' + fmt(node.selected_commission));
                    }
                    title.textContent = tipLines.join('\n');
                    g.appendChild(title);

                    svg.appendChild(g);
                });

                var wrapper = document.getElementById('wl-tree-canvas');
                if (svgW < wrapper.clientWidth) {
                    svg.style.marginLeft = ((wrapper.clientWidth - svgW) / 2) + 'px';
                }
            })();

            // Export tree as PNG image
            function exportHierarchyImage() {
                var svg = document.getElementById('wl-tree-svg');
                if (!svg) return;

                var btn = document.getElementById('btn-export-image');
                var originalText = btn.innerHTML;
                btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Generating PNG...</span>`;

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
                        downloadLink.download = 'commission_model_{{ $model->id }}_tree.png';
                        downloadLink.href = pngUrl;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    } catch (e) {
                        var downloadLink = document.createElement('a');
                        downloadLink.download = 'commission_model_{{ $model->id }}_tree.svg';
                        downloadLink.href = url;
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }
                    btn.innerHTML = originalText;
                };

                img.onerror = function() {
                    var downloadLink = document.createElement('a');
                    downloadLink.download = 'commission_model_{{ $model->id }}_tree.svg';
                    downloadLink.href = url;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);
                    btn.innerHTML = originalText;
                };

                img.src = url;
            }
            </script>
            @endif
        </div>
    </div>

    <!-- Level-by-Level Calculation Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-900">Level-by-Level Calculation Breakdown</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Evaluated from bottom Level {{ $model->number_of_levels }} upward to Level 1 using <code>MIN(main child, side salesperson)</code>.
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 font-mono">
                {{ $model->levels->count() }} Levels Computed
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4 text-center w-16">Level</th>
                        <th class="py-3 px-3 text-center w-20">Rate %</th>
                        <th class="py-3 px-5">Main Child</th>
                        <th class="py-3 px-4">Main Sales</th>
                        <th class="py-3 px-4">Main Branch Comm.</th>
                        <th class="py-3 px-5">Side Person</th>
                        <th class="py-3 px-4">Side Sales</th>
                        <th class="py-3 px-4">Side Comm.</th>
                        <th class="py-3 px-5 bg-indigo-50/40 text-indigo-900 font-bold">Selected MIN</th>
                        <th class="py-3 px-5 text-right font-bold">Leader Comm.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-mono">
                    @foreach ($model->levels as $lvl)
                        @php
                            $isWeakestRow = ($lvl->main_person === $model->weakest_person || $lvl->side_person === $model->weakest_person);
                            $isBottomLevel = ($lvl->level === $model->number_of_levels);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors {{ $isWeakestRow ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md {{ $isBottomLevel ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} text-xs">
                                    {{ $lvl->level }}
                                </span>
                                @if ($isBottomLevel)
                                    <span class="block text-[9px] text-amber-600 font-sans font-medium">Bottom</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3 text-center font-bold text-indigo-700 bg-indigo-50/20">
                                {{ number_format($lvl->commission_rate ?? $model->commission_rate, 2) }}%
                            </td>
                            <td class="py-3.5 px-5 font-sans font-semibold text-slate-900">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $lvl->main_person }}</span>
                                    @if ($lvl->main_person === $model->weakest_person)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 border border-rose-200 font-semibold font-sans">Weakest</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                ₹{{ number_format($lvl->main_sales, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold {{ $lvl->main_commission <= $lvl->side_commission ? 'text-indigo-600 font-bold' : 'text-slate-500' }}">
                                ₹{{ number_format($lvl->main_commission, 2) }}
                            </td>
                            <td class="py-3.5 px-5 font-sans font-semibold text-slate-900">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $lvl->side_person }}</span>
                                    @if ($lvl->side_person === $model->weakest_person)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 border border-rose-200 font-semibold font-sans">Weakest</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                ₹{{ number_format($lvl->side_sales, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold {{ $lvl->side_commission <= $lvl->main_commission ? 'text-indigo-600 font-bold' : 'text-slate-500' }}">
                                ₹{{ number_format($lvl->side_commission, 2) }}
                            </td>
                            <td class="py-3.5 px-5 bg-indigo-50/40 text-indigo-900 font-bold">
                                ₹{{ number_format($lvl->selected_commission, 2) }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-extrabold text-emerald-600">
                                ₹{{ number_format($lvl->leader_commission, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <div>
                Rule: <code>Leader Commission = MIN(Main Branch Comm, Side Comm)</code>
            </div>
            <div class="font-medium text-slate-700">
                Top Leader Final Payout: <span class="text-emerald-600 font-bold font-mono text-sm">₹{{ number_format($model->final_commission, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
