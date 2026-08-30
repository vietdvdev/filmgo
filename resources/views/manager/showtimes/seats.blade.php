@extends('layouts.manager')

@section('title', 'Tình Trạng Ghế Suất Chiếu - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Suất chiếu: {{ Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($showtime->end_time)->format('H:i') }} | Ngày: {{ $showtime->show_date->format('d/m/Y') }}
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase mt-0.5">
                Tình Trạng Ghế: {{ $showtime->movie->title }}
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Phòng: <span class="font-bold text-slate-700">{{ $showtime->room->room_name }}</span> | Rạp: <span class="font-bold text-slate-700">{{ $showtime->room->cinema->name }}</span>
            </p>
        </div>
        <a href="{{ route('manager.showtimes.index', ['date' => $showtime->show_date->toDateString()]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại danh sách
        </a>
    </div>

    <!-- Stats Dashboard Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white border border-slate-200 shadow-sm p-5 rounded-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng số ghế</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="w-12 h-12 bg-slate-100 flex items-center justify-center text-slate-500 rounded-none">
                <span class="material-symbols-outlined text-2xl">grid_on</span>
            </div>
        </div>

        <div class="bg-white border border-emerald-200 shadow-sm p-5 rounded-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ghế còn trống</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['available'] }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 flex items-center justify-center text-emerald-500 rounded-none">
                <span class="material-symbols-outlined text-2xl">event_seat</span>
            </div>
        </div>

        <div class="bg-white border border-amber-200 shadow-sm p-5 rounded-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ghế đang chọn/giữ</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $stats['holding'] }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-50 flex items-center justify-center text-amber-500 rounded-none">
                <span class="material-symbols-outlined text-2xl">pending</span>
            </div>
        </div>

        <div class="bg-white border border-red-200 shadow-sm p-5 rounded-none flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ghế đã bán/đặt</p>
                <p class="text-2xl font-black text-red-600 mt-1">{{ $stats['booked'] }}</p>
            </div>
            <div class="w-12 h-12 bg-red-50 flex items-center justify-center text-red-500 rounded-none">
                <span class="material-symbols-outlined text-2xl">local_activity</span>
            </div>
        </div>
    </div>

    <!-- Seat Layout Map Container -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none space-y-12">
        <!-- Screen Visualizer -->
        <div class="flex flex-col items-center">
            <div class="w-full max-w-xl h-2.5 bg-slate-400 shadow-[0_4px_20px_rgba(100,116,139,0.3)]"></div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-2">Màn Hình Chiếu</p>
        </div>

        <!-- Seat Grid -->
        <div class="w-full overflow-x-auto py-4">
            @php
                $maxCol = 0;
                $seatMap = [];
                foreach ($seatsGrouped as $rowKey => $rowSeats) {
                    foreach ($rowSeats as $ss) {
                        $seatMap[$rowKey][$ss->seat->seat_number] = $ss;
                        if ($ss->seat->seat_number > $maxCol) {
                            $maxCol = $ss->seat->seat_number;
                        }
                    }
                }
            @endphp
            <div class="w-fit min-w-max mx-auto space-y-2.5">
                @forelse($seatsGrouped as $rowName => $showtimeSeats)
                    <div class="flex items-center gap-2 flex-nowrap">
                        <!-- Row Name Label Left -->
                        <span class="w-6 text-sm font-black text-slate-400 text-center flex-shrink-0">{{ $rowName }}</span>
                        
                        <!-- Seats in Row -->
                        <div class="flex items-center gap-1.5 flex-nowrap">
                            @for($col = 1; $col <= $maxCol; $col++)
                                @if(isset($seatMap[$rowName][$col]))
                                    @php
                                        $showtimeSeat = $seatMap[$rowName][$col];
                                        $seat = $showtimeSeat->seat;
                                        $seatType = $seat->seatType;
                                        
                                        // Mặc định CSS class cho ghế
                                        $seatClass = 'border text-[10px] font-bold flex items-center justify-center select-none rounded-none transition-all h-8 w-8 flex-shrink-0 ';
                                        $tooltip = "Ghế " . $rowName . $seat->seat_number . " - " . ($seatType->name ?? 'Thường');
                                        
                                        if ($showtimeSeat->status === 'booked') {
                                            // Đã đặt/bán
                                            $seatClass .= 'bg-red-600 border-red-700 text-white cursor-not-allowed';
                                            $tooltip .= " (Đã bán)";
                                        } elseif ($showtimeSeat->status === 'holding') {
                                            // Đang giữ/chọn
                                            $seatClass .= 'bg-amber-400 border-amber-500 text-slate-900 animate-pulse cursor-wait';
                                            $tooltip .= " (Đang giữ)";
                                        } else {
                                            // Ghế còn trống (available) - Đổi màu theo loại ghế
                                            if ($seatType && strtolower($seatType->name) === 'vip') {
                                                $seatClass .= 'bg-amber-50 hover:bg-amber-100 border-amber-300 text-amber-800 cursor-pointer';
                                                $tooltip .= " (Trống)";
                                            } elseif ($seatType && (strtolower($seatType->name) === 'sweetbox' || str_contains(strtolower($seatType->name), 'đôi'))) {
                                                $seatClass .= 'bg-pink-50 hover:bg-pink-100 border-pink-300 text-pink-800 cursor-pointer';
                                                $tooltip .= " (Trống)";
                                            } else {
                                                $seatClass .= 'bg-slate-100 hover:bg-slate-200 border-slate-300 text-slate-600 cursor-pointer';
                                                $tooltip .= " (Trống)";
                                            }
                                        }
                                    @endphp
                                    
                                    <div class="{{ $seatClass }}" title="{{ $tooltip }}">
                                        {{ $rowName }}{{ $seat->seat_number }}
                                    </div>
                                @else
                                    {{-- Lối đi / Ô trống --}}
                                    <div class="w-8 h-8 flex-shrink-0 invisible pointer-events-none" aria-hidden="true"></div>
                                @endif
                            @endfor
                        </div>

                        <!-- Row Name Label Right -->
                        <span class="w-6 text-sm font-black text-slate-400 text-center flex-shrink-0">{{ $rowName }}</span>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400 italic">Không có sơ đồ ghế hoặc suất chiếu chưa khởi tạo ghế.</div>
                @endforelse
            </div>
        </div>

        <!-- Legend -->
        <div class="flex flex-col md:flex-row justify-center gap-6 md:gap-12 border-t border-slate-100 pt-6 text-xs">
            <div class="flex flex-wrap justify-center gap-6">
                <span class="font-bold text-slate-400 uppercase tracking-wider self-center mr-2">Phân loại ghế:</span>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-slate-100 border border-slate-300 rounded-none"></div>
                    <span class="font-semibold text-slate-600">Ghế Thường</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-amber-50 border border-amber-300 rounded-none"></div>
                    <span class="font-semibold text-slate-600">Ghế VIP</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-pink-50 border border-pink-300 rounded-none"></div>
                    <span class="font-semibold text-slate-600">Ghế Đôi (Sweetbox)</span>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-6 border-t md:border-t-0 md:border-l border-slate-200 pt-4 md:pt-0 md:pl-8">
                <span class="font-bold text-slate-400 uppercase tracking-wider self-center mr-2">Trạng thái:</span>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-white border border-slate-300 rounded-none flex items-center justify-center font-bold text-[9px] text-slate-400">AA</div>
                    <span class="font-semibold text-slate-600">Trống (Available)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-amber-400 border border-amber-500 rounded-none"></div>
                    <span class="font-semibold text-slate-600">Đang giữ (Holding)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-red-600 border border-red-700 rounded-none"></div>
                    <span class="font-semibold text-slate-600">Đã bán (Booked)</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
