<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Đăng Nhập Nhân Viên - FilmGo</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50 text-slate-900 antialiased">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="flex justify-center items-center gap-2">
            <span class="material-symbols-outlined text-blue-600 text-4xl font-bold">badge</span>
            <span class="text-2xl font-extrabold tracking-wider text-slate-800">FILMGO <span class="text-blue-600 text-base font-semibold tracking-normal">STAFF</span></span>
        </div>
        <h2 class="mt-6 text-center text-2xl font-bold tracking-tight text-slate-900">
            Cổng Nhân Viên
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Dành riêng cho nhân viên rạp chiếu phim
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 border border-slate-200 shadow-sm sm:px-10">

            {{-- Success message --}}
            @if(session('success'))
                <div class="mb-5 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <form class="space-y-6" action="{{ route('staff.login.submit') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tài khoản Email
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email"
                               value="{{ old('email') }}" required
                               class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:bg-white transition-all duration-200 rounded-none @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                               placeholder="nhanvien@filmgo.vn">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Mật khẩu --}}
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Mật khẩu
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password"
                               required
                               class="block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 focus:bg-white transition-all duration-200 rounded-none @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-lg" id="eye-icon">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded-none">
                    <label for="remember" class="ml-2 block text-sm text-slate-700">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                {{-- Submit --}}
                <div>
                    <button type="submit"
                            class="w-full flex justify-center items-center gap-2 py-2.5 px-4 border border-transparent text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 rounded-none">
                        <span class="material-symbols-outlined text-base">login</span>
                        Đăng Nhập
                    </button>
                </div>
            </form>

            {{-- Link về admin portal --}}
            <div class="mt-6 pt-5 border-t border-slate-100 text-center space-y-2">
                <p class="text-xs text-slate-400">Bạn là quản trị viên?
                    <a href="{{ route('admin.login') }}" class="text-blue-600 hover:underline font-medium">Đăng nhập Admin</a>
                </p>
                <p class="text-xs text-slate-400">Bạn là quản lý rạp?
                    <a href="{{ route('manager.login') }}" class="text-blue-600 hover:underline font-medium">Đăng nhập Manager</a>
                </p>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            <p>&copy; {{ date('Y') }} FilmGo Portal. Bảo mật SSL 256-bit.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>
