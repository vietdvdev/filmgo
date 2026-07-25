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

            {{-- Form lọc theo ngày chiếu --}}
            <form method="GET" action="{{ route('staff.bookings.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl px-3.5 py-2">
                    <label for="date" class="text-xs font-semibold text-gray-600 dark:text-gray-300 whitespace-nowrap">
                        Ngày chiếu:
                    </label>
                    <input 
                        type="date" 
                        id="date" 
                        name="date" 
                        value="{{ $selectedDate }}"
                        class="bg-transparent text-sm font-medium text-gray-900 dark:text-white focus:outline-none"
                    />
                </div>

                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold rounded-xl transition-all shadow-sm shadow-indigo-200 dark:shadow-none"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Lọc dữ liệu</span>
                </button>

                @if($selectedDate !== now()->toDateString())
                    <a 
                        href="{{ route('staff.bookings.index') }}" 
                        class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 text-sm font-medium rounded-xl transition-colors"
                    >
                        Hôm nay
                    </a>
                @endif
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
                        <th class="py-4 px-5 text-center">Trạng thái đơn</th>
                        <th class="py-4 px-5 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    @forelse($bookings as $booking)
                        <tr class="hover:bg-indigo-50/40 dark:hover:bg-gray-700/40 transition-colors">
                            
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

                            {{-- Thao tác (CỘT NÚT XEM QR VÀ IN VÉ & BẮP NƯỚC) --}}
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    
                                    {{-- Nút Xem QR --}}
                                    <button 
                                        type="button" 
                                        data-id="{{ $booking->id }}"
                                        data-code="{{ $booking->booking_code }}"
                                        class="btn-view-qr inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors shadow-sm"
                                        title="Xem mã QR vé"
                                    >
                                        <span>📱 Xem QR</span>
                                    </button>

                                    {{-- Nút In Vé & Bắp Nước (Máy in nhiệt tại quầy) --}}
                                    <a 
                                        href="{{ route('staff.bookings.print-tickets', $booking->id) }}" 
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 dark:hover:bg-amber-900/80 text-amber-700 dark:text-amber-300 text-xs font-bold rounded-lg border border-amber-200 dark:border-amber-800 transition-colors shadow-sm"
                                        title="In cuống vé và phiếu bắp nước qua máy in nhiệt tại quầy"
                                    >
                                        <span>🍿 In Vé & Bắp Nước</span>
                                    </a>

                                </div>
                            </td>

                        </tr>
                    @empty
                        {{-- ── EMPTY STATE ── --}}
                        <tr>
                            <td colspan="7" class="py-16 text-center">
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

{{-- ── 3. MODAL HIỂN THỊ MÃ QR CODE ── --}}
<div id="qrModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-200">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-2xl overflow-hidden transform transition-all">
        
        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-center gap-2">
                <span class="text-xl">📱</span>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    Mã QR Vé - Đơn <span id="modalBookingCode" class="text-indigo-600 dark:text-indigo-400 font-mono"></span>
                </h3>
            </div>
            <button id="closeQrModal" type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl font-bold p-1 rounded-lg hover:bg-gray-200/50 transition-colors">&times;</button>
        </div>

        {{-- Modal Body --}}
        <div id="qrModalBody" class="p-6 max-h-[75vh] overflow-y-auto">
            {{-- Skeleton Loading --}}
            <div id="qrLoading" class="flex flex-col items-center justify-center py-12 space-y-3">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Đang tải mã QR của vé...</p>
            </div>

            {{-- Grid Render Danh Sách Vé QR --}}
            <div id="qrTicketsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 hidden"></div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex justify-end">
            <button id="closeQrModalBtn" type="button" class="px-5 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold text-sm rounded-xl transition-colors">
                Đóng
            </button>
        </div>

    </div>
</div>

{{-- ── 4. JAVASCRIPT XỬ LÝ MODAL & FETCH AJAX QR CODE ── --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal          = document.getElementById('qrModal');
    const modalCode      = document.getElementById('modalBookingCode');
    const loading        = document.getElementById('qrLoading');
    const grid           = document.getElementById('qrTicketsGrid');
    const closeBtn       = document.getElementById('closeQrModal');
    const closeBtnFooter = document.getElementById('closeQrModalBtn');

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        grid.innerHTML = '';
        grid.classList.add('hidden');
        loading.classList.remove('hidden');
    }

    closeBtn.addEventListener('click', closeModal);
    closeBtnFooter.addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });

    // Lắng nghe sự kiện Click nút [ 📱 Xem QR ]
    document.querySelectorAll('.btn-view-qr').forEach(function (button) {
        button.addEventListener('click', function () {
            const bookingId   = this.getAttribute('data-id');
            const bookingCode = this.getAttribute('data-code');

            modalCode.textContent = bookingCode || ('#' + bookingId);
            openModal();

            // Fetch AJAX lấy mảng mã QR vé
            fetch(`/staff/bookings/${bookingId}/qr`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Không thể tải mã QR');
                return response.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                grid.innerHTML = '';
                grid.classList.remove('hidden');

                if (data.tickets && data.tickets.length > 0) {
                    data.tickets.forEach(ticket => {
                        const card = document.createElement('div');
                        card.className = 'flex flex-col items-center bg-gray-50 dark:bg-gray-700/60 p-4 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm';

                        // QR Image Container (render trực tiếp SVG hoặc Image)
                        const qrBox = document.createElement('div');
                        qrBox.className = 'w-48 h-48 bg-white p-2 rounded-lg border border-gray-300 flex items-center justify-center overflow-hidden mb-3';

                        if (ticket.qr_image) {
                            if (ticket.qr_image.startsWith('<svg') || ticket.qr_image.startsWith('<?xml')) {
                                qrBox.innerHTML = ticket.qr_image;
                            } else {
                                const img = document.createElement('img');
                                img.src = ticket.qr_image;
                                img.alt = 'Mã QR Vé ' + ticket.seat_name;
                                img.className = 'w-full h-full object-contain';
                                qrBox.appendChild(img);
                            }
                        } else {
                            qrBox.innerHTML = '<span class="text-xs text-gray-400 italic">Chưa có mã QR</span>';
                        }

                        // Label Tên Ghế
                        const label = document.createElement('p');
                        label.className = 'text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1';
                        label.innerHTML = `<span>🎟️</span> <span>Ghế: ${ticket.seat_name}</span>`;

                        card.appendChild(qrBox);
                        card.appendChild(label);
                        grid.appendChild(card);
                    });
                } else {
                    grid.innerHTML = '<p class="col-span-2 text-center text-sm text-gray-500 py-6">Đơn hàng này chưa tạo vé hoặc không có mã QR.</p>';
                }
            })
            .catch(error => {
                loading.classList.add('hidden');
                grid.classList.remove('hidden');
                grid.innerHTML = `<p class="col-span-2 text-center text-sm text-rose-500 py-6 font-semibold">${error.message || 'Lỗi khi tải mã QR'}</p>`;
            });
        });
    });
});
</script>
@endpush

@endsection
