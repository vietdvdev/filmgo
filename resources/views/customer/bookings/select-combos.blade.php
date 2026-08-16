@extends('layouts.customer')

@section('title', 'Chọn Combo - FilmGo')

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-850 antialiased py-12 selection:bg-brand-primary selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ── Countdown Timer ── --}}
            <div id="countdown-wrapper" class="fixed top-4 right-4 bg-white text-slate-800 px-4 py-2 rounded-full font-bold shadow-md z-50 flex items-center gap-2 border border-slate-200">
                <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">⏳ Giữ ghế:</span>
                <span id="seat-countdown" class="text-base font-black text-brand-primary">05:00</span>
            </div>

            {{-- ── Progress Steps ── --}}
            <div class="max-w-xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <div class="absolute inset-x-0 top-5 h-0.5 bg-slate-200 z-0"></div>
                    <div class="absolute left-0 right-[33.33%] top-5 h-0.5 bg-brand-primary z-0"></div>

                    @php
                        $steps = ['Chọn Phim', 'Chọn Ghế', 'Bắp Nước', 'Thanh Toán'];
                        $currentStep = 3;
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
            <form action="{{ route('booking.process-combos', $showtime->id) }}" method="POST" id="comboForm">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left: Combo Selection List (2/3 width) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white rounded-none border border-slate-200 shadow-sm p-6 md:p-8">
                            <!-- Header -->
                            <div class="border-b border-slate-200 pb-4 mb-8 flex justify-between items-center">
                                <div>
                                    <h2 class="text-xl font-bold text-slate-900 uppercase tracking-tight flex items-center gap-2">
                                        <span class="material-symbols-outlined text-brand-primary">local_pizza</span>
                                        Combo Bắp Nước Ưu Đãi
                                    </h2>
                                    <p class="text-xs text-slate-400 font-medium mt-1">Gia tăng trải nghiệm xem phim với các gói bắp nước combo cực tiết kiệm.</p>
                                </div>
                            </div>

                            <!-- Combo Cards Grid -->
                            @if($combos->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    @foreach($combos as $combo)
                                        @php
                                            $qty = isset($savedCombos[$combo->id]) ? intval($savedCombos[$combo->id]) : 0;
                                        @endphp
                                        <div class="flex flex-col sm:flex-row bg-slate-50 border border-slate-200 rounded-none overflow-hidden p-4 gap-4 transition-all duration-200 hover:shadow-md hover:border-brand-primary">
                                            
                                            <!-- Combo Image -->
                                            <div class="w-full sm:w-28 aspect-video sm:aspect-square rounded-none bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-300">
                                                <img src="{{ $combo->image_url }}" 
                                                     alt="{{ $combo->combo_name }}" 
                                                     class="w-full h-full object-cover">
                                            </div>

                                            <!-- Combo Details -->
                                            <div class="flex-grow flex flex-col justify-between space-y-3">
                                                <div>
                                                    <h3 class="font-bold text-slate-900 text-sm md:text-base tracking-tight leading-tight mb-1">
                                                        {{ $combo->combo_name }}
                                                    </h3>
                                                    @if($combo->items->isNotEmpty())
                                                        <div class="flex flex-wrap gap-1.5 mb-1.5">
                                                            @foreach($combo->items as $item)
                                                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-50 text-amber-800 border border-amber-200">
                                                                    <span>{{ $item->pivot->quantity }}x</span> {{ $item->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <p class="text-xs text-slate-500 font-semibold leading-relaxed line-clamp-2">
                                                        {{ $combo->description }}
                                                    </p>
                                                </div>

                                                <div class="flex justify-between items-end">
                                                    <!-- Price -->
                                                    <div class="flex flex-col">
                                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Đơn Giá</span>
                                                        <span class="text-sm font-black text-brand-primary combo-price-unit" data-price="{{ $combo->price }}">
                                                            {{ number_format($combo->price) }}đ
                                                        </span>
                                                    </div>

                                                    <!-- Quantity selector -->
                                                    <div class="flex items-center border border-slate-350 bg-white rounded-none px-1.5 py-1">
                                                        <button type="button" 
                                                                class="w-7 h-7 rounded-none text-slate-500 hover:bg-slate-100 flex items-center justify-center font-bold text-base transition-colors btn-qty-dec" 
                                                                data-id="{{ $combo->id }}">-</button>
                                                        
                                                        <input type="text" 
                                                               name="combos[{{ $combo->id }}]" 
                                                               id="qty-input-{{ $combo->id }}" 
                                                               value="{{ $qty }}" 
                                                               readonly
                                                               class="w-8 text-center text-xs font-black text-slate-900 focus:outline-none bg-transparent combo-qty-input border-none ring-0 focus:ring-0 p-0"
                                                               data-id="{{ $combo->id }}"
                                                               data-price="{{ $combo->price }}">
                                                        
                                                        <button type="button" 
                                                                class="w-7 h-7 rounded-none text-slate-500 hover:bg-slate-100 flex items-center justify-center font-bold text-base transition-colors btn-qty-inc" 
                                                                data-id="{{ $combo->id }}">+</button>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Empty state combos -->
                                <div class="text-center py-12 bg-white border border-dashed border-slate-200 rounded-none shadow-sm">
                                    <div class="w-16 h-16 bg-slate-50 rounded-none flex items-center justify-center mx-auto mb-4 text-slate-350 shadow-inner">
                                        <span class="material-symbols-outlined text-3xl">restaurant</span>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800 mb-1">Hiện không có Combo</h3>
                                    <p class="text-xs text-slate-400 font-medium">Rạp tạm thời chưa mở bán các sản phẩm bắp nước. Bạn có thể nhấn Tiếp tục đặt vé.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Sticky Order summary & navigation (1/3 width) -->
                    <div class="space-y-6">
                        <!-- Movie Showtime Summary -->
                        <div class="bg-white rounded-none border border-slate-200 shadow-sm p-6">
                            <div class="flex gap-4 pb-4 border-b border-slate-200">
                                <div class="w-20 aspect-[2/3] rounded-none overflow-hidden bg-slate-200 flex-shrink-0 border border-slate-300">
                                    <img src="{{ $showtime->movie->poster ? asset('storage/' . $showtime->movie->poster) : asset('images/no-image.jpg') }}" 
                                         alt="" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="space-y-1">
                                    <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary text-white rounded-none uppercase tracking-wider">{{ $showtime->movie->age_limit }}</span>
                                    <h3 class="font-bold text-slate-900 text-sm line-clamp-2 uppercase mt-1 leading-tight">{{ $showtime->movie->title }}</h3>
                                </div>
                            </div>

                            <!-- Showtime info summary -->
                            <div class="py-4 space-y-3 border-b border-slate-200 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-bold">Rạp Chiếu</span>
                                    <span class="font-bold text-slate-700">{{ $showtime->room->cinema->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-bold">Phòng Chiếu</span>
                                    <span class="font-bold text-slate-700">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-bold">Suất Chiếu</span>
                                    <span class="font-bold text-brand-primary">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | {{ $showtime->show_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-slate-400 font-bold flex-shrink-0">Ghế Đã Chọn</span>
                                    <div class="font-bold text-slate-800 text-right">
                                        @foreach($selectedSeats as $ss)
                                            <span class="inline-block bg-brand-primary/5 border border-brand-primary/20 text-brand-primary px-1.5 py-0.5 rounded-none font-black ml-1 mb-1">{{ $ss->seat->seat_row . $ss->seat->seat_number }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing detail breakdown -->
                            <div class="py-4 space-y-3 border-b border-slate-200 text-xs">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-bold">Tiền Vé Ghế</span>
                                    <span class="font-bold text-slate-700">{{ number_format($totalSeatPrice) }}đ</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-400 font-bold">Tiền Bắp Nước</span>
                                    <span class="font-bold text-slate-700" id="comboPriceSummary">0đ</span>
                                </div>
                            </div>

                            <!-- Grand total -->
                            <div class="pt-4 flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-900">Tổng Thanh Toán</span>
                                <span class="text-xl font-black text-brand-primary" id="grandTotalPrice" data-seat-total="{{ $totalSeatPrice }}">
                                    {{ number_format($totalSeatPrice) }}đ
                                </span>
                            </div>
                        </div>

                        <!-- Action buttons -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            {{-- Nút Quay Lại Chọn Ghế:
                                 Dùng JS fetch gọi API nhả ghế rồi redirect (không dùng form lồng nhau) --}}
                            <button type="button"
                                    id="btnBackToSeats"
                                    class="w-full sm:w-1/2 bg-white border-2 border-slate-300 hover:border-brand-primary hover:text-brand-primary text-slate-500 font-bold py-3.5 rounded-xl flex items-center justify-center gap-2 transition-all duration-200 shadow-sm text-sm uppercase tracking-wide cursor-pointer">
                                <span class="material-symbols-outlined text-base">arrow_back</span>
                                Quay Lại Chọn Ghế
                            </button>

                            {{-- Nút Tiếp Tục: submit form combo sang trang Thanh Toán --}}
                            <button type="submit"
                                    id="btnContinue"
                                    class="w-full sm:w-1/2 bg-brand-primary hover:bg-red-700 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-brand-primary/25 transition-all duration-200 flex items-center justify-center gap-2 uppercase tracking-wide text-sm cursor-pointer">
                                Tiếp Tục
                                <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </button>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript to Dynamic Handle Combo quantity and strict Back / Timer Rules -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const RELEASE_URL = "{{ route('booking.release-seats', $showtime->id) }}";
            const CSRF = "{{ csrf_token() }}";
            let navigatingIntentionally = false;

            // ── CHẶN NÚT BACK TRÌNH DUYỆT (Nhả ghế ngay lập tức và về lại trang Chọn Ghế) ──
            history.pushState({ page: 'combos', showtimeId: {{ $showtime->id }} }, '', window.location.href);

            window.addEventListener('popstate', async function (event) {
                if (navigatingIntentionally) return;
                
                // Gọi API nhả ghế ngay lập tức khi người dùng bấm nút back trình duyệt
                try {
                    await fetch(RELEASE_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ redirect_to: 'seats' })
                    });
                } catch (e) {
                    console.error('Lỗi nhả ghế:', e);
                }
                window.location.replace("{{ route('booking.select-seats', $showtime->id) }}");
            });

            // ── NÚT "QUAY LẠI CHỌN GHẾ" — Gọi API nhả ghế rồi redirect ──
            const btnBack = document.getElementById('btnBackToSeats');
            if (btnBack) {
                btnBack.addEventListener('click', async function () {
                    navigatingIntentionally = true;
                    btnBack.disabled = true;
                    btnBack.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">progress_activity</span> Đang nhả ghế...';

                    try {
                        await fetch(RELEASE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ redirect_to: 'seats' })
                        });
                    } catch (e) {
                        console.error('Lỗi nhả ghế:', e);
                    }

                    window.location.href = "{{ route('booking.select-seats', $showtime->id) }}";
                });
            }

            const comboForm = document.getElementById('comboForm');
            if (comboForm) {
                comboForm.addEventListener('submit', function () {
                    navigatingIntentionally = true;
                });
            }

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

            // ================= COUNTDOWN TIMER LOGIC (5 PHÚT LIÊN TỤC) =================
            if (typeof Swal === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                document.head.appendChild(script);
            }

            const timerWrapper = document.getElementById('countdown-wrapper');
            const timerDisplay = document.getElementById('seat-countdown');
            const expireTimestamp = {{ $holdExpiresAt ?? (time() + 300) }} * 1000; 

            const countdownInterval = setInterval(async function() {
                const now = new Date().getTime();
                const distance = expireTimestamp - now;

                if (distance <= 0) {
                    clearInterval(countdownInterval);
                    if (timerDisplay) timerDisplay.textContent = "00:00";
                    
                    // Tự động gọi API nhả ghế khi hết giờ
                    try {
                        await fetch(RELEASE_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ redirect_to: 'home' })
                        });
                    } catch (e) {
                        console.error('Lỗi nhả ghế:', e);
                    }

                    // Hiển thị cảnh báo và điều hướng về Trang Chủ
                    Swal.fire({
                        title: 'Thời gian giữ ghế đã hết!',
                        text: 'Phiên đặt vé đã kết thúc. Vui lòng thực hiện lại từ đầu.',
                        icon: 'warning',
                        allowOutsideClick: false,
                        confirmButtonText: 'Về Trang Chủ',
                        confirmButtonColor: '#EF4444'
                    }).then((result) => {
                        window.location.href = "{{ route('home') }}";
                    });
                    return;
                }

                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                if (timerDisplay) {
                    timerDisplay.textContent = 
                        (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                        (seconds < 10 ? "0" + seconds : seconds);
                }

                // Cảnh báo khi thời gian còn <= 60 giây (1 phút)
                if (distance <= 60000) {
                    if (timerWrapper) {
                        timerWrapper.classList.remove('bg-white', 'border-slate-200');
                        timerWrapper.classList.add('bg-red-600', 'border-red-500', 'text-white', 'animate-pulse');
                    }
                }
            }, 1000);
        });
    </script>
@endsection
