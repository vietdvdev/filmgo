@extends('layouts.customer')

@section('title', 'Đăng Ký Tài Khoản - FilmGo')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-brand-dark to-brand-secondary">
    <div class="max-w-md w-full space-y-8 bg-brand-dark/50 p-8 border border-white/10 shadow-2xl backdrop-blur-sm">
        <!-- Header -->
        <div class="text-center">
            <span class="material-symbols-outlined text-brand-primary text-5xl font-bold animate-pulse">movie_filter</span>
            <h2 class="mt-4 text-3xl font-black tracking-tight text-white uppercase">Tạo tài khoản mới</h2>
            <p class="mt-2 text-sm text-gray-400">
                Đã có tài khoản?
                <a href="{{ route('login') }}" class="font-medium text-brand-primary hover:text-red-500 transition-colors duration-200">
                    đăng nhập ngay
                </a>
            </p>
        </div>

        <!-- Form -->
        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <!-- Họ tên -->
                <div>
                    <label for="full_name" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Họ và Tên</label>
                    <div class="mt-1 relative">
                        <input id="full_name" name="full_name" type="text" value="{{ old('full_name') }}" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('full_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="Nguyễn Văn A">
                    </div>
                    @error('full_name')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Địa chỉ Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Số điện thoại</label>
                    <div class="mt-1 relative">
                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('phone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="0987654321">
                    </div>
                    @error('phone')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Mật khẩu</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Xác nhận mật khẩu -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Xác nhận mật khẩu</label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300" 
                               placeholder="••••••••">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 bg-brand-primary text-white font-bold text-sm uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 transition-all duration-200">
                    Đăng Ký Tài Khoản
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
