@extends('layouts.customer')

@section('title', 'Chọn Ghế - FilmGo')

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-850 antialiased py-12 selection:bg-brand-primary selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── Progress Steps ── --}}
            <div class="max-w-xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <div class="absolute inset-x-0 top-5 h-0.5 bg-slate-200 z-0"></div>
                    <div class="absolute left-0 right-[66.67%] top-5 h-0.5 bg-brand-primary z-0"></div>

                    @php
                        $steps = ['Chọn Phim', 'Chọn Ghế', 'Bắp Nước', 'Thanh Toán'];
                        $currentStep = 2;
                    @endphp
                    @foreach($steps as $i => $label)
                        <div class="z-10 flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2 transition-all duration-200
                                {{ ($i + 1) < $currentStep ? 'bg-brand-primary border-brand-primary text-white' : '' }}
                                {{ ($i + 1) === $currentStep ? 'bg-brand-primary border-brand-primary text-white ring-4 ring-brand-primary/20' : '' }}
                                {{ ($i + 1) > $currentStep ? 'bg-white border-slate-300 text-slate-400' : '' }}">
                                @if(($i + 1) < $currentStep)
                                    <span class="material-symbols-outlined text-base">check</span>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <span class="text-[10px] font-bold uppercase tracking-widest
                                {{ ($i + 1) === $currentStep ? 'text-brand-primary' : 'text-slate-400' }}">
                                {{ $label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Seats Selection (2/3 width) -->
                <div class="lg:col-span-2 bg-white rounded-none border border-slate-200 shadow-sm p-6 md:p-8 flex flex-col justify-between">
                    <div>
                        <!-- Title & Note -->
                        <div class="border-b border-slate-200 pb-4 mb-8">
                            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                                <span class="material-symbols-outlined text-brand-primary">event_seat</span>
                                Sơ Đồ Chọn Ghế Ngồi
                            </h2>
                            <p class="text-xs text-slate-500 font-medium mt-1">Vui lòng chọn vị trí ghế ngồi mong muốn. Bạn có thể chọn tối đa 10 ghế.</p>
                        </div>

                        <!-- Screen Visualizer -->
                        <div class="w-full max-w-lg mx-auto mb-16 text-center">
                            <div class="h-1.5 w-full bg-slate-300 rounded-full shadow-[0_4px_16px_rgba(229,9,20,0.15)]"></div>
                            <span class="inline-block text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Màn Hình Chiếu Phim</span>
                        </div>

                        <!-- Grid Seat map -->
                        <div class="w-full overflow-x-auto pb-6 pt-2 px-2 no-scrollbar relative">
                            <div class="min-w-max flex flex-col gap-3 md:justify-center w-max mx-auto">
                                @foreach($seatsByRow as $row => $seats)
                                    <div class="flex items-center gap-2 relative">
                                        <!-- Row Label Left -->
                                        <div class="sticky left-0 z-20 w-8 h-9 flex items-center justify-center text-sm font-black text-slate-600 bg-white/95 backdrop-blur-sm rounded-r-lg shadow-[2px_0_5px_rgba(0,0,0,0.05)] border-y border-r border-slate-100">
                                            {{ $row }}
                                        </div>
                                        
                                        <!-- Seats in Row -->
                                        <div class="flex gap-2 px-1">
                                            @foreach($seats as $ss)
                                                @php
                                                    $seatType = $ss->seat->seatType;
                                                    $isSaved  = in_array($ss->id, $savedSeatIds);

                                                    // Ghế do CHÍNH user hiện tại đang giữ (holding/locked) từ session trước
                                                    $isHeldByMe = $isSaved && in_array($ss->status, ['holding', 'locked']);

                                                    // Ghế bị chiếm khi: không phải available VÀ không phải do mình giữ
                                                    $isBooked = ($ss->status !== 'available') && !$isHeldByMe;

                                                    // Xác định class CSS
                                                    $btnClass = 'w-9 h-9 rounded-xl border-2 flex items-center justify-center text-xs font-black transition-all duration-200 ';

                                                    if ($isHeldByMe) {
                                                        // Ghế đang do mình giữ → hiển thị màu đỏ selected
                                                        $btnClass .= 'bg-brand-primary border-brand-primary text-white shadow-[0_0_12px_rgba(229,9,20,0.4)] scale-110 z-10 selected-seat';
                                                    } elseif ($isBooked) {
                                                        // Ghế đã có người khác đặt → disabled
                                                        $btnClass .= 'bg-slate-200 border-slate-300 text-slate-400 cursor-not-allowed opacity-70';
                                                    } else {
                                                        // Ghế trống → màu theo loại ghế
                                                        if ($seatType->name === 'VIP') {
                                                            $btnClass .= 'bg-gradient-to-br from-amber-100 to-amber-200 border-amber-400 text-amber-800 hover:from-amber-200 hover:to-amber-300 shadow-sm seat-available';
                                                        } elseif ($seatType->name === 'Sweetbox') {
                                                            $btnClass .= 'bg-gradient-to-br from-pink-100 to-pink-200 border-pink-400 text-pink-800 hover:from-pink-200 hover:to-pink-300 shadow-sm seat-available';
                                                        } else {
                                                            $btnClass .= 'bg-white border-slate-200 text-slate-600 hover:border-brand-primary hover:text-brand-primary shadow-sm seat-available';
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
                                        <div class="sticky right-0 z-20 w-8 h-9 flex items-center justify-center text-sm font-black text-slate-600 bg-white/95 backdrop-blur-sm rounded-l-lg shadow-[-2px_0_5px_rgba(0,0,0,0.05)] border-y border-l border-slate-100">
                                            {{ $row }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="flex flex-wrap justify-center gap-6 mt-12 pt-6 border-t border-slate-200">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-white border-2 border-slate-200 block shadow-sm"></span>
                            <span class="text-xs text-slate-600 font-semibold">Ghế Thường</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-amber-400 block shadow-sm"></span>
                            <span class="text-xs text-slate-600 font-semibold">Ghế VIP (+{{ number_format(20000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-gradient-to-br from-pink-100 to-pink-200 border-2 border-pink-400 block shadow-sm"></span>
                            <span class="text-xs text-slate-600 font-semibold">Sweetbox (+{{ number_format(40000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-brand-primary border-2 border-brand-primary block shadow-[0_0_8px_rgba(229,9,20,0.4)]"></span>
                            <span class="text-xs text-slate-600 font-semibold">Đang Chọn</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-200 border-2 border-slate-300 block opacity-70"></span>
                            <span class="text-xs text-slate-400 font-semibold">Đã Có Khách</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Showtime Summary & Form (1/3 width) -->
                <div class="space-y-6">
                    <!-- Movie / Showtime Info Panel -->
                    <div class="bg-white rounded-none border border-slate-200 shadow-sm overflow-hidden p-6">
                        <div class="flex gap-4 pb-4 border-b border-slate-200">
                            <div class="w-20 aspect-[2/3] rounded-none overflow-hidden bg-slate-100 border border-slate-200 flex-shrink-0">
                                <img src="{{ $showtime->movie->poster ? asset('storage/' . $showtime->movie->poster) : asset('images/no-image.jpg') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary text-white rounded-none uppercase tracking-wider">{{ $showtime->movie->age_limit }}</span>
                                <h3 class="font-bold text-slate-900 text-sm line-clamp-2 uppercase mt-1 leading-tight">{{ $showtime->movie->title }}</h3>
                            </div>
                        </div>

                        <!-- Showtime details -->
                        <div class="py-4 space-y-3 border-b border-slate-200 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Rạp Chiếu</span>
                                <span class="font-bold text-slate-800">{{ $showtime->room->cinema->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Phòng Chiếu</span>
                                <span class="font-bold text-slate-800">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Suất Chiếu</span>
                                <span class="font-bold text-brand-primary">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | Hôm nay, {{ $showtime->show_date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Ticket Price Info -->
                        <div class="py-4 space-y-3 border-b border-slate-200 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Giá Vé Cơ Bản</span>
                                <span class="font-bold text-slate-800">{{ number_format($showtime->base_price) }}đ</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-slate-500 font-medium">Ghế Đã Chọn</span>
                                <span class="font-bold text-slate-800 text-right" id="selectedSeatsLabel">Chưa chọn</span>
                            </div>
                        </div>

                        <!-- Total pricing -->
                        <div class="pt-4 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-800">Tổng Tiền Vé</span>
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
                        
                        <div id="seat-error-msg" style="display:none" class="mb-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-none items-center gap-2">
                            <span class="material-symbols-outlined text-base text-brand-primary">error</span>
                            <span id="seat-error-text"></span>
                        </div>

                        <button type="submit" 
                                id="submitBtn"
                                class="w-full bg-brand-primary hover:bg-red-700 text-white font-bold py-4 px-6 rounded-none shadow-md shadow-brand-primary/20 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm uppercase tracking-wider"
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
            history.replaceState({ page: 'select-seats', showtimeId: {{ $showtime->id }} }, '', window.location.href);

            const buttons = document.querySelectorAll('.seat-available, .selected-seat');
            const hiddenContainer = document.getElementById('hiddenInputsContainer');
            const submitBtn = document.getElementById('submitBtn');
            const selectedSeatsLabel = document.getElementById('selectedSeatsLabel');
            const totalPriceLabel = document.getElementById('totalPriceLabel');

            const allSeatData = {};
            document.querySelectorAll('[data-id]').forEach(btn => {
                allSeatData[btn.dataset.id] = {
                    row: btn.dataset.row,
                    number: parseInt(btn.dataset.number),
                    available: !btn.disabled,
                };
            });

            let selectedSeats = [];

            // Khởi tạo từ session (ghế đã chọn / đang giữ trước)
            document.querySelectorAll('.selected-seat').forEach(btn => {
                const seatId = btn.dataset.id;
                selectedSeats.push({
                    id: seatId,
                    name: btn.dataset.row + btn.dataset.number,
                    price: parseInt(btn.dataset.price),
                    row: btn.dataset.row,
                    number: parseInt(btn.dataset.number),
                });

                if (!document.getElementById('input-' + seatId)) {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'seat_ids[]';
                    input.value = seatId;
                    input.id    = 'input-' + seatId;
                    hiddenContainer.appendChild(input);
                }
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
                    btn.classList.remove('bg-gradient-to-br', 'from-amber-100', 'to-amber-200', 'border-amber-400', 'text-amber-800', 'hover:from-amber-200', 'hover:to-amber-300', 'shadow-sm', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.remove('bg-gradient-to-br', 'from-pink-100', 'to-pink-200', 'border-pink-400', 'text-pink-800', 'hover:from-pink-200', 'hover:to-pink-300', 'shadow-sm', 'seat-available');
                } else {
                    btn.classList.remove('bg-white', 'border-slate-200', 'text-slate-600', 'hover:border-brand-primary', 'hover:text-brand-primary', 'shadow-sm', 'seat-available');
                }
                btn.classList.add('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-[0_0_12px_rgba(229,9,20,0.4)]', 'scale-110', 'z-10', 'selected-seat');
                
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
                
                btn.classList.remove('bg-brand-primary', 'border-brand-primary', 'text-white', 'shadow-[0_0_12px_rgba(229,9,20,0.4)]', 'scale-110', 'z-10', 'selected-seat');
                const type = btn.dataset.type;
                if (type === 'VIP') {
                    btn.classList.add('bg-gradient-to-br', 'from-amber-100', 'to-amber-200', 'border-amber-400', 'text-amber-800', 'hover:from-amber-200', 'hover:to-amber-300', 'shadow-sm', 'seat-available');
                } else if (type === 'Sweetbox') {
                    btn.classList.add('bg-gradient-to-br', 'from-pink-100', 'to-pink-200', 'border-pink-400', 'text-pink-800', 'hover:from-pink-200', 'hover:to-pink-300', 'shadow-sm', 'seat-available');
                } else {
                    btn.classList.add('bg-white', 'border-slate-200', 'text-slate-600', 'hover:border-brand-primary', 'hover:text-brand-primary', 'shadow-sm', 'seat-available');
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

                const err = checkSingleSeatRule(selectedSeats);
                if (err) {
                    e.preventDefault();
                    showError(err);
                }
            });

            function checkSingleSeatRule(testSelected) {
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

                        const leftExists  = states[number - 1] !== undefined;
                        const rightExists = states[number + 1] !== undefined;

                        if (!leftExists && !rightExists) continue;

                        if (!leftExists && rightExists) {
                            if (states[number + 1] === 'S') {
                                return `Lựa chọn của bạn bỏ trống ghế góc cô đơn ở đầu hàng ${row} (ghế số ${number}). Vui lòng chọn từ ghế đầu hàng hoặc chọn liên tiếp.`;
                            }
                            continue;
                        }

                        if (leftExists && !rightExists) {
                            if (states[number - 1] === 'S') {
                                return `Lựa chọn của bạn bỏ trống ghế góc cô đơn ở cuối hàng ${row} (ghế số ${number}). Vui lòng chọn đến ghế cuối hàng hoặc chọn liên tiếp.`;
                            }
                            continue;
                        }

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

            function updateSummary() {
                if (selectedSeats.length > 0) {
                    selectedSeatsLabel.innerHTML = selectedSeats
                        .sort((a, b) => a.row.localeCompare(b.row) || a.number - b.number)
                        .map(s => `<span class="inline-block bg-brand-primary/10 border border-brand-primary/20 text-brand-primary px-2 py-0.5 rounded font-black ml-1 mb-1">${s.name}</span>`)
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

            let leavingViaForm = false;

            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm) {
                bookingForm.addEventListener('submit', function () {
                    leavingViaForm = true;
                });
            }

            const beaconUrl  = "{{ route('booking.release-seats-beacon', $showtime->id) }}";
            const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content ?? "{{ csrf_token() }}";

            function buildBeaconPayload() {
                const seatIds = selectedSeats.map(s => s.id);
                return new Blob(
                    [JSON.stringify({ seat_ids: seatIds, _token: csrfToken })],
                    { type: 'application/json' }
                );
            }

            window.addEventListener('beforeunload', function () {
                if (leavingViaForm) return;
                if (selectedSeats.length === 0) return;
                navigator.sendBeacon(beaconUrl, buildBeaconPayload());
            });

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden' && !leavingViaForm && selectedSeats.length > 0) {
                    navigator.sendBeacon(beaconUrl, buildBeaconPayload());
                }
            });

        });
    </script>

    @if(session('showtime_started'))
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
