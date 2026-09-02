@extends('layouts.manager')

@section('title', 'Tình Trạng Ghế Suất Chiếu - FilmGo')

@section('content')
@php
    $nowStr = now()->toDateTimeString();
    $startDateTimeStr = $showtime->show_date->format('Y-m-d') . ' ' . $showtime->start_time;
    $isPastOrOngoing = in_array($showtime->status, ['showing', 'finished', 'cancelled']) || ($startDateTimeStr <= $nowStr);
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between md:items-center border-b border-slate-200 pb-4 gap-4">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Suất chiếu: {{ Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($showtime->end_time)->format('H:i') }} | Ngày: {{ $showtime->show_date->format('d/m/Y') }}
            </span>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase mt-0.5">
                Tình Trạng Ghế: {{ $showtime->movie->title }}
            </h2>
            <p class="text-sm text-slate-500 mt-0.5">
                Phòng: <span class="font-bold text-slate-700">{{ $showtime->room->room_name }}</span> | Rạp: <span class="font-bold text-slate-700">{{ $showtime->room->cinema->name }}</span>
                @if($isPastOrOngoing)
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-bold bg-amber-100 text-amber-800 rounded-none">
                        Suất chiếu đã bắt đầu / kết thúc (Khóa chỉnh sửa)
                    </span>
                @else
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-none">
                        Chưa bắt đầu chiếu (Có thể đổi trạng thái ghế trống/bảo trì)
                    </span>
                @endif
            </p>
        </div>
        <a href="{{ route('manager.showtimes.index', ['date' => $showtime->show_date->toDateString()]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại danh sách
        </a>
    </div>

    <!-- Alert Toast -->
    <div id="toast-message" class="hidden p-4 text-sm font-semibold rounded-none border"></div>

    <!-- Stats Dashboard Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm p-4 rounded-none flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tổng số ghế</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5" id="stat-total">{{ $stats['total'] }}</p>
            </div>
            <div class="w-10 h-10 bg-slate-100 flex items-center justify-center text-slate-500 rounded-none">
                <span class="material-symbols-outlined text-xl">grid_on</span>
            </div>
        </div>

        <div class="bg-white border border-emerald-200 shadow-sm p-4 rounded-none flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ghế còn trống</p>
                <p class="text-2xl font-black text-emerald-600 mt-0.5" id="stat-available">{{ $stats['available'] }}</p>
            </div>
            <div class="w-10 h-10 bg-emerald-50 flex items-center justify-center text-emerald-500 rounded-none">
                <span class="material-symbols-outlined text-xl">event_seat</span>
            </div>
        </div>

        <div class="bg-white border border-amber-200 shadow-sm p-4 rounded-none flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Đang chọn/giữ</p>
                <p class="text-2xl font-black text-amber-600 mt-0.5" id="stat-holding">{{ $stats['holding'] }}</p>
            </div>
            <div class="w-10 h-10 bg-amber-50 flex items-center justify-center text-amber-500 rounded-none">
                <span class="material-symbols-outlined text-xl">pending</span>
            </div>
        </div>

        <div class="bg-white border border-red-200 shadow-sm p-4 rounded-none flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Đã bán/đặt</p>
                <p class="text-2xl font-black text-red-600 mt-0.5" id="stat-booked">{{ $stats['booked'] }}</p>
            </div>
            <div class="w-10 h-10 bg-red-50 flex items-center justify-center text-red-500 rounded-none">
                <span class="material-symbols-outlined text-xl">local_activity</span>
            </div>
        </div>

        <div class="bg-white border border-rose-300 shadow-sm p-4 rounded-none flex items-center justify-between col-span-2 md:col-span-1">
            <div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ghế bảo trì</p>
                <p class="text-2xl font-black text-rose-600 mt-0.5" id="stat-maintenance">{{ $stats['maintenance'] }}</p>
            </div>
            <div class="w-10 h-10 bg-rose-50 flex items-center justify-center text-rose-500 rounded-none">
                <span class="material-symbols-outlined text-xl">build</span>
            </div>
        </div>
    </div>

    <!-- Seat Layout Map Container -->
    <div class="bg-white border border-slate-200 shadow-sm p-6 md:p-8 rounded-none space-y-10">
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
                                        $seatLabel = $rowName . $seat->seat_number;
                                        
                                        // Mặc định CSS class cho ghế
                                        $seatClass = 'border text-[10px] font-bold flex items-center justify-center select-none rounded-none transition-all h-8 w-8 flex-shrink-0 ';
                                        $tooltip = "Ghế " . $seatLabel . " - " . ($seatType->name ?? 'Thường');
                                        $canClick = false;
                                        
                                        if ($showtimeSeat->status === 'booked') {
                                            // Đã đặt/bán
                                            $seatClass .= 'bg-red-600 border-red-700 text-white cursor-not-allowed';
                                            $tooltip .= " (Đã bán - Không thể sửa)";
                                        } elseif ($showtimeSeat->status === 'holding' || $showtimeSeat->status === 'hold') {
                                            // Đang giữ/chọn
                                            $seatClass .= 'bg-amber-400 border-amber-500 text-slate-900 animate-pulse cursor-not-allowed';
                                            $tooltip .= " (Đang giữ chỗ - Không thể sửa)";
                                        } elseif ($showtimeSeat->status === 'maintenance') {
                                            // Đang bảo trì
                                            $seatClass .= 'bg-rose-50 border-rose-300 text-rose-600 ';
                                            if (!$isPastOrOngoing) {
                                                $seatClass .= 'cursor-pointer hover:bg-rose-100 hover:scale-105';
                                                $tooltip .= " (Đang bảo trì - Nhấn để mở bán lại)";
                                                $canClick = true;
                                            } else {
                                                $seatClass .= 'cursor-not-allowed';
                                                $tooltip .= " (Đang bảo trì)";
                                            }
                                        } else {
                                            // Ghế còn trống (available) - Đổi màu theo loại ghế
                                            if ($seatType && strtolower($seatType->name) === 'vip') {
                                                $seatClass .= 'bg-amber-50 border-amber-300 text-amber-800 ';
                                            } elseif ($seatType && (strtolower($seatType->name) === 'sweetbox' || str_contains(strtolower($seatType->name), 'đôi'))) {
                                                $seatClass .= 'bg-pink-50 border-pink-300 text-pink-800 ';
                                            } else {
                                                $seatClass .= 'bg-slate-100 border-slate-300 text-slate-600 ';
                                            }
                                            
                                            if (!$isPastOrOngoing) {
                                                $seatClass .= 'cursor-pointer hover:bg-slate-200 hover:scale-105';
                                                $tooltip .= " (Trống - Nhấn để chuyển sang bảo trì)";
                                                $canClick = true;
                                            } else {
                                                $seatClass .= 'cursor-not-allowed';
                                                $tooltip .= " (Trống)";
                                            }
                                        }
                                    @endphp
                                    
                                    <div id="seat-node-{{ $showtimeSeat->id }}"
                                         class="{{ $seatClass }}" 
                                         title="{{ $tooltip }}"
                                         data-id="{{ $showtimeSeat->id }}"
                                         data-label="{{ $seatLabel }}"
                                         data-status="{{ $showtimeSeat->status }}"
                                         @if($canClick) onclick="handleSeatClick(this)" @endif>
                                        @if($showtimeSeat->status === 'maintenance')
                                            <span class="material-symbols-outlined text-[13px]">build</span>
                                        @else
                                            {{ $seatLabel }}
                                        @endif
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
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 bg-rose-50 border border-rose-300 text-rose-500 rounded-none flex items-center justify-center">
                        <span class="material-symbols-outlined text-[11px]">build</span>
                    </div>
                    <span class="font-semibold text-slate-600">Ghế Bảo Trì</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const showtimeId = "{{ $showtime->id }}";
    const csrfToken = "{{ csrf_token() }}";
    const isPastOrOngoing = {{ $isPastOrOngoing ? 'true' : 'false' }};

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast-message');
        toast.innerText = message;
        toast.className = `p-4 text-sm font-semibold rounded-none border mb-4 block ${
            type === 'success' 
                ? 'bg-emerald-50 text-emerald-800 border-emerald-200' 
                : 'bg-red-50 text-red-800 border-red-200'
        }`;
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(() => { toast.className = 'hidden'; }, 5000);
    }

    function handleSeatClick(element) {
        if (isPastOrOngoing) {
            showToast('Suất chiếu này đã bắt đầu hoặc đã kết thúc. Không thể thay đổi trạng thái ghế.', 'error');
            return;
        }

        const seatId = element.getAttribute('data-id');
        const seatLabel = element.getAttribute('data-label');
        const currentStatus = element.getAttribute('data-status');

        let confirmMsg = '';
        if (currentStatus === 'available') {
            confirmMsg = `Bạn có chắc chắn muốn chuyển ghế ${seatLabel} sang trạng thái BẢO TRÌ cho suất chiếu này không? Khách hàng sẽ không thể đặt ghế này.`;
        } else if (currentStatus === 'maintenance') {
            confirmMsg = `Bạn có chắc chắn muốn MỞ LẠI ghế ${seatLabel} (Trống) để khách hàng có thể đặt vé trong suất chiếu này không?`;
        } else {
            return;
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        fetch(`/manager/showtimes/${showtimeId}/seats/${seatId}/toggle-maintenance`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');

                // Cập nhật giao diện các ghế (bao gồm cả ghế đôi nếu có)
                if (data.toggled_seats) {
                    data.toggled_seats.forEach(seatData => {
                        const node = document.getElementById('seat-node-' + seatData.id);
                        if (node) {
                            node.setAttribute('data-status', seatData.status);
                            
                            if (seatData.status === 'maintenance') {
                                node.className = "border text-[10px] font-bold flex items-center justify-center select-none rounded-none transition-all h-8 w-8 flex-shrink-0 bg-rose-50 border-rose-300 text-rose-600 cursor-pointer hover:bg-rose-100 hover:scale-105";
                                node.title = `Ghế ${seatData.seat_label} - ${seatData.seat_type} (Đang bảo trì - Nhấn để mở bán lại)`;
                                node.innerHTML = '<span class="material-symbols-outlined text-[13px]">build</span>';
                            } else {
                                let typeClass = "bg-slate-100 border-slate-300 text-slate-600 hover:bg-slate-200";
                                const seatType = (seatData.seat_type || '').toLowerCase();
                                if (seatType.includes('vip')) {
                                    typeClass = "bg-amber-50 border-amber-300 text-amber-800 hover:bg-amber-100";
                                } else if (seatType.includes('sweetbox') || seatType.includes('đôi') || seatType.includes('couple') || seatType.includes('doi')) {
                                    typeClass = "bg-pink-50 border-pink-300 text-pink-800 hover:bg-pink-100";
                                }
                                
                                node.className = `border text-[10px] font-bold flex items-center justify-center select-none rounded-none transition-all h-8 w-8 flex-shrink-0 ${typeClass} cursor-pointer hover:scale-105`;
                                node.title = `Ghế ${seatData.seat_label} - ${seatData.seat_type} (Trống - Nhấn để chuyển sang bảo trì)`;
                                node.innerHTML = seatData.seat_label;
                            }
                        }
                    });
                }

                // Cập nhật thẻ thống kê
                if (data.stats) {
                    if (document.getElementById('stat-total')) document.getElementById('stat-total').innerText = data.stats.total;
                    if (document.getElementById('stat-available')) document.getElementById('stat-available').innerText = data.stats.available;
                    if (document.getElementById('stat-holding')) document.getElementById('stat-holding').innerText = data.stats.holding;
                    if (document.getElementById('stat-booked')) document.getElementById('stat-booked').innerText = data.stats.booked;
                    if (document.getElementById('stat-maintenance')) document.getElementById('stat-maintenance').innerText = data.stats.maintenance;
                }
            }
        })
        .catch(err => {
            showToast(err.message || 'Đã xảy ra lỗi khi chuyển trạng thái ghế.', 'error');
        });
    }
</script>
@endsection
