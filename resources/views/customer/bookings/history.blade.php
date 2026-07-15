@extends('layouts.admin')

@section('title', 'Lịch Sử Đặt Vé - FilmGo')

@section('content')
<div class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="max-w-5xl mx-auto px-6 py-8">

        <div class="mb-6">
            <p class="text-xs font-bold text-outline uppercase tracking-widest mb-1">Tài Khoản</p>
            <h1 class="text-headline-md font-headline-md text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">history</span>
                Lịch Sử Đặt Vé
            </h1>
            <p class="text-body-md text-on-surface-variant mt-1">Tất cả đơn đặt vé của bạn tại FilmGo</p>
        </div>

        @if($bookings->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-on-surface-variant">
                <span class="material-symbols-outlined text-6xl mb-4 text-outline">confirmation_number</span>
                <p class="text-headline-sm font-headline-sm text-on-surface">Bạn chưa có đơn đặt vé nào</p>
                <p class="text-body-md mt-1 mb-6">Hãy đặt vé ngay để trải nghiệm FilmGo!</p>
                <a href="{{ route('home') }}"
                   class="px-6 py-3 bg-primary hover:bg-blue-700 text-on-primary font-bold rounded-xl transition-colors text-sm">
                    Khám Phá Phim
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($bookings as $booking)
                @php
                    $statusMap = [
                        'pending'   => ['label' => 'Chờ thanh toán', 'bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'dot' => 'bg-amber-500'],
                        'paid'      => ['label' => 'Đã thanh toán',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                        'cancelled' => ['label' => 'Đã hủy',         'bg' => 'bg-red-50',     'text' => 'text-red-700',     'dot' => 'bg-red-500'],
                        'refunded'  => ['label' => 'Đã hoàn tiền',   'bg' => 'bg-gray-100',   'text' => 'text-gray-600',    'dot' => 'bg-gray-400'],
                    ];
                    $ps = $statusMap[$booking->payment_status] ?? $statusMap['pending'];
                    $seats = $booking->bookingDetails->map(fn($d) => $d->showtimeSeat->seat->seat_row . $d->showtimeSeat->seat->seat_number)->join(', ');
                @endphp
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden hover:shadow-ambient-sm transition-shadow">
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">

                        {{-- Poster --}}
                        <div class="flex-shrink-0">
                            @if($booking->showtime->movie->poster_url)
                                <img src="{{ $booking->showtime->movie->poster_url }}"
                                     alt="poster"
                                     class="w-14 h-20 object-cover rounded-lg border border-outline-variant">
                            @else
                                <div class="w-14 h-20 bg-surface-container rounded-lg flex items-center justify-center border border-outline-variant">
                                    <span class="material-symbols-outlined text-outline text-2xl">movie</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <p class="font-bold text-on-surface text-base leading-tight truncate max-w-xs">
                                        {{ $booking->showtime->movie->title }}
                                    </p>
                                    <p class="text-xs text-outline font-mono mt-0.5">#{{ $booking->booking_code }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $ps['bg'] }} {{ $ps['text'] }} flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $ps['dot'] }}"></span>
                                    {{ $ps['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1 text-xs">
                                <div>
                                    <p class="text-outline font-semibold">Rạp</p>
                                    <p class="text-on-surface font-bold truncate">{{ $booking->showtime->room->cinema->name }}</p>
                                </div>
                                <div>
                                    <p class="text-outline font-semibold">Suất chiếu</p>
                                    <p class="text-primary font-bold">
                                        {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }}
                                        · {{ $booking->showtime->show_date->format('d/m/Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-outline font-semibold">Ghế</p>
                                    <p class="text-on-surface font-bold">{{ $seats ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-outline font-semibold">Ngày đặt</p>
                                    <p class="text-on-surface font-bold">{{ $booking->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total + Action --}}
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 flex-shrink-0">
                            <div class="text-right">
                                <p class="text-xs text-outline uppercase tracking-widest">Tổng tiền</p>
                                <p class="text-xl font-black text-primary">
                                    {{ number_format($booking->final_total ?? $booking->total_amount) }}đ
                                </p>
                            </div>
                            <a href="{{ route('booking.history.show', $booking->id) }}"
                               class="px-4 py-2 bg-surface-container hover:bg-surface-container-high border border-outline-variant text-on-surface text-xs font-bold rounded-lg transition-all flex items-center gap-1.5 whitespace-nowrap">
                                <span class="material-symbols-outlined text-sm">receipt_long</span>
                                Chi Tiết
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $bookings->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
