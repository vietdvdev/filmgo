@extends('layouts.staff')

@section('title', 'POS — Bán Vé Tại Quầy | FilmGo')

@push('styles')
<style>
    /* ── POS Layout: Full-height, no scroll ─── */
    #pos-root { height: calc(100vh - 64px); display: grid; grid-template-columns: 280px 1fr 340px; }

    /* ── Tab Mode F&B: ẩn cột giữa (sơ đồ ghế), mở rộng cột trái ─── */
    #pos-root.fnb-mode { grid-template-columns: 1fr 340px; }
    #pos-root.fnb-mode #pos-col-seat { display: none; }
    #pos-root.fnb-mode #ticket-cart-panel { display: none; }
    #pos-root.fnb-mode #total-seat-row { display: none; }
    #pos-root.fnb-mode #right-fnb-list { display: none; }

    .pos-tab-btn {
        flex: 1; padding: 6px; border-radius: 8px; font-size: 11px; font-weight: 700;
        cursor: pointer; transition: all 0.2s; border: none; outline: none;
        display: flex; align-items: center; justify-content: center; gap: 4px;
    }
    .pos-tab-btn.active { background: #3b82f6; color: #fff; box-shadow: 0 2px 8px rgba(59,130,246,0.4); }
    .pos-tab-btn.fnb-active { background: #f97316; color: #fff; box-shadow: 0 2px 8px rgba(249,115,22,0.4); }
    .pos-tab-btn:not(.active):not(.fnb-active) { background: #f3f4f6; color: #6b7280; }

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

    /* ── Print styles — Máy in nhiệt 80mm ─── */
    @@media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 80mm !important;
            max-width: 80mm !important;
            min-height: auto !important;
        }
        * { box-sizing: border-box !important; }
        #pos-root, #checkout-modal, #success-modal, #pos-toast,
        aside, header { display: none !important; }
        #print-ticket-area { display: block !important; }

        @@page {
            size: 80mm auto;
            margin: 0;
        }
        #print-ticket-area {
            width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 1mm 1.5mm 0.5mm;
            font-family: 'Courier New', Courier, monospace;
            font-size: 10pt;
            line-height: 1.1;
            color: #000;
        }
        #print-ticket-area div,
        #print-ticket-area table,
        #print-ticket-area td,
        #print-ticket-area th {
            margin: 0 !important;
            padding: 0 !important;
        }
    }
    /* Ẩn trong giao diện thường */
    #print-ticket-area { display: none; }
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

            <div class="mb-3">
                <input id="pos-search" type="search" placeholder="Tìm phim, phòng, giờ..."
                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"
                       autocomplete="off">
            </div>

            {{-- Tab Switcher: Bán Vé / Bán F&B --}}
            <div class="flex gap-1 p-1 rounded-xl mb-2" style="background:#f1f5f9">
                <button class="pos-tab-btn active" id="tab-ticket" onclick="_FNB.switchMode('ticket')">
                    <span class="material-symbols-outlined" style="font-size:14px">confirmation_number</span>
                    Bán Vé
                </button>
                <button class="pos-tab-btn" id="tab-fnb" onclick="_FNB.switchMode('fnb')">
                    <span class="material-symbols-outlined" style="font-size:14px">fastfood</span>
                    Bán F&B
                </button>
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
        {{-- F&B Panel: sản phẩm đồ ăn (hiện khi mode fnb) --}}
        <div id="fnb-panel" class="hidden flex-1 overflow-y-auto p-3">
            <div class="text-center py-6 text-gray-400" id="fnb-loading">
                <span class="material-symbols-outlined text-3xl block mb-2">hourglass_empty</span>
                <p class="text-xs">Đang tải danh sách...</p>
            </div>
            <div id="fnb-product-list"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CỘT GIỮA — Sơ đồ ghế real-time
    ════════════════════════════════════════════════════════════ --}}
    {{-- CỘT GIỮA — Sơ đồ ghế real-time --}}
    <div class="flex flex-col overflow-hidden" id="pos-col-seat">

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
        <div id="ticket-cart-panel">
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
        </div>

        {{-- ── F&B Combos ──────────────────────── --}}
        <div id="right-fnb-list" class="border-t border-gray-100 flex-shrink-0">
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
            <div id="total-seat-row" class="flex justify-between text-xs text-gray-600">
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

            {{-- Nút Bán F&B (chỉ hiện trong mode F&B) --}}
            <button id="btn-checkout-fnb" onclick="_FNB.checkoutFnb()"
                    disabled
                    class="hidden w-full py-3.5 font-black text-sm rounded-xl
                           disabled:opacity-40 disabled:cursor-not-allowed
                           transition-all flex items-center justify-center gap-2 text-white"
                    style="background:#f97316">
                <span class="material-symbols-outlined" style="font-size:20px">fastfood</span>
                BÁN F&amp;B
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MODAL THÀNH CÔNG BÁN F&B
════════════════════════════════════════════════════════════ --}}
<div id="fnb-success-modal"
     class="hidden fixed inset-0 z-[60] flex items-center justify-center no-print"
     role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('fnb-success-modal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
        {{-- Header --}}
        <div class="px-6 pt-6 pb-4 text-center" style="background:linear-gradient(135deg,#f97316,#ea580c)">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3"
                 style="background:rgba(255,255,255,0.2)">
                <span class="material-symbols-outlined text-white" style="font-size:36px; font-variation-settings:'FILL' 1">check_circle</span>
            </div>
            <h2 class="text-xl font-black text-white">Bán F&amp;B Thành Công!</h2>
            <div class="mt-3 inline-flex items-center gap-2 px-4 py-1.5 rounded-full"
                 style="background:rgba(255,255,255,0.2)">
                <span class="text-white font-mono font-black tracking-widest text-base" id="fnb-success-code">—</span>
            </div>
        </div>
        {{-- Items --}}
        <div class="px-5 py-3">
            <p class="text-xs font-bold uppercase text-gray-500 tracking-wider mb-2">Sản phẩm đã bán:</p>
            <ul id="fnb-success-items" class="space-y-0.5 max-h-40 overflow-y-auto"></ul>
            <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                <span class="font-bold text-gray-700">Tổng cộng</span>
                <span class="font-black text-orange-600 text-xl" id="fnb-success-total"></span>
            </div>
        </div>
        {{-- Actions --}}
        <div class="px-5 pb-5">
            <button onclick="document.getElementById('fnb-success-modal').classList.add('hidden')"
                    class="w-full py-3 font-black text-white rounded-xl transition-all"
                    style="background:#f97316"
                    onmouseover="this.style.background='#ea580c'"
                    onmouseout="this.style.background='#f97316'">
                Hoàn Tất &amp; Bán Tiếp
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
     PHẦN 2: KHU VỰC IN VÉ NHIỆT 80mm (ẩn trong giao diện, hiện khi print)
════════════════════════════════════════════════════════════ --}}
<div id="print-ticket-area">
    {{-- Header rạp --}}
    <div style="text-align:center; border-bottom:2px solid #000; padding-bottom:6px; margin-bottom:8px;">
        <div style="font-size:20pt; font-weight:900; letter-spacing:2px;">★ FilmGo ★</div>
        <div id="pt-cinema" style="font-size:10pt; margin-top:2px;"></div>
        <div style="font-size:9pt; color:#444;">Hotline: 1900 xxxx</div>
    </div>

    {{-- Thông tin vé --}}
    <div style="margin-bottom:8px;">
        <div style="font-size:9pt; text-align:center; letter-spacing:1px; color:#555; margin-bottom:4px;">── VÉ XEM PHIM ──</div>
        <table style="width:100%; font-size:10pt; border-collapse:collapse;">
            <tr><td style="width:35%; color:#555;">Mã vé:</td>   <td id="pt-code"  style="font-weight:900;"></td></tr>
            <tr><td style="color:#555;">Phim:</td>               <td id="pt-movie" style="font-weight:700;"></td></tr>
            <tr><td style="color:#555;">Ngày:</td>               <td id="pt-date"></td></tr>
            <tr><td style="color:#555;">Giờ:</td>                <td id="pt-time"  style="font-weight:700;"></td></tr>
            <tr><td style="color:#555;">Phòng:</td>              <td id="pt-room"></td></tr>
        </table>
    </div>

    {{-- Ghế --}}
    <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin-bottom:8px;">
        <div style="font-size:9pt; font-weight:700; margin-bottom:4px;">GHẾ NGỒI</div>
        <div id="pt-seats" style="font-size:10pt;"></div>
    </div>

    {{-- Combo F&B --}}
    <div id="pt-combo-wrap" style="border-bottom:1px dashed #000; padding-bottom:6px; margin-bottom:8px; display:none;">
        <div style="font-size:9pt; font-weight:700; margin-bottom:4px;">BẮP NƯỚC (F&B)</div>
        <div id="pt-combos" style="font-size:10pt;"></div>
    </div>

    {{-- Tổng tiền --}}
    <div style="margin-bottom:8px;">
        <table style="width:100%; font-size:10pt;">
            <tr id="pt-discount-row" style="display:none; color:#e53e3e;">
                <td>Giảm giá:</td><td id="pt-discount" style="text-align:right;"></td>
            </tr>
            <tr style="font-size:13pt; font-weight:900; border-top:1px solid #000;">
                <td>TỔNG CỘNG:</td><td id="pt-total" style="text-align:right;"></td>
            </tr>
            <tr style="font-size:9pt; color:#555;">
                <td>Thanh toán:</td><td id="pt-method" style="text-align:right;"></td>
            </tr>
        </table>
    </div>

    {{-- Mã QR vé điện tử --}}
    <div style="text-align:center; border-top:1px dashed #000; padding-top:8px;">
        <div style="font-size:9pt; color:#555; margin-bottom:4px;">Quét mã để nhận vé điện tử</div>
        <div id="pt-qr-print" style="display:inline-block;"></div>
        <div id="pt-qr-text" style="font-size:8pt; color:#555; margin-top:4px; word-break:break-all;"></div>
    </div>

    {{-- Footer --}}
    <div style="text-align:center; border-top:2px solid #000; margin-top:10px; padding-top:6px; font-size:9pt; color:#555;">
        <p style="margin:2px 0;">Cảm ơn quý khách đã đến FilmGo!</p>
        <p style="margin:2px 0;">Vui lòng giữ vé khi vào rạp.</p>
        <p style="margin:4px 0; font-size:8pt;">★ Chúc quý khách xem phim vui vẻ ★</p>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     PHẦN 1: MODAL THÀNH CÔNG SAU THANH TOÁN
     Hiển thị sau khi confirmCheckout() thành công
════════════════════════════════════════════════════════════ --}}
<div id="success-modal"
     class="hidden fixed inset-0 z-[60] flex items-center justify-center no-print"
     role="dialog" aria-modal="true" aria-labelledby="success-modal-title">

    {{-- Overlay đen mờ --}}
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    {{-- Card nội dung --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden
                transform transition-all duration-300 scale-100">

        {{-- ── Phần header: Checkmark + Booking Code ── --}}
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 px-6 pt-8 pb-6 text-center">
            {{-- Animated checkmark --}}
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4
                        ring-4 ring-white/30 animate-pulse">
                <span class="material-symbols-outlined text-white" style="font-size:48px; font-variation-settings:'FILL' 1;">check_circle</span>
            </div>
            <h2 id="success-modal-title" class="text-2xl font-black text-white tracking-wide">Thanh Toán Thành Công!</h2>
            <p class="text-green-100 text-sm mt-1">Giao dịch đã được xác nhận</p>
            {{-- Booking code badge --}}
            <div class="mt-4 inline-flex items-center gap-2 bg-white/20 border border-white/30
                        rounded-full px-5 py-2">
                <span class="material-symbols-outlined text-white text-base">confirmation_number</span>
                <span class="text-white font-mono font-black text-lg tracking-widest"
                      id="success-booking-code">—</span>
            </div>
        </div>

        {{-- ── Thông tin tóm tắt ── --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 text-sm">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Phim</p>
                    <p id="success-movie" class="font-bold text-gray-900 mt-0.5 leading-tight"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Suất chiếu</p>
                    <p id="success-time" class="font-bold text-gray-900 mt-0.5"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Ghế</p>
                    <p id="success-seats" class="font-bold text-gray-900 mt-0.5"></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Tổng tiền</p>
                    <p id="success-total" class="font-black text-green-600 text-base mt-0.5"></p>
                </div>
            </div>
        </div>

        {{-- ── Khu vực hiển thị mã QR điện tử (mặc định ẩn) ── --}}
        <div id="qr-display-area" class="hidden px-6 py-4 border-b border-gray-100 text-center">
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-3">
                Khách quét để nhận vé điện tử
            </p>
            <div id="qr-code-container" class="flex justify-center mb-2"></div>
            <p id="qr-booking-code-label" class="text-[10px] font-mono text-gray-400"></p>
        </div>

        {{-- ── 3 nút hành động ── --}}
        <div class="px-6 py-4 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                {{-- Nút 1: In vé giấy --}}
                <button id="btn-print-ticket"
                        onclick="POS.handlePrintTicket()"
                        class="flex flex-col items-center gap-1.5 px-4 py-3.5
                               border-2 border-blue-600 text-blue-700 bg-blue-50
                               rounded-xl font-bold text-sm
                               hover:bg-blue-100 hover:border-blue-700
                               active:scale-95 transition-all duration-150">
                    <span class="material-symbols-outlined text-blue-600" style="font-size:26px;">print</span>
                    <span>In Vé Giấy</span>
                    <span class="text-[10px] font-normal text-blue-500">Máy in 80mm</span>
                </button>

                {{-- Nút 2: Quét mã QR --}}
                <button id="btn-show-qr"
                        onclick="POS.handleShowQR()"
                        class="flex flex-col items-center gap-1.5 px-4 py-3.5
                               bg-blue-700 text-white
                               rounded-xl font-bold text-sm
                               hover:bg-blue-800
                               active:scale-95 transition-all duration-150">
                    <span class="material-symbols-outlined" style="font-size:26px;">qr_code_scanner</span>
                    <span>Vé Điện Tử</span>
                    <span class="text-[10px] font-normal text-blue-200">Khách quét QR</span>
                </button>
            </div>

            {{-- Nút 3: Hoàn tất & Tạo đơn mới --}}
            <button id="btn-new-order"
                    onclick="POS.resetPOS()"
                    class="w-full flex items-center justify-center gap-2
                           py-3.5 bg-gray-900 hover:bg-gray-700
                           text-white font-black text-sm rounded-xl
                           active:scale-[0.98] transition-all duration-150">
                <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
                Hoàn Tất & Tạo Đơn Mới
                <kbd class="ml-1 px-1.5 py-0.5 text-[9px] bg-white/20 rounded font-mono">F2</kbd>
            </button>
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="pos-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[999] hidden no-print">
    <div class="px-5 py-3 rounded-xl text-sm font-semibold text-white shadow-xl flex items-center gap-2" id="pos-toast-inner"></div>
</div>
@endsection

@push('scripts')
{{-- Thư viện tạo mã QR điện tử --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const POS = (() => {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────────
    const state = {
        cinemaId:        {{ $cinemaId }},
        selectedSeats:   [], // [{showtime_seat_id, label, type, price}, ...]
        combos:          {}, // {combo_id: qty}
        comboInfo:       {}, // {combo_id: {name, price}}
        voucher:         null, // {code, discount_type, discount_value}
        currentShowtime: null,
        currentBooking:  null, // lưu booking sau khi checkout thành công
        paymentMethod:   'cash',
        csrfToken:       document.querySelector('meta[name="csrf-token"]').content,
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
        const search = document.getElementById('pos-search')?.value.trim() || '';
        const listEl = document.getElementById('movie-list');
        listEl.innerHTML = `<div class="text-center py-8 text-gray-300"><span class="material-symbols-outlined text-4xl block mb-2">hourglass_empty</span><p class="text-xs">Đang tải...</p></div>`;

        const url = new URL(`/staff/pos/api/showtimes`, window.location.origin);
        url.searchParams.set('date', date);
        if (search) url.searchParams.set('search', search);
        const data = await apiFetch(url.toString());
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
                                       ${s.status === 'cancelled' ? 'opacity-40 pointer-events-none' : ''}">
                            <div>
                                <span class="text-xs font-black text-gray-900">${s.start_time}</span>
                                <span class="text-[10px] text-gray-400 ml-1">→ ${s.end_time}</span>
                                <p class="text-[10px] text-gray-500 mt-0.5">${s.room_name} · ${s.room_type}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-primary">${formatMoney(s.base_price)}</span>
                                <span class="block text-[9px] font-semibold mt-0.5 px-1.5 py-0.5 rounded-full
                                    ${s.status === 'active' ? 'bg-green-100 text-green-700' : s.status === 'showing' ? 'bg-blue-100 text-blue-700' : s.status === 'finished' ? 'bg-gray-100 text-gray-500' : s.status === 'upcoming' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500'}">
                                    ${s.status === 'active' ? 'Mở bán' : s.status === 'showing' ? 'Đang chiếu' : s.status === 'finished' ? 'Đã chiếu' : s.status === 'upcoming' ? 'Sắp chiếu' : s.status}
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
            if (state.selectedSeats.length >= 10) {
                toast('Chỉ được chọn tối đa 10 ghế!', 'error');
                return;
            }
            state.selectedSeats.push({
                showtime_seat_id: s.showtime_seat_id,
                label: s.label,
                row: s.row,
                number: s.number,
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
    function checkSingleSeatRule() {
        if (!state.seatData) return null;
        const rows = {};
        Object.values(state.seatData).forEach(s => {
            if (!rows[s.row]) rows[s.row] = [];
            rows[s.row].push(s);
        });
        Object.values(rows).forEach(r => r.sort((a, b) => a.number - b.number));

        for (const [row, seats] of Object.entries(rows)) {
            for (let i = 0; i < seats.length; i++) {
                const s = seats[i];
                const isSelected = sel => state.selectedSeats.some(x => x.showtime_seat_id === sel.showtime_seat_id);
                const isBlocked = sel => ['booked','holding','maintenance'].includes(sel.status) || sel.seat_status === 'maintenance' || isSelected(sel);
                if (isBlocked(s)) continue;
                const leftBlocked  = (i === 0) || isBlocked(seats[i - 1]);
                const rightBlocked = (i === seats.length - 1) || isBlocked(seats[i + 1]);
                if (leftBlocked && rightBlocked) {
                    return `Ghế ${s.label} sẽ bị bỏ trống cô đơn. Vui lòng chọn thêm hoặc bỏ bớt ghế liền kề.`;
                }
            }
        }
        return null;
    }

    function openCheckout() {
        if (!state.selectedSeats.length || !state.currentShowtime) return;

        const singleSeatErr = checkSingleSeatRule();
        if (singleSeatErr) { toast(singleSeatErr, 'error'); return; }
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

            // ── Hiển thị modal thành công — thay vì in ngay lập tức ──
            openSuccessModal(data.booking);

            // Reload seat map (chạy nền, không cần chờ)
            if (state.currentShowtime) {
                setTimeout(() => loadSeatMap(state.currentShowtime.id), 800);
            }

        } catch (err) {
            toast(err.message || 'Lỗi không xác định. Thử lại sau.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px">check_circle</span> XÁC NHẬN & IN VÉ`;
        }
    }

    // printReceipt() cũ đã được thay bằng handlePrintTicket() dùng layout in mới (pt-*)
    // Giữ lại dòng trống để tránh lệch số dòng khi diff

    // ── Giao diện & Logic sau thanh toán thành công ────────────────────────
    function openSuccessModal(booking) {
        state.currentBooking = booking;

        // Cập nhật thông tin cơ bản lên modal
        document.getElementById('success-booking-code').textContent = booking.booking_code;
        document.getElementById('success-movie').textContent = booking.showtime?.movie || '';
        document.getElementById('success-time').textContent = `${booking.showtime?.show_date || ''} ${booking.showtime?.start_time || ''}`;

        const seatLabels = booking.seats ? booking.seats.map(s => s.label).join(', ') : '';
        document.getElementById('success-seats').textContent = seatLabels;
        document.getElementById('success-total').textContent = formatMoney(booking.final_total ?? booking.total_amount);

        // Ẩn vùng hiển thị QR code cũ
        document.getElementById('qr-display-area').classList.add('hidden');
        document.getElementById('qr-code-container').innerHTML = '';

        // Hiển thị modal thành công
        document.getElementById('success-modal').classList.remove('hidden');
    }

    function handlePrintTicket() {
        const booking = state.currentBooking;
        if (!booking) return;

        // Điền thông tin vào khu vực in vé
        document.getElementById('pt-cinema').textContent = booking.showtime?.cinema || '';
        document.getElementById('pt-code').textContent = booking.booking_code;
        document.getElementById('pt-movie').textContent = booking.showtime?.movie || '';
        document.getElementById('pt-date').textContent = booking.showtime?.show_date || '';
        document.getElementById('pt-time').textContent = booking.showtime?.start_time || '';
        document.getElementById('pt-room').textContent = booking.showtime?.room || '';

        const seatLabels = booking.seats ? booking.seats.map(s => `${s.label} (${s.type})`).join(', ') : '';
        document.getElementById('pt-seats').textContent = seatLabels;

        const comboWrap = document.getElementById('pt-combo-wrap');
        const combosDiv = document.getElementById('pt-combos');
        if (booking.combos && booking.combos.length > 0) {
            comboWrap.style.display = 'block';
            combosDiv.innerHTML = booking.combos.map(c => `
                <div style="display:flex; justify-content:space-between; margin-bottom: 2px;">
                    <span>${c.name} x${c.quantity}</span>
                    <span>${formatMoney(c.subtotal)}</span>
                </div>
            `).join('');
        } else {
            comboWrap.style.display = 'none';
        }

        const discountRow = document.getElementById('pt-discount-row');
        if (booking.discount_amount > 0) {
            discountRow.style.display = 'table-row';
            document.getElementById('pt-discount').textContent = '-' + formatMoney(booking.discount_amount);
        } else {
            discountRow.style.display = 'none';
        }

        document.getElementById('pt-total').textContent = formatMoney(booking.final_total ?? booking.total_amount);
        document.getElementById('pt-method').textContent = booking.payment_method === 'cash' ? 'Tiền mặt' : 'Chuyển khoản';

        // Tạo mã QR Code trên bản in
        const qrPrintDiv = document.getElementById('pt-qr-print');
        qrPrintDiv.innerHTML = '';
        const qrContent = booking.seats && booking.seats[0] ? booking.seats[0].qr : booking.booking_code;

        if (typeof QRCode !== 'undefined') {
            new QRCode(qrPrintDiv, {
                text: qrContent,
                width: 90,
                height: 90,
                correctLevel: QRCode.CorrectLevel.M
            });
        }
        document.getElementById('pt-qr-text').textContent = booking.booking_code;

        // Gọi lệnh in hệ thống, sau khi in xong quay về chọn ghế
        setTimeout(() => {
            window.onafterprint = () => { window.onafterprint = null; resetPOS(); };
            window.print();
        }, 250);
    }

    function handleShowQR() {
        const booking = state.currentBooking;
        if (!booking) return;

        const qrArea = document.getElementById('qr-display-area');
        const container = document.getElementById('qr-code-container');

        qrArea.classList.remove('hidden');
        container.innerHTML = '';

        // Ưu tiên dùng QR của ghế đầu tiên, nếu không dùng mã booking
        const qrString = booking.seats && booking.seats[0] ? booking.seats[0].qr : `FILMGO-${booking.booking_code}`;

        if (typeof QRCode !== 'undefined') {
            new QRCode(container, {
                text: qrString,
                width: 160,
                height: 160,
                colorDark: "#1e3a8a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            container.innerHTML = `<span class="text-xs text-red-500">Lỗi: Chưa tải được thư viện QRCode</span>`;
        }

        document.getElementById('qr-booking-code-label').textContent = `Mã booking: ${booking.booking_code}`;
        toast("Đã tạo mã QR vé điện tử thành công!", "success");
    }

    function resetPOS() {
        // Ẩn modal thành công
        document.getElementById('success-modal').classList.add('hidden');

        // Reset giỏ hàng và voucher
        state.selectedSeats = [];
        state.combos = {};
        state.voucher = null;
        state.currentBooking = null;

        // Reset hiển thị số lượng combo về 0
        document.querySelectorAll('[id^="combo-qty-"]').forEach(el => {
            el.textContent = '0';
        });

        // Reset các trường nhập liệu
        document.getElementById('voucher-input').value = '';
        const voucherInfo = document.getElementById('voucher-info');
        if (voucherInfo) voucherInfo.classList.add('hidden');
        document.getElementById('customer-phone').value = '';

        // Tải lại sơ đồ ghế để giải phóng/cập nhật trạng thái mới nhất
        if (state.currentShowtime) {
            loadSeatMap(state.currentShowtime.id);
        }

        updateCart();
        toast("Đã sẵn sàng đón khách tiếp theo!", "success");
    }

    // ── Format tiền ──────────────────────────────────────────────────────
    function debounce(fn, delay) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => fn(...args), delay);
        };
    }

    function formatMoney(n) {
        return new Intl.NumberFormat('vi-VN').format(n || 0) + 'đ';
    }

    // ── Init ──────────────────────────────────────────────────────────────
    function init() {
        loadMovies();
        document.getElementById('pos-date').addEventListener('change', loadMovies);
        document.getElementById('pos-search').addEventListener('input', debounce(loadMovies, 350));

        // Phím tắt F2: Tạo đơn mới nhanh khi đang hiển thị modal thành công
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                if (!document.getElementById('success-modal').classList.contains('hidden')) {
                    resetPOS();
                }
            }
        });
    }

    // Public API
    return {
        init, loadMovies, loadSeatMap, toggleSeat,
        changeCombo, applyVoucher, removeVoucher,
        openCheckout, closeCheckout, selectPayment, calcChange,
        confirmCheckout,
        openSuccessModal,
        handlePrintTicket, handleShowQR, resetPOS,
    };
})();


// ─── F&B MODE FUNCTIONS (outside IIFE, gán vào POS object qua switchMode etc.) ───
// Đã export trong Public API: switchMode, changeFnbCombo, changeFnbItem, checkoutFnb

const _FNB = (() => {
    'use strict';
    const fnbState = { combos:{}, comboInfo:{}, items:{}, itemInfo:{}, loaded:false };
    const csrf = () => document.querySelector('meta[name="csrf-token"]').content;

    async function apiFetch(url, opts={}) {
        const r = await fetch(url, { headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf(),'Accept':'application/json'}, ...opts });
        return r.json();
    }
    function fmt(n){ return new Intl.NumberFormat('vi-VN').format(n||0)+'đ'; }
    function toast(msg,type){ POS.init && document.dispatchEvent(new CustomEvent('pos-toast',{detail:{msg,type}})); }

    async function switchMode(mode) {
        const root      = document.getElementById('pos-root');
        const tabTicket = document.getElementById('tab-ticket');
        const tabFnb    = document.getElementById('tab-fnb');
        const movieList = document.getElementById('movie-list');
        const fnbPanel  = document.getElementById('fnb-panel');
        const dateInput = document.getElementById('pos-date');
        const btnCO     = document.getElementById('btn-checkout');
        const btnFnb    = document.getElementById('btn-checkout-fnb');

        if (mode === 'fnb') {
            root.classList.add('fnb-mode');
            tabTicket.classList.remove('active');
            tabFnb.classList.add('fnb-active');
            movieList.classList.add('hidden');
            fnbPanel.classList.remove('hidden');
            dateInput.style.display = 'none';
            if (btnCO)  btnCO.classList.add('hidden');
            if (btnFnb) btnFnb.classList.remove('hidden');
            if (!fnbState.loaded) await loadFnbProducts();
        } else {
            root.classList.remove('fnb-mode');
            tabFnb.classList.remove('fnb-active');
            tabTicket.classList.add('active');
            movieList.classList.remove('hidden');
            fnbPanel.classList.add('hidden');
            dateInput.style.display = '';
            if (btnCO)  btnCO.classList.remove('hidden');
            if (btnFnb) btnFnb.classList.add('hidden');
        }
    }

    async function loadFnbProducts() {
        const data    = await apiFetch('/staff/pos/api/combo-items');
        const groups  = data.data || [];

        // Cache item info
        groups.forEach(g => g.items.forEach(i => { fnbState.itemInfo[i.id] = i; }));

        // Cache combo info từ DOM data đã render (tránh request thêm)
        document.querySelectorAll('[data-combo-id]').forEach(el => {
            const id = el.dataset.comboId;
            fnbState.comboInfo[id] = { name: el.dataset.comboName, price: Number(el.dataset.comboPrice) };
        });

        // Render product list
        const listEl = document.getElementById('fnb-product-list');
        const loading= document.getElementById('fnb-loading');
        if (loading) loading.style.display = 'none';

        let html = '';

        // Combos (lấy từ data-combo-id trong DOM - đã render từ Blade)
        const comboEls = document.querySelectorAll('[data-combo-id]');
        if (comboEls.length) {
            html += `<div class="mb-4"><p class="text-[10px] font-bold uppercase tracking-widest text-primary mb-2">🎁 COMBO GÓI</p>`;
            comboEls.forEach(el => {
                const id    = el.dataset.comboId;
                const name  = el.dataset.comboName;
                const price = el.dataset.comboPrice;
                html += `<div class="flex items-center gap-2 mb-1.5 px-3 py-2 rounded-xl border border-gray-100 bg-white hover:border-primary/30 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-900 truncate">${name}</p>
                        <p class="text-[10px] font-black text-primary">${fmt(Number(price))}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button onclick="_FNB.changeFnbCombo(${id},-1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 font-bold text-sm flex items-center justify-center">−</button>
                        <span id="fnb-combo-qty-${id}" class="w-6 text-center text-xs font-black">0</span>
                        <button onclick="_FNB.changeFnbCombo(${id},1)" class="w-6 h-6 rounded bg-primary hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center">+</button>
                    </div></div>`;
            });
            html += '</div>';
        }

        // Đồ lẻ theo nhóm
        groups.forEach(g => {
            if (!g.items.length) return;
            html += `<div class="mb-3"><p class="text-[10px] font-bold uppercase tracking-widest text-orange-600 mb-2">🍿 ${g.type}</p>`;
            g.items.forEach(item => {
                html += `<div class="flex items-center gap-2 mb-1.5 px-3 py-2 rounded-xl border border-gray-100 bg-white hover:border-orange-300 transition-colors">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-gray-900 truncate">${item.name}</p>
                        <p class="text-[10px] font-black text-orange-600">${fmt(item.price)} <span class="text-gray-400 font-normal">/${item.unit}</span></p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button onclick="_FNB.changeFnbItem(${item.id},-1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 font-bold text-sm flex items-center justify-center">−</button>
                        <span id="fnb-item-qty-${item.id}" class="w-6 text-center text-xs font-black">0</span>
                        <button onclick="_FNB.changeFnbItem(${item.id},1)" class="w-6 h-6 rounded bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm flex items-center justify-center">+</button>
                    </div></div>`;
            });
            html += '</div>';
        });

        listEl.innerHTML = html || '<p class="text-xs text-gray-400 text-center py-6">Chưa có sản phẩm nào</p>';
        fnbState.loaded = true;
    }

    function changeFnbCombo(id, delta) {
        const qty = Math.max(0, (fnbState.combos[id] ?? 0) + delta);
        if (qty === 0) delete fnbState.combos[id]; else fnbState.combos[id] = qty;
        const el = document.getElementById(`fnb-combo-qty-${id}`);
        if (el) el.textContent = qty;
        updateFnbCart();
    }

    function changeFnbItem(id, delta) {
        const qty = Math.max(0, (fnbState.items[id] ?? 0) + delta);
        if (qty === 0) delete fnbState.items[id]; else fnbState.items[id] = qty;
        const el = document.getElementById(`fnb-item-qty-${id}`);
        if (el) el.textContent = qty;
        updateFnbCart();
    }

    function updateFnbCart() {
        let total = 0, count = 0, html = '';

        Object.entries(fnbState.combos).forEach(([id, qty]) => {
            const p = fnbState.comboInfo[id];
            if (!p || qty <= 0) return;
            const sub = p.price * qty; total += sub; count += qty;
            html += `<div class="flex justify-between items-center py-1.5 border-b border-gray-50 text-xs">
                <div><p class="font-semibold text-gray-800 truncate max-w-[140px]">🎁 ${p.name}</p><p class="text-gray-400">${qty}x</p></div>
                <span class="font-bold text-primary">${fmt(sub)}</span></div>`;
        });
        Object.entries(fnbState.items).forEach(([id, qty]) => {
            const p = fnbState.itemInfo[id];
            if (!p || qty <= 0) return;
            const sub = p.price * qty; total += sub; count += qty;
            html += `<div class="flex justify-between items-center py-1.5 border-b border-gray-50 text-xs">
                <div><p class="font-semibold text-gray-800 truncate max-w-[140px]">🍿 ${p.name}</p><p class="text-gray-400">${qty}x</p></div>
                <span class="font-bold text-orange-500">${fmt(sub)}</span></div>`;
        });

        const seatsEl = document.getElementById('cart-seats');
        seatsEl.innerHTML = html || '<p class="text-xs text-gray-400 text-center py-4">Chưa chọn sản phẩm nào</p>';
        document.getElementById('total-seat-count').textContent = count;
        document.getElementById('total-seat-price').textContent = '—';
        document.getElementById('total-combo-price').textContent = fmt(total);
        document.getElementById('grand-total').textContent = fmt(total);
        const btn = document.getElementById('btn-checkout-fnb');
        if (btn) btn.disabled = count === 0;
    }

    async function checkoutFnb() {
        const hasItems = Object.values(fnbState.combos).some(q=>q>0) || Object.values(fnbState.items).some(q=>q>0);
        if (!hasItems) { alert('Vui lòng chọn ít nhất một sản phẩm!'); return; }

        const pm     = document.querySelector('[id^="btn-cash"]')?.classList.contains('border-primary') ? 'cash' : 'transfer';
        const phone  = document.getElementById('customer-phone')?.value.trim();
        const voucher= document.getElementById('voucher-input')?.value.trim();
        const btn    = document.getElementById('btn-checkout-fnb');

        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined animate-spin" style="font-size:18px">progress_activity</span> Đang xử lý...';

        try {
            const res = await apiFetch('/staff/pos/api/checkout-fnb', {
                method:'POST',
                body: JSON.stringify({ combos: fnbState.combos, combo_items: fnbState.items, payment_method: pm, customer_phone: phone||null, voucher_code: voucher||null }),
            });
            if (!res.success) { alert('Lỗi: ' + res.message); return; }

            // Hiện modal success F&B
            openFnbSuccessModal(res.booking);

            // Reset
            fnbState.combos = {}; fnbState.items = {};
            document.querySelectorAll('[id^="fnb-combo-qty-"],[id^="fnb-item-qty-"]').forEach(el=>el.textContent='0');
            updateFnbCart();
        } catch(e) { alert('Lỗi kết nối: ' + e.message); }
        finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:18px">fastfood</span> BÁN F&B';
        }
    }

    function openFnbSuccessModal(booking) {
        const modal = document.getElementById('fnb-success-modal');
        if (!modal) return;
        document.getElementById('fnb-success-code').textContent = booking.booking_code;
        document.getElementById('fnb-success-total').textContent = fmt(booking.final_total);
        let rows = '';
        (booking.combos||[]).forEach(c => rows += `<li class="text-xs py-1 border-b border-gray-100">🎁 ${c.quantity}x ${c.name} — ${fmt(c.subtotal)}</li>`);
        (booking.combo_items||[]).forEach(i => rows += `<li class="text-xs py-1 border-b border-gray-100">🍿 ${i.quantity}x ${i.name} — ${fmt(i.subtotal)}</li>`);
        document.getElementById('fnb-success-items').innerHTML = rows;
        modal.classList.remove('hidden');
    }

    return { switchMode, changeFnbCombo, changeFnbItem, checkoutFnb, openFnbSuccessModal };
})();

// Bridge: expose F&B methods qua POS object (đã export trong IIFE)
// switchMode etc. đã được gọi trực tiếp qua _FNB.xxx trong HTML

document.addEventListener('DOMContentLoaded', POS.init);
</script>
@endpush
