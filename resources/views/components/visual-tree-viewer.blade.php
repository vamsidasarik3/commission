@php
    /**
     * Visual Tree Viewer Component
     *
     * Props:
     * - $treeData: Array containing:
     *     - 'model_type': 'generation_override' or 'weakest_link'
     *     - 'max_generations': int
     *     - 'nodes': list of node objects { name, personal_sales, total_commission, is_leaf, depth, parent }
     *     - 'edges': list of edge objects { parent, child, rate }
     *     - 'sales': list of sales with upline distributions
     *     - 'tree_hierarchy': nested tree representation
     */
    $viewerId = 'tree-viewer-' . uniqid();
    $maxGen = $treeData['max_generations'] ?? 5;
    $modelType = $treeData['model_type'] ?? 'generation_override';
@endphp

<div id="{{ $viewerId }}" class="visual-tree-viewer-root bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" data-tree-data='@json($treeData)'>
    <!-- Component Header & Interactive Toolbar -->
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">
                    Interactive Network Hierarchy Tree
                </h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Dynamic SVG
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Organizational hierarchy diagram with interactive node inspection, parent override rates, and live commission flow.
            </p>
        </div>

        <!-- Controls Toolbar -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <!-- Search Person Input -->
            <div class="relative">
                <input 
                    type="text" 
                    id="{{ $viewerId }}-search-input" 
                    placeholder="Search person (e.g. E1, F, A)..." 
                    class="w-48 sm:w-56 pl-8 pr-7 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white placeholder-slate-400 font-mono shadow-2xs transition-all"
                >
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <button 
                    type="button" 
                    id="{{ $viewerId }}-clear-search" 
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 hidden"
                    title="Clear search"
                >
                    &times;
                </button>
            </div>

            <!-- Zoom & Viewport Buttons -->
            <div class="inline-flex rounded-xl border border-slate-200 bg-white p-0.5 shadow-2xs">
                <button type="button" id="{{ $viewerId }}-btn-zoom-in" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors" title="Zoom In (+)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
                <button type="button" id="{{ $viewerId }}-btn-zoom-out" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors" title="Zoom Out (-)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <button type="button" id="{{ $viewerId }}-btn-zoom-reset" class="px-2 py-1.5 hover:bg-slate-100 rounded-lg text-[11px] font-mono font-bold text-slate-700 transition-colors" title="Reset Zoom (1:1)">
                    1:1
                </button>
                <button type="button" id="{{ $viewerId }}-btn-fit" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-700 transition-colors" title="Fit Tree to Screen">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                </button>
            </div>

            <!-- Max Generations Badge -->
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>Max Override Generations: <strong class="font-mono text-xs">{{ $maxGen }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Tree Viewer Main Content Area (SVG Canvas + Side Information Panel) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-0 relative min-h-[580px]">
        <!-- 1. SVG Tree Canvas Container (Cols 8 or 9) -->
        <div class="lg:col-span-8 xl:col-span-9 relative bg-slate-950 overflow-hidden select-none" id="{{ $viewerId }}-canvas-container">
            <!-- Canvas Watermark / Navigation Instructions -->
            <div class="absolute bottom-3 left-3 z-10 flex items-center gap-3 text-[11px] text-slate-400 font-mono bg-slate-900/90 backdrop-blur-xs px-3 py-1.5 rounded-lg border border-slate-800 shadow-sm pointer-events-none">
                <span>🖱️ Click & Drag to Pan</span>
                <span>•</span>
                <span>🔍 Scroll to Zoom</span>
                <span>•</span>
                <span>👆 Click Node for Flow</span>
            </div>

            <!-- Legend Badge Overlay -->
            <div class="absolute top-3 left-3 z-10 flex flex-wrap items-center gap-2 text-[10px] font-mono pointer-events-none">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900/95 text-slate-200 border border-slate-700 shadow-xs">
                    <span class="w-2.5 h-2.5 rounded-sm bg-indigo-500 border border-indigo-300"></span>
                    <span>Internal / Upline Leader</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900/95 text-slate-200 border border-emerald-700/80 shadow-xs">
                    <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500 border border-emerald-300"></span>
                    <span>Personal Sale / Leaf Node</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900/95 text-amber-300 border border-amber-500/50 shadow-xs">
                    <span class="w-2 h-0.5 bg-amber-400"></span>
                    <span>Override %</span>
                </span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-slate-900/95 text-slate-400 border border-slate-800 shadow-xs">
                    <span class="w-2.5 h-2.5 rounded-sm bg-slate-700 border border-dashed border-slate-600"></span>
                    <span>Outside Limit (>{{ $maxGen }})</span>
                </span>
            </div>

            <!-- Main SVG Canvas -->
            <svg id="{{ $viewerId }}-svg" class="w-full h-full min-h-[580px] cursor-grab active:cursor-grabbing block">
                <defs>
                    <!-- Flow Arrow Marker -->
                    <marker id="{{ $viewerId }}-arrow" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                        <path d="M 0 1 L 10 5 L 0 9 z" fill="#6366f1" />
                    </marker>
                    <!-- Highlighted Flow Arrow Marker -->
                    <marker id="{{ $viewerId }}-arrow-highlight" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                        <path d="M 0 1 L 10 5 L 0 9 z" fill="#10b981" />
                    </marker>
                    <!-- Drop Shadow Filter for Nodes -->
                    <filter id="{{ $viewerId }}-shadow" x="-10%" y="-10%" width="125%" height="125%">
                        <feDropShadow dx="0" dy="4" stdDeviation="5" flood-color="#000000" flood-opacity="0.5"/>
                    </filter>
                    <!-- Active Glow Filter -->
                    <filter id="{{ $viewerId }}-glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="0" stdDeviation="6" flood-color="#10b981" flood-opacity="0.8"/>
                    </filter>
                </defs>

                <!-- Viewport Group for Transformations (Pan & Zoom) -->
                <g id="{{ $viewerId }}-viewport" transform="translate(0, 0) scale(1)">
                    <!-- Layer 1: Edges Background -->
                    <g id="{{ $viewerId }}-edges-layer"></g>
                    <!-- Layer 2: Edge Rate Labels -->
                    <g id="{{ $viewerId }}-edge-labels-layer"></g>
                    <!-- Layer 3: Nodes Layer -->
                    <g id="{{ $viewerId }}-nodes-layer"></g>
                </g>
            </svg>

            <!-- Floating Hover Tooltip -->
            <div id="{{ $viewerId }}-tooltip" class="absolute z-30 hidden pointer-events-none transition-opacity duration-150 p-3 rounded-xl bg-slate-900/95 text-white border border-slate-700 shadow-2xl backdrop-blur-md max-w-xs text-xs font-mono">
                <!-- Tooltip dynamically injected -->
            </div>
        </div>

        <!-- 2. Selected Node & Sale Flow Side Panel (Cols 4 or 3) -->
        <div class="lg:col-span-4 xl:col-span-3 border-t lg:border-t-0 lg:border-l border-slate-200 bg-white flex flex-col justify-between" id="{{ $viewerId }}-side-panel">
            <div class="p-5 overflow-y-auto max-h-[580px] space-y-4">
                <!-- Side Panel Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                        Node Details & Sale Flow
                    </span>
                    <span id="{{ $viewerId }}-selected-role-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 font-mono">
                        Select Node
                    </span>
                </div>

                <!-- Empty State (No Node Selected) -->
                <div id="{{ $viewerId }}-empty-panel" class="py-12 text-center text-slate-400 space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                    </div>
                    <div class="text-xs font-semibold text-slate-700">Click any person or sale node</div>
                    <p class="text-[11px] text-slate-400 max-w-[220px] mx-auto leading-relaxed">
                        Select a salesperson to inspect how their personal sale distributes overrides upward to each upline generation.
                    </p>
                </div>

                <!-- Active Details Panel (Populated on Click) -->
                <div id="{{ $viewerId }}-active-panel" class="space-y-4 hidden">
                    <!-- Selected Person Header Card -->
                    <div class="p-4 rounded-xl bg-slate-900 text-white shadow-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">Person</span>
                            <span id="{{ $viewerId }}-panel-gen" class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-slate-800 text-slate-300 border border-slate-700">Gen 0</span>
                        </div>
                        <div id="{{ $viewerId }}-panel-name" class="text-2xl font-black font-mono text-white tracking-tight">
                            —
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800 text-xs font-mono">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Personal Sale</span>
                                <strong id="{{ $viewerId }}-panel-sale" class="text-emerald-400 font-bold text-sm">₹0</strong>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block">Total Earned</span>
                                <strong id="{{ $viewerId }}-panel-earned" class="text-indigo-300 font-bold text-sm">₹0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- SALE FLOW SECTION: Upward Distribution of Original Sale -->
                    <div id="{{ $viewerId }}-sale-flow-box" class="space-y-3 hidden">
                        <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                <span>Commission Flow</span>
                            </span>
                            <span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-semibold border border-emerald-200">
                                Original Sale Base
                            </span>
                        </div>

                        <!-- Highlight Rule Banner: Same Base Applied Everywhere -->
                        <div class="p-2.5 rounded-lg bg-emerald-50/90 border border-emerald-200 text-[11px] text-emerald-900 leading-relaxed font-sans">
                            <strong>Calculation Formula:</strong> Every upline override percentage is calculated against the 
                            <strong id="{{ $viewerId }}-flow-orig-sale" class="font-mono text-emerald-950 underline">₹0 ORIGINAL SALE</strong>:
                        </div>

                        <!-- Step-by-Step Flow List -->
                        <div id="{{ $viewerId }}-flow-steps" class="space-y-2 text-xs font-mono">
                            <!-- Populated dynamically via JS -->
                        </div>

                        <!-- Total Upline Commission Summary -->
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-between font-mono text-xs">
                            <span class="text-slate-600 font-bold">Total Overrides Paid:</span>
                            <strong id="{{ $viewerId }}-flow-total-commission" class="text-emerald-700 font-extrabold text-sm">₹0</strong>
                        </div>
                    </div>

                    <!-- OVERRIDES RECEIVED SECTION (When an Upline is Selected) -->
                    <div id="{{ $viewerId }}-earnings-box" class="space-y-3 hidden">
                        <div class="flex items-center justify-between pb-1 border-b border-slate-100">
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Overrides Earned From Downlines
                            </span>
                            <span id="{{ $viewerId }}-earnings-count" class="text-[10px] font-mono px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 font-semibold">
                                0 Overrides
                            </span>
                        </div>
                        <div id="{{ $viewerId }}-earnings-list" class="space-y-2 text-xs font-mono max-h-56 overflow-y-auto">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Footer Action -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-xs text-slate-500">
                <span id="{{ $viewerId }}-node-status-text">Click node to highlight path</span>
                <button type="button" id="{{ $viewerId }}-btn-center-selected" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 hidden font-mono">
                    Center on Node &rarr;
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const rootEl = document.getElementById('{{ $viewerId }}');
    if (!rootEl) return;

    const rawData = JSON.parse(rootEl.getAttribute('data-tree-data') || '{}');
    const viewerId = '{{ $viewerId }}';
    const maxGen = rawData.max_generations || 5;

    // Elements
    const svgEl = document.getElementById(`${viewerId}-svg`);
    const viewportEl = document.getElementById(`${viewerId}-viewport`);
    const edgesLayer = document.getElementById(`${viewerId}-edges-layer`);
    const edgeLabelsLayer = document.getElementById(`${viewerId}-edge-labels-layer`);
    const nodesLayer = document.getElementById(`${viewerId}-nodes-layer`);
    const tooltipEl = document.getElementById(`${viewerId}-tooltip`);
    const searchInput = document.getElementById(`${viewerId}-search-input`);
    const clearSearchBtn = document.getElementById(`${viewerId}-clear-search`);
    
    // Panel Elements
    const emptyPanel = document.getElementById(`${viewerId}-empty-panel`);
    const activePanel = document.getElementById(`${viewerId}-active-panel`);
    const panelName = document.getElementById(`${viewerId}-panel-name`);
    const panelGen = document.getElementById(`${viewerId}-panel-gen`);
    const panelSale = document.getElementById(`${viewerId}-panel-sale`);
    const panelEarned = document.getElementById(`${viewerId}-panel-earned`);
    const roleBadge = document.getElementById(`${viewerId}-selected-role-badge`);
    const saleFlowBox = document.getElementById(`${viewerId}-sale-flow-box`);
    const flowOrigSale = document.getElementById(`${viewerId}-flow-orig-sale`);
    const flowSteps = document.getElementById(`${viewerId}-flow-steps`);
    const flowTotalCommission = document.getElementById(`${viewerId}-flow-total-commission`);
    const earningsBox = document.getElementById(`${viewerId}-earnings-box`);
    const earningsCount = document.getElementById(`${viewerId}-earnings-count`);
    const earningsList = document.getElementById(`${viewerId}-earnings-list`);
    const btnCenterSelected = document.getElementById(`${viewerId}-btn-center-selected`);
    const nodeStatusText = document.getElementById(`${viewerId}-node-status-text`);

    // State Variables
    let transform = { x: 50, y: 50, scale: 0.95 };
    let isDragging = false;
    let startPoint = { x: 0, y: 0 };
    let selectedNodeName = null;
    let nodeMap = {};
    let edgeList = [];
    let salesMap = {};
    let earningsMap = {};
    let layoutTree = null;

    // 1. FORMAT UTILITIES
    function fmtRupee(val) {
        const num = parseFloat(val) || 0;
        return '₹' + (num % 1 === 0 ? num.toLocaleString('en-IN') : num.toFixed(2));
    }

    function fmtRate(val) {
        const num = parseFloat(val) || 0;
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(1)) + '%';
    }

    // 2. PARSE AND STRUCTURE INPUT DATA
    // 2. PARSE AND STRUCTURE INPUT DATA
    function parseData() {
        nodeMap = {};
        edgeList = [];
        salesMap = {};
        earningsMap = {};

        // A. SPECIAL HANDLER: MODEL 1 (WEAKEST LINK)
        if (rawData.model_type === 'weakest_link' && rawData.levels && rawData.levels.length > 0) {
            const levels = rawData.levels;
            const topLeader = rawData.top_leader || 'A';
            const rate = rawData.commission_rate || 5;
            const weakestPerson = rawData.weakest_person || '';

            // Top Root Leader (A)
            nodeMap[topLeader] = {
                name: topLeader,
                parent: null,
                children: [],
                personal_sales: 0,
                total_commission: parseFloat(rawData.final_commission) || 0,
                node_role: 'root',
                is_leaf: false,
                depth: 1,
                x: 0,
                y: 0,
                width: 180,
                height: 94
            };

            let currentLeader = topLeader;
            levels.forEach((lvl, idx) => {
                const sideName = lvl.side_person;
                const mainName = lvl.main_person;
                const sideSales = parseFloat(lvl.side_sales) || 0;
                const sideComm = parseFloat(lvl.side_commission) || 0;
                const mainSales = parseFloat(lvl.main_sales) || 0;
                const mainComm = parseFloat(lvl.main_commission) || 0;
                const selComm = parseFloat(lvl.selected_commission) || 0;
                const sideIsWeakest = (sideName === weakestPerson);
                const mainIsWeakest = (mainName === weakestPerson);

                // 1. Side Branch Child Node (e.g. S1, S2...)
                nodeMap[sideName] = {
                    name: sideName,
                    parent: currentLeader,
                    children: [],
                    personal_sales: sideSales,
                    total_commission: sideComm,
                    node_role: 'side',
                    is_leaf: true,
                    is_weakest: sideIsWeakest,
                    depth: idx + 2,
                    x: 0,
                    y: 0,
                    width: 180,
                    height: 94
                };

                // 2. Main Chain Child Node (e.g. B, C...)
                nodeMap[mainName] = {
                    name: mainName,
                    parent: currentLeader,
                    children: [],
                    personal_sales: mainSales,
                    total_commission: mainComm,
                    main_commission: mainComm,
                    side_commission: sideComm,
                    selected_commission: selComm,
                    node_role: 'main',
                    is_leaf: (idx === levels.length - 1),
                    is_weakest: mainIsWeakest,
                    depth: idx + 2,
                    x: 0,
                    y: 0,
                    width: 180,
                    height: 94
                };

                nodeMap[currentLeader].children.push(sideName);
                nodeMap[currentLeader].children.push(mainName);

                edgeList.push({
                    parent: currentLeader,
                    child: sideName,
                    rate: rate,
                    branch_type: 'side',
                    label: 'Side'
                });
                edgeList.push({
                    parent: currentLeader,
                    child: mainName,
                    rate: rate,
                    branch_type: 'main',
                    label: 'Main'
                });

                currentLeader = mainName;
            });

            layoutTree = nodeMap[topLeader];
            return;
        }

        // B. STANDARD HANDLER: MODEL 2 (LEVEL / GENERATION OVERRIDE)
        const edges = rawData.edges || [];
        const sales = rawData.sales || [];
        const rawPersonMap = rawData.commission_by_person || rawData.earnings_by_person || {};
        const rawSalesList = rawData.commission_by_sale || rawData.sales_breakdown || [];

        // Build salesMap (keyed by seller)
        rawSalesList.forEach(s => {
            const seller = s.seller || s.salesperson || '';
            if (seller) {
                salesMap[seller] = s;
            }
        });
        sales.forEach(s => {
            const seller = s.salesperson || s.seller || '';
            if (seller && !salesMap[seller]) {
                salesMap[seller] = {
                    seller: seller,
                    amount: parseFloat(s.amount) || 0,
                    original_sale_amount: parseFloat(s.amount) || 0,
                    commissions: []
                };
            }
        });

        // Build earningsMap (keyed by earner)
        Object.keys(rawPersonMap).forEach(p => {
            earningsMap[p] = rawPersonMap[p];
        });

        // Discover all people
        const allNames = new Set();
        edges.forEach(e => {
            allNames.add(e.parent);
            allNames.add(e.child);
        });
        sales.forEach(s => {
            allNames.add(s.salesperson || s.seller);
        });

        // Initialize node objects
        allNames.forEach(name => {
            const pData = earningsMap[name] || {};
            const sData = salesMap[name] || {};
            const pSale = pData.personal_sales || (sData.amount || sData.original_sale_amount || 0);
            const pEarned = pData.total_commission !== undefined ? pData.total_commission : (pData.override_commission || 0);

            nodeMap[name] = {
                name: name,
                parent: null,
                children: [],
                personal_sales: pSale,
                total_commission: pEarned,
                node_role: pSale > 0 ? 'salesperson' : 'upline',
                is_leaf: true,
                depth: 1,
                x: 0,
                y: 0,
                width: 180,
                height: 94
            };
        });

        // Bind parent-child relationships
        edges.forEach(e => {
            if (nodeMap[e.parent] && nodeMap[e.child]) {
                nodeMap[e.child].parent = e.parent;
                nodeMap[e.child].rate_from_parent = e.rate;
                nodeMap[e.parent].children.push(e.child);
                nodeMap[e.parent].is_leaf = false;
                edgeList.push({
                    parent: e.parent,
                    child: e.child,
                    rate: e.rate
                });
            }
        });

        // Find root nodes (no parent)
        const roots = [];
        Object.keys(nodeMap).forEach(name => {
            if (!nodeMap[name].parent) {
                roots.push(nodeMap[name]);
            }
        });

        layoutTree = roots.length > 0 ? roots[0] : null;

        // Calculate depths
        function assignDepths(node, currDepth, visited = new Set()) {
            if (visited.has(node.name)) return;
            visited.add(node.name);
            node.depth = currDepth;
            node.children.forEach(cName => {
                if (nodeMap[cName]) {
                    assignDepths(nodeMap[cName], currDepth + 1, visited);
                }
            });
        }
        roots.forEach(r => assignDepths(r, 1));
    }

    // 3. TREE LAYOUT ALGORITHM (Tidy hierarchical layout)
    function computeLayout() {
        if (!layoutTree) return;

        const NODE_WIDTH = 180;
        const NODE_HEIGHT = 94;
        const LEVEL_HEIGHT = 145;
        const SIBLING_GAP = 36;

        let nextX = 0;
        const visited = new Set();

        function layoutNode(node) {
            if (visited.has(node.name)) return;
            visited.add(node.name);

            const children = node.children.map(cName => nodeMap[cName]).filter(Boolean);

            if (children.length === 0) {
                node.x = nextX;
                nextX += NODE_WIDTH + SIBLING_GAP;
            } else {
                children.forEach(layoutNode);
                const firstChild = children[0];
                const lastChild = children[children.length - 1];
                node.x = (firstChild.x + lastChild.x) / 2;
            }

            node.y = (node.depth - 1) * LEVEL_HEIGHT;
        }

        // Layout all roots
        const roots = Object.values(nodeMap).filter(n => !n.parent);
        roots.forEach(r => {
            layoutNode(r);
            nextX += SIBLING_GAP;
        });
    // 4. SVG RENDERING (Edges, Nodes, Badges, Labels)
    function renderSvg() {
        edgesLayer.innerHTML = '';
        edgeLabelsLayer.innerHTML = '';
        nodesLayer.innerHTML = '';

        const NODE_WIDTH = 180;
        const NODE_HEIGHT = 94;
        const isModel1 = rawData.model_type === 'weakest_link';

        // Render Edges
        edgeList.forEach((edge) => {
            const pNode = nodeMap[edge.parent];
            const cNode = nodeMap[edge.child];
            if (!pNode || !cNode) return;

            const startX = pNode.x + NODE_WIDTH / 2;
            const startY = pNode.y + NODE_HEIGHT;
            const endX = cNode.x + NODE_WIDTH / 2;
            const endY = cNode.y;
            const midY = (startY + endY) / 2;

            // Smooth cubic bezier S-curve
            const d = `M ${startX} ${startY} C ${startX} ${midY}, ${endX} ${midY}, ${endX} ${endY}`;

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            path.setAttribute('class', 'tree-edge transition-all duration-300');
            path.setAttribute('stroke', edge.branch_type === 'side' ? '#38bdf8' : '#475569');
            path.setAttribute('stroke-width', '2.5');
            path.setAttribute('fill', 'none');
            path.setAttribute('marker-end', `url(#${viewerId}-arrow)`);
            path.setAttribute('id', `${viewerId}-edge-${edge.parent}-${edge.child}`);
            path.setAttribute('data-parent', edge.parent);
            path.setAttribute('data-child', edge.child);

            // Hover interactions on edge
            path.addEventListener('mouseenter', (ev) => showEdgeTooltip(ev, edge));
            path.addEventListener('mouseleave', hideTooltip);

            edgesLayer.appendChild(path);

            // Edge Label (Percentage badge pill)
            const labelX = (startX + endX) / 2;
            const labelY = midY;

            const labelGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            labelGroup.setAttribute('class', 'tree-edge-label select-none cursor-pointer');
            labelGroup.setAttribute('id', `${viewerId}-edgelabel-${edge.parent}-${edge.child}`);

            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', labelX - 22);
            rect.setAttribute('y', labelY - 11);
            rect.setAttribute('width', '44');
            rect.setAttribute('height', '22');
            rect.setAttribute('rx', '11');
            rect.setAttribute('fill', '#0f172a');
            rect.setAttribute('stroke', edge.branch_type === 'side' ? '#0ea5e9' : '#6366f1');
            rect.setAttribute('stroke-width', '1.5');
            rect.setAttribute('class', 'transition-all duration-200');

            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', labelX);
            text.setAttribute('y', labelY + 4);
            text.setAttribute('text-anchor', 'middle');
            text.setAttribute('fill', edge.branch_type === 'side' ? '#7dd3fc' : '#c7d2fe');
            text.setAttribute('font-family', 'ui-monospace, monospace');
            text.setAttribute('font-size', '11');
            text.setAttribute('font-weight', 'bold');
            text.textContent = fmtRate(edge.rate);

            labelGroup.appendChild(rect);
            labelGroup.appendChild(text);

            labelGroup.addEventListener('mouseenter', (ev) => showEdgeTooltip(ev, edge));
            labelGroup.addEventListener('mouseleave', hideTooltip);

            edgeLabelsLayer.appendChild(labelGroup);
        });

        // Render Nodes
        Object.keys(nodeMap).forEach(name => {
            const node = nodeMap[name];
            const hasSale = node.personal_sales > 0;
            const isOutsideLimit = !isModel1 && (node.depth > maxGen + 1);

            const g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
            g.setAttribute('class', `tree-node-group cursor-pointer select-none transition-transform duration-200 ${isOutsideLimit ? 'opacity-55' : ''}`);
            g.setAttribute('id', `${viewerId}-node-${node.name}`);
            g.setAttribute('transform', `translate(${node.x}, ${node.y})`);
            g.setAttribute('filter', `url(#${viewerId}-shadow)`);

            // ForeignObject allows rich HTML & Tailwind inside SVG
            const fo = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
            fo.setAttribute('width', NODE_WIDTH);
            fo.setAttribute('height', NODE_HEIGHT);
            fo.setAttribute('class', 'overflow-visible');

            // Card HTML Body
            const cardDiv = document.createElement('div');
            cardDiv.id = `${viewerId}-card-${node.name}`;

            if (isModel1) {
                // MODEL 1 (WEAKEST LINK)
                if (node.node_role === 'root') {
                    cardDiv.className = 'w-full h-full rounded-2xl border-2 border-indigo-500 bg-slate-900 p-3 shadow-lg flex flex-col justify-between';
                    cardDiv.innerHTML = `
                        <div class="flex items-center justify-between pb-1 border-b border-slate-800">
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center font-mono font-bold text-[10px] bg-indigo-600 text-white">
                                    ${node.name.substring(0, 2)}
                                </span>
                                <span class="font-bold text-sm text-white font-mono tracking-tight">${node.name}</span>
                            </div>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono bg-indigo-950 text-indigo-300 border border-indigo-800">
                                Top Leader
                            </span>
                        </div>
                        <div class="space-y-0.5 text-[11px] font-mono mt-1">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Final Earned:</span>
                                <strong class="text-emerald-400 font-extrabold">${fmtRupee(node.total_commission)}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Bottleneck:</span>
                                <span class="text-rose-400 font-bold">${rawData.weakest_person || 'Weakest Link'}</span>
                            </div>
                        </div>
                    `;
                } else if (node.node_role === 'main') {
                    cardDiv.className = `w-full h-full rounded-2xl border-2 ${node.is_weakest ? 'border-rose-500 shadow-rose-500/20' : 'border-indigo-400/80 shadow-slate-900/50'} bg-slate-900 p-3 transition-all flex flex-col justify-between`;
                    cardDiv.innerHTML = `
                        <div class="flex items-center justify-between pb-1 border-b border-slate-800">
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center font-mono font-bold text-[10px] bg-indigo-600 text-white">
                                    ${node.name.substring(0, 2)}
                                </span>
                                <span class="font-bold text-sm text-white font-mono tracking-tight">${node.name}</span>
                            </div>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono ${node.is_weakest ? 'bg-rose-950 text-rose-300 border border-rose-800' : 'bg-slate-800 text-slate-300 border border-slate-700'}">
                                ${node.is_weakest ? 'Weakest' : 'Main Chain'}
                            </span>
                        </div>
                        <div class="space-y-0.5 text-[10px] font-mono mt-0.5">
                            <div class="flex justify-between text-slate-400">
                                <span>Main: ${fmtRupee(node.main_commission)}</span>
                                <span>Side: ${fmtRupee(node.side_commission)}</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-800/80">
                                <span class="text-slate-300 font-bold">Selected:</span>
                                <strong class="${node.is_weakest ? 'text-rose-400' : 'text-emerald-400'} font-bold">${fmtRupee(node.selected_commission)}</strong>
                            </div>
                        </div>
                    `;
                } else {
                    // Side branch
                    cardDiv.className = `w-full h-full rounded-2xl border-2 ${node.is_weakest ? 'border-rose-500 shadow-rose-500/20' : 'border-sky-500/80 shadow-sky-500/10'} bg-slate-900 p-3 transition-all flex flex-col justify-between`;
                    cardDiv.innerHTML = `
                        <div class="flex items-center justify-between pb-1 border-b border-slate-800">
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-md flex items-center justify-center font-mono font-bold text-[10px] bg-sky-600 text-white">
                                    ${node.name.substring(0, 2)}
                                </span>
                                <span class="font-bold text-sm text-white font-mono tracking-tight">${node.name}</span>
                            </div>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono ${node.is_weakest ? 'bg-rose-950 text-rose-300 border border-rose-800' : 'bg-sky-950 text-sky-300 border border-sky-800'}">
                                ${node.is_weakest ? 'Weakest' : 'Side Branch'}
                            </span>
                        </div>
                        <div class="space-y-0.5 text-[11px] font-mono mt-1">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Sale:</span>
                                <strong class="text-sky-400 font-bold">${fmtRupee(node.personal_sales)}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Commission:</span>
                                <strong class="${node.is_weakest ? 'text-rose-400 font-bold' : 'text-slate-200'}">${fmtRupee(node.total_commission)}</strong>
                            </div>
                        </div>
                    `;
                }
            } else {
                // MODEL 2 (LEVEL / GENERATION OVERRIDE)
                // Exactly matching specification:
                // For a salesperson: Sale: ₹200, Commission: ₹0
                // For an upline: Personal Sale: ₹0, Earned: ₹27
                cardDiv.className = `w-full h-full rounded-2xl border-2 p-3 transition-all duration-200 flex flex-col justify-between ${
                    isOutsideLimit 
                        ? 'bg-slate-900 border-dashed border-slate-600 shadow-none'
                        : (hasSale 
                            ? 'bg-slate-900 border-emerald-500 shadow-emerald-500/20' 
                            : 'bg-slate-900 border-slate-700 hover:border-slate-500 shadow-slate-900/50')
                }`;

                cardDiv.innerHTML = `
                    <div class="flex items-center justify-between pb-1 border-b border-slate-800">
                        <div class="flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md flex items-center justify-center font-mono font-bold text-[10px] ${
                                hasSale ? 'bg-emerald-600 text-white' : 'bg-indigo-600 text-white'
                            }">
                                ${node.name.substring(0, 2)}
                            </span>
                            <span class="font-bold text-sm text-white font-mono tracking-tight">${node.name}</span>
                        </div>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold font-mono ${
                            isOutsideLimit 
                                ? 'bg-slate-800 text-slate-400 border border-dashed border-slate-600'
                                : (hasSale 
                                    ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' 
                                    : 'bg-slate-800 text-slate-300 border border-slate-700')
                        }">
                            ${isOutsideLimit ? 'Outside Limit' : (hasSale ? 'Salesperson' : 'Upline')}
                        </span>
                    </div>
                    <div class="space-y-0.5 text-[11px] font-mono mt-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">${hasSale ? 'Sale:' : 'Personal Sale:'}</span>
                            <strong class="${hasSale ? 'text-emerald-400 font-extrabold' : 'text-slate-400 font-medium'}">${fmtRupee(node.personal_sales)}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">${hasSale ? 'Commission:' : 'Earned:'}</span>
                            <strong class="${node.total_commission > 0 ? 'text-indigo-300 font-bold' : 'text-slate-400'}">${fmtRupee(node.total_commission)}</strong>
                        </div>
                    </div>
                `;
            }

            fo.appendChild(cardDiv);
            g.appendChild(fo);

            // Node Click Event
            g.addEventListener('click', (ev) => {
                ev.stopPropagation();
                selectNode(node.name);
            });

            // Node Hover Tooltip
            g.addEventListener('mouseenter', (ev) => showNodeTooltip(ev, node));
            g.addEventListener('mouseleave', hideTooltip);

            nodesLayer.appendChild(g);
        });

        applyViewportTransform();
    }

    // 5. TOOLTIPS
    function showNodeTooltip(ev, node) {
        tooltipEl.innerHTML = `
            <div class="pb-1.5 mb-1.5 border-b border-slate-800 flex items-center justify-between gap-3">
                <strong class="text-white text-sm font-mono">${node.name}</strong>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold ${node.personal_sales > 0 ? 'bg-emerald-900 text-emerald-300' : 'bg-indigo-900 text-indigo-300'}">
                    Generation ${node.depth - 1} from Root
                </span>
            </div>
            <div class="space-y-1 text-slate-300 text-[11px]">
                <div class="flex justify-between gap-4"><span class="text-slate-400">Personal Sales:</span> <strong class="text-white">${fmtRupee(node.personal_sales)}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-400">Commission Earned:</span> <strong class="text-emerald-400">${fmtRupee(node.total_commission)}</strong></div>
                <div class="flex justify-between gap-4"><span class="text-slate-400">Direct Parent:</span> <strong class="text-slate-200">${node.parent || 'None (Root)'}</strong></div>
            </div>
        `;
        positionTooltip(ev);
    }

    function showEdgeTooltip(ev, edge) {
        tooltipEl.innerHTML = `
            <div class="pb-1 mb-1 border-b border-slate-800 font-bold text-white text-xs">
                Relationship: ${edge.parent} → ${edge.child}
            </div>
            <div class="space-y-1 text-slate-300 text-[11px]">
                <div class="flex justify-between gap-3"><span class="text-slate-400">Override Percentage:</span> <strong class="text-emerald-400">${fmtRate(edge.rate)}</strong></div>
                <div class="flex justify-between gap-3"><span class="text-slate-400">Type:</span> <span>Direct Parent-Child Edge</span></div>
            </div>
        `;
        positionTooltip(ev);
    }

    function positionTooltip(ev) {
        tooltipEl.classList.remove('hidden');
        const rect = rootEl.getBoundingClientRect();
        const x = ev.clientX - rect.left + 15;
        const y = ev.clientY - rect.top + 15;
        tooltipEl.style.left = `${x}px`;
        tooltipEl.style.top = `${y}px`;
    }

    function hideTooltip() {
        tooltipEl.classList.add('hidden');
    }

    // 6. NODE SELECTION, UPLINE PATH HIGHLIGHTING & DETAILS PANEL
    function selectNode(name) {
        selectedNodeName = name;
        const node = nodeMap[name];
        if (!node) return;

        // Reset previous highlights
        document.querySelectorAll('.tree-node-group').forEach(el => el.classList.remove('node-selected'));
        document.querySelectorAll('.tree-edge').forEach(el => {
            el.setAttribute('stroke', '#475569');
            el.setAttribute('stroke-width', '2.5');
            el.setAttribute('stroke-dasharray', 'none');
            el.setAttribute('marker-end', `url(#${viewerId}-arrow)`);
        });

        // 1. Highlight clicked node card
        const nodeEl = document.getElementById(`${viewerId}-node-${name}`);
        const cardEl = document.getElementById(`${viewerId}-card-${name}`);
        if (nodeEl && cardEl) {
            nodeEl.classList.add('node-selected');
            cardEl.classList.add('ring-4', 'ring-emerald-400', 'border-emerald-300');
        }

        // 2. Trace and highlight Upline Path to Root
        const uplineChain = [];
        let curr = node;
        while (curr && curr.parent) {
            const pName = curr.parent;
            uplineChain.push({ parent: pName, child: curr.name });
            
            // Highlight Edge
            const edgePath = document.getElementById(`${viewerId}-edge-${pName}-${curr.name}`);
            if (edgePath) {
                edgePath.setAttribute('stroke', '#10b981');
                edgePath.setAttribute('stroke-width', '4');
                edgePath.setAttribute('stroke-dasharray', '6 3');
                edgePath.setAttribute('marker-end', `url(#${viewerId}-arrow-highlight)`);
            }

            // Highlight Parent Node
            const pCard = document.getElementById(`${viewerId}-card-${pName}`);
            if (pCard) {
                pCard.classList.add('ring-2', 'ring-indigo-400');
            }

            curr = nodeMap[pName];
        }

        // 3. Update Side Information Panel
        emptyPanel.classList.add('hidden');
        activePanel.classList.remove('hidden');
        btnCenterSelected.classList.remove('hidden');
        nodeStatusText.textContent = `Selected: ${name}`;

        panelName.textContent = node.name;
        panelGen.textContent = `Generation ${node.depth - 1} from Root`;
        panelSale.textContent = fmtRupee(node.personal_sales);
        panelEarned.textContent = fmtRupee(node.total_commission);
        roleBadge.textContent = node.personal_sales > 0 ? 'Salesperson' : 'Upline Leader';
        roleBadge.className = `px-2 py-0.5 rounded text-[10px] font-bold font-mono ${
            node.personal_sales > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200'
        }`;

        // 4. Render Sale Flow IF node has a personal sale
        const saleRecord = salesMap[name];
        if (saleRecord && saleRecord.commissions && saleRecord.commissions.length > 0) {
            saleFlowBox.classList.remove('hidden');
            const saleAmt = saleRecord.amount || (saleRecord.original_sale_amount || node.personal_sales);
            flowOrigSale.textContent = `${fmtRupee(saleAmt)} ORIGINAL SALE`;

            let stepsHtml = '';
            let totalDist = 0;

            saleRecord.commissions.forEach(c => {
                const earner = c.earner || c.recipient;
                const gen = c.generation;
                const rate = c.rate;
                const commVal = c.commission !== undefined ? c.commission : c.commission_amount;
                const isEligible = c.is_eligible !== false;
                if (isEligible) totalDist += commVal;

                stepsHtml += `
                    <div class="p-2.5 rounded-xl border transition-all ${isEligible ? 'bg-slate-50 border-slate-200' : 'bg-rose-50/50 border-dashed border-rose-200 opacity-60'}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-slate-800 flex items-center gap-1.5">
                                <span class="w-4 h-4 rounded-full bg-slate-200 text-slate-700 inline-flex items-center justify-center text-[9px] font-bold">${gen}</span>
                                <span>${earner}</span>
                            </span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold ${isEligible ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200'}">
                                ${isEligible ? 'paid' : 'NOT PAID'}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-600">
                            <span>↓ ${fmtRate(rate)} override on ${fmtRupee(saleAmt)}</span>
                            <strong class="${isEligible ? 'text-emerald-700 font-bold' : 'text-slate-400 line-through'}">${fmtRupee(commVal)}</strong>
                        </div>
                    </div>
                `;
            });

            flowSteps.innerHTML = stepsHtml;
            flowTotalCommission.textContent = fmtRupee(totalDist);
        } else {
            saleFlowBox.classList.add('hidden');
        }

        // 5. Render Overrides Earned IF node is an upline with commissions received
        const personRecord = earningsMap[name];
        if (personRecord && personRecord.breakdown && personRecord.breakdown.length > 0) {
            earningsBox.classList.remove('hidden');
            earningsCount.textContent = `${personRecord.breakdown.length} Overrides`;

            let earnHtml = '';
            personRecord.breakdown.forEach(item => {
                earnHtml += `
                    <div class="p-2 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between text-[11px]">
                        <div>
                            <span class="text-slate-500">From <strong>${item.seller}</strong> (Sale: ${fmtRupee(item.original_sale_amount)})</span>
                            <div class="text-[10px] text-slate-400">Generation ${item.generation} • Rate: ${fmtRate(item.rate)}</div>
                        </div>
                        <strong class="text-indigo-700 font-bold">${fmtRupee(item.commission)}</strong>
                    </div>
                `;
            });
            earningsList.innerHTML = earnHtml;
        } else {
            earningsBox.classList.add('hidden');
        }
    }

    // 7. PAN & ZOOM CONTROLS
    function applyViewportTransform() {
        viewportEl.setAttribute('transform', `translate(${transform.x}, ${transform.y}) scale(${transform.scale})`);
    }

    function zoom(delta, clientX, clientY) {
        const factor = delta > 0 ? 1.15 : 0.85;
        const newScale = Math.min(Math.max(transform.scale * factor, 0.2), 3.0);

        if (clientX !== undefined && clientY !== undefined) {
            const rect = svgEl.getBoundingClientRect();
            const mouseX = clientX - rect.left;
            const mouseY = clientY - rect.top;
            transform.x = mouseX - (mouseX - transform.x) * (newScale / transform.scale);
            transform.y = mouseY - (mouseY - transform.y) * (newScale / transform.scale);
        }

        transform.scale = newScale;
        applyViewportTransform();
    }

    function panToNode(name) {
        const node = nodeMap[name];
        if (!node) return;

        const rect = svgEl.getBoundingClientRect();
        const targetX = rect.width / 2 - (node.x + 85) * transform.scale;
        const targetY = rect.height / 3 - (node.y + 45) * transform.scale;

        transform.x = targetX;
        transform.y = targetY;
        applyViewportTransform();
    }

    function fitToScreen() {
        if (!layoutTree) return;
        const rect = svgEl.getBoundingClientRect();

        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        Object.values(nodeMap).forEach(n => {
            minX = Math.min(minX, n.x);
            maxX = Math.max(maxX, n.x + 175);
            minY = Math.min(minY, n.y);
            maxY = Math.max(maxY, n.y + 90);
        });

        const treeW = maxX - minX;
        const treeH = maxY - minY;
        const padding = 60;

        const scaleX = (rect.width - padding * 2) / treeW;
        const scaleY = (rect.height - padding * 2) / treeH;
        const fitScale = Math.min(Math.min(scaleX, scaleY), 1.2);

        transform.scale = Math.max(fitScale, 0.25);
        transform.x = (rect.width - treeW * transform.scale) / 2 - minX * transform.scale;
        transform.y = padding;

        applyViewportTransform();
    }

    // 8. EVENT LISTENERS: Mouse Dragging & Wheel Zooming
    svgEl.addEventListener('mousedown', (e) => {
        isDragging = true;
        startPoint = { x: e.clientX - transform.x, y: e.clientY - transform.y };
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        transform.x = e.clientX - startPoint.x;
        transform.y = e.clientY - startPoint.y;
        applyViewportTransform();
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
    });

    svgEl.addEventListener('wheel', (e) => {
        e.preventDefault();
        zoom(-e.deltaY, e.clientX, e.clientY);
    }, { passive: false });

    // Buttons
    document.getElementById(`${viewerId}-btn-zoom-in`).addEventListener('click', () => zoom(1));
    document.getElementById(`${viewerId}-btn-zoom-out`).addEventListener('click', () => zoom(-1));
    document.getElementById(`${viewerId}-btn-zoom-reset`).addEventListener('click', () => {
        transform.scale = 1.0;
        fitToScreen();
    });
    document.getElementById(`${viewerId}-btn-fit`).addEventListener('click', fitToScreen);
    btnCenterSelected.addEventListener('click', () => {
        if (selectedNodeName) panToNode(selectedNodeName);
    });

    // 9. SEARCH PERSON
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim().toUpperCase();
        clearSearchBtn.classList.toggle('hidden', query === '');

        if (!query) return;

        const matchedNode = Object.values(nodeMap).find(n => n.name.toUpperCase() === query || n.name.toUpperCase().startsWith(query));
        if (matchedNode) {
            selectNode(matchedNode.name);
            panToNode(matchedNode.name);
        }
    });

    clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        clearSearchBtn.classList.add('hidden');
    });

    // 10. INITIALIZATION
    parseData();
    computeLayout();
    renderSvg();

    // Auto fit on load
    setTimeout(() => {
        fitToScreen();
        // If an initial sale node exists (e.g. E1 or first salesperson), auto-select it for instant wow factor!
        const initialSalePerson = Object.keys(salesMap)[0] || Object.keys(nodeMap)[0];
        if (initialSalePerson) {
            selectNode(initialSalePerson);
        }
    }, 100);

    // Responsive resize handler
    window.addEventListener('resize', () => {
        fitToScreen();
    });

    // 11. EXTERNAL REACTIVE UPDATE HOOK (Live calculation / Model 2 builder)
    function updateData(newData) {
        if (!newData) return;
        Object.assign(rawData, newData);
        parseData();
        computeLayout();
        renderSvg();
        fitToScreen();
        if (selectedNodeName && nodeMap[selectedNodeName]) {
            selectNode(selectedNodeName);
        } else {
            const initialSalePerson = Object.keys(salesMap)[0] || Object.keys(nodeMap)[0];
            if (initialSalePerson) {
                selectNode(initialSalePerson);
            }
        }
    }

    window.updateVisualTreeViewer = updateData;
    rootEl.updateTreeData = updateData;
})();
</script>
