@extends('layouts.customer')

@section('title', 'Chọn Ghế - FilmGo')

@section('content')
    <div class="bg-brand-dark w-full min-h-screen font-sans text-white antialiased py-12 selection:bg-brand-primary selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── Progress Steps ── --}}
            <div class="max-w-xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <div class="absolute inset-x-0 top-5 h-0.5 bg-zinc-700 z-0"></div>
                    <div class="absolute left-0 right-[66.67%] top-5 h-0.5 bg-brand-primary z-0"></div>

                    @php
                        $steps = ['Chọn Phim', 'Chọn Ghế', 'Bắp Nước', 'Thanh Toán'];
                        $currentStep = 2;
                    @endphp
                    @foreach($steps as $i => $label)
                        <div class="z-10 flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-200
                                {{ ($i + 1) < $currentStep ? 'bg-brand-primary border-brand-primary text-white' : '' }}
                                {{ ($i + 1) === $currentStep ? 'bg-brand-primary border-brand-primary text-white ring-4 ring-brand-primary/30' : '' }}
                                {{ ($i + 1) > $currentStep ? 'bg-zinc-800 border-zinc-600 text-zinc-400' : '' }}">
                                @if(($i + 1) < $currentStep)
                                    <span class="material-symbols-outlined text-base">check</span>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest
                                {{ ($i + 1) === $currentStep ? 'text-brand-primary' : 'text-zinc-500' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Seats Selection (2/3 width) -->
                <div class="lg:col-span-2 bg-[#1A1A1A] rounded-[32px] border border-zinc-800 shadow-sm p-6 md:p-8 flex flex-col justify-between">
                    <div>
                        <!-- Title & Note -->
                        <div class="border-b border-zinc-800 pb-4 mb-8">
                            <h2 class="text-xl font-bold text-white uppercase tracking-tight flex items-center gap-2">
                                <span class="material-symbols-outlined text-brand-primary">event_seat</span>
                                Sơ Đồ Chọn Ghế Ngồi
                            </h2>
                            <p class="text-xs text-zinc-400 font-medium mt-1">Vui lòng chọn vị trí ghế ngồi mong muốn. Bạn có thể chọn tối đa 8 ghế.</p>
                        </div>

                        <!-- Screen Visualizer -->
                        <div class="w-full max-w-lg mx-auto mb-16 text-center">
                            <div class="h-1.5 w-full bg-zinc-700 rounded-full shadow-[0_4px_16px_rgba(229,9,20,0.3)]"></div>
                            <span class="inline-block text-[10px] font-black text-zinc-500 uppercase tracking-widest mt-2">Màn Hình Chiếu Phim</span>
                        </div>

                        <!-- Grid Seat map -->
                        <div class="w-full overflow-x-auto pb-4 no-scrollbar">
                            <div class="min-w-[600px] flex flex-col gap-3 justify-center items-center">
                                @foreach($seatsByRow as $row => $seats)
                                    <div class="flex items-center gap-3">
                                        <!-- Row Label Left -->
                                        <div class="w-8 text-center text-sm font-black text-zinc-500">{{ $row }}</div>
                                        
                                        <!-- Seats in Row -->
                                        <div class="flex gap-2">
                                            @foreach($seats as $ss)
                                                @php
                                                    $seatType = $ss->seat->seatType;
                                                    $isBooked = $ss->status !== 'available';
                                                    $isSaved = in_array($ss->id, $savedSeatIds);
                                                    
                                                    // Determine base class by seat type
                                                    $btnClass = 'w-9 h-9 rounded-xl border flex items-center justify-center text-xs font-black transition-all duration-150 ';
                                                    
                                                    if ($isBooked) {
                                                        $btnClass .= 'bg-zinc-800/40 border-zinc-800 text-zinc-600 cursor-not-allowed';
                                                    } else {
                                                        if ($isSaved) {
                                                            $btnClass .= 'bg-brand-primary border-brand-primary text-white shadow-md shadow-brand-primary/20 selected-seat';
                                                        } else {
                                                            if ($seatType->name === 'VIP') {
                                                                $btnClass .= 'bg-red-950/30 hover:bg-red-900/30 border-red-800/50 text-red-400 seat-available';
                                                            } elseif ($seatType->name === 'Sweetbox') {
                                                                $btnClass .= 'bg-pink-950/30 hover:bg-pink-900/30 border-pink-800/50 text-pink-400 seat-available';
                                                            } else {
                                                                $btnClass .= 'bg-zinc-900/50 hover:bg-zinc-800 border-zinc-700 text-zinc-300 seat-available';
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <button type="button" 
                                                        class="{{ $btnClass }}" 
                                                        data-id="{{ $ss->id }}" 
                                                        data-row="{{ $row }}" 
                                                        data-number="{{ $ss->seat->seat_number }}"
                                                        data-price="{{ $showtime->base_price + ($seatType->surcharge_price ?? 0) }}"
                                                        data-type="{{ $seatType->name }}"
                                                        @if($isBooked) disabled @endif>
                                                    {{ $ss->seat->seat_number }}
                                                </button>
                                            @endforeach
                                        </div>
                                        
                                        <!-- Row Label Right -->
                                        <div class="w-8 text-center text-sm font-black text-zinc-500">{{ $row }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="flex flex-wrap justify-center gap-6 mt-12 pt-6 border-t border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-zinc-900/50 border border-zinc-700 block shadow-inner"></span>
                            <span class="text-xs text-zinc-400 font-semibold">Ghế Thường</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-red-950/30 border border-red-800/50 block shadow-inner"></span>
                            <span class="text-xs text-zinc-400 font-semibold">Ghế VIP (+{{ number_format(20000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-pink-950/30 border border-pink-800/50 block shadow-inner"></span>
                            <span class="text-xs text-zinc-400 font-semibold">Sweetbox (+{{ number_format(40000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-brand-primary border border-brand-primary block shadow-md"></span>
                            <span class="text-xs text-zinc-400 font-semibold">Đang Chọn</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-zinc-800/40 border border-zinc-800 block shadow-inner"></span>
                            <span class="text-xs text-zinc-500 font-semibold">Đã Có Khách</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Showtime Summary & Form (1/3 width) -->
                <div class="space-y-6">
                    <!-- Movie / Showtime Info Panel -->
                    <div class="bg-[#1A1A1A] rounded-[32px] border border-zinc-800 shadow-sm overflow-hidden p-6">
                        <div class="flex gap-4 pb-4 border-b border-zinc-800">
                            <div class="w-20 aspect-[2/3] rounded-lg overflow-hidden bg-zinc-800 flex-shrink-0">
                                <img src="{{ $showtime->movie->poster ? asset('storage/' . $showtime->movie->poster) : asset('images/no-image.jpg') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary text-white rounded-md uppercase tracking-wider">{{ $showtime->movie->age_limit }}</span>
                                <h3 class="font-bold text-white text-sm line-clamp-2 uppercase mt-1 leading-tight">{{ $showtime->movie->title }}</h3>
                            </div>
                        </div>

                        <!-- Showtime details -->
                        <div class="py-4 space-y-3 border-b border-zinc-800 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500 font-medium">Rạp Chiếu</span>
                                <span class="font-bold text-white">{{ $showtime->room->cinema->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500 font-medium">Phòng Chiếu</span>
                                <span class="font-bold text-white">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500 font-medium">Suất Chiếu</span>
                                <span class="font-bold text-brand-primary">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | Hôm nay, {{ $showtime->show_date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Ticket Price Info -->
                        <div class="py-4 space-y-3 border-b border-zinc-800 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500 font-medium">Giá Vé Cơ Bản</span>
                                <span class="font-bold text-white">{{ number_format($showtime->base_price) }}đ</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-zinc-500 font-medium">Ghế Đã Chọn</span>
                                <span class="font-bold text-white text-right" id="selectedSeatsLabel">Chưa chọn</span>
                            </div>
                        </div>

                        <!-- Total pricing -->
                        <div class="pt-4 flex justify-between items-center">
                            <span class="text-sm font-bold text-white">Tổng Tiền Vé</span>
                            <span class="text-xl font-black text-brand-primary" id="totalPriceLabel">0đ</span>
                        </div>
                    </div>

                    <!-- Submission Form -->
                    <form action="{{ route('booking.process-seats', $showtime->id) }}" method="POST" id="bookingForm">
                        @csrf
                        <div id="hiddenInputsContainer">
                            @foreach($savedSeatIds as $id)
                                <input type="hidden" name="seat_ids[]" value="{{ $id }}" id="input-{{ $id }}">
                            @endforeach
                        </div>
                        
                        <div id="seat-error-msg" style="display:none" class="mb-3 px-4 py-3 bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-semibold rounded-xl items-center gap-2">
                            <span class="material-symbols-outlined text-base">error</span>
                            <span id="seat-error-text"></span>
                        </div>

                        <button type="submit" 
                                id="submitBtn"
                                class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-brand-primary/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm uppercase tracking-wider"
                                @if(empty($savedSeatIds)) disabled @endif>
                            Tiếp Tục Chọn Combo
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- JavaScript to Handle Interactive Selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.seat-available, .selected-seat');
            const hiddenContainer = document.getElementById('hiddenInputsContainer');
            const submitBtn = document.getElementById('submitBtn');
            const selectedSeatsLabel = document.getElementById('selectedSeatsLabel');
            const totalPriceLabel = document.getElementById('totalPriceLabel');

            // Build map: seatId -> { row, number, status } cho toàn bộ ghế trong phòng
            const allSeatData = {};
            document.querySelectorAll('[data-id]').forEach(btn => {
                allSeatData[btn.dataset.id] = {
                    row: btn.dataset.row,
                    number: parseInt(btn.dataset.number),
                    available: !btn.disabled,
                };
            });

            let selectedSeats = [];

            // Khởi tạo từ session (ghế đã chọn trước)
            document.querySelectorAll('.selected-seat').forEach(btn => {
                selectedSeats.push({
                    id: btn.dataset.id,
                    name: btn.dataset.row + btn.dataset.number,
                    price: parseInt(btn.dataset.price),
                    row: btn.dataset.row,
                    number: parseInt(btn.dataset.number),
                });
            });
            updateSummary();

            function selectSeat(btn) {
                const seatId = btn.dataset.id;
                const row = btn.dataset.row;
                const number = parseInt(btn.dataset.number);
                const price = parseInt(btn.dataset.price);
                const name = row + number;
                
                selectedSeats.push({ id: seatId, name: name, price: price, row: row, number: number });
                
                const type = btn.dataset.type;
                if (type === 'VIP') {
                    btn.classList.remove('bg-red-950/30', 'hover:bg-red-900/30', 'border-red-800/50', 'text-red-400', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.remove('bg-pink-950/30', 'hover:bg-pink-900/30', 'border-pink-800/50', 'text-pink-400', 'seat-available');
                } else {
                    btn.classList.remove('bg-zinc-900/50', 'hover:bg-zinc-800', 'border-zinc-700', 'text-zinc-300', 'seat-available');
                }
                btn.classList.add('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-md', 'shadow-brand-primary/20', 'selected-seat');
                
                if (!document.getElementById('input-' + seatId)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'seat_ids[]';
                    input.value = seatId;
                    input.id = 'input-' + seatId;
                    hiddenContainer.appendChild(input);
                }
            }

            function deselectSeat(btn) {
                const seatId = btn.dataset.id;
                const index = selectedSeats.findIndex(item => item.id === seatId);
                if (index > -1) {
                    selectedSeats.splice(index, 1);
                }
                
                btn.classList.remove('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-md', 'shadow-brand-primary/20', 'selected-seat');
                const type = btn.dataset.type;
                if (type === 'VIP') {
                    btn.classList.add('bg-red-950/30', 'hover:bg-red-900/30', 'border-red-800/50', 'text-red-400', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.add('bg-pink-950/30', 'hover:bg-pink-900/30', 'border-pink-800/50', 'text-pink-400', 'seat-available');
                } else {
                    btn.classList.add('bg-zinc-900/50', 'hover:bg-zinc-800', 'border-zinc-700', 'text-zinc-300', 'seat-available');
                }
                
                const input = document.getElementById('input-' + seatId);
                if (input) input.remove();
            }

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const seatId   = this.dataset.id;
                    const row      = this.dataset.row;
                    const number   = parseInt(this.dataset.number);
                    const price    = parseInt(this.dataset.price);
                    const type     = this.dataset.type;
                    const isCurrentlySelected = selectedSeats.some(s => s.id === seatId);

                    if (type === 'Sweetbox') {
                        const siblingNumber = (number % 2 === 1) ? (number + 1) : (number - 1);
                        const siblingBtn = document.querySelector(`button[data-row="${row}"][data-number="${siblingNumber}"]`);
                        
                        if (siblingBtn) {
                            if (siblingBtn.disabled) {
                                showError('Ghế đôi Sweetbox này đã có một ghế được đặt trước đó, không thể chọn.');
                                return;
                            }

                            if (isCurrentlySelected) {
                                deselectSeat(this);
                                deselectSeat(siblingBtn);
                            } else {
                                if (selectedSeats.length + 2 > 10) {
                                    showError('Bạn chỉ được chọn tối đa 10 ghế.');
                                    return;
                                }
                                selectSeat(this);
                                selectSeat(siblingBtn);
                            }
                        } else {
                            if (isCurrentlySelected) {
                                deselectSeat(this);
                            } else {
                                if (selectedSeats.length >= 10) {
                                    showError('Bạn chỉ được chọn tối đa 10 ghế.');
                                    return;
                                }
                                selectSeat(this);
                            }
                        }
                    } else {
                        if (isCurrentlySelected) {
                            deselectSeat(this);
                        } else {
                            if (selectedSeats.length >= 10) {
                                showError('Bạn chỉ được chọn tối đa 10 ghế.');
                                return;
                            }
                            selectSeat(this);
                        }
                    }

                    updateSummary();
                });
            });

            document.getElementById('bookingForm').addEventListener('submit', function (e) {
                // 1. Kiểm tra lại quy tắc ghế đôi Sweetbox
                for (let s of selectedSeats) {
                    const btn = document.querySelector(`button[data-id="${s.id}"]`);
                    if (btn && btn.dataset.type === 'Sweetbox') {
                        const row = btn.dataset.row;
                        const number = parseInt(btn.dataset.number);
                        const siblingNumber = (number % 2 === 1) ? (number + 1) : (number - 1);
                        
                        const isSiblingSelected = selectedSeats.some(item => item.name === (row + siblingNumber));
                        if (!isSiblingSelected) {
                            e.preventDefault();
                            showError(`Ghế đôi Sweetbox ${row}${number} và ${row}${siblingNumber} phải được chọn cùng nhau.`);
                            return;
                        }
                    }
                }

                // 2. Kiểm tra quy tắc chống ghế trống đơn lẻ
                const err = checkSingleSeatRule(selectedSeats);
                if (err) {
                    e.preventDefault();
                    showError(err);
                }
            });

            /**
             * Thuật toán Single Seat Rule phía client.
             * Xây dựng mảng trạng thái từng hàng:
             *   X = không khả dụng (disabled)
             *   S = đang chọn
             *   O = trống
             * Nếu có ghế O bị chặn cả hai bên bởi X hoặc S hoặc biên hàng → lỗi.
             */
            function checkSingleSeatRule(testSelected) {
                // Nhóm toàn bộ ghế theo hàng
                const rowMap = {};
                Object.entries(allSeatData).forEach(([id, data]) => {
                    if (!rowMap[data.row]) rowMap[data.row] = [];
                    rowMap[data.row].push({ id, number: data.number, available: data.available });
                });

                const selectedSet = new Set(testSelected.map(s => s.id));

                for (const row in rowMap) {
                    const states = {};
                    rowMap[row].forEach(s => {
                        if (selectedSet.has(s.id)) states[s.number] = 'S';
                        else if (!s.available) states[s.number] = 'X';
                        else states[s.number] = 'O';
                    });

                    for (const numStr in states) {
                        const number = parseInt(numStr);
                        if (states[number] !== 'O') continue;

                        const leftExists = states[number - 1] !== undefined;
                        const rightExists = states[number + 1] !== undefined;

                        if (!leftExists || !rightExists) continue;

                        const leftBlocked  = (states[number - 1] === 'X' || states[number - 1] === 'S');
                        const rightBlocked = (states[number + 1] === 'X' || states[number + 1] === 'S');

                        if (leftBlocked && rightBlocked) {
                            if (states[number - 1] === 'S' || states[number + 1] === 'S') {
                                return `Lựa chọn của bạn tạo ra ghế trống cô đơn ở hàng ${row}. Vui lòng chọn lại để không bỏ trống 1 ghế đơn lẻ.`;
                            }
                        }
                    }
                }
                return null;
            }

            function selectVisual(btn) {
                const type = btn.dataset.type;
                if (type === 'VIP') {
                    btn.classList.remove('bg-red-950/30', 'hover:bg-red-900/30', 'border-red-800/50', 'text-red-400', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.remove('bg-pink-950/30', 'hover:bg-pink-900/30', 'border-pink-800/50', 'text-pink-400', 'seat-available');
                } else {
                    btn.classList.remove('bg-zinc-900/50', 'hover:bg-zinc-800', 'border-zinc-700', 'text-zinc-300', 'seat-available');
                }
                btn.classList.add('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-md', 'shadow-brand-primary/20', 'selected-seat');
            }

            function deselectVisual(btn) {
                btn.classList.remove('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-md', 'shadow-brand-primary/20', 'selected-seat');
                const type = btn.dataset.type;
                if (type === 'VIP') {
                    btn.classList.add('bg-red-950/30', 'hover:bg-red-900/30', 'border-red-800/50', 'text-red-400', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.add('bg-pink-950/30', 'hover:bg-pink-900/30', 'border-pink-800/50', 'text-pink-400', 'seat-available');
                } else {
                    btn.classList.add('bg-zinc-900/50', 'hover:bg-zinc-800', 'border-zinc-700', 'text-zinc-300', 'seat-available');
                }
            }

            function updateSummary() {
                if (selectedSeats.length > 0) {
                    selectedSeatsLabel.innerHTML = selectedSeats
                        .sort((a, b) => a.row.localeCompare(b.row) || a.number - b.number)
                        .map(s => `<span class="inline-block bg-brand-primary/10 border border-brand-primary/30 text-brand-primary px-2 py-0.5 rounded font-black ml-1 mb-1">${s.name}</span>`)
                        .join('');
                    const total = selectedSeats.reduce((sum, s) => sum + s.price, 0);
                    totalPriceLabel.textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
                    submitBtn.removeAttribute('disabled');
                } else {
                    selectedSeatsLabel.textContent = 'Chưa chọn';
                    totalPriceLabel.textContent = '0đ';
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            }

            function showError(msg) {
                const el = document.getElementById('seat-error-msg');
                document.getElementById('seat-error-text').textContent = msg;
                el.style.display = 'flex';
                if (window._errorTimer) clearTimeout(window._errorTimer);
                window._errorTimer = setTimeout(() => { el.style.display = 'none'; }, 5000);
            }
        });
    </script>

    @if(session('showtime_started'))
    {{-- Modal thông báo Suất chiếu đã bắt đầu --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: '🎬 Suất chiếu đã bắt đầu!',
                html: `
                    <div style="line-height: 1.7; color: #374151;">
                        <p style="font-size: 15px; margin-bottom: 8px;">
                            Suất chiếu này đã bắt đầu chiếu.
                        </p>
                        <p style="font-size: 14px; color: #6B7280;">
                            Vui lòng đến trực tiếp quầy vé tại rạp để được hỗ trợ mua vé.
                        </p>
                    </div>
                `,
                icon: 'warning',
                iconColor: '#EF4444',
                confirmButtonText: '🏠 Về trang mua vé',
                confirmButtonColor: '#EF4444',
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: 'rgba(0,0,0,0.75)',
                customClass: {
                    popup: 'rounded-2xl',
                    title: 'font-bold text-xl',
                    confirmButton: 'font-bold px-6 py-3 rounded-xl'
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('movies.showing') }}";
                }
            });
        });
    </script>
    @endif

@endsection
