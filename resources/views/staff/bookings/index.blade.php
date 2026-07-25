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
                    Tra cứu và kiểm tra thông tin vé đặt theo ngày chiếu của rạp.
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
                                            $seatNum = $detail->showtimeSeat?->seat?->seat_number;
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

                            {{-- Thao tác --}}
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-semibold rounded-lg transition-colors"
                                        title="Xem chi tiết đơn"
                                    >
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Chi tiết</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/80 text-indigo-600 dark:text-indigo-400 text-xs font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 transition-colors"
                                        title="Quét QR Check-in"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4iM16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        <span>Check-in QR</span>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        {{-- ── 3. EMPTY STATE ── --}}
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
                                        Chưa có đơn đặt vé nào cho suất chiếu ngày <span class="font-semibold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>. Vui lòng chọn ngày chiếu khác để kiểm tra.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang (Pagination) --}}
        @if($bookings->hasPages())
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
