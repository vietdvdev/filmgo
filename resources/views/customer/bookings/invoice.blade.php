<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn #{{ $booking->booking_code }} - FilmGo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">

    {{-- Nút in --}}
    <div class="no-print flex gap-3 justify-center mb-6">
        <button onclick="window.print()"
                class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-sm transition-colors">
            <span class="material-symbols-outlined text-base">print</span>
            In Hóa Đơn
        </button>
        <a href="{{ route('booking.history.show', $booking->id) }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 font-bold rounded-lg text-sm transition-colors">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            Quay Lại
        </a>
    </div>

    {{-- Hóa đơn --}}
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">

        {{-- Header --}}
        <div class="bg-red-600 px-8 py-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-black tracking-wider">FILM<span class="opacity-80">GO</span></p>
                    <p class="text-xs opacity-70 mt-0.5">Hệ thống đặt vé xem phim trực tuyến</p>
                </div>
                <div class="text-right">
                    <p class="text-xs opacity-70 uppercase tracking-widest">Hóa Đơn</p>
                    <p class="text-xl font-black font-mono">#{{ $booking->booking_code }}</p>
                    <p class="text-xs opacity-70 mt-0.5">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="px-8 py-6 space-y-6">

            {{-- Thông tin khách hàng --}}
            <div class="grid grid-cols-2 gap-4 pb-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Khách Hàng</p>
                    <p class="font-bold text-gray-900">{{ optional($booking->user)->full_name ?? Auth::user()->full_name }}</p>
                    <p class="text-xs text-gray-500">{{ optional($booking->user)->email ?? Auth::user()->email }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Trạng Thái</p>
                    @php
                        $statusLabel = ['pending'=>'Chờ thanh toán','paid'=>'Đã thanh toán','cancelled'=>'Đã hủy','refunded'=>'Đã hoàn tiền'];
                        $statusClass = ['pending'=>'text-amber-700 bg-amber-50 border-amber-200','paid'=>'text-emerald-700 bg-emerald-50 border-emerald-200','cancelled'=>'text-red-700 bg-red-50 border-red-200','refunded'=>'text-gray-600 bg-gray-100 border-gray-200'];
                    @endphp
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold border {{ $statusClass[$booking->payment_status] ?? 'text-gray-600 bg-gray-100 border-gray-200' }}">
                        {{ $statusLabel[$booking->payment_status] ?? $booking->payment_status }}
                    </span>
                </div>
            </div>

            {{-- Thông tin suất chiếu --}}
            <div class="pb-5 border-b border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Thông Tin Suất Chiếu</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Phim</p>
                        <p class="font-bold text-gray-900">{{ optional($booking->showtime->movie)->title ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Rạp</p>
                        <p class="font-bold text-gray-900">{{ optional(optional(optional($booking->showtime)->room)->cinema)->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Phòng chiếu</p>
                        <p class="font-bold text-gray-900">{{ optional(optional($booking->showtime)->room)->room_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Suất chiếu</p>
                        <p class="font-bold text-red-600">
                            {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '—' }}
                            · {{ $booking->showtime ? $booking->showtime->show_date->format('d/m/Y') : '' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Chi tiết ghế --}}
            <div class="pb-5 border-b border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Chi Tiết Ghế</p>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <th class="text-left pb-2">Ghế</th>
                            <th class="text-left pb-2">Loại</th>
                            <th class="text-right pb-2">Giá</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($booking->bookingDetails as $detail)
                        <tr>
                            <td class="py-2 font-bold text-gray-900">
                                {{ optional(optional($detail->showtimeSeat)->seat)->seat_row }}{{ optional(optional($detail->showtimeSeat)->seat)->seat_number }}
                            </td>
                            <td class="py-2 text-gray-500">{{ optional(optional(optional($detail->showtimeSeat)->seat)->seatType)->type_name ?? 'Thường' }}</td>
                            <td class="py-2 text-right font-semibold text-gray-800">{{ number_format($detail->price) }}đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Combo --}}
            @if($booking->combos->count() > 0)
            <div class="pb-5 border-b border-gray-100">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Bắp Nước</p>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
                        @foreach($booking->combos as $combo)
                        <tr>
                            <td class="py-2 text-gray-800">{{ $combo->combo_name }}</td>
                            <td class="py-2 text-gray-400 text-center">×{{ $combo->pivot->quantity }}</td>
                            <td class="py-2 text-right font-semibold text-gray-800">{{ number_format($combo->pivot->subtotal) }}đ</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Tổng tiền --}}
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Tiền ghế ({{ $booking->bookingDetails->count() }} ghế)</span>
                    <span>{{ number_format($booking->bookingDetails->sum('price')) }}đ</span>
                </div>
                @if($booking->combos->count() > 0)
                <div class="flex justify-between text-gray-500">
                    <span>Bắp nước</span>
                    <span>{{ number_format($booking->combos->sum('pivot.subtotal')) }}đ</span>
                </div>
                @endif
                @if($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Giảm giá
                        @if($booking->promotions->first())
                            ({{ $booking->promotions->first()->code }})
                        @endif
                    </span>
                    <span>-{{ number_format($booking->discount_amount) }}đ</span>
                </div>
                @endif
                <div class="flex justify-between items-center pt-3 border-t-2 border-gray-100">
                    <span class="font-black text-gray-900 uppercase tracking-wider text-sm">Tổng Cộng</span>
                    <span class="text-2xl font-black text-red-600">
                        {{ number_format($booking->final_total ?? $booking->total_amount) }}đ
                    </span>
                </div>
            </div>

            {{-- Phương thức thanh toán --}}
            @php $payment = $booking->payments->first(); @endphp
            @if($payment)
            <div class="pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Phương thức TT</p>
                    <p class="text-gray-800 font-bold uppercase">{{ $payment->payment_method ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Mã giao dịch</p>
                    <p class="text-gray-800 font-bold font-mono">{{ $payment->transaction_code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Thời gian TT</p>
                    <p class="text-gray-800 font-bold">
                        {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('H:i d/m/Y') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Trạng thái TT</p>
                    <p class="text-gray-800 font-bold">{{ $statusLabel[$payment->payment_status] ?? $payment->payment_status }}</p>
                </div>
            </div>
            @endif

            {{-- Footer --}}
            <div class="pt-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">Cảm ơn bạn đã sử dụng dịch vụ của FilmGo!</p>
                <p class="text-[10px] text-gray-300 mt-0.5">Vui lòng xuất trình hóa đơn này khi vào rạp.</p>
            </div>

        </div>
    </div>

</body>
</html>
