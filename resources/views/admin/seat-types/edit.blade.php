@extends('layouts.admin')

@section('title', 'Sửa Loại Ghế - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.seat-types.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">event_seat</span> Quản Lý Loại Ghế
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Sửa Loại Ghế: {{ $seatType->name }}</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl">
            <form action="{{ route('admin.seat-types.update', $seatType->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Tên loại ghế -->
                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">
                        Tên Loại Ghế <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $seatType->name) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror"
                        placeholder="Ví dụ: Thường, VIP, Sweetbox..."
                        maxlength="50"
                        oninput="updateCharCount(this, 'nameCount', 50)"
                    >
                    <div class="flex justify-between items-center text-xs text-on-surface-variant">
                        <span>Loại ghế cần đặt tên phân biệt</span>
                        <div><span id="nameCount">{{ strlen(old('name', $seatType->name)) }}</span>/50</div>
                    </div>
                    @error('name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Giá phụ thu -->
                <div class="space-y-2">
                    <label for="surcharge_price" class="block font-label-md text-label-md text-on-surface">
                        Giá Phụ Thu (VNĐ) <span class="text-error">*</span>
                    </label>
                    <input
                        type="number"
                        id="surcharge_price"
                        name="surcharge_price"
                        value="{{ old('surcharge_price', $seatType->surcharge_price) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('surcharge_price') border-error @enderror"
                        placeholder="0"
                        min="0"
                    >
                    <p class="text-xs text-on-surface-variant">Giá phụ thu cộng thêm so với giá vé cơ bản</p>
                    @error('surcharge_price')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Thông tin thêm -->
                <div class="bg-surface-container/50 rounded-lg p-4 border border-outline-variant/40">
                    <p class="text-xs text-on-surface-variant">
                        <strong>Thống kê:</strong> Loại ghế này đang được sử dụng bởi <strong>{{ $seatType->seats_count }}</strong> ghế trong các phòng chiếu.
                    </p>
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Cập Nhật
                    </button>
                    <a href="{{ route('admin.seat-types.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function updateCharCount(input, countId, max) {
        document.getElementById(countId).textContent = input.value.length;
    }
</script>
@endsection
