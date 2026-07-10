@extends('layouts.staff')

@section('title', 'POS — Bán Vé Tại Quầy | FilmGo')

@push('styles')
<style>
    /* ── POS Layout: Full-height, no scroll ─── */
    #pos-root { height: calc(100vh - 64px); display: grid; grid-template-columns: 280px 1fr 340px; }

    /* ── Seat Map ─── */
    .seat-btn {
        width: 36px; height: 36px; border-radius: 6px; border: 2px solid transparent;
        font-size: 10px; font-weight: 700; cursor: pointer; transition: all .15s;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .seat-available  { background: #f0fdf4; border-color: #22c55e; color: #15803d; }
    .seat-available:hover { background: #dcfce7; transform: scale(1.12); }
    .seat-holding    { background: #fef9c3; border-color: #eab308; color: #a16207; cursor: not-allowed; }
    .seat-booked     { background: #fef2f2; border-color: #ef4444; color: #b91c1c; cursor: not-allowed; }
    .seat-selected   { background: #3b82f6; border-color: #1d4ed8; color: #fff; transform: scale(1.08); }
    .seat-maintenance{ background: #f3f4f6; border-color: #d1d5db; color: #9ca3af; cursor: not-allowed; }
    .seat-vip        { border-style: dashed; }

    /* ── Screen indicator ─── */
    .screen-bar { height: 6px; background: linear-gradient(90deg, #93c5fd, #3b82f6, #93c5fd); border-radius: 4px; }

    /* ── Print styles ─── */
    @media print {
        body > *:not(#print-area) { display: none !important; }
        #print-area { display: block !important; }
        .no-print { display: none !important; }
    }
    #print-area { display: none; }
</style>
@endpush

@section('content')
<div id="pos-root" class="bg-gray-50">

    {{-- ════════════════════════════════════════════════════════════
         CỘT TRÁI — Chọn Phim & Suất chiếu
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-r border-gray-200 flex flex-col overflow-hidden">

        {{-- Header + Date picker --}}
        <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-primary" style="font-size:18px">movie</span>
                    Phim & Suất Chiếu
                </h2>
                <span id="movie-count" class="text-[10px] font-bold px-2 py-0.5 bg-primary/10 text-primary rounded-full">—</span>
            </div>
            <input type="date" id="pos-date" value="{{ today()->toDateString() }}"
                   class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
        </div>

        {{-- Movie + Showtime list --}}
        <div id="movie-list" class="flex-1 overflow-y-auto p-3 space-y-2">
            <div class="text-center py-8 text-gray-400">
                <span class="material-symbols-outlined text-4xl mb-2 block">hourglass_empty</span>
                <p class="text-xs">Đang tải danh sách phim...</p>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CỘT GIỮA — Sơ đồ ghế real-time
    ════════════════════════════════════════════════════════════ --}}
    <div class="flex flex-col overflow-hidden">

        {{-- Header suất chiếu đang chọn --}}
        <div id="seat-header" class="px-5 py-3 bg-white border-b border-gray-200 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Sơ đồ ghế</p>
                    <h2 id="seat-showtime-title" class="text-sm font-bold text-gray-800 mt-0.5">
                        Chọn phim và suất chiếu để bắt đầu
                    </h2>
                </div>
                <div class="flex items-center gap-4 text-[10px]">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-100 border border-green-500 inline-block"></span>Trống</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-blue-500 inline-block"></span>Đang chọn</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-yellow-100 border border-yellow-400 inline-block"></span>Tạm giữ</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-100 border border-red-400 inline-block"></span>Đã bán</span>
                </div>
            </div>
        </div>

        {{-- Seat map container --}}
        <div id="seat-map-container" class="flex-1 overflow-auto p-5 flex flex-col items-center">
            <div class="text-center py-16 text-gray-300">
                <span class="material-symbols-outlined text-6xl mb-3 block">event_seat</span>
                <p class="text-sm font-medium">Chưa chọn suất chiếu</p>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CỘT PHẢI — Giỏ hàng + Checkout
    ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white border-l border-gray-200 flex flex-col overflow-hidden">

        {{-- ── Giỏ hàng ghế ─────────────────────── --}}
        <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary" style="font-size:16px">shopping_cart</span>
                Giỏ hàng
            </h3>
        </div>

        {{-- Danh sách ghế đã chọn --}}
        <div id="cart-seats" class="px-4 py-2 flex-shrink-0 min-h-[80px] max-h-[160px] overflow-y-auto">
            <p id="no-seat-msg" class="text-xs text-gray-400 text-center py-4">Chưa chọn ghế nào</p>
        </div>

        {{-- ── F&B Combos ──────────────────────── --}}
        <div class="border-t border-gray-100 flex-shrink-0">
            <div class="px-4 py-2 bg-orange-50 border-b border-orange-100">
                <h3 class="text-xs font-bold text-orange-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="material-symbols-outlined" style="font-size:16px">fastfood</span>
                    Bắp Nước (F&B)
                </h3>
            </div>
            <div id="combo-list" class="px-4 py-2 space-y-2 max-h-[180px] overflow-y-auto">
                @forelse($combos as $combo)
                <div class="flex items-center gap-2" data-combo-id="{{ $combo->id }}"
                     data-combo-name="{{ $combo->combo_name }}" data-combo-price="{{ $combo->price }}">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $combo->combo_name }}</p>
                        <p class="text-[10px] text-orange-600 font-bold">{{ number_format($combo->price) }}đ</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button onclick="POS.changeCombo({{ $combo->id }}, -1)"
                                class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold text-sm flex items-center justify-center transition-colors">−</button>
                        <span id="combo-qty-{{ $combo->id }}" class="w-6 text-center text-xs font-bold text-gray-800">0</span>
                        <button onclick="POS.changeCombo({{ $combo->id }}, 1)"
                                class="w-6 h-6 rounded bg-primary hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center transition-colors">+</button>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 text-center py-2">Chưa có combo nào</p>
                @endforelse
            </div>
        </div>

        {{-- ── Voucher ──────────────────────────── --}}
        <div class="border-t border-gray-100 px-4 py-3 flex-shrink-0">
            <div class="flex gap-2">
                <input id="voucher-input" type="text" placeholder="Mã giảm giá..."
                       class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none uppercase"
                       oninput="this.value = this.value.toUpperCase()">
                <button onclick="POS.applyVoucher()"
                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded-lg transition-colors">
                    Áp dụng
                </button>
            </div>
            <div id="voucher-info" class="hidden mt-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center justify-between">
                    <span id="voucher-label" class="text-xs font-semibold text-green-700"></span>
                    <button onclick="POS.removeVoucher()" class="text-red-500 hover:text-red-700 text-xs font-bold">✕ Xóa</button>
                </div>
            </div>
        </div>

        {{-- ── Tổng tiền ──────────────────────── --}}
        <div class="border-t border-gray-200 px-4 py-3 flex-shrink-0 bg-gray-50 space-y-1.5">
            <div class="flex justify-between text-xs text-gray-600">
                <span>Tiền ghế (<span id="total-seat-count">0</span> ghế)</span>
                <span id="total-seat-price">0đ</span>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>Combo F&B</span>
                <span id="total-combo-price">0đ</span>
            </div>
            <div id="discount-row" class="hidden flex justify-between text-xs text-green-600">
                <span>Giảm giá</span>
                <span id="total-discount">0đ</span>
            </div>
            <div class="flex justify-between text-sm font-black text-gray-900 pt-1 border-t border-gray-200">
                <span>TỔNG CỘNG</span>
                <span id="grand-total" class="text-primary text-base">0đ</span>
            </div>
        </div>

        {{-- ── Thông tin khách & Nút thanh toán ─ --}}
        <div class="border-t border-gray-200 px-4 py-3 flex-shrink-0 space-y-3">
            <div>
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider block mb-1">SĐT Khách (tuỳ chọn)</label>
                <input id="customer-phone" type="tel" placeholder="0901234567..."
                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/20 outline-none">
            </div>
            <button id="btn-checkout" onclick="POS.openCheckout()"
                    disabled
                    class="w-full py-3.5 bg-primary text-white font-black text-sm rounded-xl
                           disabled:opacity-40 disabled:cursor-not-allowed
                           enabled:hover:bg-blue-700 enabled:active:scale-[0.98]
                           transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined" style="font-size:20px">point_of_sale</span>
                THANH TOÁN
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MODAL THANH TOÁN
════════════════════════════════════════════════════════════ --}}
<div id="checkout-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm no-print">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div class="bg-primary px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-white font-black text-lg">Xác Nhận Thanh Toán</h2>
                <p id="modal-showtime-info" class="text-blue-200 text-xs mt-0.5"></p>
            </div>
            <button onclick="POS.closeCheckout()" class="text-white/60 hover:text-white text-2xl leading-none">✕</button>
        </div>

        {{-- Summary --}}
        <div class="px-6 py-4 bg-gray-50 border-b">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500">Ghế: <span id="modal-seats" class="font-bold text-gray-800"></span></p>
                    <p class="text-xs text-gray-500 mt-0.5">Combo: <span id="modal-combos" class="font-bold text-gray-800"></span></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Tổng cần thanh toán</p>
                    <p id="modal-total" class="text-2xl font-black text-primary"></p>
                </div>
            </div>
        </div>

        {{-- Payment method selector --}}
        <div class="px-6 py-4">
            <p class="text-xs font-bold text-gray-600 uppercase tracking-wider mb-3">Phương thức thanh toán</p>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <button id="btn-cash" onclick="POS.selectPayment('cash')"
                        class="flex flex-col items-center gap-2 p-4 border-2 border-primary bg-primary/5 rounded-xl transition-all">
                    <span class="material-symbols-outlined text-primary text-3xl">payments</span>
                    <span class="text-sm font-bold text-primary">Tiền Mặt</span>
                </button>
                <button id="btn-transfer" onclick="POS.selectPayment('transfer')"
                        class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-xl hover:border-primary/40 transition-all">
                    <span class="material-symbols-outlined text-gray-500 text-3xl">qr_code_2</span>
                    <span class="text-sm font-bold text-gray-600">Chuyển Khoản</span>
                </button>
            </div>

            {{-- Panel Tiền Mặt --}}
            <div id="cash-panel" class="space-y-3">
                <div>
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Khách đưa</label>
                    <input id="cash-given" type="number" placeholder="Nhập số tiền khách đưa..."
                           oninput="POS.calcChange()"
                           class="w-full border border-gray-200 rounded-lg px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                </div>
                <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-700">Tiền thối lại</span>
                    <span id="change-amount" class="text-xl font-black text-green-700">0đ</span>
                </div>
            </div>

            {{-- Panel Chuyển Khoản / QR --}}
            <div id="transfer-panel" class="hidden text-center space-y-3">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    {{-- QR tĩnh — nhúng URL bank transfer với số tiền --}}
                    <div id="qr-display" class="flex justify-center mb-3">
                        <img id="qr-img" src="" alt="QR Chuyển khoản" class="w-40 h-40 rounded-lg border-2 border-gray-200">
                    </div>
                    <p class="text-xs text-gray-500">STK: <strong class="text-gray-800">MB 0123456789</strong></p>
                    <p class="text-xs text-gray-500 mt-0.5">Chủ TK: <strong class="text-gray-800">CINEMA FILMGO</strong></p>
                    <p id="transfer-amount-label" class="text-primary font-black mt-1"></p>
                </div>
                <p class="text-[10px] text-gray-400">Nhân viên xác nhận sau khi khách chuyển khoản thành công</p>
            </div>
        </div>

        {{-- Confirm button --}}
        <div class="px-6 pb-5">
            <button id="btn-confirm-payment" onclick="POS.confirmCheckout()"
                    class="w-full py-3.5 bg-green-600 hover:bg-green-700 active:scale-[0.98] text-white font-black text-sm rounded-xl transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined" style="font-size:20px">check_circle</span>
                XÁC NHẬN & IN VÉ
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     KHU VỰC IN VÉ (ẩn, chỉ hiện khi print)
════════════════════════════════════════════════════════════ --}}
<div id="print-area">
    <div style="font-family: monospace; font-size: 12px; padding: 16px; max-width: 300px;">
        <div style="text-align:center; margin-bottom:12px;">
            <h1 style="font-size:18px; font-weight:900; margin:0;">FilmGo</h1>
            <p style="margin:2px 0; font-size:11px;" id="print-cinema"></p>
            <p style="margin:2px 0; font-size:11px;">Hotline: 1900 xxxx</p>
            <hr style="border:1px dashed #000; margin:8px 0">
        </div>
        <div id="print-content"></div>
        <div style="text-align:center; margin-top:12px;">
            <hr style="border:1px dashed #000; margin:8px 0">
            <p style="font-size:10px; color:#666;">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
            <p style="font-size:10px; color:#666;">Vui lòng giữ vé để kiểm soát vé vào rạp.</p>
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="pos-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[999] hidden no-print">
    <div class="px-5 py-3 rounded-xl text-sm font-semibold text-white shadow-xl flex items-center gap-2" id="pos-toast-inner"></div>
</div>
@endsection

@push('scripts')
<script>
const POS = (() => {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────────
    const state = {
        cinemaId:      {{ $cinemaId }},
        selectedSeats: [], // [{showtime_seat_id, label, type, price}, ...]
        combos:        {}, // {combo_id: qty}
        comboInfo:     {}, // {combo_id: {name, price}}
        voucher:       null, // {code, discount_type, discount_value}
        currentShowtime: null,
        paymentMethod: 'cash',
        csrfToken:     document.querySelector('meta[name="csrf-token"]').content,
    };

    // ── API helpers ───────────────────────────────────────────────────────────
    async function apiFetch(url, options = {}) {
        const res = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': state.csrfToken,
                'Accept': 'application/json',
            },
            ...options,
        });
        return res.json();
    }

    // ── Toast notification ────────────────────────────────────────────────────
    function toast(msg, type = 'success') {
        const el = document.getElementById('pos-toast');
        const inner = document.getElementById('pos-toast-inner');
        inner.className = `px-5 py-3 rounded-xl text-sm font-semibold text-white shadow-xl flex items-center gap-2 ${
            type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-gray-800'
        }`;
        inner.innerHTML = `<span class="material-symbols-outlined" style="font-size:18px">${type === 'success' ? 'check_circle' : 'error'}</span> ${msg}`;
        el.classList.remove('hidden');
        clearTimeout(el._timer);
        el._timer = setTimeout(() => el.classList.add('hidden'), 3500);
    }

    // ── Load danh sách phim theo ngày ─────────────────────────────────────────
    async function loadMovies() {
        const date = document.getElementById('pos-date').value;
        const listEl = document.getElementById('movie-list');
        listEl.innerHTML = `<div class="text-center py-8 text-gray-300"><span class="material-symbols-outlined text-4xl block mb-2">hourglass_empty</span><p class="text-xs">Đang tải...</p></div>`;

        const data = await apiFetch(`/staff/pos/api/showtimes?date=${date}`);
        const movies = data.data || [];

        document.getElementById('movie-count').textContent = movies.length + ' phim';

        if (!movies.length) {
            listEl.innerHTML = `<div class="text-center py-8 text-gray-400"><span class="material-symbols-outlined text-4xl block mb-2">event_busy</span><p class="text-xs">Không có phim nào hôm nay</p></div>`;
            return;
        }

        listEl.innerHTML = movies.map(movie => `
            <div class="rounded-xl border border-gray-100 overflow-hidden mb-2">
                <div class="px-3 py-2.5 bg-gray-50 flex items-start gap-2.5">
                    ${movie.poster
                        ? `<img src="${movie.poster}" class="w-10 h-14 object-cover rounded flex-shrink-0">`
                        : `<div class="w-10 h-14 bg-gray-200 rounded flex-shrink-0 flex items-center justify-center"><span class="material-symbols-outlined text-gray-400" style="font-size:20px">movie</span></div>`
                    }
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-900 leading-tight line-clamp-2">${movie.title}</p>
                        <div class="flex gap-1.5 mt-1 flex-wrap">
                            <span class="px-1.5 py-0.5 text-[9px] font-black border rounded uppercase ${movie.age_limit === 'P' ? 'bg-green-50 text-green-700 border-green-300' : movie.age_limit === 'T18' ? 'bg-red-50 text-red-700 border-red-300' : 'bg-amber-50 text-amber-700 border-amber-300'}">${movie.age_limit}</span>
                            <span class="text-[10px] text-gray-500">${movie.duration} phút</span>
                        </div>
                    </div>
                </div>
                <div class="px-3 py-2 space-y-1">
                    ${movie.showtimes.map(s => `
                        <button onclick="POS.loadSeatMap(${s.id})"
                                id="st-btn-${s.id}"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg border text-left transition-all
                                       hover:border-primary hover:bg-primary/5
                                       ${state.currentShowtime?.id === s.id ? 'border-primary bg-primary/10' : 'border-gray-100 bg-white'}
                                       ${['finished','cancelled'].includes(s.status) ? 'opacity-40 pointer-events-none' : ''}">
                            <div>
                                <span class="text-xs font-black text-gray-900">${s.start_time}</span>
                                <span class="text-[10px] text-gray-400 ml-1">→ ${s.end_time}</span>
                                <p class="text-[10px] text-gray-500 mt-0.5">${s.room_name} · ${s.room_type}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-primary">${formatMoney(s.base_price)}</span>
                                <span class="block text-[9px] font-semibold mt-0.5 px-1.5 py-0.5 rounded-full
                                    ${s.status === 'active' ? 'bg-green-100 text-green-700' : s.status === 'showing' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'}">
                                    ${s.status === 'active' ? 'Mở bán' : s.status === 'showing' ? 'Đang chiếu' : s.status}
                                </span>
                            </div>
                        </button>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }

    // ── Load sơ đồ ghế ─────────────────────────────────────────────────────
    async function loadSeatMap(showtimeId) {
        state.selectedSeats = [];
        updateCart();

        const container = document.getElementById('seat-map-container');
        container.innerHTML = `<div class="flex items-center gap-2 text-gray-400 py-10"><span class="material-symbols-outlined animate-spin">progress_activity</span><span class="text-sm">Đang tải sơ đồ ghế...</span></div>`;

        const data = await apiFetch(`/staff/pos/api/seat-map/${showtimeId}`);
        if (data.error) { toast(data.error, 'error'); return; }

        const { showtime, rows, seats } = data.data;
        state.currentShowtime = showtime;
        state.currentShowtime.id = showtimeId;

        document.getElementById('seat-showtime-title').textContent =
            `${showtime.movie} — ${showtime.room} — ${String(showtime.start_time).substring(0,5)}`;

        // Nhóm ghế theo hàng
        const seatsByRow = {};
        rows.forEach(r => seatsByRow[r] = []);
        seats.forEach(s => { if (seatsByRow[s.row]) seatsByRow[s.row].push(s); });

        const maxCols = Math.max(...Object.values(seatsByRow).map(r => r.length));

        container.innerHTML = `
            <div class="w-full max-w-2xl">
                <!-- Màn chiếu -->
                <div class="mb-6 px-8">
                    <div class="screen-bar mb-1"></div>
                    <p class="text-center text-[10px] text-gray-400 font-semibold tracking-widest uppercase">MÀN CHIẾU</p>
                </div>

                <!-- Sơ đồ ghế -->
                <div class="space-y-2">
                    ${rows.map(row => `
                        <div class="flex items-center gap-2">
                            <span class="w-6 text-center text-[11px] font-black text-gray-500 flex-shrink-0">${row}</span>
                            <div class="flex flex-wrap gap-1.5">
                                ${seatsByRow[row].map(s => renderSeat(s)).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>

                <!-- Thống kê -->
                <div class="mt-6 pt-4 border-t border-gray-100 flex gap-4 justify-center text-xs text-gray-500">
                    <span>Tổng ghế: <b>${seats.length}</b></span>
                    <span>Còn trống: <b class="text-green-600">${seats.filter(s => s.status === 'available').length}</b></span>
                    <span>Đã bán: <b class="text-red-500">${seats.filter(s => s.status === 'booked').length}</b></span>
                </div>
            </div>
        `;

        // Lưu seat data để toggle
        state.seatData = {};
        seats.forEach(s => state.seatData[s.showtime_seat_id] = s);

        // Active button suất chiếu
        document.querySelectorAll('[id^="st-btn-"]').forEach(b => {
            b.className = b.className.replace('border-primary bg-primary/10', 'border-gray-100 bg-white');
        });
        const activeBtn = document.getElementById(`st-btn-${showtimeId}`);
        if (activeBtn) activeBtn.className = activeBtn.className.replace('border-gray-100 bg-white', 'border-primary bg-primary/10');
    }

    // ── Render một ghế ────────────────────────────────────────────────────
    function renderSeat(s) {
        const isUnavailable = ['booked','holding','maintenance'].includes(s.status) || s.seat_status === 'maintenance';
        const isVip = s.type && s.type.toLowerCase().includes('vip');
        const isSelected = state.selectedSeats.some(sel => sel.showtime_seat_id === s.showtime_seat_id);

        let cls = 'seat-btn';
        if (isSelected) cls += ' seat-selected';
        else if (s.seat_status === 'maintenance') cls += ' seat-maintenance';
        else if (s.status === 'booked') cls += ' seat-booked';
        else if (s.status === 'holding') cls += ' seat-holding';
        else cls += ' seat-available';

        if (isVip) cls += ' seat-vip';

        const clickFn = isUnavailable && !isSelected ? '' : `onclick="POS.toggleSeat(${s.showtime_seat_id})"`;

        return `<button id="seat-${s.showtime_seat_id}" class="${cls}" title="${s.label} — ${s.type} — ${formatMoney(s.price)}" ${clickFn}>${s.label}</button>`;
    }

    // ── Toggle chọn ghế ──────────────────────────────────────────────────
    function toggleSeat(showtimeSeatId) {
        const s = state.seatData[showtimeSeatId];
        if (!s) return;

        const idx = state.selectedSeats.findIndex(sel => sel.showtime_seat_id === showtimeSeatId);
        if (idx > -1) {
            state.selectedSeats.splice(idx, 1);
        } else {
            state.selectedSeats.push({
                showtime_seat_id: s.showtime_seat_id,
                label: s.label,
                type: s.type,
                price: s.price,
            });
        }

        // Cập nhật UI ghế
        const btn = document.getElementById(`seat-${showtimeSeatId}`);
        if (btn) {
            const isNowSelected = state.selectedSeats.some(sel => sel.showtime_seat_id === showtimeSeatId);
            btn.className = btn.className
                .replace(/seat-(available|selected)/g, '')
                .trim();
            btn.className += isNowSelected ? ' seat-selected' : ' seat-available';
        }

        updateCart();
    }

    // ── Combo ─────────────────────────────────────────────────────────────
    function changeCombo(comboId, delta) {
        const el = document.getElementById(`combo-qty-${comboId}`);
        if (!el) return;

        const comboEl = el.closest('[data-combo-id]');
        state.comboInfo[comboId] = {
            name: comboEl.dataset.comboName,
            price: parseInt(comboEl.dataset.comboPrice),
        };

        const cur = state.combos[comboId] || 0;
        const next = Math.max(0, cur + delta);
        state.combos[comboId] = next;
        el.textContent = next;
        updateCart();
    }

    // ── Voucher ──────────────────────────────────────────────────────────
    async function applyVoucher() {
        const code = document.getElementById('voucher-input').value.trim();
        if (!code) { toast('Nhập mã giảm giá trước!', 'error'); return; }

        const data = await apiFetch('/staff/pos/api/voucher', {
            method: 'POST',
            body: JSON.stringify({ code }),
        });

        if (!data.valid) { toast(data.message, 'error'); return; }

        state.voucher = { code: data.code, discount_type: data.discount_type, discount_value: data.discount_value };
        document.getElementById('voucher-info').classList.remove('hidden');
        document.getElementById('voucher-label').textContent =
            `🏷 ${data.code}: ${data.discount_type === 'percent' ? data.discount_value + '%' : formatMoney(data.discount_value)} off`;
        toast(data.message, 'success');
        updateCart();
    }

    function removeVoucher() {
        state.voucher = null;
        document.getElementById('voucher-info').classList.add('hidden');
        document.getElementById('voucher-input').value = '';
        updateCart();
    }

    // ── Tính toán giỏ hàng ───────────────────────────────────────────────
    function calcTotals() {
        const seatTotal  = state.selectedSeats.reduce((s, seat) => s + seat.price, 0);
        const comboTotal = Object.entries(state.combos).reduce((s, [id, qty]) => {
            return s + ((state.comboInfo[id]?.price || 0) * qty);
        }, 0);

        let discount = 0;
        const subtotal = seatTotal + comboTotal;
        if (state.voucher) {
            discount = state.voucher.discount_type === 'percent'
                ? Math.round(subtotal * state.voucher.discount_value / 100)
                : Math.min(state.voucher.discount_value, subtotal);
        }

        return { seatTotal, comboTotal, discount, grand: Math.max(0, subtotal - discount) };
    }

    // ── Cập nhật UI giỏ hàng ─────────────────────────────────────────────
    function updateCart() {
        const { seatTotal, comboTotal, discount, grand } = calcTotals();
        const hasItems = state.selectedSeats.length > 0;

        // Seats list
        const cartEl = document.getElementById('cart-seats');
        const noMsg  = document.getElementById('no-seat-msg');
        if (state.selectedSeats.length) {
            noMsg?.remove();
            cartEl.innerHTML = state.selectedSeats.map(s => `
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <div>
                        <span class="text-xs font-bold text-gray-900">${s.label}</span>
                        <span class="text-[10px] text-gray-400 ml-1">(${s.type})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-primary">${formatMoney(s.price)}</span>
                        <button onclick="POS.toggleSeat(${s.showtime_seat_id})" class="text-red-400 hover:text-red-600 text-xs">✕</button>
                    </div>
                </div>
            `).join('');
        } else {
            cartEl.innerHTML = `<p id="no-seat-msg" class="text-xs text-gray-400 text-center py-4">Chưa chọn ghế nào</p>`;
        }

        // Totals
        document.getElementById('total-seat-count').textContent = state.selectedSeats.length;
        document.getElementById('total-seat-price').textContent = formatMoney(seatTotal);
        document.getElementById('total-combo-price').textContent = formatMoney(comboTotal);
        document.getElementById('grand-total').textContent = formatMoney(grand);

        const discountRow = document.getElementById('discount-row');
        if (discount > 0) {
            discountRow.classList.remove('hidden');
            document.getElementById('total-discount').textContent = '−' + formatMoney(discount);
        } else {
            discountRow.classList.add('hidden');
        }

        // Checkout button
        document.getElementById('btn-checkout').disabled = !hasItems || !state.currentShowtime;
    }

    // ── Checkout Modal ───────────────────────────────────────────────────
    function openCheckout() {
        if (!state.selectedSeats.length || !state.currentShowtime) return;
        const totals = calcTotals();

        document.getElementById('modal-showtime-info').textContent =
            `${state.currentShowtime.movie} — ${String(state.currentShowtime.start_time).substring(0,5)}`;
        document.getElementById('modal-seats').textContent = state.selectedSeats.map(s => s.label).join(', ');
        document.getElementById('modal-combos').textContent =
            Object.entries(state.combos).filter(([,q]) => q > 0).map(([id, q]) => `${state.comboInfo[id]?.name} x${q}`).join(', ') || '—';
        document.getElementById('modal-total').textContent = formatMoney(totals.grand);
        document.getElementById('transfer-amount-label').textContent = formatMoney(totals.grand);

        // Reset cash input
        document.getElementById('cash-given').value = '';
        document.getElementById('change-amount').textContent = '0đ';

        // QR chuyển khoản
        const qrUrl = `https://img.vietqr.io/image/MB-0123456789-compact2.png?amount=${totals.grand}&addInfo=${encodeURIComponent('Dat ve FilmGo')}&accountName=CINEMA%20FILMGO`;
        document.getElementById('qr-img').src = qrUrl;

        document.getElementById('checkout-modal').classList.remove('hidden');
        selectPayment('cash');
    }

    function closeCheckout() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    function selectPayment(method) {
        state.paymentMethod = method;
        const cashBtn     = document.getElementById('btn-cash');
        const transferBtn = document.getElementById('btn-transfer');
        const cashPanel   = document.getElementById('cash-panel');
        const transferPanel = document.getElementById('transfer-panel');

        const activeClass   = 'border-primary bg-primary/5';
        const inactiveClass = 'border-gray-200';

        if (method === 'cash') {
            cashBtn.className     = cashBtn.className.replace(inactiveClass, activeClass);
            transferBtn.className = transferBtn.className.replace(activeClass, inactiveClass);
            cashPanel.classList.remove('hidden');
            transferPanel.classList.add('hidden');
        } else {
            transferBtn.className = transferBtn.className.replace(inactiveClass, activeClass);
            cashBtn.className     = cashBtn.className.replace(activeClass, inactiveClass);
            transferPanel.classList.remove('hidden');
            cashPanel.classList.add('hidden');
        }
    }

    function calcChange() {
        const totals = calcTotals();
        const given  = parseInt(document.getElementById('cash-given').value) || 0;
        const change = Math.max(0, given - totals.grand);
        document.getElementById('change-amount').textContent = formatMoney(change);
        document.getElementById('change-amount').className =
            `text-xl font-black ${change >= 0 ? 'text-green-700' : 'text-red-600'}`;
    }

    // ── Xử lý thanh toán ─────────────────────────────────────────────────
    async function confirmCheckout() {
        const btn = document.getElementById('btn-confirm-payment');
        btn.disabled = true;
        btn.innerHTML = `<span class="material-symbols-outlined animate-spin" style="font-size:18px">progress_activity</span> Đang xử lý...`;

        try {
            const payload = {
                showtime_id:     state.currentShowtime.id,
                seat_ids:        state.selectedSeats.map(s => s.showtime_seat_id),
                combos:          state.combos,
                payment_method:  state.paymentMethod,
                customer_phone:  document.getElementById('customer-phone').value || null,
                voucher_code:    state.voucher?.code || null,
            };

            const data = await apiFetch('/staff/pos/api/checkout', {
                method: 'POST',
                body: JSON.stringify(payload),
            });

            if (!data.success) throw new Error(data.message);

            closeCheckout();
            toast('Bán vé thành công! Đang in vé...', 'success');

            // In vé
            printReceipt(data.booking);

            // Reset state
            state.selectedSeats = [];
            state.combos = {};
            state.comboInfo = {};
            state.voucher = null;
            removeVoucher();
            // Reset combo qty displays
            document.querySelectorAll('[id^="combo-qty-"]').forEach(el => el.textContent = '0');
            updateCart();

            // Reload seat map để cập nhật trạng thái ghế
            if (state.currentShowtime) {
                setTimeout(() => loadSeatMap(state.currentShowtime.id), 1000);
            }

        } catch (err) {
            toast(err.message || 'Lỗi không xác định. Thử lại sau.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px">check_circle</span> XÁC NHẬN & IN VÉ`;
        }
    }

    // ── In vé ─────────────────────────────────────────────────────────────
    function printReceipt(booking) {
        document.getElementById('print-cinema').textContent = booking.showtime?.cinema || '';

        const seatsHtml = booking.seats.map(s => `
            <div style="margin-bottom:6px; padding-bottom:6px; border-bottom:1px dashed #eee;">
                <b>Ghế ${s.label}</b> (${s.type})<br>
                Giá: ${formatMoney(s.price)}<br>
                QR: <code style="font-size:10px">${s.qr}</code>
            </div>
        `).join('');

        const combosHtml = booking.combos.length
            ? booking.combos.map(c => `<div>${c.name} x${c.quantity}: ${formatMoney(c.subtotal)}</div>`).join('')
            : '';

        document.getElementById('print-content').innerHTML = `
            <div style="margin-bottom:8px;">
                <b>Mã đặt vé:</b> ${booking.booking_code}<br>
                <b>Phim:</b> ${booking.showtime?.movie}<br>
                <b>Ngày:</b> ${booking.showtime?.show_date}<br>
                <b>Giờ:</b> ${booking.showtime?.start_time}<br>
                <b>Phòng:</b> ${booking.showtime?.room}<br>
            </div>
            <hr style="border:1px dashed #000; margin:8px 0">
            ${seatsHtml}
            ${combosHtml ? `<hr style="border:1px dashed #000; margin:8px 0"><b>Combo:</b><br>${combosHtml}` : ''}
            <hr style="border:1px dashed #000; margin:8px 0">
            <div style="font-size:13px;">
                <div style="display:flex; justify-content:space-between;">
                    <b>Giảm giá:</b><span>-${formatMoney(booking.discount_amount || 0)}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:15px; margin-top:4px;">
                    <b>TỔNG CỘNG:</b><b>${formatMoney(booking.total_amount)}</b>
                </div>
                <div style="font-size:11px; margin-top:4px;">
                    Thanh toán: ${booking.payment_method === 'cash' ? 'Tiền mặt' : 'Chuyển khoản'}
                </div>
            </div>
        `;

        setTimeout(() => window.print(), 300);
    }

    // ── Format tiền ──────────────────────────────────────────────────────
    function formatMoney(n) {
        return new Intl.NumberFormat('vi-VN').format(n || 0) + 'đ';
    }

    // ── Init ──────────────────────────────────────────────────────────────
    function init() {
        loadMovies();
        document.getElementById('pos-date').addEventListener('change', loadMovies);
    }

    // Public API
    return { init, loadMovies, loadSeatMap, toggleSeat, changeCombo, applyVoucher, removeVoucher, openCheckout, closeCheckout, selectPayment, calcChange, confirmCheckout };
})();

document.addEventListener('DOMContentLoaded', POS.init);
</script>
@endpush
