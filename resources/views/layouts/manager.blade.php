<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản Lý Rạp - FilmGo')</title>
    
    <!-- Tailwinds CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    
    <!-- Google Fonts & Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    @yield('styles')
</head>
<body class="h-full flex bg-slate-100 text-slate-800 antialiased">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-20 flex w-64 flex-col bg-slate-900 text-white overflow-hidden">
        <!-- Brand -->
        <div class="flex h-16 items-center gap-2 border-b border-slate-800 px-6 flex-shrink-0">
            <span class="material-symbols-outlined text-blue-500 text-3xl font-bold">domain</span>
            <span class="text-lg font-black tracking-wider uppercase">FilmGo <span class="text-xs font-semibold text-blue-500 lowercase">manager</span></span>
        </div>

        <!-- Scoped Cinema Tag -->
        <div class="px-6 py-3 border-b border-slate-800 bg-slate-950 flex-shrink-0">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rạp Đang Quản Lý</p>
            <p class="text-sm font-semibold text-white mt-0.5 truncate" title="{{ auth()->user()->cinemas()->first()?->name ?? 'Chưa phân công' }}">
                {{ auth()->user()->cinemas()->first()?->name ?? 'Chưa phân công' }}
            </p>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto space-y-1 px-4 py-4 min-h-0">
            <a href="{{ route('manager.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition-colors duration-200 rounded-none {{ request()->routeIs('manager.dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="material-symbols-outlined text-lg">dashboard</span>
                Tổng Quan
            </a>
            <a href="{{ route('manager.staff.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition-colors duration-200 rounded-none {{ request()->routeIs('manager.staff.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="material-symbols-outlined text-lg">group</span>
                Nhân Sự Rạp
            </a>
            <a href="{{ route('manager.rooms.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition-colors duration-200 rounded-none {{ request()->routeIs('manager.rooms.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="material-symbols-outlined text-lg">meeting_room</span>
                Phòng Chiếu
            </a>
            <a href="{{ route('manager.showtimes.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition-colors duration-200 rounded-none {{ request()->routeIs('manager.showtimes.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="material-symbols-outlined text-lg">schedule</span>
                Suất Chiếu
            </a>
            <a href="{{ route('manager.reports.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold transition-colors duration-200 rounded-none {{ request()->routeIs('manager.reports.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <span class="material-symbols-outlined text-lg">insert_chart</span>
                Báo Cáo Doanh Thu
            </a>
        </nav>

        <!-- Footer / User Info + Logout -->
        <div class="border-t border-slate-700 bg-slate-950 flex-shrink-0">
            {{-- Thông tin user --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-800">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white text-base">person</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            {{-- Nút Đăng Xuất toàn chiều rộng --}}
            <form id="manager-logout-form" action="{{ route('manager.logout') }}" method="POST" class="hidden">
                @csrf
            </form>
            <button
                type="button"
                id="manager-logout-btn"
                onclick="openManagerLogoutModal()"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 group">
                <span class="material-symbols-outlined text-lg group-hover:translate-x-0.5 transition-transform">logout</span>
                <span>Đăng Xuất</span>
            </button>
        </div>
    </aside>

    {{-- Modal xác nhận đăng xuất Manager --}}
    <div id="manager-logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeManagerLogoutModal()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-[380px] mx-4 p-6 z-10">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-500 text-3xl">logout</span>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Xác nhận đăng xuất</h2>
                <p class="text-sm text-slate-500">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống Quản lý Rạp FilmGo không?</p>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeManagerLogoutModal()"
                    class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors rounded-lg">
                    Hủy bỏ
                </button>
                <button type="button" onclick="document.getElementById('manager-logout-form').submit()"
                    class="flex-1 px-4 py-2.5 bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition-colors rounded-lg flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">logout</span>
                    Đăng xuất
                </button>
            </div>
        </div>
    </div>

    <script>
        function openManagerLogoutModal() {
            const m = document.getElementById('manager-logout-modal');
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeManagerLogoutModal() {
            const m = document.getElementById('manager-logout-modal');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeManagerLogoutModal();
        });
    </script>

    <!-- Main Content Wrapper -->
    <div class="flex flex-1 flex-col pl-64">
        <!-- Top Bar -->
        <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-8 shadow-sm">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Khu Vực Làm Việc</span>
                <h1 class="text-sm font-bold text-slate-800">Cinema Manager Portal</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800">
                        {{ auth()->user()->roles()->first()?->description ?? 'Quản lý' }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Page Canvas -->
        <main class="flex-grow p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
