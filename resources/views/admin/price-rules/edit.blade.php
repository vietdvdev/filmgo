@extends('layouts.admin')

@section('title', 'Sửa Quy Tắc Giá - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.price-rules.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">local_offer</span> Quản Lý Quy Tắc Giá
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Sửa Quy Tắc Giá: {{ $priceRule->name }}</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-3xl">
            <form action="{{ route('admin.price-rules.update', $priceRule->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Tên quy tắc -->
                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">
                        Tên Quy Tắc Giá <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $priceRule->name) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror"
                        placeholder="Ví dụ: Giờ vàng cuối tuần, Suất chiếu sớm, Ngày lễ..."
                        maxlength="100"
                    >
                    @error('name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Ngày trong tuần -->
                    <div class="space-y-2">
                        <label for="day_of_week" class="block font-label-md text-label-md text-on-surface">
                            Ngày Trong Tuần <span class="text-error">*</span>
                        </label>
                        <select
                            id="day_of_week"
                            name="day_of_week"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('day_of_week') border-error @enderror"
                        >
                            <option value="">-- Chọn ngày --</option>
                            <option value="0" {{ old('day_of_week', $priceRule->day_of_week) == '0' ? 'selected' : '' }}>Chủ Nhật</option>
                            <option value="1" {{ old('day_of_week', $priceRule->day_of_week) == '1' ? 'selected' : '' }}>Thứ Hai</option>
                            <option value="2" {{ old('day_of_week', $priceRule->day_of_week) == '2' ? 'selected' : '' }}>Thứ Ba</option>
                            <option value="3" {{ old('day_of_week', $priceRule->day_of_week) == '3' ? 'selected' : '' }}>Thứ Tư</option>
                            <option value="4" {{ old('day_of_week', $priceRule->day_of_week) == '4' ? 'selected' : '' }}>Thứ Năm</option>
                            <option value="5" {{ old('day_of_week', $priceRule->day_of_week) == '5' ? 'selected' : '' }}>Thứ Sáu</option>
                            <option value="6" {{ old('day_of_week', $priceRule->day_of_week) == '6' ? 'selected' : '' }}>Thứ Bảy</option>
                        </select>
                        @error('day_of_week')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Mức điều chỉnh giá -->
                    <div class="space-y-2">
                        <label for="adjustment_amount" class="block font-label-md text-label-md text-on-surface">
                            Mức Điều Chỉnh Giá (VNĐ) <span class="text-error">*</span>
                        </label>
                        <input
                            type="number"
                            id="adjustment_amount"
                            name="adjustment_amount"
                            value="{{ old('adjustment_amount', $priceRule->adjustment_amount) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('adjustment_amount') border-error @enderror"
                            placeholder="0"
                            step="1000"
                        >
                        <p class="text-xs text-on-surface-variant">
                            Dương (+): Tăng giá | Âm (-): Giảm giá | 0: Không thay đổi
                        </p>
                        @error('adjustment_amount')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Giờ bắt đầu -->
                    <div class="space-y-2">
                        <label for="start_time" class="block font-label-md text-label-md text-on-surface">
                            Giờ Bắt Đầu <span class="text-error">*</span>
                        </label>
                        <input
                            type="time"
                            id="start_time"
                            name="start_time"
                            value="{{ old('start_time', \Carbon\Carbon::createFromFormat('H:i:s', $priceRule->start_time)->format('H:i')) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('start_time') border-error @enderror"
                        >
                        @error('start_time')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Giờ kết thúc -->
                    <div class="space-y-2">
                        <label for="end_time" class="block font-label-md text-label-md text-on-surface">
                            Giờ Kết Thúc <span class="text-error">*</span>
                        </label>
                        <input
                            type="time"
                            id="end_time"
                            name="end_time"
                            value="{{ old('end_time', \Carbon\Carbon::createFromFormat('H:i:s', $priceRule->end_time)->format('H:i')) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('end_time') border-error @enderror"
                        >
                        @error('end_time')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Trạng thái -->
                <div class="space-y-2">
                    <label class="block font-label-md text-label-md text-on-surface">
                        Trạng Thái Hoạt Động
                    </label>
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', $priceRule->is_active) ? 'checked' : '' }} class="w-4 h-4">
                            <span class="font-body-md text-body-md text-on-surface">✓ Hoạt động (áp dụng quy tắc này)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="0" {{ old('is_active', $priceRule->is_active) === 0 ? 'checked' : '' }} class="w-4 h-4">
                            <span class="font-body-md text-body-md text-on-surface">✗ Tắt (không áp dụng)</span>
                        </label>
                    </div>
                </div>

                <!-- Info box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <span class="material-symbols-outlined text-blue-600">info</span>
                        <div class="text-sm text-blue-800">
                            <strong>Quy tắc hiện tại:</strong>
                            <ul class="list-disc list-inside mt-2 space-y-1">
                                <li>Giờ: {{ \Carbon\Carbon::createFromFormat('H:i:s', $priceRule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $priceRule->end_time)->format('H:i') }}</li>
                                <li>Điều chỉnh: {{ $priceRule->adjustment_amount > 0 ? '+' : '' }}{{ number_format($priceRule->adjustment_amount) }}₫</li>
                                <li>Trạng thái: {{ $priceRule->is_active ? 'Hoạt động' : 'Tắt' }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Cập Nhật
                    </button>
                    <a href="{{ route('admin.price-rules.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection
