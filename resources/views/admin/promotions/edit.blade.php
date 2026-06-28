@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Khuyến Mãi - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.promotions.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">sell</span> Quản Lý Khuyến Mãi
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Chỉnh Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Mã Khuyến Mãi</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl">
            <form action="{{ route('admin.promotions.update', $promotion->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Mã Code -->
                <div class="space-y-2">
                    <label for="code" class="block font-label-md text-label-md text-on-surface">
                        Mã Code <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        value="{{ old('code', $promotion->code) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors uppercase @error('code') border-error @enderror"
                        placeholder="Ví dụ: SALE50, MOVIE2026..."
                        maxlength="50"
                        required
                    >
                    <p class="text-xs text-on-surface-variant">Mã nhập của khách hàng khi thanh toán (không dấu, không chứa khoảng trắng, viết hoa tự động)</p>
                    @error('code')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Loại giảm giá -->
                    <div class="space-y-2">
                        <label for="discount_type" class="block font-label-md text-label-md text-on-surface">
                            Loại Giảm Giá <span class="text-error">*</span>
                        </label>
                        <select
                            id="discount_type"
                            name="discount_type"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('discount_type') border-error @enderror"
                            required
                        >
                            <option value="percent" {{ old('discount_type', $promotion->discount_type) == 'percent' ? 'selected' : '' }}>Giảm theo phần trăm (%)</option>
                            <option value="fixed" {{ old('discount_type', $promotion->discount_type) == 'fixed' ? 'selected' : '' }}>Giảm tiền mặt cố định (đ)</option>
                        </select>
                        @error('discount_type')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Giá trị giảm -->
                    <div class="space-y-2">
                        <label for="discount_value" class="block font-label-md text-label-md text-on-surface">
                            Giá Trị Giảm <span class="text-error">*</span>
                        </label>
                        <input
                            type="number"
                            id="discount_value"
                            name="discount_value"
                            value="{{ old('discount_value', $promotion->discount_value) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('discount_value') border-error @enderror"
                            min="1"
                            required
                        >
                        <p class="text-xs text-on-surface-variant" id="discount_value_help">Nhập phần trăm giảm (ví dụ: 10, 20...) hoặc số tiền giảm (ví dụ: 20000, 50000...)</p>
                        @error('discount_value')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Giá trị đơn hàng tối thiểu -->
                    <div class="space-y-2">
                        <label for="min_order_amount" class="block font-label-md text-label-md text-on-surface">
                            Đơn Hàng Tối Thiểu (VNĐ) <span class="text-error">*</span>
                        </label>
                        <input
                            type="number"
                            id="min_order_amount"
                            name="min_order_amount"
                            value="{{ old('min_order_amount', $promotion->min_order_amount) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('min_order_amount') border-error @enderror"
                            min="0"
                            required
                        >
                        <p class="text-xs text-on-surface-variant">Đơn hàng đạt giá trị tối thiểu này mới được áp dụng mã (để 0 nếu không yêu cầu)</p>
                        @error('min_order_amount')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lượt dùng tối đa mỗi user -->
                    <div class="space-y-2">
                        <label for="max_uses_per_user" class="block font-label-md text-label-md text-on-surface">
                            Số Lần Dùng Tối Đa / User <span class="text-error">*</span>
                        </label>
                        <input
                            type="number"
                            id="max_uses_per_user"
                            name="max_uses_per_user"
                            value="{{ old('max_uses_per_user', $promotion->max_uses_per_user) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('max_uses_per_user') border-error @enderror"
                            min="1"
                            required
                        >
                        <p class="text-xs text-on-surface-variant">Số lần tối đa một tài khoản khách hàng được dùng mã này</p>
                        @error('max_uses_per_user')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tổng số lượng phát hành -->
                    <div class="space-y-2">
                        <label for="quantity" class="block font-label-md text-label-md text-on-surface">
                            Tổng Số Lượng Phát Hành
                        </label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="{{ old('quantity', $promotion->quantity) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('quantity') border-error @enderror"
                            placeholder="Không giới hạn"
                            min="1"
                        >
                        <p class="text-xs text-on-surface-variant">Tổng số lượt sử dụng mã khuyến mãi này trên toàn hệ thống (để trống nếu không giới hạn)</p>
                        @error('quantity')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Trạng thái -->
                    <div class="space-y-2">
                        <label for="status" class="block font-label-md text-label-md text-on-surface">
                            Trạng Thái Hoạt Động <span class="text-error">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('status') border-error @enderror"
                            required
                        >
                            <option value="active" {{ old('status', $promotion->status) == 'active' ? 'selected' : '' }}>Hoạt động (Active)</option>
                            <option value="inactive" {{ old('status', $promotion->status) == 'inactive' ? 'selected' : '' }}>Ngưng hoạt động (Inactive)</option>
                        </select>
                        @error('status')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Thời gian bắt đầu -->
                    <div class="space-y-2">
                        <label for="start_date" class="block font-label-md text-label-md text-on-surface">
                            Ngày Bắt Đầu Áp Dụng <span class="text-error">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            id="start_date"
                            name="start_date"
                            value="{{ old('start_date', $promotion->start_date ? $promotion->start_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('start_date') border-error @enderror"
                            required
                        >
                        @error('start_date')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Thời gian kết thúc -->
                    <div class="space-y-2">
                        <label for="end_date" class="block font-label-md text-label-md text-on-surface">
                            Ngày Kết Thúc Áp Dụng <span class="text-error">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            id="end_date"
                            name="end_date"
                            value="{{ old('end_date', $promotion->end_date ? $promotion->end_date->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('end_date') border-error @enderror"
                            required
                        >
                        @error('end_date')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nút Submit/Hủy -->
                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button
                        type="submit"
                        class="bg-primary text-on-primary font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200"
                    >
                        Cập Nhật
                    </button>
                    <a
                        href="{{ route('admin.promotions.index') }}"
                        class="bg-surface-container-high text-on-surface font-label-md text-label-md px-6 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors"
                    >
                        Hủy Bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('discount_type');
        const valueHelp = document.getElementById('discount_value_help');

        function updateHelpText() {
            if (typeSelect.value === 'percent') {
                valueHelp.textContent = 'Nhập phần trăm giảm (ví dụ: 10, 20...). Tối đa 100%.';
            } else {
                valueHelp.textContent = 'Nhập số tiền giảm bằng VNĐ (ví dụ: 20000, 50000...).';
            }
        }

        typeSelect.addEventListener('change', updateHelpText);
        updateHelpText();
    });
</script>
@endsection
