@extends('layouts.customer')

@section('title', 'Thanh Toán - FilmGo')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-850 font-sans antialiased py-10 selection:bg-brand-primary selection:text-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Countdown Timer ── --}}
        <div id="countdown-wrapper" class="fixed top-4 right-4 bg-white text-slate-800 px-4 py-2 rounded-full font-bold shadow-md z-50 flex items-center gap-2 border border-slate-200">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">⏳ Giữ ghế:</span>
            <span id="seat-countdown" class="text-base font-black text-brand-primary">10:00</span>
        </div>

        {{-- ── Progress Steps ── --}}
        <div class="max-w-xl mx-auto mb-10">
            <div class="flex items-center justify-between relative">
                <div class="absolute inset-x-0 top-5 h-0.5 bg-slate-200 z-0"></div>
                <div class="absolute left-0 right-[0%] top-5 h-0.5 bg-brand-primary z-0"></div>

                @php
                    $steps = ['Chọn Phim', 'Chọn Ghế', 'Bắp Nước', 'Thanh Toán'];
                    $currentStep = 4;
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

        @if(session('error'))
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-none flex items-center gap-2">
                <span class="material-symbols-outlined text-brand-primary">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-none flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- ── Main Grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ═══ LEFT — Vé Điện Tử ═══ --}}
            <div class="lg:col-span-2">
                <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-brand-primary px-5 py-3">
                        <h3 class="text-xs font-black uppercase tracking-widest text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">confirmation_number</span>
                            Vé Điện Tử
                        </h3>
                    </div>

                    <div class="p-5 space-y-4 text-sm">
                        {{-- Film poster + info --}}
                        <div class="flex gap-3">
                            @if($showtime->movie->poster_url)
                            <img src="{{ $showtime->movie->poster_url }}" alt="poster"
                                 class="w-16 h-24 object-cover rounded-none flex-shrink-0 border border-slate-200">
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-slate-900 text-sm leading-tight line-clamp-2 uppercase">{{ $showtime->movie->title }}</p>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary/10 text-brand-primary rounded-none border border-brand-primary/20 uppercase">
                                        {{ $showtime->movie->age_limit }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 font-bold">{{ $showtime->movie->duration }} phút</span>
                                </div>
                                <p class="text-[10px] text-slate-600 mt-2 font-bold">
                                    {{ $showtime->room->cinema->name }}
                                </p>
                                <p class="text-[10px] text-brand-primary font-bold mt-0.5">
                                    {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} • {{ $showtime->show_date->format('d/m/Y') }}
                                </p>
                                <p class="text-[10px] text-slate-500 mt-0.5 font-medium">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</p>
                            </div>
                        </div>

                        {{-- Seats detail --}}
                        <div class="border-t border-slate-200 pt-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Chi Tiết Ghế</p>
                            <div class="space-y-1.5">
                                @foreach($selectedSeats as $ss)
                                @php
                                    $seatPrice = $ss->price;
                                @endphp
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-none bg-slate-100 border border-slate-300 flex items-center justify-center text-[10px] font-black text-slate-800">
                                            {{ $ss->seat->seat_row }}{{ $ss->seat->seat_number }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-medium">{{ $ss->seat->seatType->type_name ?? 'Thường' }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-800">{{ number_format($seatPrice) }}đ</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Combos --}}
                        @if(count($selectedCombos) > 0)
                        <div class="border-t border-slate-200 pt-4">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bắp Nước</p>
                            <div class="space-y-1.5">
                                @foreach($selectedCombos as $sc)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-slate-700 font-medium">{{ $sc['name'] }}
                                        <span class="text-slate-400 ml-1 font-bold">×{{ $sc['quantity'] }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-slate-800">{{ number_format($sc['subtotal']) }}đ</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Price breakdown --}}
                        <div class="border-t border-dashed border-slate-300 pt-4 space-y-2">
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Tiền ghế ({{ $selectedSeats->count() }} ghế)</span>
                                <span>{{ number_format($totalSeatPrice) }}đ</span>
                            </div>
                            @if($totalComboPrice > 0)
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Bắp nước</span>
                                <span>{{ number_format($totalComboPrice) }}đ</span>
                            </div>
                            @endif
                            @if($discountAmount > 0)
                            <div class="flex justify-between text-xs text-emerald-600 font-semibold" id="discountRow">
                                <span>Giảm giá (<span id="appliedCodeLabel">{{ $appliedVoucher['code'] ?? '' }}</span>)</span>
                                <span id="discountLabel">-{{ number_format($discountAmount) }}đ</span>
                            </div>
                            @else
                            <div class="hidden justify-between text-xs text-emerald-600 font-semibold" id="discountRow">
                                <span>Giảm giá (<span id="appliedCodeLabel"></span>)</span>
                                <span id="discountLabel"></span>
                            </div>
                            @endif
                        </div>

                        {{-- Grand total --}}
                        <div class="border-t border-slate-200 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Tổng Cộng</span>
                                <span class="text-2xl font-black text-brand-primary" id="grandTotalLabel">
                                    {{ number_format($finalTotal) }}đ
                                </span>
                            </div>
                            @if($discountAmount > 0)
                            <p class="text-right text-[10px] text-slate-400 mt-1 line-through" id="originalTotalLabel">
                                {{ number_format($grandTotal) }}đ
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ RIGHT — Voucher + Thanh toán ═══ --}}
            <div class="lg:col-span-3 space-y-5">

                {{-- ── Mã Khuyến Mãi ── --}}
                <div class="bg-white border border-slate-200 rounded-none shadow-sm p-5">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-brand-primary">sell</span>
                        Mã Khuyến Mãi
                    </h4>

                    {{-- Alert box --}}
                    <div id="voucherAlert" class="hidden mb-3 px-4 py-3 rounded-none text-xs font-semibold flex items-start gap-2"></div>

                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="voucherInput"
                            placeholder="Nhập mã tại đây..."
                            value="{{ $appliedVoucher['code'] ?? '' }}"
                            maxlength="50"
                            class="flex-1 bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm px-4 py-3 rounded-none focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50 uppercase tracking-wider transition-colors"
                        >
                        <button
                            type="button"
                            id="applyVoucherBtn"
                            class="px-5 py-3 bg-slate-800 hover:bg-brand-primary text-white text-xs font-black uppercase tracking-widest rounded-none transition-all duration-200 border border-slate-800 hover:border-brand-primary whitespace-nowrap">
                            Áp Dụng
                        </button>
                    </div>

                    {{-- Applied badge --}}
                    <div id="appliedBadge" class="{{ $appliedVoucher ? 'flex' : 'hidden' }} items-center justify-between mt-3 px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-none">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-base">check_circle</span>
                            <div>
                                <p class="text-xs font-black text-emerald-700">
                                    Đã áp dụng: <span id="badgeCode">{{ $appliedVoucher['code'] ?? '' }}</span>
                                </p>
                                <p class="text-[10px] text-emerald-600 font-medium" id="badgeDesc">
                                    @if($appliedVoucher)
                                        Giảm {{ $appliedVoucher['discount_type'] === 'percent' ? $appliedVoucher['discount_value'].'%' : number_format($appliedVoucher['discount_value']).'đ' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button type="button" id="removeVoucherBtn"
                                class="text-slate-400 hover:text-red-500 transition-colors">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </div>

                {{-- ── Phương thức thanh toán ── --}}
                <div class="bg-white border border-slate-200 rounded-none shadow-sm p-5">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-brand-primary">payments</span>
                        Phương Thức Thanh Toán
                    </h4>

                    <div class="rounded-none border border-brand-primary/20 bg-brand-primary/5 p-4 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-none bg-brand-primary/10 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-lg text-brand-primary">credit_card</span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Thanh toán qua VNPay</p>
                            <p class="text-[10px] text-slate-500 mt-1 font-medium leading-relaxed">Hệ thống chuyển sang cổng thanh toán VNPay. Vui lòng chọn ngân hàng trước khi tiếp tục.</p>
                        </div>
                    </div>
                </div>

                {{-- ── Confirm Form ── --}}
                <form action="{{ route('booking.confirm', $showtime->id) }}" method="POST" id="confirmForm">
                    @csrf
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="vnpay">
                    <input type="hidden" name="bank_code" id="bankCodeInput" value="NCB">
                    <div id="hiddenCombosContainer"></div>

                    <div class="mb-4 rounded-none border border-slate-200 bg-white p-4 shadow-sm">
                        <label for="bankCodeSelect" class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">
                            Chọn ngân hàng thanh toán
                        </label>
                        <select id="bankCodeSelect" class="w-full rounded-none border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 focus:border-brand-primary focus:outline-none font-medium">
                            <option value="NCB">Ngân hàng NCB</option>
                            <option value="VNPAYQR">VNPAYQR</option>
                            <option value="VIETCOMBANK">Vietcombank</option>
                            <option value="VIETINBANK">VietinBank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="AGRIBANK">Agribank</option>
                            <option value="SACOMBANK">Sacombank</option>
                            <option value="MBBANK">MB Bank</option>
                        </select>
                    </div>

                    <button type="submit"
                            id="payNowButton"
                            form="confirmForm"
                            class="w-full bg-brand-primary hover:bg-red-700 text-white font-black py-4 rounded-none shadow-md shadow-brand-primary/20 transition-all duration-200 flex items-center justify-center gap-2 text-base uppercase tracking-wider">
                        Thanh Toán Ngay
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>

                    <p class="text-center text-[10px] text-slate-400 mt-3 font-medium">
                        Bằng việc bấm nút Thanh Toán, bạn đồng ý với
                        <a href="#" class="text-slate-600 underline">Điều khoản giao dịch</a> của chúng tôi.
                    </p>
                </form>

                {{-- Back --}}
                <a href="{{ route('booking.select-combos', $showtime->id) }}"
                   class="flex items-center justify-center gap-1 text-xs text-slate-500 hover:text-brand-primary transition-colors py-2 font-bold uppercase tracking-wider">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Quay lại chọn bắp nước
                </a>
            </div>

        </div>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const SHOWTIME_ID  = {{ $showtime->id }};
    const APPLY_URL    = "{{ route('booking.voucher.apply', $showtime->id) }}";
    const REMOVE_URL   = "{{ route('booking.voucher.remove', $showtime->id) }}";
    const CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const bankSelect   = document.getElementById('bankCodeSelect');
    const bankInput    = document.getElementById('bankCodeInput');

    if (bankSelect && bankInput) {
        bankSelect.addEventListener('change', function () {
            bankInput.value = this.value;
        });
    }

    const paymentMethodInput = document.getElementById('paymentMethodInput');
    if (paymentMethodInput) {
        paymentMethodInput.value = 'vnpay';
    }

    const confirmForm = document.getElementById('confirmForm');
    if (confirmForm) {
        confirmForm.addEventListener('submit', function (e) {
            if (paymentMethodInput) {
                paymentMethodInput.value = 'vnpay';
            }
            if (bankInput) {
                bankInput.value = bankSelect ? bankSelect.value : 'NCB';
            }
        });
    }

    let seatTotal      = {{ $totalSeatPrice }};
    let comboTotal     = {{ $totalComboPrice }};
    let discountAmount = {{ $discountAmount }};

    const combosState = {
        @foreach($allCombos ?? [] as $combo)
        "{{ $combo->id }}": {
            id: "{{ $combo->id }}",
            price: {{ $combo->price }},
            qty: {{ collect($selectedCombos)->firstWhere('combo.id', $combo->id)['quantity'] ?? 0 }}
        },
        @endforeach
    };

    function updateTotals(newDiscount, code) {
        discountAmount = newDiscount;
        const grand = Math.max(0, seatTotal + comboTotal - discountAmount);

        document.getElementById('grandTotalLabel').textContent =
            new Intl.NumberFormat('vi-VN').format(grand) + 'đ';

        const discountRow = document.getElementById('discountRow');
        const discountLabel = document.getElementById('discountLabel');
        const appliedCodeLabel = document.getElementById('appliedCodeLabel');

        if (discountAmount > 0 && code) {
            discountRow.classList.remove('hidden');
            discountRow.classList.add('flex');
            discountLabel.textContent = '-' + new Intl.NumberFormat('vi-VN').format(discountAmount) + 'đ';
            appliedCodeLabel.textContent = code;
        } else {
            discountRow.classList.add('hidden');
            discountRow.classList.remove('flex');
        }
    }

    function showAlert(message, type) {
        const el = document.getElementById('voucherAlert');
        el.className = 'mb-3 px-4 py-3 rounded-none text-xs font-semibold flex items-start gap-2 ';
        if (type === 'success') {
            el.className += 'bg-emerald-50 border border-emerald-200 text-emerald-700';
            el.innerHTML = '<span class="material-symbols-outlined text-sm flex-shrink-0 text-emerald-600">check_circle</span><span>' + message + '</span>';
        } else {
            el.className += 'bg-red-50 border border-red-200 text-red-700';
            el.innerHTML = '<span class="material-symbols-outlined text-sm flex-shrink-0 text-brand-primary">error</span><span>' + message + '</span>';
        }
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 5000);
    }

    document.getElementById('applyVoucherBtn').addEventListener('click', function () {
        const code = document.getElementById('voucherInput').value.trim();
        if (!code) { showAlert('Vui lòng nhập mã giảm giá.', 'error'); return; }

        const subtotal = seatTotal + comboTotal;
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Đang kiểm tra...';

        fetch(APPLY_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ voucher_code: code, subtotal: subtotal })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                updateTotals(data.discount_amount, data.code);

                const badge = document.getElementById('appliedBadge');
                document.getElementById('badgeCode').textContent = data.code;
                document.getElementById('badgeDesc').textContent =
                    'Giảm ' + (data.discount_type === 'percent'
                        ? data.discount_value + '%'
                        : new Intl.NumberFormat('vi-VN').format(data.discount_value) + 'đ');
                badge.classList.remove('hidden');
                badge.classList.add('flex');

                document.getElementById('voucherInput').value = data.code;
            } else {
                showAlert(data.message, 'error');
                updateTotals(0, null);
                document.getElementById('appliedBadge').classList.add('hidden');
                document.getElementById('appliedBadge').classList.remove('flex');
            }
        })
        .catch(() => showAlert('Có lỗi kết nối. Vui lòng thử lại.', 'error'))
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Áp Dụng';
        });
    });

    document.getElementById('voucherInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('applyVoucherBtn').click();
        }
    });

    document.getElementById('removeVoucherBtn').addEventListener('click', function () {
        fetch(REMOVE_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(() => {
            updateTotals(0, null);
            document.getElementById('voucherInput').value = '';
            document.getElementById('appliedBadge').classList.add('hidden');
            document.getElementById('appliedBadge').classList.remove('flex');
            showAlert('Đã xóa mã giảm giá.', 'success');
        });
    });

    function syncHiddenCombos() {
        const container = document.getElementById('hiddenCombosContainer');
        let html = '';
        for (const id in combosState) {
            if (combosState[id].qty > 0) {
                html += `<input type="hidden" name="combos[${id}]" value="${combosState[id].qty}">`;
            }
        }
        container.innerHTML = html;
    }
    syncHiddenCombos();

    if (typeof Swal === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
        document.head.appendChild(script);
    }

    const timerWrapper = document.getElementById('countdown-wrapper');
    const timerDisplay = document.getElementById('seat-countdown');
    const expireTimestamp = {{ $holdExpiresAt ?? (time() + 600) }} * 1000; 

    const countdownInterval = setInterval(function() {
        const now = new Date().getTime();
        const distance = expireTimestamp - now;

        if (distance <= 0) {
            clearInterval(countdownInterval);
            if (timerDisplay) timerDisplay.textContent = "00:00";
            
            Swal.fire({
                title: 'Hết thời gian giữ ghế!',
                text: 'Vui lòng đặt lại từ đầu.',
                icon: 'warning',
                allowOutsideClick: false,
                confirmButtonText: 'Đồng ý',
                confirmButtonColor: '#EF4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('booking.select-seats', $showtime->id ?? 0) }}";
                }
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

        if (distance <= 120000) {
            if (timerWrapper) {
                timerWrapper.classList.remove('bg-white', 'border-slate-200');
                timerWrapper.classList.add('bg-red-600', 'border-red-500', 'text-white', 'animate-pulse');
            }
        }
    }, 1000);

});
</script>
@endsection
@endsection