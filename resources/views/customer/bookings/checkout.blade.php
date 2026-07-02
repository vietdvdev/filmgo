@extends('layouts.customer')

@section('title', 'Thanh Toán - FilmGo')

@section('content')
<div class="min-h-screen bg-[#0F0F0F] text-white font-sans antialiased py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Progress Steps ── --}}
        <div class="max-w-lg mx-auto mb-10">
            <div class="flex items-center justify-between relative">
                <div class="absolute inset-x-0 top-5 h-0.5 bg-zinc-700 z-0"></div>
                <div class="absolute left-0 right-[25%] top-5 h-0.5 bg-brand-primary z-0"></div>

                @php
                    $steps = ['Chọn Phim','Chọn Ghế','Bắp Nước','Thanh Toán'];
                    $currentStep = 4;
                @endphp
                @foreach($steps as $i => $label)
                    <div class="z-10 flex flex-col items-center gap-2">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm border-2
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

        {{-- ── Main Grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ═══ LEFT — Vé Điện Tử ═══ --}}
            <div class="lg:col-span-2">
                <div class="bg-[#1A1A1A] border border-zinc-800 rounded-2xl overflow-hidden">
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
                                 class="w-16 h-24 object-cover rounded-lg flex-shrink-0 border border-zinc-700">
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-black text-white text-sm leading-tight line-clamp-2">{{ $showtime->movie->title }}</p>
                                <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 text-[9px] font-black bg-brand-primary/20 text-brand-primary rounded border border-brand-primary/30 uppercase">
                                        {{ $showtime->movie->age_limit }}
                                    </span>
                                    <span class="text-[10px] text-zinc-500">{{ $showtime->movie->duration }} phút</span>
                                </div>
                                <p class="text-[10px] text-zinc-400 mt-2 font-semibold">
                                    {{ $showtime->room->cinema->name }}
                                </p>
                                <p class="text-[10px] text-brand-primary font-bold mt-0.5">
                                    {{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} • {{ $showtime->show_date->format('d/m/Y') }}
                                </p>
                                <p class="text-[10px] text-zinc-500 mt-0.5">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</p>
                            </div>
                        </div>

                        {{-- Seats detail --}}
                        <div class="border-t border-zinc-800 pt-4">
                            <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-2">Chi Tiết Ghế</p>
                            <div class="space-y-1.5">
                                @foreach($selectedSeats as $ss)
                                @php
                                    $seatPrice = $showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0);
                                @endphp
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-md bg-zinc-800 border border-zinc-700 flex items-center justify-center text-[10px] font-black text-white">
                                            {{ $ss->seat->seat_row }}{{ $ss->seat->seat_number }}
                                        </span>
                                        <span class="text-[10px] text-zinc-400">{{ $ss->seat->seatType->type_name ?? 'Thường' }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-zinc-300">{{ number_format($seatPrice) }}đ</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Combos --}}
                        @if(count($selectedCombos) > 0)
                        <div class="border-t border-zinc-800 pt-4">
                            <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-2">Bắp Nước</p>
                            <div class="space-y-1.5">
                                @foreach($selectedCombos as $sc)
                                <div class="flex justify-between items-center">
                                    <span class="text-xs text-zinc-300">{{ $sc['combo']->combo_name }}
                                        <span class="text-zinc-500 ml-1">×{{ $sc['quantity'] }}</span>
                                    </span>
                                    <span class="text-xs font-semibold text-zinc-300">{{ number_format($sc['subtotal']) }}đ</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Price breakdown --}}
                        <div class="border-t border-dashed border-zinc-700 pt-4 space-y-2">
                            <div class="flex justify-between text-xs text-zinc-400">
                                <span>Tiền ghế ({{ $selectedSeats->count() }} ghế)</span>
                                <span>{{ number_format($totalSeatPrice) }}đ</span>
                            </div>
                            @if($totalComboPrice > 0)
                            <div class="flex justify-between text-xs text-zinc-400">
                                <span>Bắp nước</span>
                                <span>{{ number_format($totalComboPrice) }}đ</span>
                            </div>
                            @endif
                            @if($discountAmount > 0)
                            <div class="flex justify-between text-xs text-emerald-400 font-semibold" id="discountRow">
                                <span>Giảm giá (<span id="appliedCodeLabel">{{ $appliedVoucher['code'] ?? '' }}</span>)</span>
                                <span id="discountLabel">-{{ number_format($discountAmount) }}đ</span>
                            </div>
                            @else
                            <div class="hidden justify-between text-xs text-emerald-400 font-semibold" id="discountRow">
                                <span>Giảm giá (<span id="appliedCodeLabel"></span>)</span>
                                <span id="discountLabel"></span>
                            </div>
                            @endif
                        </div>

                        {{-- Grand total --}}
                        <div class="border-t border-zinc-700 pt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-bold text-zinc-400 uppercase tracking-widest">Tổng Cộng</span>
                                <span class="text-2xl font-black text-brand-primary" id="grandTotalLabel">
                                    {{ number_format($finalTotal) }}đ
                                </span>
                            </div>
                            @if($discountAmount > 0)
                            <p class="text-right text-[10px] text-zinc-500 mt-1 line-through" id="originalTotalLabel">
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
                <div class="bg-[#1A1A1A] border border-zinc-800 rounded-2xl p-5">
                    <h4 class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-brand-primary">sell</span>
                        Mã Khuyến Mãi
                    </h4>

                    {{-- Alert box --}}
                    <div id="voucherAlert" class="hidden mb-3 px-4 py-3 rounded-xl text-xs font-semibold flex items-start gap-2"></div>

                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="voucherInput"
                            placeholder="Nhập mã tại đây..."
                            value="{{ $appliedVoucher['code'] ?? '' }}"
                            maxlength="50"
                            class="flex-1 bg-zinc-900 border border-zinc-700 text-white placeholder-zinc-600 text-sm px-4 py-3 rounded-xl focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50 uppercase tracking-wider transition-colors"
                        >
                        <button
                            type="button"
                            id="applyVoucherBtn"
                            class="px-5 py-3 bg-zinc-800 hover:bg-brand-primary text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-200 border border-zinc-700 hover:border-brand-primary whitespace-nowrap">
                            Áp Dụng
                        </button>
                    </div>

                    {{-- Applied badge --}}
                    <div id="appliedBadge" class="{{ $appliedVoucher ? 'flex' : 'hidden' }} items-center justify-between mt-3 px-4 py-2.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-400 text-base">check_circle</span>
                            <div>
                                <p class="text-xs font-black text-emerald-400">
                                    Đã áp dụng: <span id="badgeCode">{{ $appliedVoucher['code'] ?? '' }}</span>
                                </p>
                                <p class="text-[10px] text-emerald-500/70" id="badgeDesc">
                                    @if($appliedVoucher)
                                        Giảm {{ $appliedVoucher['discount_type'] === 'percent' ? $appliedVoucher['discount_value'].'%' : number_format($appliedVoucher['discount_value']).'đ' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <button type="button" id="removeVoucherBtn"
                                class="text-zinc-500 hover:text-red-400 transition-colors">
                            <span class="material-symbols-outlined text-base">close</span>
                        </button>
                    </div>
                </div>

                {{-- ── Phương thức thanh toán ── --}}
                <div class="bg-[#1A1A1A] border border-zinc-800 rounded-2xl p-5">
                    <h4 class="text-xs font-black text-zinc-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-brand-primary">payments</span>
                        Phương Thức Thanh Toán
                    </h4>

                    <div class="space-y-2" id="paymentMethods">
                        @php
                            $methods = [
                                ['id' => 'vnpay', 'icon' => 'credit_card',             'label' => 'Cổng thanh toán VNPay', 'sub' => 'Thẻ ATM, Thẻ quốc tế, QR Code'],
                                ['id' => 'momo',  'icon' => 'account_balance_wallet', 'label' => 'Ví Điện Tử MoMo',      'sub' => 'Thanh toán qua ứng dụng MoMo'],
                            ];
                        @endphp

                        @foreach($methods as $idx => $m)
                        <label class="payment-option flex items-center gap-4 px-4 py-3.5 rounded-xl border cursor-pointer transition-all duration-200
                            {{ $idx === 0 ? 'border-brand-primary bg-brand-primary/10' : 'border-zinc-800 bg-zinc-900/50 hover:border-zinc-600' }}"
                            data-id="{{ $m['id'] }}">
                            <input type="radio" name="payment_method" value="{{ $m['id'] }}"
                                   class="hidden" {{ $idx === 0 ? 'checked' : '' }}>
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                                {{ $idx === 0 ? 'bg-brand-primary/20' : 'bg-zinc-800' }}">
                                <span class="material-symbols-outlined text-base {{ $idx === 0 ? 'text-brand-primary' : 'text-zinc-400' }}">
                                    {{ $m['icon'] }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-white">{{ $m['label'] }}</p>
                                <p class="text-[10px] text-zinc-500">{{ $m['sub'] }}</p>
                            </div>
                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                {{ $idx === 0 ? 'border-brand-primary' : 'border-zinc-600' }}" data-radio>
                                @if($idx === 0)
                                    <div class="w-2 h-2 rounded-full bg-brand-primary"></div>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- ── Confirm Form ── --}}
                <form action="{{ route('booking.confirm', $showtime->id) }}" method="POST" id="confirmForm">
                    @csrf
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="vnpay">
                    <div id="hiddenCombosContainer"></div>

                    <button type="submit"
                            id="payNowButton"
                            class="w-full bg-brand-primary hover:bg-red-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-brand-primary/25 transition-all duration-200 flex items-center justify-center gap-2 text-base uppercase tracking-wider">
                        Thanh Toán Ngay
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>

                    <p class="text-center text-[10px] text-zinc-600 mt-3">
                        Bằng việc bấm nút Thanh Toán, bạn đồng ý với
                        <a href="#" class="text-zinc-400 underline">Điều khoản giao dịch</a> của chúng tôi.
                    </p>
                </form>

                {{-- Back --}}
                <a href="{{ route('booking.select-combos', $showtime->id) }}"
                   class="flex items-center justify-center gap-1 text-xs text-zinc-500 hover:text-white transition-colors py-2">
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

    // ─── Config từ PHP ───────────────────────────────────────────
    const SHOWTIME_ID  = {{ $showtime->id }};
    const APPLY_URL    = "{{ route('booking.voucher.apply', $showtime->id) }}";
    const REMOVE_URL   = "{{ route('booking.voucher.remove', $showtime->id) }}";
    const CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    // ─── Cập nhật tổng tiền UI ───────────────────────────────────
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

    // ─── Hiển thị alert ──────────────────────────────────────────
    function showAlert(message, type) {
        const el = document.getElementById('voucherAlert');
        el.className = 'mb-3 px-4 py-3 rounded-xl text-xs font-semibold flex items-start gap-2 ';
        if (type === 'success') {
            el.className += 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-400';
            el.innerHTML = '<span class="material-symbols-outlined text-sm flex-shrink-0">check_circle</span><span>' + message + '</span>';
        } else {
            el.className += 'bg-red-500/10 border border-red-500/30 text-red-400';
            el.innerHTML = '<span class="material-symbols-outlined text-sm flex-shrink-0">error</span><span>' + message + '</span>';
        }
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 5000);
    }

    // ─── Áp dụng voucher ─────────────────────────────────────────
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

    // ─── Xóa voucher ─────────────────────────────────────────────
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

    // ─── Chọn phương thức thanh toán ─────────────────────────────
    document.querySelectorAll('.payment-option').forEach(label => {
        label.addEventListener('click', function (e) {
            // SỬA TẠI ĐÂY: Ngăn click đúp của trình duyệt lên thẻ input ẩn
            e.preventDefault(); 

            document.querySelectorAll('.payment-option').forEach(l => {
                l.classList.remove('border-brand-primary', 'bg-brand-primary/10');
                l.classList.add('border-zinc-800', 'bg-zinc-900/50');
                const dot = l.querySelector('[data-radio]');
                dot.classList.remove('border-brand-primary');
                dot.classList.add('border-zinc-600');
                dot.innerHTML = '';
            });

            this.classList.add('border-brand-primary', 'bg-brand-primary/10');
            this.classList.remove('border-zinc-800', 'bg-zinc-900/50');
            const dot = this.querySelector('[data-radio]');
            dot.classList.add('border-brand-primary');
            dot.classList.remove('border-zinc-600');
            dot.innerHTML = '<div class="w-2 h-2 rounded-full bg-brand-primary"></div>';

            const val = this.dataset.id;
            document.getElementById('paymentMethodInput').value = val;
            this.querySelector('input[type=radio]').checked = true;
        });
    });

    // ─── Sync hidden combos vào form ─────────────────────────────
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

});
</script>
@endsection
@endsection