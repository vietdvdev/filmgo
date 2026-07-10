@extends('layouts.staff')

@section('title', 'Lịch Chiếu Hôm Nay - FilmGo')

@section('content')
<div class="p-8 space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-label-md text-on-surface-variant uppercase tracking-widest">Nhân Viên</p>
            <h1 class="text-headline-md font-bold text-on-surface flex items-center gap-2 mt-0.5">
                <span class="material-symbols-outlined text-primary text-3xl">today</span>
                Lịch Chiếu Hôm Nay
            </h1>
            <p class="text-body-md text-on-surface-variant mt-1">
                {{ now()->isoFormat('dddd, DD/MM/YYYY') }}
            </p>
        </div>

        {{-- Tổng số suất --}}
        <div class="flex items-center gap-2 px-5 py-3 bg-primary/10 rounded-xl border border-primary/20">
            <span class="material-symbols-outlined text-primary text-2xl">confirmation_number</span>
            <div>
                <p class="text-[11px] font-bold text-primary uppercase tracking-wider">Tổng suất chiếu</p>
                <p class="text-2xl font-black text-primary leading-none">{{ $showtimes->count() }}</p>
            </div>
        </div>
    </div>

    {{-- ── Filter Bar ── --}}
    <form method="GET" action="{{ route('staff.showtimes.index') }}"
          class="bg-white border border-outline-variant rounded-xl p-5 shadow-ambient-sm">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Tìm kiếm tên phim --}}
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl pointer-events-none">search</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Tìm theo tên phim..."
                    class="w-full pl-10 pr-4 py-2.5 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-surface transition-all"
                >
            </div>

            {{-- Lọc phòng chiếu --}}
            <div class="relative sm:w-56">
                <select
                    name="room_id"
                    class="w-full pl-4 pr-10 py-2.5 border border-outline-variant rounded-lg text-body-md focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-surface appearance-none transition-all"
                >
                    <option value="">-- Tất cả phòng --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->room_name }} ({{ $room->room_type }})
                        </option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-on-surface-variant pointer-events-none text-xl">keyboard_arrow_down</span>
            </div>

            {{-- Nút tìm --}}
            <button type="submit"
                    class="px-6 py-2.5 bg-primary text-white font-bold text-label-md rounded-lg hover:bg-primary/90 transition-colors flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-lg">filter_list</span>
                Lọc
            </button>

            @if(request('search') || request('room_id'))
            <a href="{{ route('staff.showtimes.index') }}"
               class="px-5 py-2.5 border border-outline-variant text-on-surface-variant font-bold text-label-md rounded-lg hover:bg-surface-container-low transition-colors flex items-center gap-2 whitespace-nowrap">
                <span class="material-symbols-outlined text-lg">close</span>
                Xóa lọc
            </a>
            @endif
        </div>
    </form>

    {{-- ── Table ── --}}
    <div class="bg-white border border-outline-variant rounded-xl shadow-ambient-sm overflow-hidden">
        @if($showtimes->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-on-surface-variant">
                <span class="material-symbols-outlined text-6xl text-outline mb-4">event_busy</span>
                <p class="text-headline-sm font-semibold text-on-surface">Không có suất chiếu nào</p>
                <p class="text-body-md mt-1">
                    @if(request('search') || request('room_id'))
                        Không tìm thấy suất chiếu phù hợp với bộ lọc hiện tại.
                    @else
                        Hôm nay chưa có suất chiếu nào được lên lịch tại rạp.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low border-b border-outline-variant">
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Giờ Chiếu</th>
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Tên Phim</th>
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Phòng Chiếu</th>
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold">Loại Phòng</th>
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold text-right">Giá Vé</th>
                            <th class="px-5 py-3.5 text-label-md text-on-surface-variant uppercase tracking-wider font-bold text-center">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/50">
                        @foreach($showtimes as $showtime)
                        @php
                            $statusMap = [
                                'upcoming'  => ['label' => 'Chờ mở bán',   'bg' => 'bg-slate-100',   'text' => 'text-slate-600',   'dot' => 'bg-slate-400'],
                                'active'    => ['label' => 'Đang mở bán',  'bg' => 'bg-emerald-50',  'text' => 'text-emerald-700', 'dot' => 'bg-emerald-500'],
                                'showing'   => ['label' => 'Đang chiếu',   'bg' => 'bg-blue-50',     'text' => 'text-blue-700',    'dot' => 'bg-blue-500'],
                                'finished'  => ['label' => 'Đã kết thúc',  'bg' => 'bg-gray-100',    'text' => 'text-gray-500',    'dot' => 'bg-gray-400'],
                                'cancelled' => ['label' => 'Đã hủy',       'bg' => 'bg-red-50',      'text' => 'text-red-600',     'dot' => 'bg-red-500'],
                            ];
                            $s = $statusMap[$showtime->status] ?? $statusMap['upcoming'];
                        @endphp
                        <tr class="hover:bg-surface-container-lowest transition-colors">
                            {{-- Giờ chiếu --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary text-lg">schedule</span>
                                    <div>
                                        <p class="font-bold text-on-surface text-body-md">
                                            {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}
                                        </p>
                                        <p class="text-[11px] text-on-surface-variant">
                                            → {{ \Carbon\Carbon::parse($showtime->end_time)->format('H:i') }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Tên phim --}}
                            <td class="px-5 py-4">
                                <p class="font-semibold text-on-surface text-body-md leading-tight max-w-xs truncate"
                                   title="{{ $showtime->movie->title }}">
                                    {{ $showtime->movie->title }}
                                </p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="px-1.5 py-0.5 text-[10px] font-black border rounded uppercase
                                        {{ $showtime->movie->age_limit === 'P' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' :
                                          ($showtime->movie->age_limit === 'T18' ? 'bg-red-50 text-red-700 border-red-300' :
                                          'bg-amber-50 text-amber-700 border-amber-300') }}">
                                        {{ $showtime->movie->age_limit }}
                                    </span>
                                    <span class="text-[11px] text-on-surface-variant">{{ $showtime->movie->duration }} phút</span>
                                </div>
                            </td>

                            {{-- Phòng chiếu --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-on-surface-variant text-lg">meeting_room</span>
                                    <span class="font-medium text-on-surface text-body-md">{{ $showtime->room->room_name }}</span>
                                </div>
                            </td>

                            {{-- Loại phòng --}}
                            <td class="px-5 py-4">
                                @php
                                    $typeColor = match($showtime->room->room_type) {
                                        'IMAX' => 'bg-purple-50 text-purple-700 border-purple-300',
                                        '4DX'  => 'bg-orange-50 text-orange-700 border-orange-300',
                                        '3D'   => 'bg-blue-50 text-blue-700 border-blue-300',
                                        default => 'bg-slate-50 text-slate-600 border-slate-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[11px] font-black border rounded-lg uppercase {{ $typeColor }}">
                                    {{ $showtime->room->room_type }}
                                </span>
                            </td>

                            {{-- Giá vé --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <span class="font-bold text-on-surface text-body-md">
                                    {{ number_format($showtime->base_price) }}đ
                                </span>
                            </td>

                            {{-- Trạng thái --}}
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border {{ $s['bg'] }} {{ $s['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $s['dot'] }}
                                        {{ in_array($showtime->status, ['active','showing']) ? 'animate-pulse' : '' }}">
                                    </span>
                                    {{ $s['label'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer tổng kết --}}
            <div class="px-5 py-3 bg-surface-container-lowest border-t border-outline-variant/50 flex items-center justify-between text-[12px] text-on-surface-variant">
                <span>Hiển thị <strong class="text-on-surface">{{ $showtimes->count() }}</strong> suất chiếu</span>
                <span>Cập nhật lúc {{ now()->format('H:i') }}</span>
            </div>
        @endif
    </div>

</div>
@endsection
