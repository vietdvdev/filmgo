<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chưa Được Phân Công - FilmGo</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col bg-slate-100 text-slate-800 antialiased">

    {{-- Top Bar đơn giản — Không có sidebar, không có menu --}}
    <header class="h-16 flex items-center justify-between px-8 bg-slate-900 shadow-md">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-400 text-3xl">domain</span>
            <span class="text-lg font-black tracking-wider uppercase text-white">FilmGo <span class="text-xs font-semibold text-blue-400 lowercase">manager</span></span>
        </div>

        {{-- Nút Đăng xuất --}}
        <form method="POST" action="{{ route('manager.logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-all rounded-none">
                <span class="material-symbols-outlined text-base">logout</span>
                Đăng Xuất
            </button>
        </form>
    </header>

    {{-- Main Content — Blank Slate --}}
    <main class="flex-1 flex items-center justify-center p-8">
        <div class="max-w-lg w-full text-center">

            {{-- Icon minh họa --}}
            <div class="mx-auto mb-6 w-24 h-24 rounded-full bg-amber-50 border-2 border-amber-200 flex items-center justify-center">
                <span class="material-symbols-outlined text-amber-500 text-5xl">location_off</span>
            </div>

            {{-- Tiêu đề --}}
            <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight mb-3">
                Chưa Được Phân Công
            </h1>

            {{-- Mô tả --}}
            <div class="bg-amber-50 border border-amber-200 p-5 mb-6 text-left">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-500 text-xl mt-0.5 shrink-0">warning</span>
                    <div>
                        <p class="text-sm font-bold text-amber-800 mb-1">Tài khoản của bạn chưa được phân công làm việc tại cụm rạp nào.</p>
                        <p class="text-sm text-amber-700">Vui lòng liên hệ <strong>Quản trị viên hệ thống (Admin)</strong> để hoàn tất thiết lập tài khoản trước khi có thể sử dụng hệ thống.</p>
                    </div>
                </div>
            </div>

            {{-- Thông tin tài khoản --}}
            <div class="bg-white border border-slate-200 p-4 mb-6 text-left">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Thông Tin Tài Khoản</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-600 text-lg">person</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                    </div>
                    <span class="ml-auto inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold bg-blue-100 text-blue-700">
                        <span class="material-symbols-outlined text-xs">manage_accounts</span>
                        Quản lý rạp
                    </span>
                </div>
            </div>

            {{-- Hướng dẫn hành động --}}
            <div class="bg-slate-50 border border-slate-200 p-4 mb-6 text-left">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Các bước tiếp theo</p>
                <ol class="space-y-2">
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <span class="material-symbols-outlined text-blue-500 text-base mt-0.5 shrink-0">looks_one</span>
                        Liên hệ <strong>Quản trị viên</strong> của hệ thống FilmGo.
                    </li>
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <span class="material-symbols-outlined text-blue-500 text-base mt-0.5 shrink-0">looks_two</span>
                        Yêu cầu Admin phân công bạn vào một <strong>Cụm Rạp</strong> cụ thể.
                    </li>
                    <li class="flex items-start gap-2 text-sm text-slate-700">
                        <span class="material-symbols-outlined text-blue-500 text-base mt-0.5 shrink-0">looks_3</span>
                        Sau khi được phân công, <strong>đăng xuất và đăng nhập lại</strong> để cập nhật quyền.
                    </li>
                </ol>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                {{-- Đăng nhập lại (để load session mới sau khi admin phân công) --}}
                <form method="POST" action="{{ route('manager.logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 rounded-none">
                        <span class="material-symbols-outlined text-base">refresh</span>
                        Đăng Xuất & Đăng Nhập Lại
                    </button>
                </form>
            </div>

        </div>
    </main>

    {{-- Footer --}}
    <footer class="h-10 flex items-center justify-center text-xs text-slate-400 border-t border-slate-200 bg-white">
        © {{ date('Y') }} FilmGo — Hệ thống Quản lý Rạp Chiếu Phim
    </footer>

</body>
</html>
