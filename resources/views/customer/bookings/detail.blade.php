@extends('layouts.customer')

@section('title', 'Chi Tiết Đơn #' . $booking->booking_code . ' - FilmGo')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-gray-500 mb-6">
            <a href="{{ route('booking.history.index') }}" class="hover:text-brand-primary transition-colors flex items-center gap-1 font-medium">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Lịch sử đặt vé
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-600 font-mono">#{{ $booking->booking_code }}</span>
        </div>

        @php
            $statusMap = [
                'pending'   => ['label' => 'Chờ thanh toán', 'bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'border' => 'border-amber-200'],
                'paid'      => ['label' => 'Đã thanh toán',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-200'],
                'cancelled' => ['label' => 'Đã hủy',         'bg' => 'bg-red-50',     'text' => 'text-red-600',     'border' => 'border-red-200'],
                'refunded'  => ['label' => 'Đã hoàn tiền',   'bg' => 'bg-gray-100',    'text' => 'text-gray-600',    'border' => 'border-gray-200'],
            ];
            $ps = $statusMap[$booking->payment_status] ?? $statusMap['pending'];
            $payment = $booking->payments->first();
            $promotion = $booking->promotions->first();
        @endphp

        {{-- Header card --}}
        <div class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden shadow-sm mb-4">
            <div class="bg-brand-primary px-5 py-3 flex items-center justify-between">
                <h2 class="text-xs font-black uppercase tracking-widest text-white flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">receipt_long</span>
                    Chi Tiết Đơn Đặt Vé
                </h2>
                <span class="text-xs font-black text-white/90 font-mono">#{{ $booking->booking_code }}</span>
            </div>

            <div class="p-5 flex flex-col sm:flex-row gap-4">
                @if(optional($booking->showtime->movie)->poster_url)
                <img src="{{ $booking->showtime->movie->poster_url }}"
                     alt="poster"
                     class="w-20 h-28 object-cover rounded-lg border border-gray-100 flex-shrink-0 shadow-sm">
                @endif
                <div class="flex-1 space-y-1.5">
                    <p class="font-black text-gray-900 text-lg leading-tight">{{ optional($booking->showtime->movie)->title ?? '—' }}</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(optional($booking->showtime->movie)->age_limit)
                        <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary/10 text-brand-primary rounded border border-brand-primary/20 uppercase">
                            {{ $booking->showtime->movie->age_limit }}
                        </span>
                        @endif
                        @if(optional($booking->showtime->movie)->duration)
                        <span class="text-xs text-gray-500 font-medium">{{ $booking->showtime->movie->duration }} phút</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 pt-1">
                        <span class="font-bold text-gray-800">{{ optional(optional(optional($booking->showtime)->room)->cinema)->name ?? '—' }}</span>
                        @if(optional(optional($booking->showtime)->room)->room_name)
                        · {{ $booking->showtime->room->room_name }}
                        @endif
                    </p>
                    <p class="text-xs text-brand-primary font-bold">
                        {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '—' }}
                        @if($booking->showtime && $booking->showtime->end_time)
                        – {{ \Carbon\Carbon::parse($booking->showtime->end_time)->format('H:i') }}
                        @endif
                        · {{ $booking->showtime ? $booking->showtime->show_date->format('d/m/Y') : '—' }}
                    </p>
                    <div class="pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $ps['bg'] }} {{ $ps['text'] }} border {{ $ps['border'] }}">
                            {{ $ps['label'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ghế đã chọn --}}
        <div class="bg-white border border-gray-200/80 rounded-2xl p-5 shadow-sm mb-4">
            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-brand-primary">event_seat</span>
                Ghế Đã Chọn
            </h3>
            <div class="space-y-2">
                @foreach($booking->bookingDetails as $detail)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-xs font-black text-gray-800">
                            {{ optional(optional($detail->showtimeSeat)->seat)->seat_row }}{{ optional(optional($detail->showtimeSeat)->seat)->seat_number }}
                        </span>
                        <span class="text-gray-500 text-xs font-medium">{{ optional(optional(optional($detail->showtimeSeat)->seat)->seatType)->type_name ?? 'Thường' }}</span>
                    </div>
                    <span class="font-bold text-gray-800">{{ number_format($detail->price) }}đ</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Combo (nếu có) --}}
        @if($booking->combos->count() > 0)
        <div class="bg-white border border-gray-200/80 rounded-2xl p-5 shadow-sm mb-4">
            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-brand-primary">fastfood</span>
                Bắp Nước
            </h3>
            <div class="space-y-2">
                @foreach($booking->combos as $combo)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-800 font-medium">{{ $combo->combo_name }}
                        <span class="text-gray-400 ml-1 font-normal">×{{ $combo->pivot->quantity }}</span>
                    </span>
                    <span class="font-bold text-gray-800">{{ number_format($combo->pivot->subtotal) }}đ</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Thanh toán --}}
        <div class="bg-white border border-gray-200/80 rounded-2xl p-5 shadow-sm mb-4">
            <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-brand-primary">payments</span>
                Thanh Toán
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Tiền ghế ({{ $booking->bookingDetails->count() }} ghế)</span>
                    <span class="font-medium text-gray-800">{{ number_format($booking->bookingDetails->sum('price')) }}đ</span>
                </div>
                @if($booking->combos->count() > 0)
                <div class="flex justify-between text-gray-500">
                    <span>Bắp nước</span>
                    <span class="font-medium text-gray-800">{{ number_format($booking->combos->sum('pivot.subtotal')) }}đ</span>
                </div>
                @endif
                @if($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Giảm giá
                        @if($promotion)
                            <span class="text-[10px] font-black ml-1 px-1.5 py-0.5 bg-emerald-50 border border-emerald-200 rounded text-emerald-600">
                                {{ $promotion->code }}
                            </span>
                        @endif
                    </span>
                    <span>-{{ number_format($booking->discount_amount) }}đ</span>
                </div>
                @endif
                <div class="border-t border-gray-100 pt-3 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tổng Cộng</span>
                    <span class="text-2xl font-black text-brand-primary">
                        {{ number_format($booking->final_total ?? $booking->total_amount) }}đ
                    </span>
                </div>
            </div>

            @if($payment)
            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Phương thức</p>
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
                    <p class="text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Ngày đặt</p>
                    <p class="text-gray-800 font-bold">{{ $booking->created_at->format('H:i d/m/Y') }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Vé điện tử & QR Code --}}
        @if($booking->payment_status === 'paid')
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm text-brand-primary">qr_code_2</span>
                    Vé Điện Tử
                </h3>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase tracking-wider">Đã thanh toán</span>
            </div>
            @include('customer.bookings.partials.ticket-card', ['booking' => $booking])
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex justify-center">
            <a href="{{ route('booking.history.index') }}"
               class="w-full flex items-center justify-center gap-2 py-3 bg-brand-primary hover:bg-red-700 text-white font-bold rounded-xl shadow-sm transition-all text-sm">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Quay Lại Lịch Sử
            </a>
        </div>

    </div>
</div>
@endsection