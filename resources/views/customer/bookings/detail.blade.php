@extends('layouts.admin')

@section('title', 'Chi Tiết Đơn #' . $booking->booking_code . ' - FilmGo')

@section('content')
<div class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="max-w-3xl mx-auto px-6 py-8">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-on-surface-variant mb-6">
            <a href="{{ route('booking.history.index') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Lịch sử đặt vé
            </a>
            <span class="material-symbols-outlined text-xs text-outline">chevron_right</span>
            <span class="text-on-surface font-mono">#{{ $booking->booking_code }}</span>
        </div>

        @php
            $statusMap = [
                'pending'   => ['label' => 'Chờ thanh toán', 'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'border' => 'border-amber-200'],
                'paid'      => ['label' => 'Đã thanh toán',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200'],
                'cancelled' => ['label' => 'Đã hủy',         'bg' => 'bg-red-50',     'text' => 'text-red-700',     'border' => 'border-red-200'],
                'refunded'  => ['label' => 'Đã hoàn tiền',   'bg' => 'bg-gray-100',   'text' => 'text-gray-600',    'border' => 'border-gray-200'],
            ];
            $ps = $statusMap[$booking->payment_status] ?? $statusMap['pending'];
            $payment = $booking->payments->first();
            $promotion = $booking->promotions->first();
        @endphp

        {{-- Header card --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden mb-4">
            <div class="bg-primary px-5 py-3 flex items-center justify-between">
                <h2 class="text-xs font-black uppercase tracking-widest text-on-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">receipt_long</span>
                    Chi Tiết Đơn Đặt Vé
                </h2>
                <span class="text-xs font-black text-on-primary/80 font-mono">#{{ $booking->booking_code }}</span>
            </div>

            <div class="p-5 flex flex-col sm:flex-row gap-4">
                @if($booking->showtime->movie->poster_url)
                <img src="{{ $booking->showtime->movie->poster_url }}"
                     alt="poster"
                     class="w-20 h-28 object-cover rounded-lg border border-outline-variant flex-shrink-0">
                @endif
                <div class="flex-1 space-y-1">
                    <p class="font-black text-on-surface text-lg leading-tight">{{ $booking->showtime->movie->title }}</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 text-[9px] font-black bg-primary/10 text-primary rounded border border-primary/20 uppercase">
                            {{ $booking->showtime->movie->age_limit }}
                        </span>
                        <span class="text-xs text-on-surface-variant">{{ $booking->showtime->movie->duration }} phút</span>
                    </div>
                    <p class="text-xs text-on-surface-variant pt-1">
                        <span class="font-bold text-on-surface">{{ $booking->showtime->room->cinema->name }}</span>
                        · {{ $booking->showtime->room->room_name }} ({{ $booking->showtime->room->room_type }})
                    </p>
                    <p class="text-xs text-primary font-bold">
                        {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }}
                        – {{ \Carbon\Carbon::parse($booking->showtime->end_time)->format('H:i') }}
                        · {{ $booking->showtime->show_date->format('d/m/Y') }}
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
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 mb-4">
            <h3 class="text-xs font-black text-on-surface-variant uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-primary">event_seat</span>
                Ghế Đã Chọn
            </h3>
            <div class="space-y-2">
                @foreach($booking->bookingDetails as $detail)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-lg bg-surface-container border border-outline-variant flex items-center justify-center text-xs font-black text-on-surface">
                            {{ $detail->showtimeSeat->seat->seat_row }}{{ $detail->showtimeSeat->seat->seat_number }}
                        </span>
                        <span class="text-on-surface-variant text-xs">{{ $detail->showtimeSeat->seat->seatType->type_name ?? 'Thường' }}</span>
                    </div>
                    <span class="font-bold text-on-surface">{{ number_format($detail->price) }}đ</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Combo (nếu có) --}}
        @if($booking->combos->count() > 0)
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 mb-4">
            <h3 class="text-xs font-black text-on-surface-variant uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-primary">fastfood</span>
                Bắp Nước
            </h3>
            <div class="space-y-2">
                @foreach($booking->combos as $combo)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-on-surface">{{ $combo->combo_name }}
                        <span class="text-on-surface-variant ml-1">×{{ $combo->pivot->quantity }}</span>
                    </span>
                    <span class="font-bold text-on-surface">{{ number_format($combo->pivot->subtotal) }}đ</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Thanh toán --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 mb-4">
            <h3 class="text-xs font-black text-on-surface-variant uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-sm text-primary">payments</span>
                Thanh Toán
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between text-on-surface-variant">
                    <span>Tiền ghế ({{ $booking->bookingDetails->count() }} ghế)</span>
                    <span>{{ number_format($booking->bookingDetails->sum('price')) }}đ</span>
                </div>
                @if($booking->combos->count() > 0)
                <div class="flex justify-between text-on-surface-variant">
                    <span>Bắp nước</span>
                    <span>{{ number_format($booking->combos->sum('pivot.subtotal')) }}đ</span>
                </div>
                @endif
                @if($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Giảm giá
                        @if($promotion)
                            <span class="text-[10px] font-black ml-1 px-1.5 py-0.5 bg-emerald-50 border border-emerald-200 rounded text-emerald-700">
                                {{ $promotion->code }}
                            </span>
                        @endif
                    </span>
                    <span>-{{ number_format($booking->discount_amount) }}đ</span>
                </div>
                @endif
                <div class="border-t border-outline-variant pt-3 flex justify-between items-center">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-widest">Tổng Cộng</span>
                    <span class="text-2xl font-black text-primary">
                        {{ number_format($booking->final_total ?? $booking->total_amount) }}đ
                    </span>
                </div>
            </div>

            @if($payment)
            <div class="mt-4 pt-4 border-t border-outline-variant grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-outline font-semibold uppercase tracking-wider mb-0.5">Phương thức</p>
                    <p class="text-on-surface font-bold uppercase">{{ $payment->payment_method ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-outline font-semibold uppercase tracking-wider mb-0.5">Mã giao dịch</p>
                    <p class="text-on-surface font-bold font-mono">{{ $payment->transaction_code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-outline font-semibold uppercase tracking-wider mb-0.5">Thời gian TT</p>
                    <p class="text-on-surface font-bold">
                        {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('H:i d/m/Y') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-outline font-semibold uppercase tracking-wider mb-0.5">Ngày đặt</p>
                    <p class="text-on-surface font-bold">{{ $booking->created_at->format('H:i d/m/Y') }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('booking.history.invoice', $booking->id) }}"
               target="_blank"
               class="flex-1 flex items-center justify-center gap-2 py-3 bg-surface-container hover:bg-surface-container-high border border-outline-variant text-on-surface font-bold rounded-xl transition-all text-sm">
                <span class="material-symbols-outlined text-lg">print</span>
                Xem / In Hóa Đơn
            </a>
            <a href="{{ route('booking.history.index') }}"
               class="flex-1 flex items-center justify-center gap-2 py-3 bg-primary hover:bg-blue-700 text-on-primary font-bold rounded-xl transition-all text-sm">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
                Quay Lại Lịch Sử
            </a>
        </div>

    </div>
</div>
@endsection
