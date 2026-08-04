@extends('layouts.staff')

@section('title', 'Tài Khoản Cá Nhân - FilmGo')

@section('content')
<div class="p-8 max-w-6xl mx-auto space-y-8">

    {{-- Header section --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface tracking-tight">Tài Khoản Cá Nhân</h1>
            <p class="text-xs text-on-surface-variant mt-1">Cập nhật thông tin chi tiết và bảo mật tài khoản nhân viên của bạn.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                <span class="material-symbols-outlined text-sm">badge</span>
                Tài khoản Nhân Viên
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Cột trái: Thông tin tổng quan & Avatar --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant shadow-ambient-sm text-center">
                {{-- Avatar Preview --}}
                <div class="relative inline-block mb-4 group">
                    @if($user->avatar)
                        <img id="avatar-preview" src="{{ asset($user->avatar) }}" alt="{{ $user->full_name }}" class="w-28 h-28 rounded-full object-cover border-4 border-primary/20 shadow-md">
                    @else
                        <div id="avatar-preview-fallback" class="w-28 h-28 rounded-full bg-primary/10 border-4 border-primary/20 flex items-center justify-center text-primary font-bold text-3xl shadow-md">
                            {{ mb_substr($user->full_name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h2 class="text-lg font-bold text-on-surface">{{ $user->full_name }}</h2>
                <p class="text-xs text-on-surface-variant font-medium mt-0.5">{{ $user->email }}</p>

                <div class="mt-4 pt-4 border-t border-outline-variant/40 space-y-2 text-left text-xs">
                    <div class="flex items-center justify-between py-1">
                        <span class="text-on-surface-variant">Rạp làm việc:</span>
                        <span class="font-bold text-primary">{{ $cinema?->name ?? 'Chưa phân công' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-on-surface-variant">Số điện thoại:</span>
                        <span class="font-semibold text-on-surface">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-on-surface-variant">Trạng thái:</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Hoạt động
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột phải: Form chỉnh sửa thông tin & Đổi mật khẩu --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Form 1: Cập nhật thông tin cá nhân --}}
            <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant shadow-ambient-sm">
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-outline-variant/40">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">Thông Tin Cá Nhân</h2>
                        <p class="text-xs text-on-surface-variant">Cập nhật họ tên, số điện thoại và ảnh đại diện</p>
                    </div>
                </div>

                @if(session('success_profile'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 text-emerald-700 text-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span class="font-semibold">{{ session('success_profile') }}</span>
                    </div>
                @endif

                <form action="{{ route('staff.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Họ và tên --}}
                        <div>
                            <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                                Họ và Tên <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                                   class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-xl text-sm font-medium text-on-surface focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-200 @error('full_name') border-red-400 @enderror" required>
                            @error('full_name')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                                Số Điện Thoại
                            </label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="VD: 0987654321"
                                   class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-xl text-sm font-medium text-on-surface focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-200 @error('phone') border-red-400 @enderror">
                            @error('phone')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email (Readonly) --}}
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                            Địa Chỉ Email (Không thể thay đổi)
                        </label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled
                               class="w-full px-4 py-2.5 bg-surface-container/60 border border-outline-variant/60 rounded-xl text-sm font-medium text-on-surface-variant/80 cursor-not-allowed">
                    </div>

                    {{-- Upload Ảnh đại diện --}}
                    <div>
                        <label for="avatar" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                            Ảnh Đại Diện
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(event)"
                               class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-all duration-200 cursor-pointer">
                        <p class="text-[11px] text-on-surface-variant/70 mt-1">Định dạng chấp nhận: JPG, PNG, GIF, WEBP. Tối đa 2MB.</p>
                        @error('avatar')
                            <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-primary/90 active:bg-primary/95 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition-all duration-200">
                            <span class="material-symbols-outlined text-sm">save</span>
                            Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form 2: Đổi mật khẩu --}}
            <div class="bg-surface-container-lowest rounded-2xl p-6 border border-outline-variant shadow-ambient-sm">
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-outline-variant/40">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <span class="material-symbols-outlined">lock</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-on-surface">Đổi Mật Khẩu</h2>
                        <p class="text-xs text-on-surface-variant">Đảm bảo an toàn cho tài khoản bằng mật khẩu đủ mạnh</p>
                    </div>
                </div>

                @if(session('success_password'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 text-emerald-700 text-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span class="font-semibold">{{ session('success_password') }}</span>
                    </div>
                @endif

                <form action="{{ route('staff.profile.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Mật khẩu hiện tại --}}
                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                            Mật Khẩu Hiện Tại <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-xl text-sm font-medium text-on-surface focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-200 @error('current_password') border-red-400 @enderror">
                        @error('current_password')
                            <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Mật khẩu mới --}}
                        <div>
                            <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                                Mật Khẩu Mới <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password" name="new_password" required
                                   class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-xl text-sm font-medium text-on-surface focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-200 @error('new_password') border-red-400 @enderror">
                            @error('new_password')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Xác nhận mật khẩu mới --}}
                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-1.5">
                                Xác Nhận Mật Khẩu Mới <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                                   class="w-full px-4 py-2.5 bg-surface-container-low border border-outline-variant rounded-xl text-sm font-medium text-on-surface focus:bg-white focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-200">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition-all duration-200">
                            <span class="material-symbols-outlined text-sm">key</span>
                            Cập Nhật Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatar-preview');
                const fallback = document.getElementById('avatar-preview-fallback');
                if (img) {
                    img.src = e.target.result;
                } else if (fallback) {
                    fallback.outerHTML = `<img id="avatar-preview" src="${e.target.result}" class="w-28 h-28 rounded-full object-cover border-4 border-primary/20 shadow-md">`;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
