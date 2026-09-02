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
                Lịch Sử Đặt Hàng & Đặt Vé
            </h1>
            <p class="text-sm text-gray-500 mt-1">Tất cả đơn vé xem phim và đơn mua bắp nước của bạn tại FilmGo</p>
        </div>

        @if($bookings->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-gray-400">
                <span class="material-symbols-outlined text-6xl mb-4 text-gray-300">receipt_long</span>
                <p class="text-xl font-bold text-gray-800">Bạn chưa có đơn hàng nào</p>
                <p class="text-sm mt-1 mb-6">Hãy trải nghiệm dịch vụ của FilmGo ngay!</p>
                <div class="flex gap-3">
                    <a href="{{ route('home') }}"
                       class="px-6 py-3 bg-brand-primary hover:bg-red-700 text-white font-bold rounded-xl shadow-sm transition-colors text-sm">
                        Khám Phá Phim
                    </a>
                    <a href="{{ route('combo-shop.index') }}"
                       class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-sm transition-colors text-sm">
                        Mua Bắp Nước
                    </a>
                </div>
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
                    $isComboOnly = ($booking->booking_type === 'combo_only' || !$booking->showtime_id);
                @endphp
                <div class="bg-white border border-gray-200/80 rounded-2xl overflow-hidden hover:border-gray-300 shadow-sm hover:shadow transition-all duration-200">
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center gap-4">

                        {{-- Poster --}}
                        <div class="flex-shrink-0">
                            @if(!$isComboOnly && optional(optional($booking->showtime)->movie)->poster_url)
                                <img src="{{ $booking->showtime->movie->poster_url }}"
                                     alt="poster"
                                     class="w-14 h-20 object-cover rounded-lg border border-gray-100 shadow-sm">
                            @elseif($isComboOnly)
                                <div class="w-14 h-20 bg-amber-50 rounded-lg flex flex-col items-center justify-center border border-amber-200 text-amber-500">
                                    <span class="material-symbols-outlined text-2xl">fastfood</span>
                                    <span class="text-[9px] font-bold mt-0.5">F&B</span>
                                </div>
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
                                        @if($isComboOnly)
                                            🍿 Đơn Hàng Bắp Nước &amp; Combo
                                        @else
                                            {{ optional(optional($booking->showtime)->movie)->title ?? 'Vé Xem Phim' }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">#{{ $booking->booking_code }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $ps['bg'] }} {{ $ps['text'] }} border {{ $ps['border'] }} flex-shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $ps['dot'] }}"></span>
                                    {{ $ps['label'] }}
                                </span>
                            </div>

                            @if($isComboOnly)
                                <div class="mb-2">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="material-symbols-outlined text-brand-primary text-[18px]">local_dining</span>
                                        <span class="text-xs font-bold text-gray-800 uppercase">Chi tiết Bắp Nước</span>
                                    </div>
                                    <ul class="text-xs font-semibold text-gray-700 space-y-1 mb-3 pl-6 border-l-2 border-dashed border-gray-200">
                                        @forelse($booking->combos as $combo)
                                            <li>{{ $combo->name }} <span class="text-brand-primary font-bold">x{{ $combo->pivot->quantity }}</span></li>
                                        @empty
                                            <li>Không có thông tin bắp nước</li>
                                        @endforelse
                                    </ul>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div>
                                            <p class="text-gray-400 font-semibold mb-0.5">Nhận tại rạp</p>
                                            <p class="text-brand-primary font-bold truncate">{{ optional($booking->cinema)->name ?? 'Chưa xác định' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-400 font-semibold mb-0.5">Ngày đặt</p>
                                            <p class="text-gray-800 font-bold">{{ $booking->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-1.5 text-xs">
                                    <div>
                                        <p class="text-gray-400 font-semibold mb-0.5">Loại đơn</p>
                                        <p class="text-gray-800 font-bold truncate">
                                            {{ optional(optional(optional($booking->showtime)->room)->cinema)->name ?? 'Xem Phim' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 font-semibold mb-0.5">Rạp / Suất chiếu</p>
                                        <p class="text-brand-primary font-bold">
                                            {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '—' }}
                                            · {{ $booking->showtime ? $booking->showtime->show_date->format('d/m/Y') : '' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 font-semibold mb-0.5">Ghế / Sản phẩm</p>
                                        <p class="text-gray-800 font-bold truncate">
                                            {{ $seats ?: '—' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 font-semibold mb-0.5">Ngày đặt</p>
                                        <p class="text-gray-800 font-bold">{{ $booking->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Badge hạn sử dụng bắp nước --}}
                            @if($isComboOnly)
                                @php
                                    $expiresAt = $booking->combo_expires_at ?? $booking->created_at->copy()->addDays(3);
                                    $isExpired = $expiresAt->isPast();
                                    $daysLeft  = $isExpired ? null : (int) now()->startOfDay()->diffInDays($expiresAt->startOfDay());
                                @endphp
                                @if($isExpired)
                                    <div class="mt-2 text-[11px] bg-red-50 text-red-700 p-2.5 rounded-lg border border-red-200">
                                        <div class="font-bold mb-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">error</span>
                                            Đã hết hạn sử dụng
                                        </div>
                                        <ul class="text-red-600 space-y-0.5 pl-0 list-none">
                                            <li><strong>Ngày đặt:</strong> {{ $booking->created_at->format('d/m/Y') }}</li>
                                            <li><strong>Ngày hết hạn:</strong> {{ $expiresAt->format('d/m/Y') }}</li>
                                        </ul>
                                        <p class="text-red-600 mt-1">Đơn bắp nước đã hết hạn sử dụng, không thể nhận hàng.</p>
                                    </div>
                                @else
                                    <div class="mt-2 text-[11px] bg-orange-50 text-orange-700 p-2.5 rounded-lg border border-orange-200">
                                        <div class="font-bold mb-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">schedule</span>
                                            Hạn nhận bắp nước
                                            @if($daysLeft !== null)
                                                &mdash; <span class="text-brand-primary">(Còn {{ $daysLeft }} ngày)</span>
                                            @endif
                                        </div>
                                        <ul class="text-orange-700 font-semibold space-y-0.5 list-none pl-0 mb-1">
                                            <li><strong>Ngày đặt:</strong> {{ $booking->created_at->format('d/m/Y') }}</li>
                                            <li><strong>Ngày hết hạn:</strong> <span class="text-red-600 font-bold">{{ $expiresAt->format('d/m/Y') }}</span></li>
                                        </ul>
                                        <div class="text-orange-600 flex gap-1 items-start leading-snug">
                                            <span class="material-symbols-outlined text-[13px] flex-shrink-0 mt-0.5">warning</span>
                                            <span>Lưu ý: Bạn cần đến quầy F&amp;B nhận hàng trước ngày hết hạn. Quá thời gian, đơn hàng sẽ mất hiệu lực.</span>
                                        </div>
                                    </div>
                                @endif
                            @endif
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