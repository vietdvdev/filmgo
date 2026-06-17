@extends('layouts.customer')

@section('title', 'Quên Mật Khẩu - FilmGo')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-brand-dark to-brand-secondary">
    <div class="max-w-md w-full space-y-8 bg-brand-dark/50 p-8 border border-white/10 shadow-2xl backdrop-blur-sm">
        <!-- Header -->
        <div class="text-center">
            <span class="material-symbols-outlined text-brand-primary text-5xl font-bold animate-pulse">movie_filter</span>
            <h2 class="mt-4 text-3xl font-black tracking-tight text-white uppercase">Quên mật khẩu?</h2>
            <p class="mt-2 text-sm text-gray-400">
                Nhập địa chỉ email của bạn để nhận liên kết khôi phục mật khẩu.
            </p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-300 uppercase tracking-wider">Địa chỉ Email</label>
                    <div class="mt-1 relative">
                        <input id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" 
                               class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white placeholder-gray-500 rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                               placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Submit -->
            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 bg-brand-primary text-white font-bold text-sm uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 transition-all duration-200">
                    Gửi Liên Kết Khôi Phục
                </button>
            </div>
            
            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white flex items-center justify-center gap-1 transition-colors duration-200">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
