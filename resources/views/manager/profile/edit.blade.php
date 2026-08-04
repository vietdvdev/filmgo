@extends('layouts.manager')

@section('title', 'Tài Khoản Cá Nhân - FilmGo Manager')

@section('content')
<div class="p-8 max-w-6xl mx-auto space-y-8">

    {{-- Header section --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Tài Khoản Cá Nhân</h1>
            <p class="text-xs text-slate-500 mt-1">Cập nhật thông tin cá nhân và cài đặt bảo mật cho tài khoản Quản lý Rạp.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold rounded-none">
                <span class="material-symbols-outlined text-sm">domain</span>
                Cinema Manager
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Cột trái: Thông tin tổng quan & Avatar --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-none p-6 border-2 border-slate-200 shadow-sm text-center">
                {{-- Avatar Preview --}}
                <div class="relative inline-block mb-4">
                    @if($user->avatar)
                        <img id="avatar-preview" src="{{ asset($user->avatar) }}" alt="{{ $user->full_name }}" class="w-28 h-28 object-cover border-4 border-blue-500 shadow-md rounded-none">
                    @else
                        <div id="avatar-preview-fallback" class="w-28 h-28 bg-blue-600 border-4 border-blue-500 flex items-center justify-center text-white font-black text-3xl shadow-md rounded-none">
                            {{ mb_substr($user->full_name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <h2 class="text-lg font-bold text-slate-900">{{ $user->full_name }}</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">{{ $user->email }}</p>

                <div class="mt-5 pt-4 border-t border-slate-200 space-y-2 text-left text-xs">
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Rạp quản lý:</span>
                        <span class="font-bold text-blue-600 truncate max-w-[150px]">{{ $cinema?->name ?? 'Chưa phân công' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Số điện thoại:</span>
                        <span class="font-semibold text-slate-800">{{ $user->phone ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Trạng thái:</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Đang hoạt động
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột phải: Form chỉnh sửa thông tin & Đổi mật khẩu --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Form 1: Cập nhật thông tin cá nhân --}}
            <div class="bg-white rounded-none p-6 border-2 border-slate-200 shadow-sm">
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-200">
                    <div class="w-9 h-9 bg-blue-600 text-white flex items-center justify-center rounded-none">
                        <span class="material-symbols-outlined text-lg">person</span>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 uppercase">Thông Tin Cá Nhân</h2>
                        <p class="text-xs text-slate-500">Cập nhật họ tên, số điện thoại và ảnh đại diện</p>
                    </div>
                </div>

                @if(session('success_profile'))
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 flex items-center gap-3 text-emerald-800 text-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span class="font-semibold">{{ session('success_profile') }}</span>
                    </div>
                @endif

                <form action="{{ route('manager.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Họ và tên --}}
                        <div>
                            <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Họ và Tên <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}"
                                   class="block w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-600 transition-colors rounded-none @error('full_name') border-red-400 @enderror" required>
                            @error('full_name')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Số điện thoại --}}
                        <div>
                            <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Số Điện Thoại
                            </label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="VD: 0988888888"
                                   class="block w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-600 transition-colors rounded-none @error('phone') border-red-400 @enderror">
                            @error('phone')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Email (Readonly) --}}
                    <div>
                        <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Địa Chỉ Email (Chỉ xem)
                        </label>
                        <input type="email" id="email" value="{{ $user->email }}" disabled
                               class="block w-full px-4 py-2.5 bg-slate-100 border-2 border-slate-200 text-sm font-medium text-slate-500 cursor-not-allowed rounded-none">
                    </div>

                    {{-- Upload Ảnh đại diện --}}
                    <div>
                        <label for="avatar" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Ảnh Đại Diện
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(event)"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors cursor-pointer rounded-none">
                        <p class="text-[11px] text-slate-400 mt-1">Định dạng hỗ trợ: JPG, PNG, GIF, WEBP. Dung lượng tối đa: 2MB.</p>
                        @error('avatar')
                            <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-xs font-bold uppercase tracking-wider transition-colors rounded-none">
                            <span class="material-symbols-outlined text-base">save</span>
                            Lưu Thay Đổi
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form 2: Đổi mật khẩu --}}
            <div class="bg-white rounded-none p-6 border-2 border-slate-200 shadow-sm">
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-slate-200">
                    <div class="w-9 h-9 bg-amber-500 text-white flex items-center justify-center rounded-none">
                        <span class="material-symbols-outlined text-lg">lock</span>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 uppercase">Đổi Mật Khẩu</h2>
                        <p class="text-xs text-slate-500">Cập nhật mật khẩu để nâng cao tính bảo mật cho tài khoản</p>
                    </div>
                </div>

                @if(session('success_password'))
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 flex items-center gap-3 text-emerald-800 text-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span class="font-semibold">{{ session('success_password') }}</span>
                    </div>
                @endif

                <form action="{{ route('manager.profile.password') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Mật khẩu hiện tại --}}
                    <div>
                        <label for="current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Mật Khẩu Hiện Tại <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="current_password" name="current_password" required
                               class="block w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-600 transition-colors rounded-none @error('current_password') border-red-400 @enderror">
                        @error('current_password')
                            <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Mật khẩu mới --}}
                        <div>
                            <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Mật Khẩu Mới <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password" name="new_password" required
                                   class="block w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-600 transition-colors rounded-none @error('new_password') border-red-400 @enderror">
                            @error('new_password')
                                <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Xác nhận mật khẩu mới --}}
                        <div>
                            <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Xác Nhận Mật Khẩu Mới <span class="text-red-500">*</span>
                            </label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                                   class="block w-full px-4 py-2.5 bg-white border-2 border-slate-200 text-sm font-medium text-slate-900 focus:outline-none focus:border-blue-600 transition-colors rounded-none">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-bold uppercase tracking-wider transition-colors rounded-none">
                            <span class="material-symbols-outlined text-base">key</span>
                            Cập Nhật Mật Khẩu
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

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
                    fallback.outerHTML = `<img id="avatar-preview" src="${e.target.result}" class="w-28 h-28 object-cover border-4 border-blue-500 shadow-md rounded-none">`;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
