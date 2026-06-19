<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng Nhập Quản Lý Rạp - FilmGo</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex antialiased overflow-hidden">

    {{-- Cột trái: Minh hoạ thương hiệu --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-between overflow-hidden"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1e40af 100%);">

        {{-- Họa tiết nền --}}
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, #3b82f6 0%, transparent 50%), radial-gradient(circle at 80% 20%, #1d4ed8 0%, transparent 40%), radial-gradient(circle at 60% 80%, #0ea5e9 0%, transparent 35%);"></div>

        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 opacity-5" style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 p-12">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 flex items-center justify-center rounded-none">
                    <span class="material-symbols-outlined text-white text-xl">movie_filter</span>
                </div>
                <span class="text-white text-xl font-black tracking-widest uppercase">FilmGo</span>
            </div>
        </div>

        <div class="relative z-10 p-12 space-y-6">
            <div class="inline-flex items-center gap-2 bg-blue-500/20 border border-blue-400/30 px-4 py-2 rounded-none">
                <span class="material-symbols-outlined text-blue-300 text-sm">meeting_room</span>
                <span class="text-blue-200 text-xs font-bold uppercase tracking-widest">Cinema Manager Portal</span>
            </div>

            <h1 class="text-4xl font-black text-white leading-tight">
                Quản lý rạp chiếu<br>
                <span class="text-blue-300">chuyên nghiệp.</span>
            </h1>

            <p class="text-slate-300 text-base leading-relaxed max-w-md">
                Kiểm soát toàn bộ hoạt động vận hành rạp chiếu của bạn — từ lịch suất chiếu, nhân viên, đến báo cáo doanh thu — tất cả trong một nơi.
            </p>

            {{-- Feature list --}}
            <div class="space-y-3 pt-4">
                @foreach ([
                    ['meeting_room', 'Quản lý phòng chiếu & sơ đồ ghế'],
                    ['schedule', 'Lên lịch suất chiếu thông minh'],
                    ['group', 'Quản lý nhân sự chi nhánh'],
                    ['insert_chart', 'Báo cáo doanh thu theo thời gian thực'],
                ] as [$icon, $label])
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-500/20 border border-blue-400/30 flex items-center justify-center flex-shrink-0 rounded-none">
                        <span class="material-symbols-outlined text-blue-300 text-base">{{ $icon }}</span>
                    </div>
                    <span class="text-slate-300 text-sm">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10 p-12">
            <p class="text-slate-500 text-xs">&copy; {{ date('Y') }} FilmGo. All rights reserved.</p>
        </div>
    </div>

    {{-- Cột phải: Form đăng nhập --}}
    <div class="flex-1 flex flex-col justify-center items-center bg-slate-50 px-6 lg:px-16 py-12">
        <div class="w-full max-w-md">

            {{-- Logo trên mobile --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="w-9 h-9 bg-blue-600 flex items-center justify-center rounded-none">
                    <span class="material-symbols-outlined text-white text-lg">movie_filter</span>
                </div>
                <span class="text-slate-900 text-lg font-black tracking-widest uppercase">FilmGo Manager</span>
            </div>

            {{-- Tiêu đề --}}
            <div class="mb-8">
                <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Đăng Nhập</h2>
                <p class="mt-1.5 text-sm text-slate-500">Cổng vào hệ thống quản lý rạp chiếu</p>
                <div class="mt-3 h-1 w-12 bg-blue-600 rounded-none"></div>
            </div>

            {{-- Thông báo lỗi tổng --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 flex items-start gap-3 rounded-none">
                    <span class="material-symbols-outlined text-red-500 text-sm mt-0.5">error</span>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700 font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Form đăng nhập --}}
            <form method="POST" action="{{ route('manager.login.submit') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-widest text-slate-600 mb-1.5">
                        Địa chỉ Email
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        value="{{ old('email') }}"
                        required
                        placeholder="manager@filmgo.vn"
                        class="block w-full px-4 py-3 bg-white border-2 border-slate-200 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-0 transition-colors duration-200 rounded-none @error('email') border-red-400 @enderror">
                </div>

                {{-- Mật khẩu --}}
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-widest text-slate-600 mb-1.5">
                        Mật Khẩu
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="block w-full px-4 py-3 pr-12 bg-white border-2 border-slate-200 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-0 transition-colors duration-200 rounded-none @error('password') border-red-400 @enderror">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 hover:text-slate-700 transition-colors">
                            <span id="eye-icon" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded-none">
                    <label for="remember" class="ml-2.5 text-sm text-slate-600 font-medium">Ghi nhớ đăng nhập</label>
                </div>

                {{-- Nút đăng nhập --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-bold uppercase tracking-wider transition-colors duration-200 rounded-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span class="material-symbols-outlined text-lg">login</span>
                        Đăng Nhập Hệ Thống
                    </button>
                </div>
            </form>

            {{-- Chuyển sang trang Admin --}}
            <div class="mt-8 pt-6 border-t border-slate-200 text-center">
                <p class="text-xs text-slate-400">
                    Bạn là Admin?
                    <a href="{{ route('admin.login') }}" class="text-blue-600 font-semibold hover:text-blue-700 ml-1">
                        Đăng nhập tại đây →
                    </a>
                </p>
            </div>
        </div>
    </div>

</body>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>
</html>
