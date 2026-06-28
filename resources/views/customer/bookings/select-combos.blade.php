@extends('layouts.customer')

@section('title', 'Chọn Combo - FilmGo')

@section('content')
    <div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Progress Bar -->
            <div class="max-w-3xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <!-- Lines -->
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 z-0"></div>
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-600 z-0"></div>
                    
                    <!-- Step 1 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Chọn Ghế</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">2</div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Chọn Combo</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-white border-2 border-slate-200 text-neutral-400 flex items-center justify-center font-bold">3</div>
                        <span class="text-xs font-semibold text-neutral-400 mt-2">Thanh Toán</span>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <form action="{{ route('booking.process-combos', $showtime->id) }}" method="POST" id="comboForm">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Combo Selection List (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm p-6 md:p-8">
                            <!-- Header -->
                            <div class="border-b border-slate-100 pb-4 mb-8 flex justify-between items-center">
                                <div>
                                    <h2 class="text-xl font-bold text-neutral-900 uppercase tracking-tight flex items-center gap-2">
                                        <span class="material-symbols-outlined text-indigo-600">local_pizza</span>
                                        Combo Bắp Nước Ưu Đãi
                                    </h2>
                                    <p class="text-xs text-neutral-400 font-medium mt-1">Gia tăng trải nghiệm xem phim với các gói bắp nước combo cực tiết kiệm.</p>
                                </div>
                            </div>

                            <!-- Combo Cards Grid -->
                            @if($combos->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($combos as $combo)
                                        @php
                                            $qty = isset($savedCombos[$combo->id]) ? intval($savedCombos[$combo->id]) : 0;
                                        @endphp
                                        <div class="flex flex-col sm:flex-row bg-neutral-50/50 border border-slate-150 rounded-2xl overflow-hidden p-4 gap-4 transition-all duration-200 hover:shadow-md hover:border-slate-250">
                                            
                                            <!-- Combo Image -->
                                            <div class="w-full sm:w-28 aspect-video sm:aspect-square rounded-xl bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-200/40">
                                                <img src="{{ $combo->image ? asset('storage/' . $combo->image) : asset('images/no-image.jpg') }}" 
                                                     alt="{{ $combo->combo_name }}" 
                                                     class="w-full h-full object-cover">
                                            </div>

                                            <!-- Combo Details -->
                                            <div class="flex-grow flex flex-col justify-between space-y-3">
                                                <div>
                                                    <h3 class="font-bold text-neutral-800 text-sm md:text-base tracking-tight leading-tight mb-1">
                                                        {{ $combo->combo_name }}
                                                    </h3>
                                                    <p class="text-xs text-neutral-400 font-semibold leading-relaxed line-clamp-2">
                                                        {{ $combo->description }}
                                                    </p>
                                                </div>

                                                <div class="flex justify-between items-end">
                                                    <!-- Price -->
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] text-neutral-400 font-bold uppercase tracking-wider">Đơn Giá</span>
                                                        <span class="text-sm font-black text-indigo-600 combo-price-unit" data-price="{{ $combo->price }}">
                                                            {{ number_format($combo->price) }}đ
                                                        </span>
                                                    </div>

                                                    <!-- Quantity selector -->
                                                    <div class="flex items-center border border-slate-200 bg-white rounded-xl px-1.5 py-1">
                                                        <button type="button" 
                                                                class="w-7 h-7 rounded-lg text-neutral-500 hover:bg-neutral-100 flex items-center justify-center font-bold text-base transition-colors btn-qty-dec" 
                                                                data-id="{{ $combo->id }}">-</button>
                                                        
                                                        <input type="text" 
                                                               name="combos[{{ $combo->id }}]" 
                                                               id="qty-input-{{ $combo->id }}" 
                                                               value="{{ $qty }}" 
                                                               readonly
                                                               class="w-8 text-center text-xs font-black text-neutral-800 focus:outline-none bg-transparent combo-qty-input"
                                                               data-id="{{ $combo->id }}"
                                                               data-price="{{ $combo->price }}">
                                                        
                                                        <button type="button" 
                                                                class="w-7 h-7 rounded-lg text-neutral-500 hover:bg-neutral-100 flex items-center justify-center font-bold text-base transition-colors btn-qty-inc" 
                                                                data-id="{{ $combo->id }}">+</button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Empty state combos -->
                                <div class="text-center py-12 bg-neutral-50/50 rounded-2xl border border-dashed border-slate-200">
                                    <div class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-neutral-400">
                                        <span class="material-symbols-outlined text-3xl">restaurant</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-neutral-800 mb-1">Hiện không có Combo</h3>
                                    <p class="text-xs text-neutral-400">Rạp tạm thời chưa mở bán các sản phẩm bắp nước. Bạn có thể nhấn Tiếp tục đặt vé.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Sticky Order summary & navigation (1/3 width) -->
                    <div class="space-y-6">
                        <!-- Movie Showtime Summary -->
                        <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm p-6">
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

                            <!-- Showtime info summary -->
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
                                    <span class="font-bold text-indigo-600">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | {{ $showtime->show_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-neutral-400 font-medium flex-shrink-0">Ghế Đã Chọn</span>
                                    <div class="font-bold text-neutral-800 text-right">
                                        @foreach($selectedSeats as $ss)
                                            <span class="inline-block bg-indigo-50 border border-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-black ml-1 mb-1">{{ $ss->seat->seat_row . $ss->seat->seat_number }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing detail breakdown -->
                            <div class="py-4 space-y-3 border-b border-slate-100 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-neutral-400 font-medium">Tiền Vé Ghế</span>
                                    <span class="font-bold text-neutral-800 shadow-none">{{ number_format($totalSeatPrice) }}đ</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-neutral-400 font-medium">Tiền Bắp Nước</span>
                                    <span class="font-bold text-neutral-800" id="comboPriceSummary">0đ</span>
                                </div>
                            </div>

                            <!-- Grand total -->
                            <div class="pt-4 flex justify-between items-center">
                                <span class="text-sm font-bold text-neutral-800">Tổng Thanh Toán</span>
                                <span class="text-xl font-black text-indigo-600" id="grandTotalPrice" data-seat-total="{{ $totalSeatPrice }}">
                                    {{ number_format($totalSeatPrice) }}đ
                                </span>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex gap-4">
                            <a href="{{ route('booking.select-seats', $showtime->id) }}" 
                               class="w-1/3 bg-white border border-slate-200 hover:border-indigo-600 hover:text-indigo-600 text-neutral-500 font-bold py-4 rounded-2xl flex items-center justify-center transition-all duration-200">
                                <span class="material-symbols-outlined text-base">arrow_back</span>
                            </a>
                            <button type="submit" 
                                    class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-600/25 transition-all duration-200 flex items-center justify-center gap-2 uppercase tracking-wider text-sm">
                                Tiếp tục đặt vé
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript to Dynamic Handle Combo quantity -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const decButtons = document.querySelectorAll('.btn-qty-dec');
            const incButtons = document.querySelectorAll('.btn-qty-inc');
            const qtyInputs = document.querySelectorAll('.combo-qty-input');
            const comboPriceSummary = document.getElementById('comboPriceSummary');
            const grandTotalPrice = document.getElementById('grandTotalPrice');
            const baseSeatPrice = parseInt(grandTotalPrice.dataset.seatTotal);

            // Decrease quantity action
            decButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const input = document.getElementById('qty-input-' + id);
                    let currentVal = parseInt(input.value);
                    if (currentVal > 0) {
                        input.value = currentVal - 1;
                        calculatePrices();
                    }
                });
            });

            // Increase quantity action
            incButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const input = document.getElementById('qty-input-' + id);
                    let currentVal = parseInt(input.value);
                    if (currentVal < 99) { // Giới hạn tối đa 99 combo
                        input.value = currentVal + 1;
                        calculatePrices();
                    }
                });
            });

            // Run pricing calculation at load (in case of saved combos in session)
            calculatePrices();

            function calculatePrices() {
                let comboTotal = 0;

                qtyInputs.forEach(input => {
                    const qty = parseInt(input.value);
                    const price = parseInt(input.dataset.price);
                    comboTotal += qty * price;
                });

                // Update UI Labels
                comboPriceSummary.textContent = new Intl.NumberFormat('vi-VN').format(comboTotal) + 'đ';
                
                const grandTotal = baseSeatPrice + comboTotal;
                grandTotalPrice.textContent = new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
            }
        });
    </script>
@endsection
