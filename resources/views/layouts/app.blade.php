<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Weakest Link Commission Model' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col min-h-screen">
    <div class="flex flex-1 min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col border-r border-slate-800">
            <!-- Brand / Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-md shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-wide leading-tight">Weakest Link</h1>
                    <span class="text-xs text-indigo-400 font-medium">Commission Engine</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <div class="px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Core Dashboard
                </div>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard Overview
                </a>

                <a href="{{ route('commission-models.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg {{ (request()->routeIs('commission-models.index') || request()->routeIs('models.index')) ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }} transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    Saved Models
                </a>

                <div class="pt-4 px-3 pb-1 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Model Engines
                </div>

                <a href="{{ route('commission-models.create') }}" class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg {{ (request()->routeIs('commission-models.create') || request()->routeIs('models.create')) ? 'bg-indigo-600/20 text-indigo-300 border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }} transition-colors">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                        <span>Model 1: Weakest Link</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-indigo-950 text-indigo-300 border border-indigo-800/60">MIN</span>
                </a>

                <a href="{{ route('override-models.create') }}" class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('override-models.*') ? 'bg-emerald-600/20 text-emerald-300 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }} transition-colors">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Model 2: Override Model</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-950 text-emerald-300 border border-emerald-800/60 font-semibold">NEW</span>
                </a>

                <a href="{{ route('unilevel-models.create') }}" class="flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('unilevel-models.*') ? 'bg-violet-600/20 text-violet-300 border border-violet-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }} transition-colors">
                    <div class="flex items-center gap-2.5">
                        <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span>
                        <span>Model 3: Unilevel MLM</span>
                    </div>
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-950 text-violet-300 border border-violet-800/60 font-semibold">MLM</span>
                </a>

                <div class="pt-5 px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    Model Analytics
                </div>

                <a href="#statistics" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Performance Stats
                </a>

                <a href="#recent-models" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Recent Models
                </a>

                <div class="pt-5 px-3 pb-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    System & Engine
                </div>

                <div class="px-3 py-2.5 rounded-lg bg-slate-800/50 border border-slate-700/60 text-xs space-y-1.5">
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Environment:</span>
                        <span class="text-emerald-400 font-mono font-semibold">Local (8084)</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Database:</span>
                        <span class="text-slate-300 font-mono">MySQL / MariaDB</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-400">
                        <span>Framework:</span>
                        <span class="text-slate-300">Laravel v{{ app()->version() }}</span>
                    </div>
                </div>
            </nav>

            <!-- User / Session Footer in Sidebar -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-xs font-semibold text-slate-200">
                        AD
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-white truncate">Administrator</p>
                        <p class="text-[11px] text-slate-400 truncate">admin@custom.local</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Sidebar Backdrop for mobile -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/50 z-30 hidden lg:hidden"></div>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col lg:pl-64 min-w-0">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-20 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <!-- Mobile Sidebar Toggle Button -->
                    <button id="sidebar-toggle" type="button" class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Breadcrumbs -->
                    <nav class="flex items-center space-x-2 text-xs font-medium text-slate-500">
                        <span>Commission Engine</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-slate-900 font-semibold">Dashboard</span>
                    </nav>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ request()->getHttpHost() }}
                    </span>

                    <a href="{{ route('models.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm shadow-indigo-600/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>New Model</span>
                    </a>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 py-4 px-4 sm:px-6 lg:px-8 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 mt-auto">
                <p>&copy; {{ date('Y') }} Weakest Link Commission Model. All rights reserved.</p>
                <div class="flex items-center gap-4 text-slate-400">
                    <span>Host: <code class="text-slate-600 font-mono">{{ request()->getHttpHost() }}</code></span>
                    <span>&bull;</span>
                    <span>Database: <code class="text-slate-600 font-mono">{{ config('database.connections.' . config('database.default') . '.database') }}</code></span>
                    <span>&bull;</span>
                    <span>Laravel v{{ app()->version() }}</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Vanilla JavaScript for Mobile Sidebar Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (toggle && sidebar && backdrop) {
                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                };

                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                };

                toggle.addEventListener('click', openSidebar);
                backdrop.addEventListener('click', closeSidebar);
            }
        });
    </script>
</body>
</html>
