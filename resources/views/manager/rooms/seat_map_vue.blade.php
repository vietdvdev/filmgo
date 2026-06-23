@extends('layouts.manager')

@section('title', 'Thiết Lập Sơ Đồ Ghế — ' . $room->room_name)

{{-- ═══════════════════════════════════════════════════════════════════════
     STYLES — Layout manager đã có meta[name="csrf-token"] ở dòng 6.
     Vite bundle cho SeatMapBuilder.vue được nhúng tại đây.
═══════════════════════════════════════════════════════════════════════ --}}
@section('styles')
    {{--
        Nhúng Vite bundle: CSS scoped của SeatMapBuilder.vue + Vue runtime.
        Khi dev: Vite HMR; khi production: file hash từ public/build/manifest.json.
    --}}
    @vite(['resources/js/seat-map.js'])
@endsection

@section('content')
<div class="space-y-6">

    {{-- ── Breadcrumb & Header ───────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between
                border-b border-slate-200 pb-4 gap-4">
        <div>
            {{-- Breadcrumb --}}
            <div class="flex items-center gap-1.5 text-xs text-slate-500 font-semibold
                        uppercase tracking-wider mb-1">
                <a href="{{ route('manager.rooms.index') }}"
                   class="hover:text-blue-600 transition-colors">
                    Phòng Chiếu
                </a>
                <span class="material-symbols-outlined text-[10px]">arrow_forward_ios</span>
                <span class="text-slate-800">{{ $room->room_name }}</span>
                <span class="material-symbols-outlined text-[10px]">arrow_forward_ios</span>
                <span class="text-blue-600">Thiết Lập Sơ Đồ Ghế</span>
            </div>

            <h1 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">
                Sơ Đồ Ghế: {{ $room->room_name }}
            </h1>

            <p class="text-sm text-slate-500 mt-0.5">
                Loại phòng:
                <span class="font-semibold text-blue-600">{{ $room->room_type }}</span>
                &nbsp;·&nbsp; Sức chứa hiện tại:
                <span class="font-semibold text-slate-700">{{ $room->capacity }} ghế</span>
                &nbsp;·&nbsp; Rạp:
                <span class="font-semibold text-slate-700">{{ $room->cinema->name ?? '—' }}</span>
            </p>
        </div>

        <a href="{{ route('manager.rooms.index') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800
                  hover:bg-slate-700 text-white text-sm font-semibold
                  rounded-none transition-colors self-start md:self-auto flex-shrink-0">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Quay Lại
        </a>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4
                    bg-emerald-50 text-emerald-800
                    border border-emerald-200 text-sm font-semibold">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-center gap-3 p-4
                    bg-red-50 text-red-800
                    border border-red-200 text-sm font-semibold">
            <span class="material-symbols-outlined text-red-600">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════
         VUE MOUNT POINT — SeatMapBuilder Component
         ──────────────────────────────────────────────────────────────────
         seat-map.js đọc:
           • data-room-id    → prop roomId  (dùng trong URL: /manager/rooms/{id}/sync-seats)
           • data-seat-types → prop seatTypes (mảng loại ghế để render legend & ánh xạ màu)
           • data-seats      → prop initialSeats (ghế hiện có để phục hồi sơ đồ khi vào trang)

         JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT:
           Escape ký tự đặc biệt HTML trong JSON để tránh lỗi XSS khi nhúng
           dữ liệu trực tiếp vào thuộc tính HTML.
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border border-slate-200 shadow-sm p-6 rounded-none">

        {{--
            #seat-map-app là mount selector trong resources/js/seat-map.js.
            Nội dung bên trong (skeleton) sẽ bị Vue replace sau khi mount xong.
        --}}
        <div
            id="seat-map-app"
            data-room-id="{{ $room->id }}"
            data-sync-url="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() . '/manager/rooms/' . $room->id . '/sync-seats' }}"
            data-seat-types="{{ json_encode($seatTypes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
            data-seats="{{ json_encode($room->seats, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
        >
            {{-- Skeleton — hiển thị trong khi JS bundle chưa tải xong --}}
            <div class="flex flex-col items-center justify-center py-24 gap-4
                        text-slate-400 animate-pulse">
                <span class="material-symbols-outlined text-5xl">event_seat</span>
                <p class="text-sm font-medium">Đang khởi tạo trình thiết lập sơ đồ ghế...</p>
            </div>
        </div>

    </div>

    {{-- ── Hướng dẫn sử dụng ────────────────────────────────────────────── --}}
    <details class="bg-blue-50 border border-blue-200 rounded-none overflow-hidden
                    text-sm text-blue-900">
        <summary class="flex items-center gap-2 px-4 py-3 cursor-pointer font-semibold
                        hover:bg-blue-100 transition-colors select-none text-blue-800">
            <span class="material-symbols-outlined text-[18px] text-blue-600">help_outline</span>
            Hướng dẫn sử dụng Trình Thiết Lập Sơ Đồ Ghế
        </summary>

        <div class="px-5 py-4 space-y-2 border-t border-blue-200">
            <p><strong>1. Cấu hình lưới:</strong>
               Nhập số hàng (A–Z, tối đa 26) và số cột (tối đa 99), nhấn
               <em>"Áp dụng lưới"</em>.</p>
            <p><strong>2. Chọn loại tô:</strong>
               Chọn loại ghế mong muốn (Thường / VIP / Sweetbox / Lối đi)
               trong thanh công cụ.</p>
            <p><strong>3. Vẽ sơ đồ:</strong>
               Click từng ô để tô. Click nhiều lần để chuyển vòng:
               <em>Loại ghế → Bảo trì → Lối đi → Loại ghế...</em></p>
            <p><strong>4. Lưu:</strong>
               Nhấn <em>"Lưu sơ đồ"</em> để đồng bộ lên server. Thao tác này
               sẽ <strong>xóa ghế cũ và ghi dữ liệu mới</strong> vào cơ sở dữ liệu,
               đồng thời cập nhật <em>capacity</em> của phòng chiếu.</p>
            <p class="font-semibold text-red-700">
                ⚠ Không thể lưu nếu phòng đang có vé đã đặt hoặc ghế đang được giữ chỗ.
            </p>
        </div>
    </details>

</div>
@endsection

@section('scripts')
{{--
    Không cần thêm gì ở đây vì:
    - @vite() ở section 'styles' đã tự thêm <script type="module">
    - CSRF token đã có trong <meta name="csrf-token"> tại layouts/manager.blade.php dòng 6
    - seat-map.js tự đọc: document.querySelector('meta[name="csrf-token"]').content
--}}
@endsection
