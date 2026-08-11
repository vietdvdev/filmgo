@extends('layouts.staff')

@section('title', 'POS — Bán Vé Tại Quầy | FilmGo')

@push('styles')
<style>
    /* ── POS Layout: Full-height, no scroll ─── */
    #pos-root { height: calc(100vh - 64px); display: grid; grid-template-columns: 340px 1fr; }

    /* ── Responsive ─── */
    @media (max-width: 1024px) {
        #pos-root { grid-template-columns: 280px 1fr; }
    }
    @media (max-width: 768px) {
        #pos-root { display: flex; flex-direction: column; height: auto; min-height: calc(100vh - 64px); }
        .movie-col { max-height: 45vh; border-right: none; border-bottom: 1px solid #e5e7eb; }
        .seat-col { flex: 1; }
    }

    /* ── Tab Mode F&B ─── */
    #pos-root.fnb-mode { grid-template-columns: 1fr 400px; max-width: none; margin: 0; }
    @media (max-width: 1024px) {
        #pos-root.fnb-mode { grid-template-columns: 1fr 340px; }
    }
    @media (max-width: 768px) {
        #pos-root.fnb-mode { display: flex; flex-direction: column; height: auto; min-height: calc(100vh - 64px); }
    }
    #pos-root.fnb-mode #pos-col-seat { display: none; }
    #pos-root.fnb-mode .movie-col { border-right: none; height: calc(100vh - 64px); max-height: none; background: #fffaf5; }
    #fnb-cart-col { display: none; }
    #pos-root.fnb-mode #fnb-cart-col { display: flex; }

    .pos-tab-btn {
        flex: 1; padding: 8px; border-radius: 10px; font-size: 12px; font-weight: 700;
        cursor: pointer; transition: all 0.2s; border: none; outline: none;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .pos-tab-btn.active { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
    .pos-tab-btn.fnb-active { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; box-shadow: 0 4px 12px rgba(249,115,22,0.3); }
    .pos-tab-btn:not(.active):not(.fnb-active) { background: transparent; color: #64748b; }
    .pos-tab-btn:not(.active):not(.fnb-active):hover { background: #f1f5f9; color: #334155; }

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
    .seat-selected   { background: #3b82f6; border-color: #1d4ed8; color: #fff; transform: scale(1.08); box-shadow: 0 0 10px rgba(59,130,246,0.5); }
    .seat-maintenance{ background: #f3f4f6; border-color: #d1d5db; color: #9ca3af; cursor: not-allowed; }
    .seat-vip        { border-style: dashed; }

    .screen-bar { height: 6px; background: linear-gradient(90deg, transparent, #3b82f6, transparent); border-radius: 4px; box-shadow: 0 2px 10px rgba(59,130,246,0.3); }

    /* ── Bottom Action Bar (Sticky) ─── */
    .pos-bottom-bar {
        position: sticky; bottom: 0; left: 0; right: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border-top: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 -10px 30px rgba(0,0,0,0.08);
        padding: 16px 24px;
        display: flex; align-items: center; justify-content: space-between;
        z-index: 40;
        transform: translateY(100%);
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .pos-bottom-bar.show { transform: translateY(0); }

    /* ── Checkout Modal Layout ─── */
    #checkout-modal .modal-content { max-height: 90vh; display: flex; flex-direction: column; }
    #checkout-modal .modal-body { overflow-y: auto; flex: 1; }

    /* ── Print styles ─── */
    @media print {
        html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; max-width: 80mm !important; min-height: auto !important; }
        * { box-sizing: border-box !important; }
        #pos-root, #checkout-modal, #success-modal, #fnb-success-modal, #pos-toast, aside, header { display: none !important; }
        /* Control print visibility by JS */
        body.printing-ticket #print-ticket-area { display: block !important; }
        body.printing-fnb #print-fnb-area { display: block !important; }
        @page { size: 80mm auto; margin: 0; }
        #print-ticket-area, #print-fnb-area {
            width: 80mm; max-width: 80mm; margin: 0; padding: 1mm 1.5mm 0.5mm;
            font-family: 'Courier New', Courier, monospace; font-size: 10pt; line-height: 1.1; color: #000;
        }
        #print-ticket-area div, #print-ticket-area table, #print-ticket-area td, #print-ticket-area th,
        #print-fnb-area div, #print-fnb-area table, #print-fnb-area td, #print-fnb-area th { margin: 0 !important; padding: 0 !important; }
    }
    #print-ticket-area, #print-fnb-area { display: none; }
</style>
@endpush

@section('content')
<div id="pos-root" class="bg-gray-50">

    {{-- ════════════════════════════════════════════════════════════
         CỘT TRÁI — Chọn Phim & Suất chiếu
    ════════════════════════════════════════════════════════════ --}}
    <div class="movie-col bg-white border-r border-gray-200 flex flex-col overflow-hidden shadow-sm z-10">

        {{-- Header + Date picker --}}
        <div class="px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-black text-gray-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">movie</span>
                    Phim & Suất Chiếu
                </h2>
                <span id="movie-count" class="text-xs font-bold px-2.5 py-1 bg-primary/10 text-primary rounded-full">—</span>
            </div>

            <div class="mb-4 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                <input id="pos-search" type="search" placeholder="Tìm phim, phòng, giờ..."
                       class="w-full text-sm bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all"
                       autocomplete="off">
            </div>

            {{-- Tab Switcher: Bán Vé / Bán F&B --}}
            <div class="flex gap-1 p-1.5 rounded-2xl mb-3 bg-gray-100 border border-gray-200 shadow-inner">
                <button class="pos-tab-btn active" id="tab-ticket" onclick="_FNB.switchMode('ticket')">
                    <span class="material-symbols-outlined text-[18px]">confirmation_number</span>
                    Bán Vé
                </button>
                <button class="pos-tab-btn" id="tab-fnb" onclick="_FNB.switchMode('fnb')">
                    <span class="material-symbols-outlined text-[18px]">fastfood</span>
                    Bán F&B
                </button>
            </div>

            <input type="date" id="pos-date" value="{{ today()->toDateString() }}"
                   class="w-full text-sm font-bold text-gray-700 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 focus:bg-white focus:ring-2 focus:ring-primary/30 outline-none transition-all">
        </div>

        {{-- Movie + Showtime list --}}
        <div id="movie-list" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50/50">
            <div class="text-center py-10 text-gray-400">
                <span class="material-symbols-outlined text-5xl mb-3 block animate-pulse">hourglass_empty</span>
                <p class="text-sm font-medium">Đang tải danh sách phim...</p>
            </div>
        </div>

        {{-- F&B Panel: sản phẩm đồ ăn (hiện khi mode fnb) --}}
        <div id="fnb-panel" class="hidden flex-1 overflow-y-auto p-4 bg-orange-50/30">
            <div class="text-center py-10 text-orange-300" id="fnb-loading">
                <span class="material-symbols-outlined text-5xl block mb-3 animate-spin">progress_activity</span>
                <p class="text-sm font-bold">Đang tải danh sách...</p>
            </div>
            <div id="fnb-product-list"></div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CỘT GIỮA — Sơ đồ ghế real-time
    ════════════════════════════════════════════════════════════ --}}
    <div class="seat-col flex flex-col overflow-hidden relative" id="pos-col-seat">

        {{-- Header suất chiếu đang chọn --}}
        <div id="seat-header" class="px-6 py-4 bg-white/80 backdrop-blur-md border-b border-gray-200 flex-shrink-0 z-10 sticky top-0 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] text-gray-500 uppercase tracking-widest font-black mb-1">Sơ đồ ghế POS</p>
                    <h2 id="seat-showtime-title" class="text-lg font-black text-gray-900">
                        Vui lòng chọn suất chiếu
                    </h2>
                </div>
            </div>
        </div>

        {{-- Seat map container --}}
        <div id="seat-map-container" class="flex-1 overflow-auto p-6 flex flex-col items-center bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:20px_20px]">
            <div class="text-center py-20 text-gray-300">
                <span class="material-symbols-outlined text-7xl mb-4 block opacity-50">event_seat</span>
                <p class="text-base font-bold text-gray-400">Chưa chọn suất chiếu</p>
            </div>
        </div>

        {{-- Sticky Bottom Bar (Mới) --}}
        <div id="pos-bottom-bar" class="pos-bottom-bar">
            <div>
                <p class="text-sm font-semibold text-gray-600 mb-0.5">Đã chọn: <span id="bb-seat-count" class="font-black text-gray-900">0</span> ghế</p>
                <div class="flex items-center gap-2">
                    <p class="text-xs text-gray-500" id="bb-seat-list">...</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-0.5">Tạm tính</p>
                    <p id="bb-total-price" class="text-2xl font-black text-primary leading-none">0đ</p>
                </div>
                <button onclick="POS.openCheckout()" id="btn-checkout" disabled
                        class="px-8 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-black text-sm rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:shadow-none">
                    TIẾP TỤC
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════
         CỘT PHẢI — Giỏ hàng F&B Độc Lập
    ════════════════════════════════════════════════════════════ --}}
    <div id="fnb-cart-col" class="hidden flex-col bg-white border-l border-gray-200 shadow-xl z-20 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex-shrink-0">
            <h3 class="text-sm font-black text-orange-600 uppercase tracking-widest flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">shopping_basket</span>
                Giỏ Hàng F&B
            </h3>
        </div>
        
        {{-- Danh sách món --}}
        <div id="fnb-only-cart-items" class="flex-1 overflow-y-auto p-5 space-y-2 bg-gray-50/50">
            <p class="text-xs text-gray-400 text-center py-4">Chưa chọn sản phẩm nào</p>
        </div>

        {{-- Thanh toán --}}
        <div class="p-5 border-t border-gray-200 bg-white flex-shrink-0 shadow-[0_-4px_20px_rgba(0,0,0,0.03)] space-y-4">
            <div class="space-y-3">
                <input id="fnb-customer-phone" type="tel" placeholder="SĐT Khách hàng..."
                       class="w-full text-sm font-semibold border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition-all">
                <div class="flex gap-2">
                    <input id="fnb-voucher-input" type="text" placeholder="Mã giảm giá..."
                           class="flex-1 text-sm font-bold border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none uppercase">
                    <button class="px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white text-xs font-black rounded-xl transition-all">ÁP DỤNG</button>
                </div>
            </div>

            {{-- Chọn thanh toán --}}
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button id="btn-fnb-cash" onclick="_FNB.selectPayment('cash')"
                        class="flex flex-col items-center gap-1 p-2 border-2 border-orange-500 bg-orange-50 rounded-xl transition-all shadow-sm">
                    <span class="material-symbols-outlined text-orange-600">payments</span>
                    <span class="text-xs font-black text-orange-700">Tiền Mặt</span>
                </button>
                <button id="btn-fnb-transfer" onclick="_FNB.selectPayment('transfer')"
                        class="flex flex-col items-center gap-1 p-2 border-2 border-gray-200 bg-white rounded-xl hover:border-orange-500/40 transition-all text-gray-500">
                    <span class="material-symbols-outlined">qr_code_2</span>
                    <span class="text-xs font-black">Chuyển Khoản</span>
                </button>
            </div>
            
            <div id="fnb-qr-panel" class="hidden animate-fade-in bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center mt-3">
                <div class="bg-white p-2 rounded-xl inline-block shadow-sm mb-2">
                    <img id="fnb-qr-img" src="" alt="QR" class="w-32 h-32 object-contain">
                </div>
                <p class="text-xs text-blue-800 font-medium">Khách quét để thanh toán.</p>
            </div>
            <div class="flex justify-between items-end pt-3 border-t border-gray-100">
                <span class="text-sm font-black text-gray-900 uppercase">Tổng Cộng</span>
                <span id="fnb-only-total" class="text-2xl font-black text-orange-600 leading-none">0đ</span>
            </div>

            <button id="btn-checkout-fnb" onclick="_FNB.checkoutFnb()" disabled
                    class="w-full py-4 mt-2 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 active:scale-[0.98] text-white font-black text-sm rounded-xl transition-all flex items-center justify-center gap-2 shadow-xl shadow-orange-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="material-symbols-outlined text-xl">point_of_sale</span>
                XÁC NHẬN & THANH TOÁN
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     MODAL THANH TOÁN (MỞ RỘNG GỘP F&B, VOUCHER)
════════════════════════════════════════════════════════════ --}}
<div id="checkout-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm no-print">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden modal-content flex flex-col scale-100 transition-transform">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-gray-900 to-slate-800 px-6 py-4 flex items-center justify-between flex-shrink-0 shadow-md z-10">
            <div>
                <h2 class="text-white font-black text-xl flex items-center gap-2">
                    <span class="material-symbols-outlined">receipt_long</span>
                    Thanh Toán & Xuất Vé
                </h2>
                <p id="modal-showtime-info" class="text-gray-300 text-sm mt-0.5 font-medium"></p>
            </div>
            <button onclick="POS.closeCheckout()" class="text-gray-400 hover:text-white text-3xl leading-none transition-colors">&times;</button>
        </div>

        {{-- Body: 2 cột (Trái: F&B + Info, Phải: Tổng kết & Thanh toán) --}}
        <div class="flex-1 flex flex-col md:flex-row overflow-hidden bg-gray-50">
            
            {{-- Cột Trái: Bắp nước & Voucher --}}
            <div class="flex-1 overflow-y-auto border-r border-gray-200 bg-white flex flex-col">
                {{-- Bắp nước --}}
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-sm font-black text-orange-600 uppercase tracking-widest flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-lg">fastfood</span>
                        Bắp Nước (F&B)
                    </h3>
                    <div id="combo-list" class="space-y-3">
                        @forelse($combos as $combo)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-orange-200 hover:bg-orange-50/50 transition-colors" 
                             data-combo-id="{{ $combo->id }}" data-combo-name="{{ $combo->combo_name }}" data-combo-price="{{ $combo->price }}">
                            <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center text-orange-500">
                                <span class="material-symbols-outlined">kebab_dining</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900">{{ $combo->combo_name }}</p>
                                <p class="text-xs font-black text-orange-600 mt-0.5">{{ number_format($combo->price) }}đ</p>
                            </div>
                            <div class="flex items-center gap-1 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                <button onclick="POS.changeCombo({{ $combo->id }}, -1)" class="w-8 h-8 rounded-md hover:bg-white hover:shadow-sm text-gray-600 font-bold flex items-center justify-center transition-all">−</button>
                                <span id="combo-qty-{{ $combo->id }}" class="w-8 text-center text-sm font-black text-gray-900">0</span>
                                <button onclick="POS.changeCombo({{ $combo->id }}, 1)" class="w-8 h-8 rounded-md bg-orange-500 text-white shadow-sm shadow-orange-500/30 hover:bg-orange-600 font-bold flex items-center justify-center transition-all">+</button>
                            </div>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400 text-center py-4 bg-gray-50 rounded-xl">Chưa có combo nào</p>
                        @endforelse
                    </div>
                </div>

                {{-- Khách hàng & Voucher --}}
                <div class="p-6 bg-slate-50 flex-1">
                    <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-lg">person</span>
                        Thông tin thêm
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-gray-500 block mb-1.5">SĐT Khách hàng (tích điểm)</label>
                            <input id="customer-phone" type="tel" placeholder="Ví dụ: 0901234567..."
                                   class="w-full text-sm font-semibold border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-500 block mb-1.5">Mã giảm giá (Voucher)</label>
                            <div class="flex gap-2">
                                <input id="voucher-input" type="text" placeholder="Nhập mã..."
                                       class="flex-1 text-sm font-bold border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 outline-none uppercase"
                                       oninput="this.value = this.value.toUpperCase()">
                                <button onclick="POS.applyVoucher()"
                                        class="px-5 py-3 bg-gray-900 hover:bg-gray-800 text-white text-sm font-black rounded-xl transition-all shadow-md">
                                    ÁP DỤNG
                                </button>
                            </div>
                            <div id="voucher-info" class="hidden mt-2 px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center justify-between">
                                <span id="voucher-label" class="text-sm font-bold text-green-700"></span>
                                <button onclick="POS.removeVoucher()" class="text-red-500 hover:text-red-700 text-xs font-black bg-red-50 px-2 py-1 rounded">✕ XÓA</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột Phải: Tổng kết & Thanh toán --}}
            <div class="w-full md:w-[380px] bg-white flex flex-col flex-shrink-0">
                <div class="p-6 border-b border-gray-100 flex-1 overflow-y-auto">
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Tổng Kết Đơn Hàng</h3>
                    
                    {{-- Bill summary --}}
                    <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 space-y-3 mb-6 shadow-inner">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-gray-500">Tiền ghế (<span id="modal-seat-count">0</span>)</p>
                                <p id="modal-seats" class="text-[10px] text-gray-400 mt-1 max-w-[150px] leading-tight"></p>
                            </div>
                            <span id="modal-seat-price" class="text-sm font-black text-gray-900">0đ</span>
                        </div>
                        <div class="flex justify-between items-start pt-3 border-t border-gray-200 border-dashed">
                            <div>
                                <p class="text-xs font-bold text-gray-500">Combo F&B</p>
                                <p id="modal-combos" class="text-[10px] text-gray-400 mt-1 max-w-[150px] leading-tight"></p>
                            </div>
                            <span id="modal-combo-price" class="text-sm font-black text-gray-900">0đ</span>
                        </div>
                        <div id="modal-discount-row" class="hidden flex justify-between items-center pt-3 border-t border-gray-200 border-dashed">
                            <span class="text-xs font-bold text-green-600">Giảm giá</span>
                            <span id="modal-discount" class="text-sm font-black text-green-600">0đ</span>
                        </div>
                        <div class="flex justify-between items-end pt-4 border-t border-gray-300 mt-2">
                            <span class="text-sm font-black text-gray-900 uppercase">Tổng Cần Thu</span>
                            <span id="modal-total" class="text-3xl font-black text-primary leading-none">0đ</span>
                        </div>
                    </div>

                    {{-- Payment methods --}}
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Phương Thức Thanh Toán</h3>
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <button id="btn-cash" onclick="POS.selectPayment('cash')"
                                class="flex flex-col items-center gap-2 p-3 border-2 border-primary bg-primary/5 rounded-xl transition-all shadow-sm">
                            <span class="material-symbols-outlined text-primary text-3xl">payments</span>
                            <span class="text-xs font-black text-primary">Tiền Mặt</span>
                        </button>
                        <button id="btn-transfer" onclick="POS.selectPayment('transfer')"
                                class="flex flex-col items-center gap-2 p-3 border-2 border-gray-200 bg-white rounded-xl hover:border-primary/40 transition-all text-gray-500 hover:text-primary">
                            <span class="material-symbols-outlined text-3xl">qr_code_2</span>
                            <span class="text-xs font-black">Chuyển Khoản</span>
                        </button>
                    </div>

                    {{-- Cash panel --}}
                    <div id="cash-panel" class="space-y-3 animate-fade-in">
                        <div>
                            <label class="text-xs font-bold text-gray-500 block mb-1">Khách đưa</label>
                            <input id="cash-given" type="number" placeholder="Nhập số tiền..."
                                   oninput="POS.calcChange()"
                                   class="w-full border-2 border-gray-200 bg-gray-50 rounded-xl px-4 py-3 text-lg font-black text-gray-900 focus:bg-white focus:border-primary outline-none transition-colors">
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex justify-between items-center shadow-inner">
                            <span class="text-xs font-bold text-emerald-800 uppercase">Thối lại khách</span>
                            <span id="change-amount" class="text-xl font-black text-emerald-700">0đ</span>
                        </div>
                    </div>

                    {{-- Transfer panel --}}
                    <div id="transfer-panel" class="hidden animate-fade-in">
                        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center shadow-inner">
                            <div class="bg-white p-2 rounded-xl inline-block shadow-sm mb-3">
                                <img id="qr-img" src="" alt="QR" class="w-32 h-32 object-contain">
                            </div>
                            <p class="text-xs text-blue-800 font-medium">Quét mã QR để thanh toán qua ứng dụng ngân hàng hoặc ví điện tử.</p>
                        </div>
                    </div>
                </div>

                {{-- Confirm Btn --}}
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <button id="btn-confirm-payment" onclick="POS.confirmCheckout()"
                            class="w-full py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 active:scale-[0.98] text-white font-black text-base rounded-xl transition-all flex items-center justify-center gap-2 shadow-xl shadow-green-500/30">
                        <span class="material-symbols-outlined text-2xl">check_circle</span>
                        XÁC NHẬN & XUẤT VÉ
                    </button>
                </div>
            </div>
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
     PHẦN 2B: KHU VỰC IN BILL F&B 80mm
════════════════════════════════════════════════════════════ --}}
<div id="print-fnb-area" class="print-fnb-area-wrapper">
    {{-- Header rạp --}}
    <div style="text-align:center; border-bottom:2px solid #000; padding-bottom:6px; margin-bottom:8px;">
        <div style="font-size:20pt; font-weight:900; letter-spacing:2px;">★ FilmGo ★</div>
        <div id="pf-cinema" style="font-size:10pt; margin-top:2px;">{{ Auth::user()->cinemas()->first()?->name }}</div>
        <div style="font-size:9pt; color:#444;">Đơn hàng F&B</div>
    </div>

    {{-- Thông tin vé --}}
    <div style="margin-bottom:8px;">
        <div style="font-size:9pt; text-align:center; letter-spacing:1px; color:#555; margin-bottom:4px;">── HOÁ ĐƠN BẮP NƯỚC ──</div>
        <table style="width:100%; font-size:10pt; border-collapse:collapse;">
            <tr><td style="width:35%; color:#555;">Mã ĐH:</td>   <td id="pf-code"  style="font-weight:900;"></td></tr>
            <tr><td style="color:#555;">Ngày:</td>               <td id="pf-date"></td></tr>
        </table>
    </div>

    {{-- Items --}}
    <div style="border-top:1px dashed #000; border-bottom:1px dashed #000; padding:6px 0; margin-bottom:8px;">
        <table style="width:100%; font-size:10pt; border-collapse:collapse;" id="pf-items">
            <!-- Items rendered here -->
        </table>
    </div>

    {{-- Totals --}}
    <table style="width:100%; font-size:10pt; font-weight:bold;">
        <tr><td style="color:#444;">Giảm giá:</td> <td style="text-align:right; color:#d9534f;" id="pf-discount">0đ</td></tr>
        <tr style="font-size:12pt;"><td style="padding-top:4px;">TỔNG TIỀN:</td> <td style="text-align:right; padding-top:4px;" id="pf-total">0đ</td></tr>
        <tr><td style="color:#444; font-size:9pt;">Thanh toán:</td> <td style="text-align:right; font-size:9pt;" id="pf-payment"></td></tr>
    </table>

    {{-- Footer --}}
    <div style="text-align:center; border-top:2px solid #000; margin-top:10px; padding-top:6px; font-size:9pt; color:#555;">
        <p style="margin:2px 0;">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
        <p style="margin:4px 0; font-size:8pt;">★ Chúc quý khách ngon miệng ★</p>
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


{{-- ════════════════════════════════════════════════════════════
     MODAL THÀNH CÔNG F&B
════════════════════════════════════════════════════════════ --}}
<div id="fnb-success-modal"
     class="hidden fixed inset-0 z-[60] flex items-center justify-center no-print"
     role="dialog" aria-modal="true">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all duration-300 scale-100">
        <div class="bg-gradient-to-br from-orange-500 to-red-500 px-6 pt-8 pb-6 text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 ring-4 ring-white/30">
                <span class="material-symbols-outlined text-white" style="font-size:40px;">check_circle</span>
            </div>
            <h2 class="text-xl font-black text-white tracking-wide">Thanh Toán F&B Thành Công!</h2>
            <div class="mt-3 inline-flex items-center gap-2 bg-white/20 border border-white/30 rounded-full px-4 py-1.5">
                <span class="text-white font-mono font-black text-base tracking-widest" id="fnb-success-code">—</span>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 text-sm">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs text-gray-500 font-semibold">Tổng tiền</span>
                <span id="fnb-success-total" class="font-black text-orange-600 text-lg">0đ</span>
            </div>
            <ul id="fnb-success-items" class="space-y-1 max-h-32 overflow-y-auto"></ul>
        </div>

        <div class="px-6 py-4 space-y-3">
            <button id="btn-print-fnb" onclick="_FNB.handlePrintFnb()"
                    class="w-full flex items-center justify-center gap-2 py-3 border-2 border-orange-500 text-orange-600 bg-orange-50 hover:bg-orange-100 font-bold text-sm rounded-xl transition-all">
                <span class="material-symbols-outlined">print</span> In Hoá Đơn F&B
            </button>
            <button onclick="_FNB.resetFnb()"
                    class="w-full flex items-center justify-center gap-2 py-3 bg-gray-900 hover:bg-gray-700 text-white font-black text-sm rounded-xl transition-all">
                <span class="material-symbols-outlined">add_circle</span> Hoàn Tất & Đơn Mới
            </button>
        </div>
    </div>
</div>

{{-- Toast notification --}}
<div id="pos-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[999] hidden no-print">
    <div class="px-5 py-3 rounded-xl text-sm font-semibold text-white shadow-xl flex items-center gap-2" id="pos-toast-inner"></div>
</div>

{{-- Floating Tooltip cho Nhân viên POS khi hover ghế --}}
<div id="seat-tooltip" class="fixed pointer-events-none z-[9999] hidden transform -translate-x-1/2 -translate-y-full mb-3 transition-all duration-75">
    <div class="bg-slate-900 text-white text-xs rounded-xl px-3.5 py-2.5 shadow-2xl border border-slate-700 min-w-[150px]">
        <div class="font-black text-sm text-yellow-400 border-b border-slate-700 pb-1 mb-1.5 flex items-center justify-between">
            <span id="tooltip-seat-num">Ghế: --</span>
            <span id="tooltip-seat-type-tag" class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold uppercase">--</span>
        </div>
        <div class="space-y-1 text-[11px] text-slate-300">
            <p class="flex justify-between"><span>Loại:</span> <strong id="tooltip-seat-type" class="text-white">--</strong></p>
            <p class="flex justify-between"><span>Giá:</span> <strong id="tooltip-seat-price" class="text-emerald-400 font-bold">--</strong></p>
            <p class="flex justify-between"><span>Trạng thái:</span> <strong id="tooltip-seat-status" class="text-white font-bold">--</strong></p>
        </div>
    </div>
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
            <div class="w-full max-w-3xl flex flex-col items-center">
                <!-- Màn chiếu -->
                <div class="mb-8 px-8 w-full max-w-xl">
                    <div class="screen-bar mb-1"></div>
                    <p class="text-center text-[10px] text-gray-400 font-semibold tracking-widest uppercase">MÀN CHIẾU PHIM</p>
                </div>

                <!-- Sơ đồ ghế -->
                <div class="space-y-3 pb-2 flex flex-col items-center">
                    ${rows.map(row => {
                        const rowSeatsList = seatsByRow[row] || [];
                        return `
                            <div class="flex items-center gap-2">
                                <span class="w-6 text-center text-xs font-black text-gray-500 flex-shrink-0 uppercase">${row}</span>
                                <div class="flex flex-wrap items-center gap-1.5 justify-center">
                                    ${renderRowSeats(rowSeatsList)}
                                </div>
                                <span class="w-6 text-center text-xs font-black text-gray-500 flex-shrink-0 uppercase">${row}</span>
                            </div>
                        `;
                    }).join('')}
                </div>

                <!-- KHUNG CHÚ THÍCH (THE LEGEND BAR) -->
                <div id="pos-seat-legend" class="mt-6 pt-4 border-t border-gray-200 w-full">
                    <div class="flex flex-wrap items-center justify-center gap-5 text-xs text-gray-700 font-semibold bg-gray-50/90 p-3 rounded-xl border border-gray-200 shadow-sm">
                        <!-- Group 1: Seat Types (Loại ghế) -->
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Loại ghế:</span>
                            <div class="flex items-center gap-1.5" title="Ghế Thường">
                                <span class="w-4 h-4 rounded border-2 border-emerald-500 bg-gray-100 inline-block shadow-sm"></span>
                                <span>Ghế Thường</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Ghế VIP">
                                <span class="w-4 h-4 rounded border-2 border-purple-600 bg-purple-50 inline-block shadow-sm"></span>
                                <span>Ghế VIP</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Ghế Đôi / Sweetbox">
                                <span class="w-6 h-4 rounded border-2 border-pink-500 bg-pink-50 inline-block shadow-sm"></span>
                                <span>Ghế Đôi</span>
                            </div>
                        </div>

                        <!-- Vertical Divider -->
                        <div class="h-5 w-px bg-gray-300"></div>

                        <!-- Group 2: Statuses (Trạng thái ghế) -->
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Trạng thái:</span>
                            <div class="flex items-center gap-1.5" title="Trống (Có thể chọn)">
                                <span class="w-4 h-4 rounded border-2 border-emerald-500 bg-emerald-100 inline-block shadow-sm"></span>
                                <span>Trống</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Đang chọn">
                                <span class="w-4 h-4 rounded bg-blue-600 ring-2 ring-blue-300 inline-block shadow-sm"></span>
                                <span>Đang chọn</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Đang giữ 10 phút">
                                <span class="w-4 h-4 rounded border-2 border-amber-500 bg-amber-400 inline-block shadow-sm"></span>
                                <span>Đang giữ</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Đã bán">
                                <span class="w-4 h-4 rounded border-2 border-red-600 bg-red-500 opacity-60 inline-block shadow-sm"></span>
                                <span>Đã bán</span>
                            </div>
                            <div class="flex items-center gap-1.5" title="Đang bảo trì">
                                <span class="w-4 h-4 rounded border-2 border-gray-500 bg-gray-400 inline-block shadow-sm"></span>
                                <span>Bảo trì</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thống kê tổng số lượng ghế -->
                <div class="mt-3 text-center text-xs text-gray-500">
                    Tổng: <b>${seats.length}</b> |
                    Trống: <b class="text-emerald-600">${seats.filter(s => s.status === 'available' && s.seat_status !== 'maintenance').length}</b> |
                    Đang chọn: <b class="text-blue-600">${state.selectedSeats.length}</b> |
                    Đã bán: <b class="text-red-500">${seats.filter(s => s.status === 'booked').length}</b>
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

    // ── Render danh sách ghế của 1 hàng (ghép cặp Sweetbox nếu có) ───────────
    function renderRowSeats(seatList) {
        let html = '';
        let i = 0;
        while (i < seatList.length) {
            const s1 = seatList[i];
            const typeName1 = (s1.type || '').toLowerCase();
            const isCouple1 = typeName1.includes('đôi') || typeName1.includes('sweetbox') || typeName1.includes('couple');

            // Nếu là ghế Đôi/Sweetbox và có ghế tiếp theo thuộc cùng loại đôi
            if (isCouple1 && (i + 1) < seatList.length) {
                const s2 = seatList[i + 1];
                const typeName2 = (s2.type || '').toLowerCase();
                const isCouple2 = typeName2.includes('đôi') || typeName2.includes('sweetbox') || typeName2.includes('couple');

                if (isCouple2) {
                    // Render thành 1 Sofa Đôi liền khối (Sofa Container)
                    html += `
                        <div class="border-2 border-pink-400 bg-pink-50/70 p-1 flex gap-1 rounded-xl items-center shadow-sm" title="Sofa Sweetbox Đôi">
                            ${renderSeatBtn(s1)}
                            ${renderSeatBtn(s2)}
                        </div>
                    `;
                    i += 2;
                    continue;
                }
            }

            html += renderSeatBtn(s1);
            i++;
        }
        return html;
    }

    // ── Render một ghế đơn lẻ với hệ thống màu Tailwind CSS chuẩn ───────────
    function renderSeatBtn(s) {
        const isSelected = state.selectedSeats.some(sel => sel.showtime_seat_id === s.showtime_seat_id);
        const isMaintenance = s.seat_status === 'maintenance' || s.status === 'maintenance';
        const isBooked = s.status === 'booked';
        const isHolding = s.status === 'holding';
        const isUnavailable = isBooked || isHolding || isMaintenance;

        const typeName = (s.type || '').toLowerCase();
        const isVip = typeName.includes('vip');
        const isCouple = typeName.includes('đôi') || typeName.includes('sweetbox') || typeName.includes('couple');

        /**
         * TAILWIND COLOR SYSTEM STRICT COMPLIANCE:
         * 1. Status Overrides Type:
         *    - Selected    : bg-blue-600 text-white ring-4 ring-blue-300 scale-105 cursor-pointer
         *    - Maintenance : bg-gray-400 text-gray-600 border-2 border-gray-500 cursor-not-allowed
         *    - Booked      : bg-red-500 text-white border-2 border-red-600 opacity-60 cursor-not-allowed
         *    - Holding     : bg-amber-400 text-white border-2 border-amber-500 cursor-not-allowed
         * 2. Default/Available by Type:
         *    - Couple      : bg-pink-50 text-pink-700 border-2 border-pink-500 cursor-pointer hover:bg-pink-100
         *    - VIP         : bg-purple-50 text-purple-700 border-2 border-purple-600 cursor-pointer hover:bg-purple-100
         *    - Standard    : bg-gray-100 text-gray-700 border-2 border-emerald-500 cursor-pointer hover:bg-emerald-100
         */
        let classes = "w-9 h-9 text-xs font-bold rounded-lg border-2 flex items-center justify-center transition-all duration-150 relative shadow-sm ";

        if (isSelected) {
            classes += "bg-blue-600 text-white ring-4 ring-blue-300 scale-105 cursor-pointer z-10";
        } else if (isMaintenance) {
            classes += "bg-gray-400 text-gray-600 border-gray-500 cursor-not-allowed";
        } else if (isBooked) {
            classes += "bg-red-500 text-white border-red-600 opacity-60 cursor-not-allowed";
        } else if (isHolding) {
            classes += "bg-amber-400 text-white border-amber-500 cursor-not-allowed";
        } else {
            if (isCouple) {
                classes += "bg-pink-50 text-pink-700 border-pink-500 cursor-pointer hover:bg-pink-100";
            } else if (isVip) {
                classes += "bg-purple-50 text-purple-700 border-purple-600 cursor-pointer hover:bg-purple-100";
            } else {
                classes += "bg-gray-100 text-gray-700 border-emerald-500 cursor-pointer hover:bg-emerald-100";
            }
        }

        const clickFn = isUnavailable && !isSelected ? '' : `onclick="POS.toggleSeat(${s.showtime_seat_id})"`;

        // Inner icon / text content
        let innerContent = s.label;
        if (isMaintenance) {
            innerContent = '<span class="material-symbols-outlined text-xs">build</span>';
        } else if (isBooked) {
            innerContent = '<span class="material-symbols-outlined text-xs">close</span>';
        } else if (isHolding) {
            innerContent = '<span class="material-symbols-outlined text-xs">lock</span>';
        }

        const statusText = isSelected ? 'Đang chọn' : (isMaintenance ? 'Bảo trì' : (isBooked ? 'Đã bán' : (isHolding ? 'Đang giữ (10 ph)' : 'Trống (Có thể chọn)')));

        return `
            <button id="seat-${s.showtime_seat_id}"
                    class="${classes}"
                    data-seat-id="${s.showtime_seat_id}"
                    data-seat-label="${s.label}"
                    data-seat-type="${s.type || 'Ghế Thường'}"
                    data-seat-price="${formatMoney(s.price)}"
                    data-seat-status="${statusText}"
                    onmouseenter="POS.showTooltip(event, this)"
                    onmousemove="POS.moveTooltip(event)"
                    onmouseleave="POS.hideTooltip()"
                    ${clickFn}>
                ${innerContent}
            </button>
        `;
    }

    // ── Tooltip Event Handlers (Floating dynamic tooltip for staff) ────────
    function showTooltip(e, el) {
        const tooltip = document.getElementById('seat-tooltip');
        if (!tooltip) return;

        document.getElementById('tooltip-seat-num').textContent = `Ghế: ${el.dataset.seatLabel}`;
        document.getElementById('tooltip-seat-type-tag').textContent = el.dataset.seatType;
        document.getElementById('tooltip-seat-type').textContent = el.dataset.seatType;
        document.getElementById('tooltip-seat-price').textContent = el.dataset.seatPrice;
        document.getElementById('tooltip-seat-status').textContent = el.dataset.seatStatus;

        tooltip.classList.remove('hidden');
        moveTooltip(e);
    }

    function moveTooltip(e) {
        const tooltip = document.getElementById('seat-tooltip');
        if (!tooltip || tooltip.classList.contains('hidden')) return;

        tooltip.style.left = `${e.clientX}px`;
        tooltip.style.top = `${e.clientY - 12}px`;
    }

    function hideTooltip() {
        const tooltip = document.getElementById('seat-tooltip');
        if (tooltip) tooltip.classList.add('hidden');
    }

    // ── Helper Cập nhật UI Nút Ghế ──────────────────────────────────────────
    function updateSeatButtonUI(showtimeSeatId) {
        const s = state.seatData[showtimeSeatId];
        if (!s) return;
        const btn = document.getElementById(`seat-${showtimeSeatId}`);
        if (!btn || !state.currentShowtime) return;

        const isNowSelected = state.selectedSeats.some(sel => sel.showtime_seat_id === showtimeSeatId);
        const statusText = isNowSelected ? 'Đang chọn' : 'Trống (Có thể chọn)';
        btn.dataset.seatStatus = statusText;

        const typeName = (s.type || '').toLowerCase();
        const isVip = typeName.includes('vip');
        const isCouple = typeName.includes('đôi') || typeName.includes('sweetbox') || typeName.includes('couple');

        let baseClasses = "w-9 h-9 text-xs font-bold rounded-lg border-2 flex items-center justify-center transition-all duration-150 relative shadow-sm ";
        if (isNowSelected) {
            baseClasses += "bg-blue-600 text-white ring-4 ring-blue-300 scale-105 cursor-pointer z-10";
        } else {
            if (isCouple) {
                baseClasses += "bg-pink-50 text-pink-700 border-pink-500 cursor-pointer hover:bg-pink-100";
            } else if (isVip) {
                baseClasses += "bg-purple-50 text-purple-700 border-purple-600 cursor-pointer hover:bg-purple-100";
            } else {
                baseClasses += "bg-gray-100 text-gray-700 border-emerald-500 cursor-pointer hover:bg-emerald-100";
            }
        }
        btn.className = baseClasses;
    }

    // ── Toggle chọn ghế (Ấn 1 chọn 2 đối với ghế đôi Sweetbox) ─────────────
    function toggleSeat(showtimeSeatId) {
        const s = state.seatData[showtimeSeatId];
        if (!s) return;

        const typeName = (s.type || '').toLowerCase();
        const isCouple = typeName.includes('đôi') || typeName.includes('sweetbox') || typeName.includes('couple');

        if (isCouple) {
            // Ghế Sweetbox / Đôi: Tự động ghép cặp (ấn 1 chọn 2)
            const siblingNum = (s.number % 2 === 1) ? s.number + 1 : s.number - 1;
            const partnerSeat = Object.values(state.seatData).find(st =>
                st.row === s.row &&
                st.number === siblingNum &&
                ((st.type || '').toLowerCase().match(/sweetbox|couple|đôi|doi/))
            );

            const isCurrentlySelected = state.selectedSeats.some(sel => sel.showtime_seat_id === showtimeSeatId);

            if (partnerSeat) {
                const partnerUnavailable = partnerSeat.status === 'booked' ||
                                           partnerSeat.status === 'holding' ||
                                           partnerSeat.seat_status === 'maintenance' ||
                                           partnerSeat.status === 'maintenance';

                if (partnerUnavailable && !isCurrentlySelected) {
                    toast(`Ghế đôi Sweetbox ${s.row}${s.number}-${s.row}${siblingNum} có 1 ghế không khả dụng!`, 'error');
                    return;
                }
            }

            if (isCurrentlySelected) {
                // Bỏ chọn cả 2 ghế trong cặp Sweetbox
                const seatsToDeselect = [showtimeSeatId];
                if (partnerSeat) seatsToDeselect.push(partnerSeat.showtime_seat_id);

                state.selectedSeats = state.selectedSeats.filter(sel => !seatsToDeselect.includes(sel.showtime_seat_id));

                updateSeatButtonUI(showtimeSeatId);
                if (partnerSeat) updateSeatButtonUI(partnerSeat.showtime_seat_id);
            } else {
                // Chọn cả 2 ghế trong cặp Sweetbox
                const seatsToAdd = [s];
                if (partnerSeat) seatsToAdd.push(partnerSeat);

                if (state.selectedSeats.length + seatsToAdd.length > 10) {
                    toast('Chỉ được chọn tối đa 10 ghế trong 1 đơn hàng POS!', 'error');
                    return;
                }

                seatsToAdd.forEach(st => {
                    if (!state.selectedSeats.some(sel => sel.showtime_seat_id === st.showtime_seat_id)) {
                        state.selectedSeats.push({
                            showtime_seat_id: st.showtime_seat_id,
                            label: st.label,
                            row: st.row,
                            number: st.number,
                            type: st.type,
                            price: st.price,
                        });
                    }
                });

                updateSeatButtonUI(showtimeSeatId);
                if (partnerSeat) updateSeatButtonUI(partnerSeat.showtime_seat_id);
            }
        } else {
            // Ghế đơn (Standard, VIP...)
            const idx = state.selectedSeats.findIndex(sel => sel.showtime_seat_id === showtimeSeatId);
            if (idx > -1) {
                state.selectedSeats.splice(idx, 1);
            } else {
                if (state.selectedSeats.length >= 10) {
                    toast('Chỉ được chọn tối đa 10 ghế trong 1 đơn hàng POS!', 'error');
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

            updateSeatButtonUI(showtimeSeatId);
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

        const bbBar = document.getElementById('pos-bottom-bar');
        const bbCount = document.getElementById('bb-seat-count');
        const bbList = document.getElementById('bb-seat-list');
        const bbTotal = document.getElementById('bb-total-price');
        const btnCheckout = document.getElementById('btn-checkout');

        if (bbBar) {
            if (hasItems) {
                bbBar.classList.add('show');
                bbCount.textContent = state.selectedSeats.length;
                bbList.textContent = state.selectedSeats.map(s => s.label).join(', ');
                bbTotal.textContent = formatMoney(seatTotal + comboTotal - discount);
                btnCheckout.disabled = false;
            } else {
                bbBar.classList.remove('show');
                btnCheckout.disabled = true;
            }
        }

        const msc = document.getElementById('modal-seat-count'); if(msc) msc.textContent = state.selectedSeats.length;
        const ms = document.getElementById('modal-seats'); if(ms) ms.textContent = state.selectedSeats.map(s => s.label).join(', ') || '—';
        const msp = document.getElementById('modal-seat-price'); if(msp) msp.textContent = formatMoney(seatTotal);

        const mc = document.getElementById('modal-combos'); if(mc) mc.textContent = Object.entries(state.combos).filter(([,q]) => q > 0).map(([id, q]) => `${state.comboInfo[id]?.name} x${q}`).join(', ') || '—';
        const mcp = document.getElementById('modal-combo-price'); if(mcp) mcp.textContent = formatMoney(comboTotal);

        const discountRow = document.getElementById('modal-discount-row');
        if (discount > 0 && discountRow) {
            discountRow.classList.remove('hidden'); discountRow.classList.add('flex');
            const md = document.getElementById('modal-discount'); if(md) md.textContent = '−' + formatMoney(discount);
        } else if (discountRow) {
            discountRow.classList.add('hidden'); discountRow.classList.remove('flex');
        }

        const mt = document.getElementById('modal-total'); if(mt) mt.textContent = formatMoney(grand);
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
        document.getElementById('modal-showtime-info').textContent = `${state.currentShowtime.movie} — ${String(state.currentShowtime.start_time).substring(0,5)}`;
        
        const cg = document.getElementById('cash-given'); if(cg) cg.value = '';
        const ca = document.getElementById('change-amount'); if(ca) ca.textContent = '0đ';
        
        const qrUrl = `https://img.vietqr.io/image/MB-0123456789-compact2.png?amount=${totals.grand}&addInfo=${encodeURIComponent('Dat ve FilmGo')}&accountName=CINEMA%20FILMGO`;
        const qi = document.getElementById('qr-img'); if(qi) qi.src = qrUrl;

        document.getElementById('checkout-modal').classList.remove('hidden');
        selectPayment('cash');
    }

    function closeCheckout() {
        document.getElementById('checkout-modal').classList.add('hidden');
    }

    function selectPayment(method) {
        const cashBtn = document.getElementById('btn-fnb-cash');
        const transferBtn = document.getElementById('btn-fnb-transfer');
        const qrPanel = document.getElementById('fnb-qr-panel');
        if (!cashBtn || !transferBtn) return;
        
        let total = 0;
        Object.entries(fnbState.combos).forEach(([id, qty]) => { total += (fnbState.comboInfo[id]?.price||0) * qty; });
        Object.entries(fnbState.items).forEach(([id, qty]) => { total += (fnbState.itemInfo[id]?.price||0) * qty; });
        
        if (method === 'cash') {
            cashBtn.className = "flex flex-col items-center gap-1 p-2 border-2 border-orange-500 bg-orange-50 rounded-xl transition-all shadow-sm";
            cashBtn.querySelector('span:last-child').className = "text-xs font-black text-orange-700";
            cashBtn.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-orange-600";
            
            transferBtn.className = "flex flex-col items-center gap-1 p-2 border-2 border-gray-200 bg-white rounded-xl hover:border-orange-500/40 transition-all text-gray-500";
            transferBtn.querySelector('span:last-child').className = "text-xs font-black";
            transferBtn.querySelector('.material-symbols-outlined').className = "material-symbols-outlined";
            
            if(qrPanel) qrPanel.classList.add('hidden');
        } else {
            transferBtn.className = "flex flex-col items-center gap-1 p-2 border-2 border-orange-500 bg-orange-50 rounded-xl transition-all shadow-sm";
            transferBtn.querySelector('span:last-child').className = "text-xs font-black text-orange-700";
            transferBtn.querySelector('.material-symbols-outlined').className = "material-symbols-outlined text-orange-600";
            
            cashBtn.className = "flex flex-col items-center gap-1 p-2 border-2 border-gray-200 bg-white rounded-xl hover:border-orange-500/40 transition-all text-gray-500";
            cashBtn.querySelector('span:last-child').className = "text-xs font-black";
            cashBtn.querySelector('.material-symbols-outlined').className = "material-symbols-outlined";
            
            if(qrPanel && total > 0) {
                qrPanel.classList.remove('hidden');
                document.getElementById('fnb-qr-img').src = `https://img.vietqr.io/image/MB-0123456789-compact2.png?amount=${total}&addInfo=${encodeURIComponent('Dat ve FilmGo')}&accountName=CINEMA%20FILMGO`;
            }
        }
    }

    let currentFnbBooking = null;

    function openFnbSuccessModal(booking) {
        currentFnbBooking = booking;
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

    function resetFnb() {
        document.getElementById('fnb-success-modal').classList.add('hidden');
        fnbState.combos = {}; fnbState.items = {};
        document.querySelectorAll('[id^="fnb-combo-qty-"],[id^="fnb-item-qty-"]').forEach(el=>el.textContent='0');
        updateFnbCart();
        if(document.getElementById('fnb-customer-phone')) document.getElementById('fnb-customer-phone').value = '';
        if(document.getElementById('fnb-voucher-input')) document.getElementById('fnb-voucher-input').value = '';
        selectPayment('cash');
        currentFnbBooking = null;
        toast("Đã sẵn sàng đơn F&B mới!", "success");
    }

    function handlePrintFnb() {
        if (!currentFnbBooking) return;
        document.body.classList.remove('printing-ticket');
        document.body.classList.add('printing-fnb');
        
        document.getElementById('pf-code').textContent = currentFnbBooking.booking_code;
        document.getElementById('pf-date').textContent = new Date().toLocaleString('vi-VN');
        let html = '';
        (currentFnbBooking.combos||[]).forEach(c => {
            html += `<tr><td style="padding:4px 0;">${c.quantity}x ${c.name}</td><td style="text-align:right;">${fmt(c.subtotal)}</td></tr>`;
        });
        (currentFnbBooking.combo_items||[]).forEach(i => {
            html += `<tr><td style="padding:4px 0;">${i.quantity}x ${i.name}</td><td style="text-align:right;">${fmt(i.subtotal)}</td></tr>`;
        });
        document.getElementById('pf-items').innerHTML = html;
        document.getElementById('pf-discount').textContent = currentFnbBooking.discount_amount > 0 ? ('-' + fmt(currentFnbBooking.discount_amount)) : '0đ';
        document.getElementById('pf-total').textContent = fmt(currentFnbBooking.final_total);
        document.getElementById('pf-payment').textContent = currentFnbBooking.payment_method === 'cash' ? 'Tiền mặt' : 'Chuyển khoản';
        
        window.print();
    }

    return { switchMode, changeFnbCombo, changeFnbItem, checkoutFnb, selectPayment, openFnbSuccessModal, resetFnb, handlePrintFnb };
})();

// Bridge: expose F&B methods qua POS object (đã export trong IIFE)
// switchMode etc. đã được gọi trực tiếp qua _FNB.xxx trong HTML

document.addEventListener('DOMContentLoaded', POS.init);
</script>
@endpush
