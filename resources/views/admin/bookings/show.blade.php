@extends('layouts.admin')

@section('title', 'Chi Tiết Đơn Hàng #' . $booking->booking_code . ' - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-4xl mx-auto space-y-stack-lg">

        {{-- Header --}}
        <div class="flex items-center justify-between pb-2 border-b border-outline-variant/20">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">Chi Tiết Đơn Hàng</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-0.5">
                        Mã đơn: <span class="font-mono font-semibold text-primary">{{ $booking->booking_code }}</span>
                    </p>
                </div>
            </div>

            {{-- Status actions --}}
            <div class="flex items-center gap-2">
                @if($booking->booking_status === 'pending')
                    <form method="POST" action="{{ route('admin.bookings.confirm', $booking->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-2 bg-emerald-600 text-white font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-emerald-700 hover:shadow-md transition-all duration-200">
                            <span class="material-symbols-outlined" style="font-size:18px;">check_circle</span>
                            Xác nhận đơn
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">
                        @csrf @method('PATCH')
                        <button type="submit" class="flex items-center gap-2 bg-red-500 text-white font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-red-600 hover:shadow-md transition-all duration-200">
                            <span class="material-symbols-outlined" style="font-size:18px;">cancel</span>
                            Hủy đơn
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Status badges row --}}
        <div class="flex flex-wrap gap-3">
            @php
                $bs = $booking->booking_status;
                $bsClass = match($bs) { 'confirmed' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'pending' => 'bg-amber-50 text-amber-700 border-amber-200', 'cancelled' => 'bg-red-50 text-red-700 border-red-200', default => 'bg-gray-50 text-gray-600 border-gray-200' };
                $bsLabel = match($bs) { 'confirmed' => 'Đã xác nhận', 'pending' => 'Chờ xác nhận', 'cancelled' => 'Đã hủy', default => $bs };
                $ps = $booking->payment_status;
                $psClass = match($ps) { 'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'pending' => 'bg-amber-50 text-amber-700 border-amber-200', 'failed' => 'bg-red-50 text-red-700 border-red-200', 'refunded' => 'bg-purple-50 text-purple-700 border-purple-200', default => 'bg-gray-50 text-gray-600 border-gray-200' };
                $psLabel = match($ps) { 'paid' => 'Đã thanh toán', 'pending' => 'Chờ thanh toán', 'failed' => 'Thất bại', 'refunded' => 'Hoàn tiền', default => $ps };
            @endphp
            <div class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest rounded-lg border border-outline-variant">
                <span class="text-xs text-on-surface-variant font-medium">Trạng thái đơn:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $bsClass }}">{{ $bsLabel }}</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest rounded-lg border border-outline-variant">
                <span class="text-xs text-on-surface-variant font-medium">Thanh toán:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $psClass }}">{{ $psLabel }}</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-surface-container-lowest rounded-lg border border-outline-variant">
                <span class="text-xs text-on-surface-variant font-medium">Ngày tạo:</span>
                <span class="text-xs font-semibold text-on-surface">{{ $booking->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- Thông tin khách hàng --}}
            <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
                <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">person</span>
                    Thông tin khách hàng
                </h3>
                @php $user = $booking->user; @endphp
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-on-surface-variant">Họ tên</dt>
                        <dd class="text-sm font-semibold text-on-surface">{{ $user->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-on-surface-variant">Email</dt>
                        <dd class="text-sm font-medium text-on-surface">{{ $user->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-on-surface-variant">Số điện thoại</dt>
                        <dd class="text-sm font-medium text-on-surface">{{ $user->phone ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Thông tin phim --}}
            <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
                <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-size:20px;">movie</span>
                    Thông tin phim
                </h3>
                @php
                    $showtime = $booking->showtime;
                    $movie    = optional($showtime)->movie;
                    $room     = optional($showtime)->room;
                    $cinema   = optional($room)->cinema;
                @endphp
                <div class="flex gap-4">
                    @if($movie && $movie->poster)
                        <div class="w-16 h-24 rounded-lg overflow-hidden border border-outline-variant flex-shrink-0">
                            <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <dl class="space-y-2 flex-1">
                        <div class="flex justify-between gap-2">
                            <dt class="text-sm text-on-surface-variant">Phim</dt>
                            <dd class="text-sm font-semibold text-on-surface text-right">{{ optional($movie)->title ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-sm text-on-surface-variant">Rạp</dt>
                            <dd class="text-sm font-medium text-on-surface text-right">{{ optional($cinema)->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-sm text-on-surface-variant">Phòng</dt>
                            <dd class="text-sm font-medium text-on-surface text-right">{{ optional($room)->room_name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-sm text-on-surface-variant">Ngày chiếu</dt>
                            <dd class="text-sm font-medium text-on-surface">{{ $showtime ? $showtime->show_date->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-2">
                            <dt class="text-sm text-on-surface-variant">Giờ chiếu</dt>
                            <dd class="text-sm font-medium text-on-surface">{{ $showtime ? \Illuminate\Support\Str::substr($showtime->start_time, 0, 5) : '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Ghế đã đặt --}}
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
            <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size:20px;">event_seat</span>
                Ghế đã đặt
            </h3>
            @php
                $seats = $booking->bookingDetails->map(fn($d) => [
                    'label' => optional(optional($d->showtimeSeat)->seat)->seat_row . optional(optional($d->showtimeSeat)->seat)->seat_number,
                    'price' => $d->price,
                ])->filter(fn($s) => $s['label'])->sortBy('label')->values();
            @endphp
            @if($seats->isEmpty())
                <p class="text-sm text-on-surface-variant">Không có dữ liệu ghế.</p>
            @else
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($seats as $seat)
                        <span class="inline-flex items-center px-3 py-1.5 bg-primary/10 text-primary border border-primary/20 rounded-lg text-sm font-semibold">
                            {{ $seat['label'] }}
                        </span>
                    @endforeach
                </div>
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-container/60 text-on-surface-variant border-b border-outline-variant/60">
                            <tr>
                                <th class="py-2.5 px-4 font-semibold">Ghế</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Giá vé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/40">
                            @foreach($seats as $seat)
                                <tr class="hover:bg-surface-container-low/40">
                                    <td class="py-2.5 px-4 font-medium">{{ $seat['label'] }}</td>
                                    <td class="py-2.5 px-4 text-right font-semibold text-primary">{{ number_format($seat['price']) }} ₫</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Combo --}}
        @if($booking->combos->isNotEmpty())
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
            <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size:20px;">fastfood</span>
                Combo đã chọn
            </h3>
            <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-container/60 text-on-surface-variant border-b border-outline-variant/60">
                        <tr>
                            <th class="py-2.5 px-4 font-semibold">Tên combo</th>
                            <th class="py-2.5 px-4 font-semibold text-right">Đơn giá</th>
                            <th class="py-2.5 px-4 font-semibold text-right">Số lượng</th>
                            <th class="py-2.5 px-4 font-semibold text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/40">
                        @foreach($booking->combos as $combo)
                            <tr class="hover:bg-surface-container-low/40">
                                <td class="py-2.5 px-4 font-medium">{{ $combo->combo_name }}</td>
                                <td class="py-2.5 px-4 text-right">{{ number_format($combo->price) }} ₫</td>
                                <td class="py-2.5 px-4 text-right">{{ $combo->pivot->quantity }}</td>
                                <td class="py-2.5 px-4 text-right font-semibold text-primary">{{ number_format($combo->pivot->subtotal) }} ₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Voucher --}}
        @if($booking->promotion)
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
            <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size:20px;">sell</span>
                Voucher áp dụng
            </h3>
            @php $promo = $booking->promotion; @endphp
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-surface-container/40 rounded-lg p-3">
                    <dt class="text-xs text-on-surface-variant mb-1">Mã voucher</dt>
                    <dd class="font-mono font-semibold text-primary">{{ $promo->code }}</dd>
                </div>
                <div class="bg-surface-container/40 rounded-lg p-3">
                    <dt class="text-xs text-on-surface-variant mb-1">Loại giảm</dt>
                    <dd class="font-semibold text-on-surface">{{ $promo->discount_type === 'percent' ? 'Phần trăm (%)' : 'Số tiền cố định' }}</dd>
                </div>
                <div class="bg-surface-container/40 rounded-lg p-3">
                    <dt class="text-xs text-on-surface-variant mb-1">Giá trị giảm</dt>
                    <dd class="font-semibold text-emerald-600">
                        @if($promo->discount_type === 'percent')
                            {{ $promo->discount_value }}%
                        @else
                            {{ number_format($promo->discount_value) }} ₫
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
        @endif

        {{-- Thanh toán --}}
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-5">
            <h3 class="font-title-md text-title-md text-on-surface mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary" style="font-size:20px;">payments</span>
                Thông tin thanh toán
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Summary --}}
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Tổng tiền vé + combo</span>
                        <span class="font-medium">{{ number_format($booking->total_amount) }} ₫</span>
                    </div>
                    @if($booking->discount_amount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Giảm giá</span>
                        <span class="font-medium text-emerald-600">- {{ number_format($booking->discount_amount) }} ₫</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm pt-3 border-t border-outline-variant/40">
                        <span class="font-semibold text-on-surface">Thành tiền</span>
                        <span class="font-bold text-lg text-primary">{{ number_format($booking->final_total) }} ₫</span>
                    </div>
                </div>

                {{-- Payment detail --}}
                @php $payment = $booking->payments->first(); @endphp
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Phương thức</span>
                        <span class="font-medium text-on-surface">{{ $payment ? strtoupper($payment->payment_method) : '—' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Trạng thái</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $psClass }}">{{ $psLabel }}</span>
                    </div>
                    @if($payment && $payment->transaction_code)
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Mã giao dịch VNPay</span>
                        <span class="font-mono font-medium text-on-surface">{{ $payment->transaction_code }}</span>
                    </div>
                    @endif
                    @if($payment && $payment->paid_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-on-surface-variant">Ngày thanh toán</span>
                        <span class="font-medium text-on-surface">{{ $payment->paid_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
