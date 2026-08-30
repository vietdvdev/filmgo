@extends('layouts.manager')

@section('title', 'Vé & Đơn Hàng - ' . $cinema->name . ' - FilmGo')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-bold text-slate-800">Vé &amp; Đơn Hàng</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    {{ $cinema->name }}
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Quản lý và tra cứu toàn bộ đơn đặt vé và bắp nước tại rạp của bạn.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('manager.bookings.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-sm">refresh</span>
                Làm mới
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl shadow-sm">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl shadow-sm">
            <span class="material-symbols-outlined text-red-600">error</span>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Card 1: Tổng đơn hàng --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tổng đơn hàng</p>
                <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($stats['total_orders']) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Khớp với bộ lọc</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">receipt_long</span>
            </div>
        </div>

        {{-- Card 2: Doanh thu thực thu --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Doanh thu đã thu</p>
                <h3 class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['total_revenue']) }} <span class="text-xs font-bold text-slate-500">đ</span></h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Đơn đã thanh toán</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        {{-- Card 3: Phân bố kênh bán --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kênh bán hàng</p>
                <div class="flex items-center gap-3 mt-1.5">
                    <div>
                        <span class="text-xs font-bold text-slate-700">Quầy: </span>
                        <span class="text-sm font-black text-blue-600">{{ number_format($stats['counter_orders']) }}</span>
                    </div>
                    <span class="text-slate-300">|</span>
                    <div>
                        <span class="text-xs font-bold text-slate-700">Online: </span>
                        <span class="text-sm font-black text-indigo-600">{{ number_format($stats['online_orders']) }}</span>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Tỷ lệ theo kênh</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">storefront</span>
            </div>
        </div>

        {{-- Card 4: Đã in vé --}}
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Đã in vé / phiếu</p>
                <h3 class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['printed_orders']) }}</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Đã xuất giấy in nhiệt</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">print</span>
            </div>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('manager.bookings.index') }}" class="space-y-4">
            {{-- Row 1: Search + Sort --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="Tìm theo mã đơn (VD: BK-...), tên khách, email, số điện thoại..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                </div>
                <div class="w-full sm:w-56">
                    <select name="sort" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Sắp xếp: Mới nhất</option>
                        <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Sắp xếp: Cũ nhất</option>
                        <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Tổng tiền: Tăng dần</option>
                        <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Tổng tiền: Giảm dần</option>
                    </select>
                </div>
            </div>

            {{-- Row 2: Select Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                {{-- Phim --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Phim</label>
                    <select name="movie_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="">Tất cả phim</option>
                        @foreach($movies as $movie)
                            <option value="{{ $movie->id }}" @selected(($filters['movie_id'] ?? '') == $movie->id)>{{ $movie->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Phòng chiếu --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Phòng chiếu</label>
                    <select name="room_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="">Tất cả phòng</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(($filters['room_id'] ?? '') == $room->id)>{{ $room->room_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Trạng thái thanh toán --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Thanh toán</label>
                    <select name="payment_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="">Tất cả trạng thái</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Đã thanh toán</option>
                        <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Chờ thanh toán</option>
                        <option value="failed" @selected(($filters['payment_status'] ?? '') === 'failed')>Thất bại</option>
                        <option value="refunded" @selected(($filters['payment_status'] ?? '') === 'refunded')>Hoàn tiền</option>
                    </select>
                </div>

                {{-- Kênh đặt --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kênh đặt hàng</label>
                    <select name="channel" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="">Tất cả kênh</option>
                        <option value="counter" @selected(($filters['channel'] ?? '') === 'counter')>Tại quầy (POS)</option>
                        <option value="online" @selected(($filters['channel'] ?? '') === 'online')>Trực tuyến (Web/App)</option>
                    </select>
                </div>

                {{-- Trạng thái in --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Trạng thái in vé</label>
                    <select name="print_status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <option value="">Tất cả</option>
                        <option value="printed" @selected(($filters['print_status'] ?? '') === 'printed')>Đã in</option>
                        <option value="not_printed" @selected(($filters['print_status'] ?? '') === 'not_printed')>Chưa in</option>
                    </select>
                </div>
            </div>

            {{-- Row 3: Date Ranges & Submit buttons --}}
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-3 pt-1 border-t border-slate-100">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 flex-1">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Ngày chiếu từ</label>
                        <input type="date" name="show_date_from" value="{{ $filters['show_date_from'] ?? '' }}"
                               class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Ngày chiếu đến</label>
                        <input type="date" name="show_date_to" value="{{ $filters['show_date_to'] ?? '' }}"
                               class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Ngày tạo từ</label>
                        <input type="date" name="created_from" value="{{ $filters['created_from'] ?? '' }}"
                               class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Ngày tạo đến</label>
                        <input type="date" name="created_to" value="{{ $filters['created_to'] ?? '' }}"
                               class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2 lg:pt-0">
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                        <span class="material-symbols-outlined text-sm">filter_alt</span>
                        Lọc kết quả
                    </button>
                    <a href="{{ route('manager.bookings.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-sm">clear_all</span>
                        Xóa lọc
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Bookings Table --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        @if($bookings->isEmpty())
            <div class="py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-3xl">inbox</span>
                </div>
                <h3 class="text-base font-bold text-slate-800">Không tìm thấy đơn hàng nào</h3>
                <p class="text-sm text-slate-500 mt-1 max-w-md mx-auto">
                    Hiện chưa có đơn đặt vé nào phù hợp với bộ lọc đã chọn. Hãy thử thay đổi từ khóa hoặc điều kiện tìm kiếm.
                </p>
                <div class="mt-4">
                    <a href="{{ route('manager.bookings.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-sm">restart_alt</span>
                        Xem tất cả đơn hàng
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3.5">Mã đơn &amp; Loại</th>
                            <th class="px-4 py-3.5">Khách hàng</th>
                            <th class="px-4 py-3.5">Suất chiếu / Phim</th>
                            <th class="px-4 py-3.5">Ghế / Combo</th>
                            <th class="px-4 py-3.5">Tổng tiền</th>
                            <th class="px-4 py-3.5">Thanh toán</th>
                            <th class="px-4 py-3.5">Trạng thái in</th>
                            <th class="px-4 py-3.5">Thời gian</th>
                            <th class="px-4 py-3.5 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bookings as $booking)
                            @php
                                $isComboOnly = ($booking->booking_type === 'combo_only' || !$booking->showtime_id);
                                $movie = optional($booking->showtime)->movie;
                                $room = optional($booking->showtime)->room;
                                
                                // Payment Status Badge
                                $ps = $booking->payment_status;
                                $psBadge = match($ps) {
                                    'paid'     => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Đã thanh toán', 'icon' => 'check_circle'],
                                    'pending'  => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Chờ thanh toán', 'icon' => 'hourglass_empty'],
                                    'failed'   => ['bg' => 'bg-red-50 text-red-700 border-red-200', 'label' => 'Thất bại', 'icon' => 'cancel'],
                                    'refunded' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'Hoàn tiền', 'icon' => 'replay'],
                                    default    => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => $ps, 'icon' => 'help_outline'],
                                };

                                // Channel Badge
                                $isCounter = ($booking->channel === 'counter');
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                {{-- Mã đơn & Loại --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <a href="{{ route('manager.bookings.show', $booking->id) }}" 
                                           class="font-mono font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $booking->booking_code }}
                                        </a>
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $isComboOnly ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $isComboOnly ? 'Bắp nước' : 'Vé xem phim' }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $isCounter ? 'bg-slate-100 text-slate-700' : 'bg-indigo-50 text-indigo-700' }}">
                                                {{ $isCounter ? 'Tại quầy' : 'Online' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Khách hàng --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800 truncate max-w-[140px]" title="{{ $booking->user->full_name ?? 'Khách vãng lai' }}">
                                            {{ $booking->user->full_name ?? 'Khách vãng lai' }}
                                        </span>
                                        <span class="text-xs text-slate-500 truncate max-w-[140px]" title="{{ $booking->user->phone ?? $booking->user->email ?? '—' }}">
                                            {{ $booking->user->phone ?? $booking->user->email ?? '—' }}
                                        </span>
                                        @if($booking->staff)
                                            <span class="text-[10px] text-blue-600 font-medium mt-0.5" title="Nhân viên thu ngân">
                                                Thu ngân: {{ $booking->staff->full_name }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Suất chiếu / Phim --}}
                                <td class="px-4 py-3">
                                    @if($isComboOnly)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <span class="material-symbols-outlined text-amber-500 text-lg">fastfood</span>
                                            <span class="text-xs font-medium">Đơn hàng bắp nước lẻ</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 truncate max-w-[180px]" title="{{ optional($movie)->title ?? '—' }}">
                                                {{ optional($movie)->title ?? '—' }}
                                            </span>
                                            <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
                                                <span>{{ optional($room)->room_name ?? 'Phòng ?' }}</span>
                                                <span>•</span>
                                                <span class="font-medium text-slate-700">
                                                    {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '' }} 
                                                    {{ $booking->showtime ? \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m') : '' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Ghế / Combo --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        @if(!$isComboOnly && $booking->bookingDetails->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 max-w-[150px]">
                                                @foreach($booking->bookingDetails as $detail)
                                                    @php
                                                        $seat = optional($detail->showtimeSeat)->seat;
                                                    @endphp
                                                    @if($seat)
                                                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 text-[11px] font-bold border border-slate-200">
                                                            {{ $seat->seat_row }}{{ $seat->seat_number }}
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Combos count --}}
                                        @php
                                            $totalCombos = $booking->combos->count() + $booking->comboItems->count();
                                        @endphp
                                        @if($totalCombos > 0)
                                            <span class="text-[11px] text-amber-700 font-semibold flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[13px]">lunch_dining</span>
                                                {{ $totalCombos }} món bắp nước
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Tổng tiền --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-black text-slate-800">{{ number_format($booking->final_total) }} <span class="text-xs font-bold text-slate-500">đ</span></span>
                                    @if($booking->discount_amount > 0)
                                        <div class="text-[10px] text-red-600 font-semibold mt-0.5">
                                            -{{ number_format($booking->discount_amount) }}đ
                                        </div>
                                    @endif
                                </td>

                                {{-- Thanh toán --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $psBadge['bg'] }}">
                                        <span class="material-symbols-outlined text-xs">{{ $psBadge['icon'] }}</span>
                                        {{ $psBadge['label'] }}
                                    </span>
                                </td>

                                {{-- Trạng thái in --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($booking->printed_at)
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold text-xs" title="In lúc: {{ $booking->printed_at->format('d/m/Y H:i:s') }}">
                                            <span class="material-symbols-outlined text-sm">print</span>
                                            Đã in
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-400 text-xs">
                                            <span class="material-symbols-outlined text-sm">do_not_disturb_on</span>
                                            Chưa in
                                        </span>
                                    @endif
                                </td>

                                {{-- Thời gian tạo --}}
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                    <div>{{ $booking->created_at->format('H:i') }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $booking->created_at->format('d/m/Y') }}</div>
                                </td>

                                {{-- Thao tác --}}
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <a href="{{ route('manager.bookings.show', $booking->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 rounded-lg text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-sm">visibility</span>
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($bookings->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $bookings->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
