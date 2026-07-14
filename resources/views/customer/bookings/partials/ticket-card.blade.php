@php
    // Chuẩn bị dữ liệu từ booking để hiển thị thông tin vé điện tử một cách nhất quán.
    $showtime = $booking->showtime;
    $movieTitle = $showtime?->movie?->title ?? 'Phim đang được cập nhật';
    $cinemaName = $showtime?->room?->cinema?->name ?? 'Rạp chiếu phim';
    $roomName = $showtime?->room?->room_name ?? 'Phòng chiếu';
    $showDate = $showtime?->show_date ? \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') : 'Đang cập nhật';
    $showTime = $showtime?->start_time ? \Carbon\Carbon::parse($showtime->start_time)->format('H:i') : 'Đang cập nhật';

    // Lấy danh sách ghế để hiển thị rõ ràng trên thẻ vé.
    $seatLabels = $booking->bookingDetails->map(function ($detail) {
        return $detail->showtimeSeat?->seat
            ? $detail->showtimeSeat->seat->seat_row . $detail->showtimeSeat->seat->seat_number
            : 'N/A';
    })->values();

    // Tạo chuỗi dữ liệu để sinh mã QR từ thông tin booking hiện tại.
    $qrPayload = implode('|', [
        'FilmGo',
        $booking->booking_code,
        $movieTitle,
        $showDate . ' ' . $showTime,
        $cinemaName . ' - ' . $roomName,
        $seatLabels->join(', '),
    ]);

    // Dùng endpoint QR miễn phí để tạo ảnh QR động cho vé điện tử.
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($qrPayload);
@endphp

{{-- Container chính của thẻ vé điện tử, có bố cục dọc tối ưu cho màn hình di động. --}}
<div class="mx-auto w-full max-w-md rounded-[30px] border border-slate-200 bg-white p-4 shadow-[0_20px_70px_-24px_rgba(15,23,42,0.45)] sm:p-6">
    {{-- Phần đầu vé: hiển thị thông tin phim và các trường dữ liệu chính. --}}
    <div class="rounded-[24px] bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-500 p-5 text-white">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.35em] text-white/80">Vé điện tử</p>
                <h3 class="mt-2 text-lg font-black leading-snug">{{ $movieTitle }}</h3>
            </div>
            <div class="rounded-full border border-white/30 bg-white/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.25em]">
                FilmGo
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70">Ngày chiếu</p>
                <p class="mt-1 font-bold">{{ $showDate }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70">Giờ chiếu</p>
                <p class="mt-1 font-bold">{{ $showTime }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70">Phòng chiếu</p>
                <p class="mt-1 font-bold">{{ $roomName }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70">Số ghế</p>
                <p class="mt-1 font-bold">{{ $seatLabels->join(', ') }}</p>
            </div>
        </div>

        <div class="mt-4 rounded-2xl border border-white/20 bg-black/10 p-3">
            <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-white/70">Mã đơn hàng</p>
            <p class="mt-1 text-base font-black tracking-[0.2em]">{{ $booking->booking_code }}</p>
        </div>
    </div>

    {{-- Đường ngăn cách dạng nét đứt để tạo cảm giác giống vé giấy thật. --}}
    <div class="relative my-4">
        <div class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 border-t border-dashed border-slate-300"></div>
        <div class="absolute left-0 top-1/2 h-6 w-6 -translate-y-1/2 rounded-full bg-slate-50"></div>
        <div class="absolute right-0 top-1/2 h-6 w-6 -translate-y-1/2 rounded-full bg-slate-50"></div>
    </div>

    {{-- Phần dưới vé: hiển thị mã QR nổi bật ở giữa để nhân viên quét nhanh. --}}
    <div class="rounded-[24px] border border-slate-100 bg-slate-50/80 p-4 text-center sm:p-6">
        <p class="text-[10px] font-black uppercase tracking-[0.35em] text-slate-500">Mã QR vé điện tử</p>
        <div class="mx-auto mt-4 flex h-56 w-56 items-center justify-center overflow-hidden rounded-3xl border border-slate-200 bg-white p-3 shadow-inner sm:h-60 sm:w-60">
            <img src="{{ $qrUrl }}" alt="Mã QR vé điện tử FilmGo" class="h-full w-full object-contain" />
        </div>
        <p class="mt-4 text-sm font-semibold text-slate-600">Xuất trình mã QR này cho nhân viên rạp khi vào cửa.</p>
    </div>
</div>
