@extends('layouts.admin')

@section('title', 'Vé & Đơn Hàng - FilmGo')

@section('content')
{{-- min-w-0 bắt buộc phải có để ngăn nội dung con đâm thủng layout flex cha --}}
<main class="flex-1 min-w-0 overflow-y-auto pt-16 bg-background">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" style="max-width: 1400px;">

        {{-- Header --}}
        <div class="flex justify-between items-center pb-3 border-b border-outline-variant/20">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Vé &amp; Đơn Hàng</h2>
                <p class="text-sm text-on-surface-variant mt-1">Quản lý toàn bộ đơn đặt vé của khách hàng.</p>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Filter Panel: Tối ưu lại flex-wrap thay vì grid ép cứng kích thước --}}
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-sm p-5">
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="space-y-4">
                {{-- Row 1: Search + Sort --}}
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="relative flex-1 min-w-[240px]">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size:20px;">search</span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Mã đơn, tên, email, SĐT..."
                            class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                    <div class="w-full sm:w-[180px]">
                        <select name="sort" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                            <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
                            <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>Cũ nhất</option>
                            <option value="amount_asc" @selected(($filters['sort'] ?? '') === 'amount_asc')>Tổng tiền tăng dần</option>
                            <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Tổng tiền giảm dần</option>
                        </select>
                    </div>
                </div>

                {{-- Row 2 & 3 gộp lại thành Flex Wrap để các ô tự nhảy hàng khi thiếu đất --}}
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Rạp chiếu</label>
                        <select name="cinema_id" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                            <option value="">Tất cả rạp</option>
                            @foreach($cinemas as $cinema)
                                <option value="{{ $cinema->id }}" @selected(($filters['cinema_id'] ?? '') == $cinema->id)>{{ $cinema->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Phim</label>
                        <select name="movie_id" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                            <option value="">Tất cả phim</option>
                            @foreach($movies as $movie)
                                <option value="{{ $movie->id }}" @selected(($filters['movie_id'] ?? '') == $movie->id)>{{ $movie->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Thanh toán</label>
                        <select name="payment_status" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                            <option value="">Tất cả</option>
                            <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Chờ thanh toán</option>
                            <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Đã thanh toán</option>
                            <option value="failed" @selected(($filters['payment_status'] ?? '') === 'failed')>Thất bại</option>
                            <option value="refunded" @selected(($filters['payment_status'] ?? '') === 'refunded')>Hoàn tiền</option>
                        </select>
                    </div>

                </div>

                {{-- Row 3: Date ranges --}}
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Ngày chiếu từ</label>
                        <input type="date" name="show_date_from" value="{{ $filters['show_date_from'] ?? '' }}"
                            class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Ngày chiếu đến</label>
                        <input type="date" name="show_date_to" value="{{ $filters['show_date_to'] ?? '' }}"
                            class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Ngày tạo từ</label>
                        <input type="date" name="created_from" value="{{ $filters['created_from'] ?? '' }}"
                            class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-on-surface-variant mb-1">Ngày tạo đến</label>
                        <input type="date" name="created_to" value="{{ $filters['created_to'] ?? '' }}"
                            class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="submit" class="bg-primary text-on-primary text-sm font-medium px-5 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px;">filter_list</span>
                        Tìm kiếm
                    </button>
                    <a href="{{ route('admin.bookings.index') }}" class="bg-surface-container-high text-on-surface text-sm font-medium px-5 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px;">restart_alt</span>
                        Đặt lại
                    </a>
                </div>
            </form>
        </div>

        {{-- Table Container: Đảm bảo bọc kín, cô lập thanh cuộn ngang độc lập --}}
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-sm w-full overflow-x-auto">
            @if($bookings->isEmpty())
                <div class="text-center py-16 text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">confirmation_number</span>
                    <p class="text-lg font-bold text-on-surface">Không có đơn hàng nào</p>
                    <p class="text-sm mt-1">Thử thay đổi bộ lọc để tìm kiếm.</p>
                </div>
            @else
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="bg-surface-container/60 text-sm text-on-surface-variant border-b border-outline-variant/60">
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">#</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Mã đơn</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Khách hàng</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Phim</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Rạp / Phòng</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Suất chiếu</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Ghế</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Tổng tiền</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Thanh toán</th>
                            <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Ngày tạo</th>
                            <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap pr-6">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-on-surface divide-y divide-outline-variant/40">
                        @foreach($bookings as $booking)
                            @php
                                $seats = $booking->bookingDetails->map(fn($d) => optional(optional($d->showtimeSeat)->seat)->seat_row . optional(optional($d->showtimeSeat)->seat)->seat_number)->filter()->sort()->values();
                                $showtime = $booking->showtime;
                                $cinema = optional(optional($showtime)->room)->cinema;
                            @endphp
                            <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                <td class="py-3.5 px-4 text-on-surface-variant">{{ $loop->iteration + ($bookings->currentPage() - 1) * $bookings->perPage() }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono text-sm font-semibold text-primary">{{ $booking->booking_code }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-on-surface">{{ optional($booking->user)->name ?? '—' }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ optional($booking->user)->email ?? '' }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ optional($booking->user)->phone ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 max-w-[160px]">
                                    <span class="font-medium text-on-surface line-clamp-2">{{ optional(optional($showtime)->movie)->title ?? '—' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-on-surface">{{ optional($cinema)->name ?? '—' }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ optional(optional($showtime)->room)->room_name ?? '' }}</div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="text-sm font-medium">{{ $showtime ? $showtime->show_date->format('d/m/Y') : '—' }}</div>
                                    <div class="text-xs text-on-surface-variant">{{ $showtime ? \Illuminate\Support\Str::substr($showtime->start_time, 0, 5) : '' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-sm font-medium text-on-surface">{{ $seats->implode(', ') ?: '—' }}</span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-primary">{{ number_format($booking->final_total) }} ₫</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $ps = $booking->payment_status;
                                        $psClass = match($ps) {
                                            'paid'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'failed'   => 'bg-red-50 text-red-700 border-red-200',
                                            'refunded' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            default    => 'bg-gray-50 text-gray-600 border-gray-200',
                                        };
                                        $psLabel = match($ps) {
                                            'paid'     => 'Đã thanh toán',
                                            'pending'  => 'Chờ thanh toán',
                                            'failed'   => 'Thất bại',
                                            'refunded' => 'Hoàn tiền',
                                            default    => $ps,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $psClass }}">{{ $psLabel }}</span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-sm text-on-surface-variant">
                                    {{ $booking->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3.5 px-4 text-right pr-6">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-primary hover:bg-primary/10 transition-colors" title="Xem chi tiết">
                                        <span class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-outline-variant/40">
                {{ $bookings->links('pagination::tailwind') }}
            </div>
        </div>

    </div>
</main>
@endsection