@extends('layouts.admin')

@section('title', 'Thêm Rạp Chiếu Mới - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

        {{-- Breadcrumb --}}
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.cinemas.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">theater_comedy</span> Quản Lý Rạp
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Thêm Mới</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Thêm Rạp Chiếu Mới</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl">
            <form action="{{ route('admin.cinemas.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">Tên Rạp Chiếu <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" maxlength="255"
                        placeholder="Ví dụ: FilmGo Hà Nội - Vincom Ba Đình"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror">
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="city" class="block font-label-md text-label-md text-on-surface">Thành Phố <span class="text-error">*</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city') }}" maxlength="100"
                        placeholder="Ví dụ: Hà Nội, Hồ Chí Minh..."
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('city') border-error @enderror">
                    @error('city')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="address" class="block font-label-md text-label-md text-on-surface">Địa Chỉ <span class="text-error">*</span></label>
                    <textarea id="address" name="address" rows="2" maxlength="500"
                        placeholder="Số nhà, tên đường, quận/huyện..."
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('address') border-error @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="phone" class="block font-label-md text-label-md text-on-surface">Số Điện Thoại <span class="text-error">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20"
                        placeholder="Ví dụ: 024 3123 4567"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('phone') border-error @enderror">
                    @error('phone')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="status" class="block font-label-md text-label-md text-on-surface">Trạng Thái <span class="text-error">*</span></label>
                    <select id="status" name="status"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        <option value="active" @selected(old('status', 'active') === 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Ngừng hoạt động</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Lưu Rạp Chiếu
                    </button>
                    <a href="{{ route('admin.cinemas.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
