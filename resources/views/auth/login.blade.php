@extends('layouts.customer')

@section('title', 'Đăng Nhập - FilmGo')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl border border-gray-200 shadow-xl">
        <!-- Header -->
        <div class="text-center">
            <span class="material-symbols-outlined text-red-600 text-5xl font-bold animate-pulse">movie_filter</span>
            <h2 class="mt-4 text-3xl font-black tracking-tight text-gray-900 uppercase">Chào mừng trở lại</h2>
            <p class="mt-2 text-sm text-gray-600">
                Hoặc
                <a href="{{ route('register') }}" class="font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                    đăng ký tài khoản mới ngay
                </a>
            </p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider">Địa chỉ Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" 
                               class="w-full pl-4 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-all duration-300 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Mật khẩu -->
                <div>
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-sm font-semibold text-gray-700 uppercase tracking-wider">Mật khẩu</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-gray-500 hover:text-red-600 transition-colors duration-200">Quên mật khẩu?</a>
                    </div>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" 
                               class="w-full pl-4 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-600 transition-all duration-300 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500 rounded cursor-pointer">
                    <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 bg-red-600 text-white font-bold text-sm uppercase tracking-wider rounded-lg hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 transition-all duration-200">
                    Đăng Nhập
                </button>
            </div>
        </form>
    </div>
</div>
@endsection