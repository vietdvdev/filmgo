@extends('layouts.staff')

@section('title', 'Quản Lý Vé Khách Đặt - FilmGo Staff')

@section('content')
<div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">

    {{-- ── 1. HEADER & TOP BAR (BỘ LỌC NGÀY CHIẾU) ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            {{-- Tiêu đề & Thông tin rạp --}}
            <div>
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h5m-5 0v-4m0 4h5m-5 0v-4" />
                    </svg>
                    <span>Rạp: {{ $cinema->name ?? 'Phân công nhân viên' }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white mt-1">
                    Quản Lý Vé Khách Đặt
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Tra cứu, kiểm tra thông tin vé đặt và xuất mã QR/PDF cho khách hàng.
                </p>
            </div>

            {{-- Form lọc theo ngày chiếu, tìm kiếm mã đơn, trạng thái in vé --}}
            <form method="GET" action="{{ route('staff.bookings.index') }}" class="flex flex-col lg:flex-row lg:items-end gap-3 w-full">
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl px-3.5 py-2">
                    <label for="date" class="text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">Ngày chiếu:</label>
                    <input type="date" id="date" name="date" value="{{ $selectedDate }}" class="bg-transparent text-sm font-medium text-gray-900 dark:text-white focus:outline-none" />
                </div>

                <div class="flex-1 min-w-[190px]">
                    <label for="booking_code" class="sr-only">Mã đơn</label>
                    <input
                        type="text"
                        id="booking_code"
                        name="booking_code"
                        value="{{ $filters['booking_code'] ?? '' }}"
                        placeholder="Tìm mã đơn"
                        class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none"
                    />
                </div>



                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 dark:shadow-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Lọc dữ liệu</span>
                    </button>
                    @if($selectedDate !== now()->toDateString() || !empty($filters['booking_code']) || !empty($filters['print_status']))
                        <a href="{{ route('staff.bookings.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-sm font-medium rounded-xl transition-colors">Xóa lọc</a>
                    @endif
                </div>
            </form>

        </div>
    </div>

    {{-- ── 2. DATA TABLE (BẢNG DANH SÁCH VÉ) ── --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs font-bold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="py-4 px-5">Mã đơn</th>
                        <th class="py-4 px-5">Khách hàng</th>
                        <th class="py-4 px-5">Phim & Suất chiếu</th>
                        <th class="py-4 px-5">Danh sách ghế</th>
                        <th class="py-4 px-5 text-center">Trạng thái thanh toán</th>
                        <th class="py-4 px-5 text-center">Trạng thái in vé</th>
                        <th class="py-4 px-5 text-center">Trạng thái đơn</th>
                        <th class="py-4 px-5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-gray-700/40 transition-colors"
                            data-booking-id="{{ $booking->id }}"
                            data-booking-code="{{ $booking->booking_code }}">
                            
                            {{-- Mã đơn --}}
                            <td class="py-4 px-5">
                                <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400 text-base">
                                    {{ $booking->booking_code }}
                                </span>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $booking->created_at?->format('H:i - d/m/Y') }}
                                </div>
                            </td>

                            {{-- Khách hàng --}}
                            <td class="py-4 px-5">
                                @if($booking->user)
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $booking->user->full_name }}
                                    </p>
                                    @if($booking->user->phone)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            {{ $booking->user->phone }}
                                        </p>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium rounded-lg text-xs">
                                        Khách vãng lai
                                    </span>
                                @endif
                            </td>

                            {{-- Phim & Suất chiếu --}}
                            <td class="py-4 px-5">
                                <p class="font-bold text-gray-900 dark:text-white line-clamp-1">
                                    {{ $booking->showtime?->movie?->title ?? 'N/A' }}
                                </p>
                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 font-bold rounded border border-indigo-200 dark:border-indigo-800">
                                        {{ $booking->showtime?->room?->room_name ?? 'Phòng N/A' }}
                                    </span>
                                    @if($booking->showtime?->start_time)
                                        <span class="font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-0.5">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Danh sách Ghế --}}
                            <td class="py-4 px-5">
                                <div class="flex flex-wrap gap-1 max-w-[220px]">
                                    @forelse($booking->bookingDetails as $detail)
                                         @php
                                             $seat = $detail->showtimeSeat?->seat;
                                             $seatNum = null;
                                             if ($seat) {
                                                 $row = trim($seat->seat_row ?? '');
                                                 $num = trim((string) ($seat->seat_number ?? ''));
                                                 $seatNum = (!empty($row) && !str_starts_with(strtoupper($num), strtoupper($row)))
                                                     ? strtoupper($row) . $num
                                                     : strtoupper($num);
                                             }
                                         @endphp
                                         @if($seatNum)
                                             <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-bold rounded text-xs border border-gray-300 dark:border-gray-600">
                                                 {{ $seatNum }}
                                             </span>
                                         @endif
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Không có ghế</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Trạng thái Thanh toán --}}
                            <td class="py-4 px-5 text-center">
                                @php
                                    $pStatus = strtolower($booking->payment_status ?? 'pending');
                                @endphp

                                @if(in_array($pStatus, ['paid', 'completed']))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-semibold rounded-full text-xs border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Đã thanh toán
                                    </span>
                                @elseif(in_array($pStatus, ['failed', 'refunded', 'canceled']))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-semibold rounded-full text-xs border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        {{ $pStatus === 'refunded' ? 'Đã hoàn tiền' : 'Thất bại' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-semibold rounded-full text-xs border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Chờ thanh toán
                                    </span>
                                @endif
                            </td>

                            {{-- Trạng thái In Vé --}}
                            <td class="py-4 px-5 text-center">
                                @if(is_null($booking->printed_at))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-semibold rounded-full text-xs border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Chưa in vé
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-semibold rounded-full text-xs border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Đã in vé
                                    </span>
                                @endif
                            </td>

                            {{-- Trạng thái Đơn --}}
                            <td class="py-4 px-5 text-center">
                                @php
                                    $bStatus = strtolower($booking->booking_status ?? 'confirmed');
                                @endphp

                                @if($bStatus === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 text-xs font-semibold rounded-lg">
                                        Đã hủy
                                    </span>
                                @elseif($bStatus === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 text-xs font-semibold rounded-lg">
                                        Hoàn tất
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 text-xs font-semibold rounded-lg">
                                        Đã xác nhận
                                    </span>
                                @endif
                            </td>

                            {{-- Thao tác nút in vé/bắp nước --}}
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                @php
                                    $showtime = $booking->showtime;
                                    $showtimeStarted30 = $showtime?->show_date && $showtime?->start_time
                                        && \Carbon\Carbon::parse($showtime->show_date->format('Y-m-d') . ' ' . $showtime->start_time)
                                            ->addMinutes(30)->isPast();
                                @endphp
                                <div class="flex items-center justify-end gap-2">
                                    @if($showtimeStarted30)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-xs font-bold rounded-lg border border-gray-200 dark:border-gray-600">
                                            <span>🚫 Đã chiếu</span>
                                        </span>
                                    @elseif(is_null($booking->printed_at))
                                        <a 
                                            href="{{ route('staff.bookings.print-tickets', $booking->id) }}" 
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/80 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-colors shadow-sm"
                                            title="In cuống vé và phiếu bắp nước qua máy in nhiệt tại quầy"
                                        >
                                            <span>🍿 In Vé & Bắp Nước</span>
                                        </a>
                                    @else
                                        <button
                                            onclick="openReprintModal({{ $booking->id }}, {{ $booking->bookingDetails->toJson() }}, {{ $booking->combos->count() > 0 ? 'true' : 'false' }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-50 hover:bg-sky-100 dark:bg-sky-950/60 dark:hover:bg-sky-900/80 text-sky-700 dark:text-sky-300 text-xs font-bold rounded-lg border border-sky-200 dark:border-sky-800 transition-colors shadow-sm"
                                        >
                                            <span>🔁 In lại vé</span>
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        {{-- ── EMPTY STATE ── --}}
                        <tr>
                            <td colspan="8" class="py-16 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700/60 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">
                                        Không có dữ liệu đặt vé trong ngày này
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Chưa có đơn đặt vé nào cho suất chiếu ngày <span class="font-semibold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang --}}
        @if($bookings->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ── MODAL CHỌN VÉ IN LẠI ── --}}
<div id="reprint-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-sky-600 to-blue-700 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-white font-black text-lg">🔁 Chọn vé in lại</h3>
                <p id="reprint-booking-code" class="text-sky-200 text-xs mt-0.5 font-mono"></p>
            </div>
            <button onclick="closeReprintModal()" class="text-white/70 hover:text-white text-2xl leading-none">&times;</button>
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-3">
            {{-- Chọn tất cả ghế --}}
            <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-sky-200 dark:border-sky-700 bg-sky-50 dark:bg-sky-950/40 cursor-pointer hover:bg-sky-100 dark:hover:bg-sky-900/40 transition-colors">
                <input type="checkbox" id="reprint-select-all" onchange="toggleSelectAll(this)"
                       class="w-4 h-4 rounded accent-sky-600 cursor-pointer">
                <span class="text-sm font-black text-sky-700 dark:text-sky-300">Chọn tất cả ghế</span>
            </label>

            {{-- Danh sách ghế --}}
            <div id="reprint-seat-list" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>

            <p id="reprint-empty" class="hidden text-xs text-gray-400 text-center py-2">Không có ghế nào trong đơn này.</p>

            {{-- Phần bắp nước --}}
            <div id="reprint-fnb-wrap" class="hidden">
                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 border-orange-200 dark:border-orange-700 bg-orange-50 dark:bg-orange-950/40 cursor-pointer hover:bg-orange-100 dark:hover:bg-orange-900/40 transition-colors">
                        <input type="checkbox" id="reprint-fnb-cb"
                               class="w-4 h-4 rounded accent-orange-500 cursor-pointer">
                        <div>
                            <span class="text-sm font-black text-orange-700 dark:text-orange-300">🍿 In lại phiếu bắp nước</span>
                            <p class="text-xs text-orange-500 dark:text-orange-400 mt-0.5">In kèm phiếu nhận bắp nước cho khách</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 pb-5 flex gap-3">
            <button onclick="closeReprintModal()" class="flex-1 py-2.5 border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-bold text-sm rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">
                Huỷ
            </button>
            <button onclick="submitReprint()" id="btn-reprint-submit"
                    class="flex-1 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white font-black text-sm rounded-xl transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                🖨️ In vé đã chọn
            </button>
        </div>
    </div>
</div>

<script>
let _reprintBookingId = null;
let _reprintDetails   = [];
let _reprintHasFnb    = false;

function openReprintModal(bookingId, details, hasFnb) {
    _reprintBookingId = bookingId;
    _reprintDetails   = details;
    _reprintHasFnb    = hasFnb;

    const modal     = document.getElementById('reprint-modal');
    const codeEl    = document.getElementById('reprint-booking-code');
    const listEl    = document.getElementById('reprint-seat-list');
    const emptyEl   = document.getElementById('reprint-empty');
    const selectAll = document.getElementById('reprint-select-all');
    const fnbWrap   = document.getElementById('reprint-fnb-wrap');
    const fnbCb     = document.getElementById('reprint-fnb-cb');

    const row = document.querySelector(`[data-booking-id="${bookingId}"]`);
    codeEl.textContent = row ? row.dataset.bookingCode : '';

    selectAll.checked = false;
    fnbCb.checked     = false;
    listEl.innerHTML  = '';

    // Hiện/ẩn phần bắp nước
    fnbWrap.classList.toggle('hidden', !hasFnb);

    if (!details || details.length === 0) {
        emptyEl.classList.remove('hidden');
        listEl.classList.add('hidden');
    } else {
        emptyEl.classList.add('hidden');
        listEl.classList.remove('hidden');

        details.forEach(d => {
            const seat = d.showtime_seat?.seat;
            let seatName = 'N/A';
            if (seat) {
                const r = (seat.seat_row || '').trim().toUpperCase();
                const n = String(seat.seat_number || '').trim().toUpperCase();
                seatName = (r && !n.startsWith(r)) ? r + n : n;
            }
            const seatType = seat?.seat_type?.name || '';

            const label = document.createElement('label');
            label.className = 'flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700/50 cursor-pointer hover:border-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/30 transition-colors';
            label.innerHTML = `
                <input type="checkbox" value="${d.id}" onchange="syncSelectAll()"
                       class="reprint-cb w-4 h-4 rounded accent-sky-600 cursor-pointer">
                <div class="flex-1">
                    <span class="text-sm font-black text-gray-900 dark:text-white">Ghế ${seatName}</span>
                    ${seatType ? `<span class="ml-2 text-xs text-gray-500 dark:text-gray-400">${seatType}</span>` : ''}
                </div>
            `;
            listEl.appendChild(label);
        });
    }

    updateSubmitBtn();
    modal.classList.remove('hidden');
}

function closeReprintModal() {
    document.getElementById('reprint-modal').classList.add('hidden');
    _reprintBookingId = null;
    _reprintDetails   = [];
    _reprintHasFnb    = false;
}

function toggleSelectAll(cb) {
    document.querySelectorAll('.reprint-cb').forEach(el => el.checked = cb.checked);
    updateSubmitBtn();
}

function syncSelectAll() {
    const all     = document.querySelectorAll('.reprint-cb');
    const checked = document.querySelectorAll('.reprint-cb:checked');
    document.getElementById('reprint-select-all').checked = all.length > 0 && all.length === checked.length;
    updateSubmitBtn();
}

function updateSubmitBtn() {
    const btn        = document.getElementById('btn-reprint-submit');
    const checkedSeats = document.querySelectorAll('.reprint-cb:checked');
    const fnbChecked   = document.getElementById('reprint-fnb-cb').checked;
    btn.disabled = checkedSeats.length === 0 && !fnbChecked;
}

document.getElementById('reprint-fnb-cb').addEventListener('change', updateSubmitBtn);

function submitReprint() {
    const checkedSeats = [...document.querySelectorAll('.reprint-cb:checked')].map(el => el.value);
    const includeFnb   = document.getElementById('reprint-fnb-cb').checked;
    if (!_reprintBookingId) return;

    const url = `{{ route('staff.bookings.print-tickets', ['bookingId' => '__ID__']) }}`
        .replace('__ID__', _reprintBookingId);

    const params = new URLSearchParams();
    checkedSeats.forEach(id => params.append('detail_ids[]', id));
    if (includeFnb) params.set('include_fnb', '1');

    window.open(url + '?' + params.toString(), '_blank');
    closeReprintModal();
}
</script>

@endsection
