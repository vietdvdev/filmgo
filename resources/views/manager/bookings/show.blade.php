@extends('layouts.manager')

@section('title', 'Chi Tiết Đơn Hàng #' . $booking->booking_code . ' - ' . $cinema->name . ' - FilmGo')

@section('content')
@php
    $isComboOnly = ($booking->booking_type === 'combo_only' || !$booking->showtime_id);
    $movie = optional($booking->showtime)->movie;
    $room = optional($booking->showtime)->room;
    
    // Payment status badge
    $ps = $booking->payment_status;
    $psBadge = match($ps) {
        'paid'     => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Đã thanh toán', 'icon' => 'check_circle'],
        'pending'  => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Chờ thanh toán', 'icon' => 'hourglass_empty'],
        'failed'   => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Thất bại', 'icon' => 'cancel'],
        'refunded' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'Hoàn tiền', 'icon' => 'replay'],
        default    => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => $ps, 'icon' => 'help_outline'],
    };

    $isCounter = ($booking->channel === 'counter');
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    {{-- Top Header & Navigation --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <a href="{{ route('manager.bookings.index') }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-800">Đơn Hàng #{{ $booking->booking_code }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isComboOnly ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $isComboOnly ? '🍿 Đơn Bắp Nước' : '🎟️ Đơn Vé Phim' }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Rạp quản trị: <span class="font-semibold text-slate-700">{{ $cinema->name }}</span> • Tạo lúc {{ $booking->created_at->format('d/m/Y H:i:s') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Trạng thái thanh toán Badge --}}
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border {{ $psBadge['bg'] }}">
                <span class="material-symbols-outlined text-sm">{{ $psBadge['icon'] }}</span>
                {{ $psBadge['label'] }}
            </span>
        </div>
    </div>

    {{-- Quick Status Strip --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <div>
            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kênh đặt hàng</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="material-symbols-outlined text-sm text-slate-600">{{ $isCounter ? 'point_of_sale' : 'language' }}</span>
                <span class="text-sm font-bold text-slate-800">{{ $isCounter ? 'Tại quầy (POS)' : 'Trực tuyến (Online)' }}</span>
            </div>
        </div>
        <div>
            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Trạng thái in</span>
            <div class="flex items-center gap-1.5 mt-1">
                @if($booking->printed_at)
                    <span class="material-symbols-outlined text-sm text-emerald-600">print</span>
                    <span class="text-sm font-bold text-emerald-600">Đã in ({{ $booking->printed_at->format('d/m H:i') }})</span>
                @else
                    <span class="material-symbols-outlined text-sm text-slate-400">do_not_disturb_on</span>
                    <span class="text-sm font-bold text-slate-500">Chưa in vé</span>
                @endif
            </div>
        </div>
        <div>
            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Trạng thái đơn</span>
            <div class="flex items-center gap-1.5 mt-1">
                <span class="material-symbols-outlined text-sm text-blue-600">fact_check</span>
                <span class="text-sm font-bold text-slate-800 capitalize">{{ $booking->booking_status ?? 'confirmed' }}</span>
            </div>
        </div>
        <div>
            <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng tiền thanh toán</span>
            <span class="block text-base font-black text-blue-600 mt-0.5">{{ number_format($booking->final_total) }} đ</span>
        </div>
    </div>

    {{-- Main Grid Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Cột trái (2 cols): Chi tiết vé/ghế & bắp nước --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. Suất chiếu & Phim (Nếu có) --}}
            @if(!$isComboOnly && $booking->showtime)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                        <span class="material-symbols-outlined text-blue-600">movie</span>
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Thông Tin Suất Chiếu</h2>
                    </div>
                    <div class="p-5 flex flex-col sm:flex-row gap-4">
                        @if($movie && $movie->poster)
                            <div class="w-24 h-36 rounded-lg overflow-hidden border border-slate-200 flex-shrink-0 bg-slate-100 shadow-sm">
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="flex-1 space-y-2.5">
                            <div>
                                <h3 class="text-lg font-black text-slate-800">{{ optional($movie)->title }}</h3>
                                @if(optional($booking->showtime)->format)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200 mt-1">
                                        {{ $booking->showtime->format->name ?? '2D Phụ Đề' }}
                                    </span>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                                <div>
                                    <span class="text-slate-400">Phòng chiếu:</span>
                                    <span class="font-bold text-slate-800 ml-1">{{ optional($room)->room_name ?? 'Chưa xác định' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Thời gian:</span>
                                    <span class="font-bold text-slate-800 ml-1">
                                        {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->showtime->end_time)->format('H:i') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Ngày chiếu:</span>
                                    <span class="font-bold text-slate-800 ml-1">
                                        {{ \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400">Thời lượng:</span>
                                    <span class="font-bold text-slate-800 ml-1">{{ optional($movie)->duration ? $movie->duration . ' phút' : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Danh sách Ghế & Vé Xem Phim --}}
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">chair</span>
                            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Danh Sách Vé &amp; Ghế Ngồi ({{ $booking->bookingDetails->count() }})</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Mã Vé (QR Code)</th>
                                    <th class="px-4 py-3">Vị trí ghế</th>
                                    <th class="px-4 py-3">Loại ghế</th>
                                    <th class="px-4 py-3 text-right">Giá vé</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($booking->bookingDetails as $detail)
                                    @php
                                        $seat = optional($detail->showtimeSeat)->seat;
                                        $seatType = optional($seat)->seatType;
                                        $ticket = $detail->ticket;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 font-mono font-bold text-slate-800">
                                            {{ $ticket->ticket_code ?? ('TK-' . $detail->id) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 font-black text-sm border border-blue-100">
                                                {{ optional($seat)->seat_row }}{{ optional($seat)->seat_number }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 font-medium">
                                            {{ optional($seatType)->name ?? 'Ghế Thường' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-800">
                                            {{ number_format($detail->price) }} đ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-slate-400">Không có dữ liệu ghế ngồi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- 2. Chi tiết Bắp Nước & Combo (Nếu có) --}}
            @if($booking->combos->isNotEmpty() || $booking->comboItems->isNotEmpty())
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2 bg-slate-50/50">
                        <span class="material-symbols-outlined text-amber-600">fastfood</span>
                        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Bắp Nước &amp; Combo Đã Mua</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-4 py-3">Tên món / Combo</th>
                                    <th class="px-4 py-3 text-center">Số lượng</th>
                                    <th class="px-4 py-3 text-right">Đơn giá</th>
                                    <th class="px-4 py-3 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                {{-- Combo Packages --}}
                                @foreach($booking->combos as $combo)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 font-semibold text-slate-800">
                                            {{ $combo->combo_name }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-slate-700">
                                            x{{ $combo->pivot->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-slate-600">
                                            {{ number_format($combo->pivot->subtotal / max(1, $combo->pivot->quantity)) }} đ
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-800">
                                            {{ number_format($combo->pivot->subtotal) }} đ
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Single Items --}}
                                @foreach($booking->comboItems as $item)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="px-4 py-3 font-semibold text-slate-800">
                                            {{ optional($item->comboItem)->name ?? 'Món lẻ' }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-slate-700">
                                            x{{ $item->quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-slate-600">
                                            {{ number_format($item->subtotal / max(1, $item->quantity)) }} đ
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-slate-800">
                                            {{ number_format($item->subtotal) }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cột phải (1 col): Khách hàng, Nhân viên & Tóm tắt tài chính --}}
        <div class="space-y-6">

            {{-- Card Khách Hàng --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-blue-600">person</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Thông Tin Khách Hàng</h2>
                </div>
                @php $user = $booking->user; @endphp
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-500">Họ và tên:</span>
                        <span class="font-bold text-slate-800 text-right">{{ $user->full_name ?? 'Khách vãng lai' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-500">Số điện thoại:</span>
                        <span class="font-medium text-slate-800 text-right">{{ $user->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-500">Email:</span>
                        <span class="font-medium text-slate-800 text-right break-all">{{ $user->email ?? '—' }}</span>
                    </div>
                </div>

                @if($booking->staff)
                    <div class="pt-3 border-t border-slate-100">
                        <span class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Nhân viên phục vụ (POS)</span>
                        <div class="flex items-center gap-2 mt-1.5">
                            <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs">
                                {{ substr($booking->staff->full_name, 0, 1) }}
                            </div>
                            <div class="text-xs font-bold text-slate-700">
                                {{ $booking->staff->full_name }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card Tóm Tắt Tài Chính --}}
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-emerald-600">receipt</span>
                    <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Chi Tiết Thanh Toán</h2>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="flex justify-between">
                        <span>Tạm tính (Subtotal):</span>
                        <span class="font-bold text-slate-800">{{ number_format($booking->subtotal ?? $booking->total_amount) }} đ</span>
                    </div>

                    @if($booking->discount_amount > 0)
                        <div class="flex justify-between text-red-600 font-semibold">
                            <span>Giảm giá khuyến mãi:</span>
                            <span>-{{ number_format($booking->discount_amount) }} đ</span>
                        </div>
                    @endif

                    @if($booking->promotion)
                        <div class="flex justify-between text-[11px] text-purple-700 bg-purple-50 p-2 rounded-lg">
                            <span>Mã giảm giá áp dụng:</span>
                            <span class="font-bold font-mono">{{ $booking->promotion->code }}</span>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-baseline">
                        <span class="text-sm font-black text-slate-800 uppercase">Tổng thanh toán:</span>
                        <span class="text-xl font-black text-blue-600">{{ number_format($booking->final_total) }} đ</span>
                    </div>
                </div>
            </div>

            {{-- Nút Quay lại --}}
            <div class="pt-2">
                <a href="{{ route('manager.bookings.index') }}" 
                   class="w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Quay lại danh sách đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
