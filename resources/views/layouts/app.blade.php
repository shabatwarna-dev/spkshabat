<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SPK Shabat &mdash; @yield('title', 'Dashboard')</title>

    <meta name="theme-color" content="#1a1f2e">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; color: #1a1f2e; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f0f2f5; }
        ::-webkit-scrollbar-thumb { background: #c8cdd8; border-radius: 3px; }

        /* ── Sidebar ── */
        .sidebar {
            background: #1a1f2e;
            border-right: 1px solid #252b3b;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 14px; border-radius: 8px;
            color: #8892a4; font-size: 13.5px; font-weight: 500;
            text-decoration: none; transition: all .15s;
        }
        .nav-link:hover { background: rgba(255,255,255,.06); color: #d1d8e8; }
        .nav-link.active { background: rgba(59,130,246,.15); color: #60a5fa; }
        .nav-link svg { flex-shrink: 0; }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid #e4e8f0;
            box-shadow: 0 1px 4px rgba(0,0,0,.05);
        }

        /* ── Badges ── */
        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 9px; border-radius: 6px;
            font-size: 11px; font-weight: 600; letter-spacing: .02em;
        }
        .badge-draft    { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-produksi { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-selesai  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-kirim    { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
        .badge-batal    { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-telat    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-edited   { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }

        /* Process status chips */
        .chip-pending { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .chip-proses  { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .chip-selesai { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .chip-telat   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* ── Process row ── */
        .process-row {
            border: 1px solid #e4e8f0;
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow .15s;
        }
        .process-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .process-row-header { padding: 12px 14px; display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
        .process-row-header:hover { background: #f8fafc; }

        .status-dot-pending { background: #94a3b8; }
        .status-dot-proses  { background: #3b82f6; animation: pulse 1.5s infinite; }
        .status-dot-selesai { background: #22c55e; }
        .status-dot-telat   { background: #ef4444; }

        /* Left accent bar */
        .process-row.status-pending { border-left: 3px solid #cbd5e1; }
        .process-row.status-proses  { border-left: 3px solid #3b82f6; }
        .process-row.status-selesai { border-left: 3px solid #22c55e; }
        .process-row.status-telat   { border-left: 3px solid #ef4444; }

        /* ── Form elements ── */
        .form-label {
            display: block; font-size: 11.5px; font-weight: 600;
            color: #4b5563; margin-bottom: 5px;
            text-transform: uppercase; letter-spacing: .05em;
        }
        .form-input {
            width: 100%; padding: 8px 11px;
            border: 1.5px solid #d1d5db; border-radius: 7px;
            font-size: 13.5px; color: #111827; background: #fff;
            font-family: 'Inter', sans-serif;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        .form-input:disabled, .form-input.readonly {
            background: #f9fafb; color: #6b7280; cursor: not-allowed;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 7px; font-size: 13.5px;
            font-weight: 600; cursor: pointer; transition: all .15s;
            border: 1.5px solid transparent; text-decoration: none;
            font-family: 'Inter', sans-serif; line-height: 1;
        }
        .btn-primary   { background: #2563eb; color: #fff; border-color: #2563eb; }
        .btn-primary:hover   { background: #1d4ed8; }
        .btn-secondary { background: #fff; color: #374151; border-color: #d1d5db; }
        .btn-secondary:hover { background: #f9fafb; }
        .btn-danger    { background: #fff; color: #dc2626; border-color: #fca5a5; }
        .btn-danger:hover    { background: #fef2f2; }
        .btn-success   { background: #16a34a; color: #fff; border-color: #16a34a; }
        .btn-success:hover   { background: #15803d; }
        .btn-warning   { background: #f59e0b; color: #fff; border-color: #f59e0b; }
        .btn-sm { padding: 5px 11px; font-size: 12px; border-radius: 6px; }
        .btn-xs { padding: 3px 8px; font-size: 11px; border-radius: 5px; }

        /* ── Progress bar ── */
        .progress-track { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
        .progress-fill  { height: 100%; background: linear-gradient(90deg, #2563eb, #60a5fa); border-radius: 3px; transition: width .5s ease; }
        .progress-fill.danger { background: linear-gradient(90deg, #dc2626, #f87171); }

        /* ── Stats card ── */
        .stat-card { background: #fff; border: 1px solid #e4e8f0; border-radius: 10px; padding: 18px 20px; }
        .stat-value { font-size: 28px; font-weight: 700; line-height: 1; }
        .stat-label { font-size: 12px; font-weight: 500; color: #6b7280; margin-top: 4px; }

        /* ── Section header ── */
        .section-header {
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 12px; margin-bottom: 16px;
            border-bottom: 1px solid #e4e8f0;
        }
        .section-accent { width: 3px; height: 18px; border-radius: 2px; flex-shrink: 0; }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; padding: 9px 12px;
            font-size: 11px; font-weight: 600; color: #6b7280;
            text-transform: uppercase; letter-spacing: .05em;
            background: #f8fafc; border-bottom: 1px solid #e4e8f0;
        }
        .data-table td {
            padding: 10px 12px; font-size: 13px;
            border-bottom: 1px solid #f1f3f7; vertical-align: top;
        }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: #fafbfd; }

        /* ── Info grid ── */
        .info-label { font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
        .info-value { font-size: 13.5px; font-weight: 500; color: #111827; }

        /* ── Alert / notification ── */
        .alert { border-radius: 8px; padding: 12px 14px; font-size: 13px; }
        .alert-warning { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
        .alert-danger  { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .alert-info    { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

        /* ── Toast ── */
        .toast {
            position: fixed; bottom: 76px; left: 50%; transform: translateX(-50%);
            background: #1a1f2e; color: #fff; padding: 10px 20px;
            border-radius: 8px; font-size: 13px; font-weight: 500;
            z-index: 9999; box-shadow: 0 4px 24px rgba(0,0,0,.25);
            white-space: nowrap;
            animation: toastIn .25s ease;
        }
        .toast.success { background: #166534; }
        .toast.error   { background: #991b1b; }
        @keyframes toastIn { from { transform: translateX(-50%) translateY(12px); opacity: 0; } to { transform: translateX(-50%) translateY(0); opacity: 1; } }

        /* ── Mobile bottom nav ── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            background: #1a1f2e; border-top: 1px solid #252b3b;
            display: flex; align-items: center; justify-content: space-around;
            padding: 6px 0 max(6px, env(safe-area-inset-bottom));
        }
        .bottom-nav-item {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            padding: 4px 14px; border-radius: 8px; text-decoration: none;
            color: #6b7280; font-size: 10.5px; font-weight: 500;
            transition: color .15s;
        }
        .bottom-nav-item.active { color: #60a5fa; }
        .bottom-nav-fab {
            width: 44px; height: 44px; background: #2563eb; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-top: -20px; box-shadow: 0 4px 12px rgba(37,99,235,.4);
        }

        /* ── Divider ── */
        .divider { border: none; border-top: 1px solid #e4e8f0; margin: 0; }

        
        @media (min-width: 768px) { .bottom-nav { display: none; } }

        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
    </style>
    @stack('styles')
</head>
<body class="h-full">

<div class="flex min-h-screen overflow-x-hidden">

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar-wrap sidebar w-56 flex-shrink-0 fixed left-0 top-0 h-full z-40 hidden md:flex flex-col">
        {{-- Logo --}}
        <div class="px-4 py-4 border-b border-slate-700/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-white" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-white font-bold text-sm leading-tight">SPK Shabat</div>
                    <div class="text-slate-500 text-xs mt-0.5">Manajemen Produksi</div>
                </div>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            <p class="text-[10px] font-600 text-slate-600 uppercase tracking-wider px-3 py-2">Menu</p>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') || request()->routeIs('orders.show') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Daftar SPK
            </a>
            @if(auth()->user()->isMarketing())
            <a href="{{ route('orders.create') }}" class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat SPK Baru
            </a>
            @endif
            <a href="{{ route('orders.history') }}" class="nav-link {{ request()->routeIs('orders.history') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Riwayat
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
        </nav>

    </aside>

    {{-- ── MAIN ── --}}
    <main class="flex-1 md:ml-56 pb-20 md:pb-0 min-h-screen w-full min-w-0 overflow-x-hidden">

        {{-- Page header --}}
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="flex items-center gap-3 px-4 py-2.5">
                <div class="flex-1 min-w-0">
                    <h1 class="text-[15px] font-bold text-gray-900 leading-tight">@yield('title', 'Dashboard')</h1>
                    @hasSection('subtitle')
                    <p class="text-xs text-gray-500 mt-0.5 truncate">@yield('subtitle')</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @yield('header-actions')
                </div>
                {{-- Profile + Logout (always visible) --}}
                <div class="flex items-center gap-2 flex-shrink-0 pl-2 border-l border-gray-200" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 rounded-lg px-2 py-1.5 hover:bg-gray-50 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <div class="text-xs font-600 text-gray-800 leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-gray-400 capitalize">{{ auth()->user()->role }}</div>
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    {{-- Dropdown --}}
                    <div x-show="open" x-cloak @click.outside="open = false"
                         class="absolute right-3 top-12 bg-white border border-gray-200 rounded-xl shadow-lg z-50 min-w-40 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="text-xs font-600 text-gray-800">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-gray-400 capitalize mt-0.5">{{ auth()->user()->role }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="toast success">
            <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="toast error">{{ session('error') }}</div>
        @endif

        <div class="p-3 md:p-6">
            @yield('content')
        </div>
    </main>
</div>

{{-- ── MOBILE BOTTOM NAV ── --}}
<nav class="bottom-nav md:hidden">
    <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('orders.index') }}" class="bottom-nav-item {{ request()->routeIs('orders.index') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        SPK
    </a>
    @if(auth()->user()->isMarketing())
    <a href="{{ route('orders.create') }}" class="bottom-nav-item">
        <div class="bottom-nav-fab">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </div>
        <span style="margin-top:2px;">Buat</span>
    </a>
    @endif
    <a href="{{ route('orders.history') }}" class="bottom-nav-item {{ request()->routeIs('orders.history') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Riwayat
    </a>
    <a href="{{ route('reports.index') }}" class="bottom-nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        Laporan
    </a>
</nav>

@stack('scripts')
</body>
</html>