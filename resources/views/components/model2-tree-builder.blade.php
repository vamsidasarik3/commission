<div id="model2-tree-builder" class="space-y-6">
    <!-- Builder Controls & Presets Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-slate-900 text-white shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500 text-white uppercase tracking-wider">
                    Interactive Tree Builder
                </span>
                <span id="node-count-badge" class="text-xs text-slate-300 font-mono">6 Nodes Configured</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Define arbitrary persons, parents, node roles, override percentages, and personal sales volume.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button 
                type="button" 
                id="btn-tb-add-node"
                onclick="window.TreeBuilder.addNode()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Node
            </button>
            <button 
                type="button" 
                id="btn-tb-add-relationship"
                onclick="window.TreeBuilder.addNode()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Relationship
            </button>
            <button 
                type="button" 
                id="btn-tb-add-sale"
                onclick="window.TreeBuilder.addNode(null, null, 100)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-emerald-500/30 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Sale
            </button>
            <button 
                type="button" 
                id="btn-tb-load-spec"
                onclick="window.TreeBuilder.loadSpecificationPreset()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-700 hover:bg-emerald-600 text-white shadow-sm transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                Load Specification Example Tree
            </button>
            <button 
                type="button" 
                onclick="window.TreeBuilder.loadChainPreset()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition-colors"
            >
                Deep Chain (J = ₹1000)
            </button>
            <button 
                type="button" 
                onclick="window.TreeBuilder.clearAllNodes()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg text-rose-300 hover:bg-rose-950/40 border border-rose-900/40 transition-colors"
            >
                Clear
            </button>
        </div>
    </div>

    <!-- Tree Validation Feedback Banner (Hidden when tree is valid) -->
    <div id="tree-validation-alert" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 space-y-1.5 hidden">
        <div class="flex items-center gap-2 font-bold text-xs">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <span id="tree-validation-title">Invalid Tree Structure Detected:</span>
        </div>
        <ul id="tree-validation-list" class="list-disc list-inside text-xs space-y-1 text-rose-700 ml-4">
        </ul>
    </div>

    <!-- 1. Node Management Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/70 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Node Configuration Table</h4>
                <p class="text-[11px] text-slate-500">Each node specifies its person name, direct parent in the hierarchy, node role, personal sale, and relationship override %.</p>
            </div>
            <button 
                type="button" 
                onclick="window.TreeBuilder.addNode()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Node
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-12 text-center">#</th>
                        <th class="py-2.5 px-4 min-w-[150px]">Person Name <span class="text-rose-500">*</span></th>
                        <th class="py-2.5 px-4 min-w-[170px]">Parent (Upline)</th>
                        <th class="py-2.5 px-4 min-w-[140px]">Node Type</th>
                        <th class="py-2.5 px-4 min-w-[140px]">Override % (from Parent)</th>
                        <th class="py-2.5 px-4 min-w-[140px]">Personal Sale (₹)</th>
                        <th class="py-2.5 px-3 w-28 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="tb-nodes-tbody" class="divide-y divide-slate-100">
                    <!-- Populated dynamically by TreeBuilder -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Dual-Panel Tree Visualizations & Summaries -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Visual Tree Display -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Visual Network Hierarchy</h4>
                </div>
                <!-- View Mode Tabs -->
                <div class="flex items-center gap-1 bg-slate-200/80 p-0.5 rounded-lg text-[11px] font-semibold">
                    <button type="button" id="tab-visual-tree" onclick="window.TreeBuilder.setTreeViewMode('graph')" class="px-2.5 py-1 rounded-md bg-white text-slate-800 shadow-xs">
                        Card Tree
                    </button>
                    <button type="button" id="tab-ascii-tree" onclick="window.TreeBuilder.setTreeViewMode('ascii')" class="px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900">
                        ASCII Tree
                    </button>
                </div>
            </div>

            <!-- Card Tree View -->
            <div id="tree-view-graph" class="p-5 overflow-auto max-h-[460px] space-y-2">
                <!-- Rendered dynamically -->
            </div>

            <!-- ASCII Text View -->
            <div id="tree-view-ascii" class="p-5 font-mono text-xs text-slate-800 overflow-auto max-h-[460px] bg-slate-50 hidden select-all whitespace-pre leading-relaxed">
                <!-- Rendered dynamically -->
            </div>
        </div>

        <!-- Relationship & Sales Summary Tables -->
        <div class="space-y-6">
            <!-- Explicit Relationship Rates Table -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Configured Relationship Rates</h4>
                    <span id="edges-count-badge" class="text-[11px] font-mono text-slate-500">5 Relationships</span>
                </div>
                <div class="overflow-x-auto max-h-52 overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-100 sticky top-0">
                            <tr>
                                <th class="py-2 px-4">Parent</th>
                                <th class="py-2 px-2 text-center">→</th>
                                <th class="py-2 px-4">Child</th>
                                <th class="py-2 px-4 font-bold text-right">Override %</th>
                            </tr>
                        </thead>
                        <tbody id="tb-summary-edges-tbody" class="divide-y divide-slate-100 font-mono">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Personal Sales Summary -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Personal / Leaf Sales</h4>
                    <span id="sales-sum-badge" class="text-[11px] font-mono font-bold text-emerald-700">Total: ₹900.00</span>
                </div>
                <div class="overflow-x-auto max-h-52 overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-500 border-b border-slate-100 sticky top-0">
                            <tr>
                                <th class="py-2 px-4">Salesperson</th>
                                <th class="py-2 px-4">Type</th>
                                <th class="py-2 px-4 font-bold text-right">Personal Sale (₹)</th>
                            </tr>
                        </thead>
                        <tbody id="tb-summary-sales-tbody" class="divide-y divide-slate-100 font-mono">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Inputs Container for Seamless Form Serialization -->
    <div id="tree-builder-hidden-inputs">
        <!-- Automatically populated with edges and sales inputs before submit/calculate -->
    </div>
</div>

<!-- ========================================================================= -->
<!-- Tree Builder Vanilla JavaScript Engine -->
<!-- ========================================================================= -->
<script>
window.TreeBuilder = (function () {
    // Core in-memory node state
    let nodes = [
        { id: 1, name: 'A', parent: null, node_type: 'Root Leader', override_rate: 0.0, personal_sale: 0.0 },
        { id: 2, name: 'B', parent: 'A', node_type: 'Regional Manager', override_rate: 2.0, personal_sale: 0.0 },
        { id: 3, name: 'C', parent: 'A', node_type: 'Salesperson', override_rate: 5.0, personal_sale: 300.0 },
        { id: 4, name: 'E', parent: 'B', node_type: 'Distributor', override_rate: 3.0, personal_sale: 0.0 },
        { id: 5, name: 'F', parent: 'B', node_type: 'Salesperson', override_rate: 5.0, personal_sale: 400.0 },
        { id: 6, name: 'E1', parent: 'E', node_type: 'Salesperson', override_rate: 5.0, personal_sale: 200.0 }
    ];

    let nextNodeId = 7;
    let treeViewMode = 'graph'; // 'graph' or 'ascii'

    /**
     * Get list of node types
     */
    const defaultNodeTypes = [
        'Root Leader',
        'Regional Manager',
        'Branch Leader',
        'Distributor',
        'Salesperson',
        'Representative',
        'Partner'
    ];

    /**
     * Format currency helper
     */
    function formatCurrency(val) {
        const num = parseFloat(val) || 0;
        return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /**
     * Add a new node to the tree
     */
    function addNode(parentName = null, name = null) {
        const autoName = name || ('Rep_' + nextNodeId);
        const defaultRateInput = document.getElementById('model2_default_override_rate');
        const defaultRate = defaultRateInput ? (parseFloat(defaultRateInput.value) || 5.0) : 5.0;
        const newNode = {
            id: nextNodeId++,
            name: autoName,
            parent: parentName || (nodes.length > 0 ? nodes[0].name : null),
            node_type: 'Salesperson',
            override_rate: defaultRate,
            personal_sale: 0.0
        };
        nodes.push(newNode);
        render();
        notifyStateChange();
    }

    /**
     * Remove a node from the tree
     */
    function removeNode(id) {
        const nodeIndex = nodes.findIndex(n => n.id === id);
        if (nodeIndex === -1) return;

        const removedName = nodes[nodeIndex].name;
        // Re-parent children to removed node's parent or null
        const parentOfRemoved = nodes[nodeIndex].parent;
        nodes.forEach(n => {
            if (n.parent === removedName) {
                n.parent = parentOfRemoved;
            }
        });

        nodes.splice(nodeIndex, 1);
        render();
        notifyStateChange();
    }

    /**
     * Load exact specification example preset
     */
    function loadSpecificationPreset() {
        nodes = [
            { id: 1, name: 'A', parent: null, node_type: 'Root Leader', override_rate: 0.0, personal_sale: 0.0 },
            { id: 2, name: 'B', parent: 'A', node_type: 'Regional Manager', override_rate: 2.0, personal_sale: 0.0 },
            { id: 3, name: 'C', parent: 'A', node_type: 'Salesperson', override_rate: 5.0, personal_sale: 300.0 },
            { id: 4, name: 'E', parent: 'B', node_type: 'Distributor', override_rate: 3.0, personal_sale: 0.0 },
            { id: 5, name: 'F', parent: 'B', node_type: 'Salesperson', override_rate: 4.5, personal_sale: 400.0 },
            { id: 6, name: 'E1', parent: 'E', node_type: 'Salesperson', override_rate: 6.0, personal_sale: 200.0 }
        ];
        nextNodeId = 7;
        render();
        notifyStateChange();
    }

    /**
     * Load 10-level chain preset with dynamic decreasing generational override rates
     */
    function loadChainPreset() {
        const chain = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        const dynamicRates = [0.0, 10.0, 8.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.5, 1.0];
        nodes = [];
        chain.forEach((letter, idx) => {
            nodes.push({
                id: idx + 1,
                name: letter,
                parent: idx === 0 ? null : chain[idx - 1],
                node_type: idx === 0 ? 'Root Leader' : (idx === chain.length - 1 ? 'Salesperson' : 'Manager'),
                override_rate: dynamicRates[idx] !== undefined ? dynamicRates[idx] : 5.0,
                personal_sale: idx === chain.length - 1 ? 1000.0 : 0.0
            });
        });
        nextNodeId = chain.length + 1;
        render();
        notifyStateChange();
    }

    /**
     * Clear all nodes except one root
     */
    function clearAllNodes() {
        nodes = [
            { id: 1, name: 'Leader_1', parent: null, node_type: 'Root Leader', override_rate: 0.0, personal_sale: 0.0 }
        ];
        nextNodeId = 2;
        render();
        notifyStateChange();
    }

    /**
     * Load custom nodes array
     */
    function loadNodes(customNodes) {
        if (Array.isArray(customNodes) && customNodes.length > 0) {
            nodes = customNodes.map((n, idx) => ({
                id: n.id || (idx + 1),
                name: String(n.name).trim(),
                parent: n.parent ? String(n.parent).trim() : null,
                node_type: n.node_type || (n.parent ? 'Salesperson' : 'Root Leader'),
                override_rate: parseFloat(n.override_rate) || 0.0,
                personal_sale: parseFloat(n.personal_sale) || 0.0
            }));
            nextNodeId = nodes.length + 1;
            render();
            notifyStateChange();
        }
    }

    /**
     * Check if ancestor is a descendant of node to prevent circular relationships
     */
    function isDescendant(potentialAncestor, startNode) {
        if (!potentialAncestor || !startNode) return false;
        if (potentialAncestor === startNode) return true;

        let curr = potentialAncestor;
        const visited = {};
        while (curr) {
            if (visited[curr]) break;
            visited[curr] = true;

            const parentNode = nodes.find(n => n.name === curr);
            if (!parentNode || !parentNode.parent) break;
            if (parentNode.parent === startNode) return true;
            curr = parentNode.parent;
        }
        return false;
    }

    /**
     * Validate graph structure
     */
    function validateTree() {
        const errors = [];
        const nameMap = {};
        const parentMap = {};

        nodes.forEach(node => {
            const name = (node.name || '').trim();
            if (!name) {
                errors.push(`Row with ID ${node.id} has an empty person name.`);
                return;
            }

            if (nameMap[name]) {
                errors.push(`Duplicate person name: '${name}'. Each person in the tree must have a unique identifier.`);
            }
            nameMap[name] = true;

            // Self-parent check
            if (node.parent && node.parent === name) {
                errors.push(`Node '${name}' cannot be its own parent.`);
            }

            // Multiple parents are prevented by single parent selector per node

            // Negative sales check
            if (node.personal_sale < 0) {
                errors.push(`Personal sale amount for '${name}' cannot be negative (received: ${node.personal_sale}).`);
            }

            // Rate bounds check
            if (node.parent && (node.override_rate < 0 || node.override_rate > 100)) {
                errors.push(`Override rate for '${node.parent} → ${name}' must be between 0% and 100% (received: ${node.override_rate}%).`);
            }

            if (node.parent) {
                parentMap[name] = node.parent;
            }
        });

        // Cycle detection
        Object.keys(parentMap).forEach(child => {
            const visited = {};
            let curr = child;
            while (parentMap[curr]) {
                visited[curr] = true;
                curr = parentMap[curr];
                if (visited[curr]) {
                    errors.push(`Circular hierarchy loop detected involving '${curr}'. A person cannot be an upline of themselves.`);
                    break;
                }
            }
        });

        // Update alert banner
        const alertBox = document.getElementById('tree-validation-alert');
        const list = document.getElementById('tree-validation-list');
        if (alertBox && list) {
            if (errors.length > 0) {
                list.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
                alertBox.classList.remove('hidden');
            } else {
                alertBox.classList.add('hidden');
                list.innerHTML = '';
            }
        }

        return errors.length === 0;
    }

    /**
     * Toggle view mode for visual tree
     */
    function setTreeViewMode(mode) {
        treeViewMode = mode;
        const btnGraph = document.getElementById('tab-visual-tree');
        const btnAscii = document.getElementById('tab-ascii-tree');
        const viewGraph = document.getElementById('tree-view-graph');
        const viewAscii = document.getElementById('tree-view-ascii');

        if (mode === 'ascii') {
            btnAscii.className = 'px-2.5 py-1 rounded-md bg-white text-slate-800 shadow-xs';
            btnGraph.className = 'px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900';
            viewGraph.classList.add('hidden');
            viewAscii.classList.remove('hidden');
        } else {
            btnGraph.className = 'px-2.5 py-1 rounded-md bg-white text-slate-800 shadow-xs';
            btnAscii.className = 'px-2.5 py-1 rounded-md text-slate-600 hover:text-slate-900';
            viewAscii.classList.add('hidden');
            viewGraph.classList.remove('hidden');
        }
        renderVisualTree();
    }

    /**
     * Render the entire tree UI
     */
    function render() {
        renderNodesTable();
        renderVisualTree();
        renderSummaryPanels();
        validateTree();
        syncHiddenInputs();
    }

    /**
     * Render the interactive nodes table
     */
    function renderNodesTable() {
        const tbody = document.getElementById('tb-nodes-tbody');
        if (!tbody) return;
        tbody.innerHTML = '';

        nodes.forEach((node, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/80 transition-colors';

            // Generate parent select options (exclude self and descendants to prevent cycles)
            let parentOptions = `<option value="" ${!node.parent ? 'selected' : ''}>(None - Root Leader)</option>`;
            nodes.forEach(otherNode => {
                if (otherNode.name === node.name) return; // Cannot be own parent
                const isCyclic = isDescendant(otherNode.name, node.name);
                const disabledAttr = isCyclic ? 'disabled' : '';
                const selectedAttr = (node.parent === otherNode.name) ? 'selected' : '';
                parentOptions += `<option value="${otherNode.name}" ${selectedAttr} ${disabledAttr}>${otherNode.name} ${isCyclic ? '(causes cycle)' : ''}</option>`;
            });

            // Node type options
            let typeOptions = '';
            defaultNodeTypes.forEach(t => {
                typeOptions += `<option value="${t}" ${node.node_type === t ? 'selected' : ''}>${t}</option>`;
            });

            const hasParent = Boolean(node.parent);

            tr.innerHTML = `
                <td class="py-2.5 px-3 text-center font-mono text-slate-400 font-semibold">${index + 1}</td>
                <td class="py-2.5 px-4">
                    <input 
                        type="text" 
                        value="${node.name}" 
                        onchange="window.TreeBuilder.updateNodeField(${node.id}, 'name', this.value)"
                        class="w-full text-xs font-bold rounded-lg border border-slate-200 px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-900"
                        placeholder="Person name"
                        required
                    >
                </td>
                <td class="py-2.5 px-4">
                    <select 
                        onchange="window.TreeBuilder.updateNodeField(${node.id}, 'parent', this.value)"
                        class="w-full text-xs rounded-lg border border-slate-200 px-2.5 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-700"
                    >
                        ${parentOptions}
                    </select>
                </td>
                <td class="py-2.5 px-4">
                    <select 
                        onchange="window.TreeBuilder.updateNodeField(${node.id}, 'node_type', this.value)"
                        class="w-full text-xs rounded-lg border border-slate-200 px-2 py-1.5 bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-600"
                    >
                        ${typeOptions}
                    </select>
                </td>
                <td class="py-2.5 px-4">
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            max="100"
                            value="${node.override_rate}" 
                            ${!hasParent ? 'disabled' : ''}
                            onchange="window.TreeBuilder.updateNodeField(${node.id}, 'override_rate', this.value)"
                            class="w-full text-xs font-mono rounded-lg border border-slate-200 pl-2.5 pr-6 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 ${!hasParent ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'text-slate-900'}"
                            placeholder="0.0"
                        >
                        <span class="absolute right-2 top-1.5 text-xs text-slate-400 pointer-events-none">%</span>
                    </div>
                </td>
                <td class="py-2.5 px-4">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-semibold pointer-events-none">₹</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            value="${node.personal_sale}" 
                            onchange="window.TreeBuilder.updateNodeField(${node.id}, 'personal_sale', this.value)"
                            class="w-full text-xs font-mono rounded-lg border border-slate-200 pl-6 pr-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-slate-900"
                            placeholder="0.00"
                        >
                    </div>
                </td>
                <td class="py-2.5 px-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <button 
                            type="button" 
                            onclick="window.TreeBuilder.addNode('${node.name}')" 
                            title="Add child downline node"
                            class="p-1 rounded text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        </button>
                        <button 
                            type="button" 
                            onclick="window.TreeBuilder.removeNode(${node.id})" 
                            title="Remove node"
                            class="p-1 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                            ${nodes.length <= 1 ? 'disabled' : ''}
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </td>
            `;

            tbody.appendChild(tr);
        });

        const badge = document.getElementById('node-count-badge');
        if (badge) badge.textContent = `${nodes.length} Nodes Configured`;
    }

    /**
     * Update node field value
     */
    function updateNodeField(id, field, value) {
        const node = nodes.find(n => n.id === id);
        if (!node) return;

        if (field === 'override_rate' || field === 'personal_sale') {
            node[field] = parseFloat(value) || 0.0;
        } else if (field === 'parent') {
            node[field] = value.trim() ? value.trim() : null;
        } else {
            const oldName = node.name;
            const newName = value.trim();
            node[field] = newName;

            // If name changed, update all children's parent references
            if (field === 'name' && oldName !== newName && newName !== '') {
                nodes.forEach(n => {
                    if (n.parent === oldName) {
                        n.parent = newName;
                    }
                });
            }
        }

        render();
        notifyStateChange();
    }

    /**
     * Render the Visual Card Tree and ASCII Tree views
     */
    function renderVisualTree() {
        const graphContainer = document.getElementById('tree-view-graph');
        const asciiContainer = document.getElementById('tree-view-ascii');
        if (!graphContainer || !asciiContainer) return;

        // Build child adjacency map
        const childrenMap = {};
        const roots = [];

        nodes.forEach(n => {
            if (!n.parent) {
                roots.push(n);
            } else {
                if (!childrenMap[n.parent]) childrenMap[n.parent] = [];
                childrenMap[n.parent].push(n);
            }
        });

        // 1. Generate ASCII tree
        let asciiLines = [];
        function buildAscii(node, prefix = '', isTail = true) {
            const children = childrenMap[node.name] || [];
            const rateStr = node.parent ? ` (${node.override_rate}%)` : ' [Root]';
            const saleStr = node.personal_sale > 0 ? ` [Sale: ${formatCurrency(node.personal_sale)}]` : '';
            
            asciiLines.push(prefix + (isTail ? '└── ' : '├── ') + node.name + rateStr + saleStr);

            for (let i = 0; i < children.length; i++) {
                const child = children[i];
                const last = (i === children.length - 1);
                buildAscii(child, prefix + (isTail ? '    ' : '│   '), last);
            }
        }

        roots.forEach(r => {
            const saleStr = r.personal_sale > 0 ? ` [Sale: ${formatCurrency(r.personal_sale)}]` : '';
            asciiLines.push(r.name + saleStr);
            const children = childrenMap[r.name] || [];
            children.forEach((c, idx) => {
                buildAscii(c, '', idx === children.length - 1);
            });
        });

        asciiContainer.textContent = asciiLines.join('\n');

        // 2. Generate Graphic Card Tree
        graphContainer.innerHTML = '';
        function buildGraphNode(node, depth = 0, isLast = false) {
            const children = childrenMap[node.name] || [];
            const card = document.createElement('div');
            card.className = `relative pl-4 border-l-2 ${node.parent ? 'border-emerald-300' : 'border-indigo-400'} transition-all`;
            if (depth > 0) {
                card.style.marginLeft = `${Math.min(depth * 1.5, 6)}rem`;
            }

            const isLeaf = children.length === 0;

            card.innerHTML = `
                <div class="rounded-xl border border-slate-200/90 bg-white p-3.5 shadow-xs hover:border-emerald-300 transition-all mb-3.5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-lg ${!node.parent ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white'} flex items-center justify-center font-bold font-mono text-xs shadow-xs">
                                ${node.name.substring(0, 3)}
                            </span>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900 text-xs">${node.name}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                        ${node.node_type}
                                    </span>
                                </div>
                                ${node.parent 
                                    ? `<div class="text-[10px] text-slate-400 font-mono mt-0.5">Parent: <span class="font-bold text-slate-600">${node.parent}</span></div>`
                                    : `<div class="text-[10px] text-indigo-600 font-semibold mt-0.5">Top-Level Root Leader</div>`}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            ${node.parent ? `
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    ${node.override_rate}% Override
                                </span>
                            ` : ''}
                            ${node.personal_sale > 0 ? `
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    Sale: ${formatCurrency(node.personal_sale)}
                                </span>
                            ` : `<span class="text-[10px] text-slate-400">Sale: ₹0.00</span>`}
                        </div>
                    </div>
                </div>
            `;

            graphContainer.appendChild(card);

            children.forEach((c, idx) => {
                buildGraphNode(c, depth + 1, idx === children.length - 1);
            });
        }

        roots.forEach(r => {
            buildGraphNode(r, 0, false);
        });
    }

    /**
     * Render the Relationships and Personal Sales summary panels
     */
    function renderSummaryPanels() {
        const edgesTbody = document.getElementById('tb-summary-edges-tbody');
        const salesTbody = document.getElementById('tb-summary-sales-tbody');
        const edgesBadge = document.getElementById('edges-count-badge');
        const salesBadge = document.getElementById('sales-sum-badge');

        if (!edgesTbody || !salesTbody) return;

        edgesTbody.innerHTML = '';
        salesTbody.innerHTML = '';

        let edgeCount = 0;
        let totalSales = 0.0;

        nodes.forEach(node => {
            // Edge
            if (node.parent) {
                edgeCount++;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-1.5 px-4 font-bold text-slate-800">${node.parent}</td>
                    <td class="py-1.5 px-2 text-center text-slate-400 font-bold">→</td>
                    <td class="py-1.5 px-4 font-bold text-emerald-700">${node.name}</td>
                    <td class="py-1.5 px-4 text-right font-bold text-slate-900">${parseFloat(node.override_rate).toFixed(2)}%</td>
                `;
                edgesTbody.appendChild(tr);
            }

            // Sales (show all with sales or leaves)
            if (node.personal_sale > 0) {
                totalSales += parseFloat(node.personal_sale);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="py-1.5 px-4 font-bold text-slate-800">${node.name}</td>
                    <td class="py-1.5 px-4 text-slate-500 font-sans text-[11px]">${node.node_type}</td>
                    <td class="py-1.5 px-4 text-right font-bold text-emerald-700">${formatCurrency(node.personal_sale)}</td>
                `;
                salesTbody.appendChild(tr);
            }
        });

        if (edgeCount === 0) {
            edgesTbody.innerHTML = `<tr><td colspan="4" class="py-3 text-center text-slate-400 italic">No parent-child relationships yet. Add a child to a node.</td></tr>`;
        }
        if (totalSales === 0) {
            salesTbody.innerHTML = `<tr><td colspan="3" class="py-3 text-center text-slate-400 italic">No personal sales entered yet.</td></tr>`;
        }

        if (edgesBadge) edgesBadge.textContent = `${edgeCount} Relationships`;
        if (salesBadge) salesBadge.textContent = `Total: ${formatCurrency(totalSales)}`;
    }

    /**
     * Synchronize hidden inputs into the active form so backend calculation and persistence receive structured edges & sales
     */
    function syncHiddenInputs() {
        const container = document.getElementById('tree-builder-hidden-inputs');
        if (!container) return;
        container.innerHTML = '';

        let edgeIdx = 0;
        let saleIdx = 0;

        nodes.forEach(node => {
            // Edges input
            if (node.parent) {
                container.innerHTML += `
                    <input type="hidden" name="edges[${edgeIdx}][parent]" value="${node.parent}">
                    <input type="hidden" name="edges[${edgeIdx}][child]" value="${node.name}">
                    <input type="hidden" name="edges[${edgeIdx}][rate]" value="${node.override_rate}">
                `;
                edgeIdx++;
            }

            // Sales input
            if (node.personal_sale > 0) {
                container.innerHTML += `
                    <input type="hidden" name="sales[${saleIdx}][salesperson]" value="${node.name}">
                    <input type="hidden" name="sales[${saleIdx}][amount]" value="${node.personal_sale}">
                `;
                saleIdx++;
            }
        });

        // Also serialize nodes array for comprehensive graph persistence
        nodes.forEach((node, idx) => {
            container.innerHTML += `
                <input type="hidden" name="nodes[${idx}][name]" value="${node.name}">
                <input type="hidden" name="nodes[${idx}][parent]" value="${node.parent || ''}">
                <input type="hidden" name="nodes[${idx}][node_type]" value="${node.node_type}">
                <input type="hidden" name="nodes[${idx}][override_rate]" value="${node.override_rate}">
                <input type="hidden" name="nodes[${idx}][personal_sale]" value="${node.personal_sale}">
            `;
        });
    }

    /**
     * Notify parent view to recalculate live preview
     */
    function notifyStateChange() {
        if (typeof window.recalculateOverrideModel === 'function') {
            window.recalculateOverrideModel();
        }
    }

    /**
     * Public API
     */
    return {
        init: function () {
            render();
        },
        addNode: addNode,
        removeNode: removeNode,
        updateNodeField: updateNodeField,
        loadSpecificationPreset: loadSpecificationPreset,
        loadChainPreset: loadChainPreset,
        clearAllNodes: clearAllNodes,
        loadNodes: loadNodes,
        setTreeViewMode: setTreeViewMode,
        validate: validateTree,
        getNodes: function () { return nodes; }
    };
})();

document.addEventListener('DOMContentLoaded', function () {
    window.TreeBuilder.init();
});
</script>
