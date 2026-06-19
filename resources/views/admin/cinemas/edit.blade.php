@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Rạp Chiếu - FilmGo')

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
                <span class="text-outline">Chỉnh Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Rạp Chiếu</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl space-y-6">

            {{-- Info Badge --}}
            <div class="flex items-center gap-3 p-4 bg-primary-fixed text-on-primary-fixed rounded-lg border border-primary-fixed-dim/20">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                <span class="font-body-md text-body-md">
                    Đang chỉnh sửa: <strong class="text-primary">{{ $cinema->name }}</strong>
                    &nbsp;·&nbsp;
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                        <span class="material-symbols-outlined" style="font-size: 14px;">meeting_room</span>
                        {{ $cinema->rooms_count }} phòng chiếu
                    </span>
                </span>
            </div>

            <form action="{{ route('admin.cinemas.update', $cinema) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')

                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">Tên Rạp Chiếu <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $cinema->name) }}" maxlength="255"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror">
                    @error('name')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="city" class="block font-label-md text-label-md text-on-surface">Thành Phố <span class="text-error">*</span></label>
                    <input type="text" id="city" name="city" value="{{ old('city', $cinema->city) }}" maxlength="100"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('city') border-error @enderror">
                    @error('city')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="address" class="block font-label-md text-label-md text-on-surface">Địa Chỉ <span class="text-error">*</span></label>
                    <textarea id="address" name="address" rows="2" maxlength="500"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('address') border-error @enderror">{{ old('address', $cinema->address) }}</textarea>
                    @error('address')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="phone" class="block font-label-md text-label-md text-on-surface">Số Điện Thoại <span class="text-error">*</span></label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $cinema->phone) }}" maxlength="20"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('phone') border-error @enderror">
                    @error('phone')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="status" class="block font-label-md text-label-md text-on-surface">Trạng Thái <span class="text-error">*</span></label>
                    <select id="status" name="status"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        <option value="active" @selected(old('status', $cinema->status) === 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status', $cinema->status) === 'inactive')>Ngừng hoạt động</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span> Cập Nhật
                    </button>
                    <a href="{{ route('admin.cinemas.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>

            {{-- Xóa nhanh --}}
            <div class="pt-6 border-t border-outline-variant/30">
                @if($cinema->rooms_count === 0)
                    <form action="{{ route('admin.cinemas.destroy', $cinema) }}" method="POST"
                        onsubmit="return confirm('Xóa rạp «{{ $cinema->name }}»? Hành động này không thể hoàn tác!')">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors font-label-md text-label-md px-4 py-2.5 rounded-lg flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span> Xóa Rạp Này
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        <span class="material-symbols-outlined text-amber-500" style="font-size: 16px;">lock</span>
                        <span>Không thể xóa rạp đang có <strong>{{ $cinema->rooms_count }} phòng chiếu</strong> liên kết.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
