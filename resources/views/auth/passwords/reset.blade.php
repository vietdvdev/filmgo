@extends('layouts.customer')

@section('title', 'Đặt Lại Mật Khẩu - FilmGo')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-brand-dark to-brand-secondary">
    <div class="max-w-md w-full space-y-8 bg-brand-dark/50 p-8 border border-white/10 shadow-2xl backdrop-blur-sm">
        <!-- Header -->
        <div class="text-center">
            <span class="material-symbols-outlined text-brand-primary text-5xl font-bold animate-pulse">movie_filter</span>
            <h2 class="mt-4 text-3xl font-black tracking-tight text-white uppercase font-bold">Đặt lại mật khẩu</h2>
            <p class="mt-2 text-sm text-gray-400">
                Nhập mật khẩu mới của bạn bên dưới để khôi phục tài khoản.
            </p>
        </div>

        <!-- Form -->
        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Địa chỉ Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $email) }}" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Mật khẩu mới -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Mật khẩu mới</label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="••••••••">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Xác nhận mật khẩu mới -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Xác nhận mật khẩu mới</label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300" 
                               placeholder="••••••••">
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 bg-brand-primary text-white font-bold text-sm uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 transition-all duration-200">
                    Đặt Lại Mật Khẩu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
