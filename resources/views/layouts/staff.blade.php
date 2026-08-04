<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nhân Viên - FilmGo')</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary-fixed-dim": "#b7c4ff",
                        "on-surface": "#191c1e",
                        "background": "#f8f9fb",
                        "primary": "#004be3",
                        "secondary": "#50606e",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f2f4f6",
                        "surface-container": "#eceef0",
                        "surface-container-high": "#e6e8ea",
                        "surface-container-highest": "#e0e3e5",
                        "surface": "#f8f9fb",
                        "on-surface-variant": "#434655",
                        "outline-variant": "#c3c5d8",
                        "outline": "#737687",
                        "primary-container": "#3366ff",
                        "error": "#ba1a1a",
                    },
                    fontFamily: {
                        "label-sm": ["Inter"],
                        "headline-lg": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "body-md": ["Inter"],
                        "label-md": ["Inter"],
                    },
                    fontSize: {
                        "label-sm":   ["12px", { lineHeight: "16px", fontWeight: "500" }],
                        "headline-lg":["32px", { lineHeight: "40px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-sm":["18px", { lineHeight: "28px", fontWeight: "600" }],
                        "body-lg":    ["16px", { lineHeight: "24px", fontWeight: "400" }],
                        "headline-md":["24px", { lineHeight: "32px", letterSpacing: "-0.01em", fontWeight: "600" }],
                        "body-md":    ["14px", { lineHeight: "22px", fontWeight: "400" }],
                        "label-md":   ["13px", { lineHeight: "18px", letterSpacing: "0.05em", fontWeight: "600" }],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .shadow-ambient-sm { box-shadow: 0px 4px 12px rgba(0,0,0,0.03); }
    </style>
    @stack('styles')
</head>
<body class="bg-background text-on-surface h-screen overflow-hidden flex antialiased">

    {{-- ── Sidebar ── --}}
    <aside class="bg-surface-container-lowest border-r border-outline-variant shadow-sm w-[280px] h-screen fixed left-0 top-0 flex flex-col py-6 z-20">

        {{-- Brand --}}
        <div class="px-6 mb-8 flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:20px">badge</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-primary tracking-wide">FilmGo</h1>
                <p class="text-[10px] font-semibold text-on-surface-variant uppercase tracking-widest -mt-0.5">Staff Portal</p>
            </div>
        </div>

        {{-- Rạp đang làm việc --}}
        <div class="mx-4 mb-4 px-4 py-3 bg-surface-container-low rounded-xl border border-outline-variant">
            <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Rạp đang làm việc</p>
            <p class="text-sm font-semibold text-on-surface mt-0.5 truncate">
                {{ Auth::user()->cinemas()->first()?->name ?? 'Chưa phân công' }}
            </p>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-2 flex flex-col gap-1">
            <a href="{{ route('staff.showtimes.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                      {{ request()->routeIs('staff.showtimes.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined"
                      style="font-variation-settings: 'FILL' {{ request()->routeIs('staff.showtimes.*') ? 1 : 0 }}">today</span>
                <span class="text-label-md">Lịch Chiếu Hôm Nay</span>
            </a>

            {{-- Quản lý vé khách đặt --}}
            <a href="{{ route('staff.bookings.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                      {{ request()->routeIs('staff.bookings.*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined"
                      style="font-variation-settings: 'FILL' {{ request()->routeIs('staff.bookings.*') ? 1 : 0 }}">confirmation_number</span>
                <span class="text-label-md">Quản Lý Vé Đặt</span>
            </a>

            {{-- Quản lý combo/F&B --}}
            <a href="{{ route('staff.combo-bookings.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                      {{ request()->routeIs('staff.combo-bookings.*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined"
                      style="font-variation-settings: 'FILL' {{ request()->routeIs('staff.combo-bookings.*') ? 1 : 0 }}">fastfood</span>
                <span class="text-label-md">Quản Lý Combo</span>
            </a>

            {{-- POS — Bán vé tại quầy --}}
            <a href="{{ route('staff.pos.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                      {{ request()->routeIs('staff.pos.*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined"
                      style="font-variation-settings: 'FILL' {{ request()->routeIs('staff.pos.*') ? 1 : 0 }}">point_of_sale</span>
                <span class="text-label-md">Bán Vé Tại Quầy (POS)</span>
            </a>

            {{-- Tài khoản cá nhân --}}
            <a href="{{ route('staff.profile.edit') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200
                      {{ request()->routeIs('staff.profile.*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <span class="material-symbols-outlined"
                      style="font-variation-settings: 'FILL' {{ request()->routeIs('staff.profile.*') ? 1 : 0 }}">account_circle</span>
                <span class="text-label-md">Tài Khoản Cá Nhân</span>
            </a>
        </nav>

        {{-- Footer: user info + logout --}}
        <div class="px-2 mt-auto pt-4 border-t border-outline-variant/30">
            <a href="{{ route('staff.profile.edit') }}" class="flex items-center gap-3 px-4 py-3 mb-1 rounded-xl hover:bg-surface-container-low transition-colors group">
                @if(Auth::user()->avatar)
                    <img src="{{ asset(Auth::user()->avatar) }}" class="w-9 h-9 rounded-full object-cover border border-primary/20 flex-shrink-0" alt="{{ Auth::user()->full_name }}">
                @else
                    <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-primary text-lg">person</span>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-on-surface truncate group-hover:text-primary transition-colors">{{ Auth::user()->full_name }}</p>
                    <p class="text-[11px] text-on-surface-variant truncate">{{ Auth::user()->email }}</p>
                </div>
            </a>

            <form id="staff-logout-form" action="{{ route('staff.logout') }}" method="POST" class="hidden">@csrf</form>
            <button type="button" onclick="document.getElementById('staff-logout-form').submit()"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 transition-all duration-200 font-semibold group">
                <span class="material-symbols-outlined text-xl group-hover:translate-x-0.5 transition-transform">logout</span>
                <span class="text-label-md">Đăng Xuất</span>
            </button>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="flex-1 flex flex-col pl-[280px]">
        {{-- Top bar --}}
        <header class="flex h-16 items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-8 shadow-ambient-sm flex-shrink-0">
            <div>
                <p class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest">Khu Vực Làm Việc</p>
                <h1 class="text-sm font-bold text-on-surface">Cinema Staff Portal</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[11px] text-on-surface-variant">{{ now()->format('H:i • d/m/Y') }}</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                    Nhân Viên
                </span>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-grow overflow-y-auto">
            @yield('content')
        </main>
    </div>

@stack('scripts')
</body>
</html>
