@extends('layouts.admin')

@section('title', 'Thiết Lập Sơ Đồ Ghế — ' . $room->room_name . ' - FilmGo Admin')

{{-- ═══════════════════════════════════════════════════════════════════════
     HEAD STACK — CSRF token + Vite assets
     Được inject vào <head> của layouts/admin.blade.php qua @stack('head')
═══════════════════════════════════════════════════════════════════════ --}}
@push('head')
    {{--
        CSRF Token dạng <meta> — Axios đọc giá trị này để gắn vào header
        X-CSRF-TOKEN cho tất cả request POST/PUT/DELETE từ SeatMapBuilder.vue.
        Được set trong onMounted() của component:
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]')?.content
    --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{--
        Vite assets: nhúng bundle CSS + JS của SeatMapBuilder.vue.
        Khi chạy `npm run dev` thì đây là HMR script;
        khi chạy `npm run build` thì trỏ vào public/build/assets/*.
    --}}
    @vite(['resources/js/seat-map.js'])
@endpush

{{-- ═══════════════════════════════════════════════════════════════════════
     CONTENT — Nội dung trang
═══════════════════════════════════════════════════════════════════════ --}}
@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

        {{-- ── Breadcrumb ────────────────────────────────────────────────── --}}
        <nav class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant">
            <a href="{{ route('admin.cinemas.index') }}"
               class="hover:text-primary transition-colors">
                Rạp Chiếu
            </a>
            <span class="material-symbols-outlined text-[12px] text-outline">chevron_right</span>
            <span class="text-on-surface-variant">Phòng Chiếu</span>
            <span class="material-symbols-outlined text-[12px] text-outline">chevron_right</span>
            <span class="text-on-surface font-semibold">Sơ Đồ Ghế</span>
        </nav>

        {{-- ── Page Header ───────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4
                    pb-4 border-b border-outline-variant/20">
            <div>
                <h1 class="font-headline-md text-headline-md text-on-surface">
                    Thiết Lập Sơ Đồ Ghế
                </h1>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Phòng:
                    <strong class="text-on-surface">{{ $room->room_name }}</strong>
                    &nbsp;·&nbsp;
                    Loại: <span class="text-primary font-semibold">{{ $room->room_type }}</span>
                    &nbsp;·&nbsp;
                    Rạp: <span class="text-on-surface font-medium">{{ $room->cinema->name ?? '—' }}</span>
                </p>
            </div>

            <a href="{{ url()->previous() }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-outline-variant
                      text-on-surface font-label-md text-label-md rounded-lg
                      hover:bg-surface-container-low transition-colors self-start sm:self-auto">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Quay lại
            </a>
        </div>

        {{-- ── Flash messages ───────────────────────────────────────────── --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4
                        bg-emerald-50 text-emerald-800
                        border border-emerald-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-3 p-4
                        bg-red-50 text-red-800
                        border border-red-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="font-body-md text-body-md font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════
             VUE MOUNT POINT — SeatMapBuilder Component
             ─────────────────────────────────────────────────────────────
             Dữ liệu được truyền vào Vue qua data-* attributes:

             • data-room-id    : ID phòng chiếu (int), dùng để xây dựng
                                 URL API: /api/manager/rooms/{roomId}/seats/sync

             • data-seat-types : JSON array của bảng seat_types, bao gồm
                                 id, name, surcharge_price. Vue component
                                 parse chuỗi này thành array để render
                                 legend và ánh xạ state → seat_type_id.

             seat-map.js đọc hai attributes này trong onMounted() và
             truyền vào createApp(SeatMapBuilder, { roomId, seatTypes }).
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant
                    shadow-ambient-sm overflow-hidden p-stack-lg">

            {{--
                #seat-map-app là selector mà seat-map.js dùng để mount.
                JSON_ENCODE_UNESCAPED_UNICODE: giữ nguyên ký tự tiếng Việt
                trong tên loại ghế (Thường, VIP, Sweetbox).
            --}}
            <div
                id="seat-map-app"
                data-room-id="{{ $room->id }}"
                data-seat-types="{{ json_encode($seatTypes, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) }}"
            >
                {{-- Skeleton loading — hiển thị khi JS chưa mount xong --}}
                <div class="flex flex-col items-center justify-center py-20 gap-4
                            text-on-surface-variant animate-pulse">
                    <span class="material-symbols-outlined text-5xl text-outline-variant">
                        event_seat
                    </span>
                    <p class="font-body-md text-body-md">Đang tải trình thiết lập sơ đồ ghế...</p>
                </div>
            </div>

        </div>

        {{-- ── Hướng dẫn sử dụng (collapsible) ────────────────────────── --}}
        <details class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden">
            <summary class="flex items-center gap-2 px-4 py-3 cursor-pointer
                            font-label-md text-label-md text-blue-800
                            hover:bg-blue-100 transition-colors select-none">
                <span class="material-symbols-outlined text-[18px] text-blue-600">help</span>
                Hướng dẫn sử dụng Trình Thiết Lập Sơ Đồ Ghế
            </summary>
            <div class="px-5 py-4 text-body-md font-body-md text-blue-900 space-y-2
                        border-t border-blue-200 bg-blue-50/60">
                <p>
                    <strong>1. Cấu hình lưới:</strong>
                    Nhập số hàng (tối đa 26) và số cột (tối đa 99), sau đó nhấn
                    <em>"Áp dụng lưới"</em> để tạo ma trận ghế mới.
                </p>
                <p>
                    <strong>2. Chọn loại tô:</strong>
                    Nhấn vào loại ghế mong muốn trong thanh công cụ
                    (Thường / VIP / Sweetbox / Lối đi) để chọn "bút tô".
                </p>
                <p>
                    <strong>3. Vẽ sơ đồ:</strong>
                    Click vào từng ô để tô màu theo loại ghế đang chọn.
                    Click nhiều lần để chuyển trạng thái:
                    <em>Loại ghế → Bảo trì → Lối đi → ...</em>
                </p>
                <p>
                    <strong>4. Lưu sơ đồ:</strong>
                    Nhấn <em>"Lưu sơ đồ"</em> để đồng bộ toàn bộ cấu hình lên server.
                    Thao tác này sẽ xóa dữ liệu ghế cũ và ghi dữ liệu mới vào Database.
                </p>
                <p class="text-red-700 font-semibold">
                    ⚠ Không thể lưu nếu phòng chiếu đang có vé đã đặt hoặc ghế đang được giữ chỗ.
                </p>
            </div>
        </details>

    </div>
</main>
@endsection

{{-- ═══════════════════════════════════════════════════════════════════════
     SCRIPTS STACK — Không cần thêm gì ở đây vì @vite() đã đặt ở @push('head').
     Để lại section này làm nơi bổ sung JS tùy chỉnh trong tương lai nếu cần.
═══════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
{{--
    Vite tự động thêm `type="module"` vào script tag, đảm bảo Vue runtime
    (ESM) hoạt động đúng. Nếu cần thêm script inline tương tác với Vue app,
    cần đặt sau khi #seat-map-app đã được mount:

    document.getElementById('seat-map-app').__vue_app__  →  truy cập app instance
--}}
@endpush
