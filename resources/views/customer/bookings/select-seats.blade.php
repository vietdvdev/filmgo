@extends('layouts.customer')

@section('title', 'Chọn Ghế - FilmGo')

@section('content')
    <div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Progress Bar -->
            <div class="max-w-3xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <!-- Lines -->
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 z-0"></div>
                    <div class="absolute left-0 right-1/2 top-1/2 -translate-y-1/2 h-1 bg-indigo-600 z-0"></div>
                    
                    <!-- Step 1 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">1</div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Chọn Ghế</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-white border-2 border-slate-200 text-neutral-400 flex items-center justify-center font-bold">2</div>
                        <span class="text-xs font-semibold text-neutral-400 mt-2">Chọn Combo</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-white border-2 border-slate-200 text-neutral-400 flex items-center justify-center font-bold">3</div>
                        <span class="text-xs font-semibold text-neutral-400 mt-2">Thanh Toán</span>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Seats Selection (2/3 width) -->
                <div class="lg:col-span-2 bg-white rounded-[32px] border border-slate-200/60 shadow-sm p-6 md:p-8 flex flex-col justify-between">
                    <div>
                        <!-- Title & Note -->
                        <div class="border-b border-slate-100 pb-4 mb-8">
                            <h2 class="text-xl font-bold text-neutral-900 uppercase tracking-tight flex items-center gap-2">
                                <span class="material-symbols-outlined text-indigo-600">event_seat</span>
                                Sơ Đồ Chọn Ghế Ngồi
                            </h2>
                            <p class="text-xs text-neutral-400 font-medium mt-1">Vui lòng chọn vị trí ghế ngồi mong muốn. Bạn có thể chọn tối đa 10 ghế.</p>
                        </div>

                        <!-- Screen Visualizer -->
                        <div class="w-full max-w-lg mx-auto mb-16 text-center">
                            <div class="h-1.5 w-full bg-slate-300 rounded-full shadow-[0_4px_16px_rgba(99,102,241,0.2)]"></div>
                            <span class="inline-block text-[10px] font-black text-neutral-400 uppercase tracking-widest mt-2">Màn Hình Chiếu Phim</span>
                        </div>

                        <!-- Grid Seat map -->
                        <div class="w-full overflow-x-auto pb-4">
                            <div class="min-w-[600px] flex flex-col gap-3 justify-center items-center">
                                @foreach($seatsByRow as $row => $seats)
                                    <div class="flex items-center gap-3">
                                        <!-- Row Label Left -->
                                        <div class="w-8 text-center text-sm font-black text-neutral-400">{{ $row }}</div>
                                        
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
                                                        $btnClass .= 'bg-neutral-200 border-neutral-300 text-neutral-400 cursor-not-allowed';
                                                    } else {
                                                        if ($isSaved) {
                                                            $btnClass .= 'bg-indigo-600 border-indigo-700 text-white shadow-md shadow-indigo-600/20 selected-seat';
                                                        } else {
                                                            if ($seatType->name === 'VIP') {
                                                                $btnClass .= 'bg-red-50 hover:bg-red-100 border-red-200 text-red-700 seat-available';
                                                            } elseif ($seatType->name === 'Sweetbox') {
                                                                $btnClass .= 'bg-pink-50 hover:bg-pink-100 border-pink-200 text-pink-700 seat-available';
                                                            } else {
                                                                $btnClass .= 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700 seat-available';
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
                                        <div class="w-8 text-center text-sm font-black text-neutral-400">{{ $row }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="flex flex-wrap justify-center gap-6 mt-12 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-slate-50 border border-slate-200 block shadow-inner"></span>
                            <span class="text-xs text-neutral-500 font-semibold">Ghế Thường</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-red-50 border border-red-200 block shadow-inner"></span>
                            <span class="text-xs text-neutral-500 font-semibold">Ghế VIP (+{{ number_format(20000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-pink-50 border border-pink-200 block shadow-inner"></span>
                            <span class="text-xs text-neutral-500 font-semibold">Sweetbox (+{{ number_format(40000) }}đ)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-600 border border-indigo-700 block shadow-md"></span>
                            <span class="text-xs text-neutral-500 font-semibold">Đang Chọn</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-neutral-200 border border-neutral-300 block shadow-inner"></span>
                            <span class="text-xs text-neutral-500 font-semibold">Đã Có Khách</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Showtime Summary & Form (1/3 width) -->
                <div class="space-y-6">
                    <!-- Movie / Showtime Info Panel -->
                    <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm overflow-hidden p-6">
                        <div class="flex gap-4 pb-4 border-b border-slate-100">
                            <div class="w-20 aspect-[2/3] rounded-lg overflow-hidden bg-slate-100 flex-shrink-0">
                                <img src="{{ $showtime->movie->poster ? asset('storage/' . $showtime->movie->poster) : asset('images/no-image.jpg') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-1">
                                <span class="px-2 py-0.5 text-[9px] font-black bg-indigo-600 text-white rounded-md uppercase tracking-wider">{{ $showtime->movie->age_limit }}</span>
                                <h3 class="font-bold text-neutral-800 text-sm line-clamp-2 uppercase mt-1">{{ $showtime->movie->title }}</h3>
                            </div>
                        </div>

                        <!-- Showtime details -->
                        <div class="py-4 space-y-3 border-b border-slate-100 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-400 font-medium">Rạp Chiếu</span>
                                <span class="font-bold text-neutral-800">{{ $showtime->room->cinema->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-400 font-medium">Phòng Chiếu</span>
                                <span class="font-bold text-neutral-800">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-400 font-medium">Suất Chiếu</span>
                                <span class="font-bold text-indigo-600">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | Hôm nay, {{ $showtime->show_date->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Ticket Price Info -->
                        <div class="py-4 space-y-3 border-b border-slate-100 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-neutral-400 font-medium">Giá Vé Cơ Bản</span>
                                <span class="font-bold text-neutral-800">{{ number_format($showtime->base_price) }}đ</span>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-neutral-400 font-medium">Ghế Đã Chọn</span>
                                <span class="font-bold text-neutral-800 text-right" id="selectedSeatsLabel">Chưa chọn</span>
                            </div>
                        </div>

                        <!-- Total pricing -->
                        <div class="pt-4 flex justify-between items-center">
                            <span class="text-sm font-bold text-neutral-800">Tổng Tiền Vé</span>
                            <span class="text-xl font-black text-indigo-600" id="totalPriceLabel">0đ</span>
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
                        
                        <button type="submit" 
                                id="submitBtn"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-indigo-600/25 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm uppercase tracking-wider"
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

            let selectedSeats = [];

            // Initialize selectedSeats from existing values in DOM
            document.querySelectorAll('.selected-seat').forEach(btn => {
                selectedSeats.push({
                    id: btn.dataset.id,
                    name: btn.dataset.row + btn.dataset.number,
                    price: parseInt(btn.dataset.price)
                });
            });
            updateSummary();

            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const seatId = this.dataset.id;
                    const row = this.dataset.row;
                    const number = this.dataset.number;
                    const price = parseInt(this.dataset.price);
                    const name = row + number;

                    const index = selectedSeats.findIndex(item => item.id === seatId);

                    if (index > -1) {
                        // Deselect seat
                        selectedSeats.splice(index, 1);
                        
                        // Toggle visual class
                        this.classList.remove('bg-indigo-600', 'border-indigo-700', 'text-white', 'shadow-md', 'shadow-indigo-600/20', 'selected-seat');
                        
                        // Restore base visual classes
                        const type = this.dataset.type;
                        if (type === 'VIP') {
                            this.classList.add('bg-red-50', 'hover:bg-red-100', 'border-red-200', 'text-red-700', 'seat-available');
                        } else if (type === 'Sweetbox') {
                            this.classList.add('bg-pink-50', 'hover:bg-pink-100', 'border-pink-200', 'text-pink-700', 'seat-available');
                        } else {
                            this.classList.add('bg-slate-50', 'hover:bg-slate-100', 'border-slate-200', 'text-slate-700', 'seat-available');
                        }

                        // Remove hidden input
                        const input = document.getElementById('input-' + seatId);
                        if (input) input.remove();
                    } else {
                        // Check limit (max 10 seats)
                        if (selectedSeats.length >= 10) {
                            alert('Bạn chỉ được chọn tối đa 10 ghế ngồi.');
                            return;
                        }

                        // Select seat
                        selectedSeats.push({ id: seatId, name: name, price: price });
                        
                        // Toggle visual class
                        const type = this.dataset.type;
                        if (type === 'VIP') {
                            this.classList.remove('bg-red-50', 'hover:bg-red-100', 'border-red-200', 'text-red-700', 'seat-available');
                        } else if (type === 'Sweetbox') {
                            this.classList.remove('bg-pink-50', 'hover:bg-pink-100', 'border-pink-200', 'text-pink-700', 'seat-available');
                        } else {
                            this.classList.remove('bg-slate-50', 'hover:bg-slate-100', 'border-slate-200', 'text-slate-700', 'seat-available');
                        }

                        this.classList.add('bg-indigo-600', 'border-indigo-700', 'text-white', 'shadow-md', 'shadow-indigo-600/20', 'selected-seat');

                        // Add hidden input
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'seat_ids[]';
                        input.value = seatId;
                        input.id = 'input-' + seatId;
                        hiddenContainer.appendChild(input);
                    }

                    updateSummary();
                });
            });

            function updateSummary() {
                if (selectedSeats.length > 0) {
                    selectedSeatsLabel.innerHTML = selectedSeats.map(s => `<span class="inline-block bg-indigo-50 border border-indigo-100 text-indigo-700 px-2 py-0.5 rounded font-black ml-1 mb-1">${s.name}</span>`).join('');
                    const total = selectedSeats.reduce((sum, s) => sum + s.price, 0);
                    totalPriceLabel.textContent = new Intl.NumberFormat('vi-VN').format(total) + 'đ';
                    submitBtn.removeAttribute('disabled');
                } else {
                    selectedSeatsLabel.textContent = 'Chưa chọn';
                    totalPriceLabel.textContent = '0đ';
                    submitBtn.setAttribute('disabled', 'disabled');
                }
            }
        });
    </script>
@endsection
