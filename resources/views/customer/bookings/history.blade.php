@extends('layouts.customer')

@section('title', 'Lịch Sử Đặt Vé - FilmGo')

@section('content')
<div class="min-h-screen bg-gray-50 py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        {{-- Header --}}
        <div class="mb-8">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Tài Khoản</p>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-primary">history</span>
                Lịch Sử Đặt Vé
            </h1>
            <p class="text-sm text-gray-500 mt-1">Tất cả đơn đặt vé của bạn tại FilmGo</p>
        </div>

        @if($bookings->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-gray-400">
                <span class="material-symbols-outlined text-6xl mb-4 text-gray-300">confirmation_number</span>
                <p class="text-xl font-bold text-gray-800">Bạn chưa có đơn đặt vé nào</p>
                <p class="text-sm mt-1 mb-6">Hãy đặt vé ngay để trải nghiệm FilmGo!</p>
                <a href="{{ route('home') }}"
                   class="px-6 py-3 bg-brand-primary hover:bg-red-700 text-white font-bold rounded-xl shadow-sm transition-colors text-sm">
                    Khám Phá Phim
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($bookings as $booking)
                @php
                    $statusMap = [
                        'pending'   => ['label' => 'Chờ thanh toán', 'bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'dot' => 'bg-amber-500',   'border' => 'border-amber-200'],
                        'paid'      => ['label' => 'Đã thanh toán',  'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'dot' => 'bg-emerald-500', 'border' => 'border-emerald-200'],
                        'cancelled' => ['label' => 'Đã hủy',         'bg' => 'bg-red-50',     'text' => 'text-red-600',     'dot' => 'bg-red-500',     'border' => 'border-red-200'],
                        'refunded'  => ['label' => 'Đã hoàn tiền',   'bg' => 'bg-gray-100',    'text' => 'text-gray-600',    'dot' => 'bg-gray-400',    'border' => 'border-gray-200'],
                    ];
                    $ps = $statusMap[$booking->payment_status] ?? $statusMap['pending'];
                    $seats = $booking->bookingDetails->map(fn($d) => optional(optional($d->showtimeSeat)->seat)->seat_row . optional(optional($d->showtimeSeat)->seat)->seat_number)->filter()->join(', ');
                @endphp
                <div class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden hover:border-gray-300 shadow-sm hover:shadow transition-all duration-200">
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">

                        {{-- Poster --}}
                        <div class="flex-shrink-0">
                            @if(optional($booking->showtime->movie)->poster_url)
                                <img src="{{ $booking->showtime->movie->poster_url }}"
                                     alt="poster"
                                     class="w-14 h-20 object-cover rounded-lg border border-gray-100 shadow-sm">
                            @else
                                <div class="w-14 h-20 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200">
                                    <span class="material-symbols-outlined text-gray-400 text-2xl">movie</span>
                                </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            <div class="flex items-start justify-between gap-3 flex-wrap">
                                <div>
                                    <p class="font-bold text-gray-900 text-base leading-tight truncate max-w-xs">
                                        {{ optional($booking->showtime->movie)->title ?? '—' }}
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">#{{ $booking->booking_code }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $ps['bg'] }} {{ $ps['text'] }} border {{ $ps['border'] }} flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $ps['dot'] }}"></span>
                                    {{ $ps['label'] }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1.5 text-xs">
                                <div>
                                    <p class="text-gray-400 font-semibold mb-0.5">Rạp</p>
                                    <p class="text-gray-800 font-bold truncate">{{ optional(optional(optional($booking->showtime)->room)->cinema)->name ?? '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 font-semibold mb-0.5">Suất chiếu</p>
                                    <p class="text-brand-primary font-bold">
                                        {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '—' }}
                                        · {{ $booking->showtime ? $booking->showtime->show_date->format('d/m/Y') : '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 font-semibold mb-0.5">Ghế</p>
                                    <p class="text-gray-800 font-bold">{{ $seats ?: '—' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 font-semibold mb-0.5">Ngày đặt</p>
                                    <p class="text-gray-800 font-bold">{{ $booking->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Total + Action --}}
                        <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-3 flex-shrink-0">
                            <div class="text-right">
                                <p class="text-xs text-gray-400 uppercase tracking-widest">Tổng tiền</p>
                                <p class="text-xl font-black text-brand-primary">
                                    {{ number_format($booking->final_total ?? $booking->total_amount) }}đ
                                </p>
                            </div>
                            <a href="{{ route('booking.history.show', $booking->id) }}"
                               class="px-4 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-lg shadow-sm transition-all flex items-center gap-1.5 whitespace-nowrap">
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