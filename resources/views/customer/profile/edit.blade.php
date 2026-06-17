@extends('layouts.customer')

@section('title', 'Thông Tin Tài Khoản - FilmGo')

@section('content')
<div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-brand-dark to-brand-secondary">
    <div class="max-w-4xl mx-auto space-y-10">
        <!-- Page Header -->
        <div class="border-b border-white/10 pb-4">
            <h1 class="text-3xl font-black uppercase text-white tracking-wide">Cài Đặt Tài Khoản</h1>
            <p class="text-gray-400 text-sm mt-1">Quản lý thông tin hồ sơ cá nhân và bảo mật mật khẩu của bạn.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Section 1: Cập nhật thông tin cá nhân -->
            <div class="bg-brand-dark/50 p-8 border border-white/10 shadow-xl backdrop-blur-sm space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-white uppercase tracking-wide border-l-4 border-brand-primary pl-3">Thông Tin Cá Nhân</h2>
                    <p class="text-xs text-gray-400 mt-1">Cập nhật họ tên và số điện thoại liên lạc của bạn.</p>
                </div>

                @if(session('success_profile'))
                    <div class="p-4 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
                        {{ session('success_profile') }}
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Ảnh Đại Diện & Xem Trước -->
                    <div class="flex flex-col items-center space-y-3 pb-4 border-b border-white/5">
                        <div class="relative group w-24 h-24 rounded-full overflow-hidden border-2 border-brand-primary bg-white/5 flex items-center justify-center">
                            @if($user->avatar)
                                <img id="avatar-preview" src="{{ asset($user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div id="avatar-placeholder" class="w-full h-full flex items-center justify-center text-gray-400">
                                    <span class="material-symbols-outlined text-5xl">person</span>
                                </div>
                                <img id="avatar-preview" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        <label for="avatar" class="cursor-pointer bg-white/10 hover:bg-brand-primary text-white text-xs font-semibold px-4 py-2 transition-all duration-200">
                            Chọn ảnh đại diện
                            <input id="avatar" name="avatar" type="file" accept="image/*" class="hidden" onchange="previewImage(event)">
                        </label>
                        @error('avatar')
                            <p class="text-xs text-red-500 flex items-center gap-1 font-medium mt-1">
                                <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Email (Readonly) -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Địa chỉ Email</label>
                        <div class="mt-1 relative">
                            <input type="email" value="{{ $user->email }}" disabled 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-gray-400 rounded-none cursor-not-allowed focus:outline-none">
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1 italic">Địa chỉ email không thể thay đổi.</p>
                    </div>

                    <!-- Họ và Tên -->
                    <div>
                        <label for="full_name" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Họ và Tên</label>
                        <div class="mt-1 relative">
                            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('full_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   placeholder="Nguyễn Văn A">
                        </div>
                        @error('full_name')
                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                                <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Số điện thoại</label>
                        <div class="mt-1 relative">
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('phone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   placeholder="0987654321">
                        </div>
                        @error('phone')
                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                                <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full py-3.5 bg-brand-primary text-white font-bold text-xs uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 transition-all duration-200">
                            Lưu Thông Tin
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Đổi mật khẩu -->
            <div class="bg-brand-dark/50 p-8 border border-white/10 shadow-xl backdrop-blur-sm space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-white uppercase tracking-wide border-l-4 border-brand-primary pl-3">Đổi Mật Khẩu</h2>
                    <p class="text-xs text-gray-400 mt-1">Thay đổi mật khẩu đăng nhập để bảo vệ tài khoản.</p>
                </div>

                @if(session('success_password'))
                    <div class="p-4 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
                        {{ session('success_password') }}
                    </div>
                @endif

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Mật khẩu hiện tại -->
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Mật khẩu hiện tại</label>
                        <div class="mt-1 relative">
                            <input id="current_password" name="current_password" type="password" 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('current_password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   placeholder="••••••••">
                        </div>
                        @error('current_password')
                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                                <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Mật khẩu mới -->
                    <div>
                        <label for="new_password" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Mật khẩu mới</label>
                        <div class="mt-1 relative">
                            <input id="new_password" name="new_password" type="password" 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300 @error('new_password') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror" 
                                   placeholder="••••••••">
                        </div>
                        @error('new_password')
                            <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1 font-medium">
                                <span class="material-symbols-outlined text-xs">error</span> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Xác nhận mật khẩu mới -->
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-semibold text-gray-300 uppercase tracking-wider">Xác nhận mật khẩu mới</label>
                        <div class="mt-1 relative">
                            <input id="new_password_confirmation" name="new_password_confirmation" type="password" 
                                   class="w-full pl-4 pr-4 py-3 bg-white/5 border border-white/10 text-sm text-white rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary transition-all duration-300" 
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full py-3.5 bg-brand-primary text-white font-bold text-xs uppercase tracking-wider hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/20 transition-all duration-200">
                            Cập Nhật Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        const preview = document.getElementById('avatar-preview');
        const placeholder = document.getElementById('avatar-placeholder');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
