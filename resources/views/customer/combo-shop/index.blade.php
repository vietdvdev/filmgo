@extends('layouts.customer')

@section('title', 'Mua Bắp Nước & Combo — FilmGo')

@section('styles')
<style>
    .combo-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .combo-card:hover {
        transform: translateY(-4px);
        border-color: #ef4444;
        box-shadow: 0 12px 24px -6px rgba(239, 68, 68, 0.15);
    }

    .item-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        transition: all 0.2s ease;
    }
    .item-card:hover {
        border-color: #f97316;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
    }

    .qty-btn {
        width: 32px; height: 32px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; font-weight: 700;
        cursor: pointer; transition: all 0.15s;
        border: none; outline: none;
    }
    .qty-btn-minus { background: #f1f5f9; color: #475569; }
    .qty-btn-minus:hover { background: #e2e8f0; color: #0f172a; }
    .qty-btn-plus { background: #ef4444; color: #ffffff; }
    .qty-btn-plus:hover { background: #dc2626; transform: scale(1.05); }

    .tab-btn {
        padding: 10px 24px;
        border-radius: 12px;
        font-size: 14px; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
        border: 1px solid transparent;
    }
    .tab-btn.active {
        background: #ef4444;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
    }
    .tab-btn:not(.active) {
        background: #ffffff;
        color: #64748b;
        border-color: #cbd5e1;
    }
    .tab-btn:not(.active):hover {
        color: #0f172a;
        border-color: #94a3b8;
    }

    .checkout-btn {
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: #ffffff;
        border: none;
        border-radius: 16px;
        padding: 16px;
        font-size: 15px; font-weight: 800;
        cursor: pointer; width: 100%;
        transition: all 0.25s;
        box-shadow: 0 4px 14px rgba(239,68,68,0.3);
    }
    .checkout-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239,68,68,0.4);
    }
</style>
@endsection

@section('content')
<div class="bg-slate-50 min-h-screen text-slate-800">

    {{-- Hero Banner --}}
    <div class="bg-gradient-to-r from-red-600 via-rose-600 to-amber-500 text-white py-8 px-6 shadow-md">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider mb-3">
                    <span class="material-symbols-outlined text-sm">local_mall</span> Bắp Nước Trực Tuyến
                </div>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight leading-tight">Thưởng Thức Bắp Nước Chuẩn Rạp</h1>
                <p class="text-red-100 text-sm mt-1 max-w-xl">
                    Đặt trước combo bắp nước yêu thích dễ dàng mà không cần mua vé phim. Nhận nhanh tại quầy F&B!
                </p>
            </div>
            <div class="hidden md:flex gap-3">
                <div class="px-5 py-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 text-center">
                    <span class="block text-2xl font-black">100%</span>
                    <span class="text-xs text-red-100 font-medium">Bắp Giòn Rụm</span>
                </div>
                <div class="px-5 py-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 text-center">
                    <span class="block text-2xl font-black">Fast Pass</span>
                    <span class="text-xs text-red-100 font-medium">Nhận Tại Quầy</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ── Step Progress Indicator ── --}}
        <div class="max-w-xl mx-auto mb-8 px-4">
            <div class="flex items-center justify-between relative">
                <div class="absolute inset-x-0 top-5 h-0.5 bg-slate-200 z-0"></div>
                <div id="step-progress-bar" class="absolute left-0 top-5 h-0.5 bg-red-600 z-0 transition-all duration-300 w-1/2"></div>

                <div class="z-10 flex flex-col items-center gap-1.5 cursor-pointer" onclick="SHOP.goToStep(1)">
                    <div id="step-1-badge" class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-red-600 text-white ring-4 ring-red-600/20 shadow-md">
                        1
                    </div>
                    <span id="step-1-text" class="text-xs font-bold uppercase tracking-wider text-red-600">
                        1. Chọn Combo & Rạp
                    </span>
                </div>

                <div class="z-10 flex flex-col items-center gap-1.5 cursor-pointer" onclick="SHOP.goToStep(2)">
                    <div id="step-2-badge" class="w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-white border-2 border-slate-300 text-slate-400">
                        2
                    </div>
                    <span id="step-2-text" class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        2. Xác nhận & Thanh toán
                    </span>
                </div>
            </div>
        </div>

        @if(session('error'))
        <div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-sm font-semibold text-red-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-500">error</span>
            {{ session('error') }}
        </div>
        @endif

        {{-- ───────────────────────────────────────────────────────────────────────── --}}
        {{-- STEP 1: CHỌN SẢN PHẨM VÀ CHỌN RẠP --}}
        {{-- ───────────────────────────────────────────────────────────────────────── --}}
        <div id="step-1-container">

            {{-- Section Switcher --}}
            <div class="flex items-center justify-between mb-6 flex-wrap gap-4 border-b border-slate-200 pb-4">
                <div class="flex gap-3">
                    <button type="button" class="tab-btn active" onclick="switchTab('combos', this)" id="tab-combos">
                        🎁 Combo Ưu Đãi
                    </button>
                    <button type="button" class="tab-btn" onclick="switchTab('items', this)" id="tab-items">
                        🍿 Đồ Ăn Lẻ
                    </button>
                </div>
                <span class="text-xs font-semibold text-slate-500">
                    Giao dịch an toàn qua cổng thanh toán VNPay
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- Cột trái (Sản phẩm) --}}
                <div class="lg:col-span-8">

                    {{-- ── Panel: Combo Gói ────────────────── --}}
                    <div id="panel-combos">
                        @if($combos->isEmpty())
                        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 text-slate-400">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">inventory_2</span>
                            <p class="font-bold">Chưa có combo nào khả dụng</p>
                        </div>
                        @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            @foreach($combos as $combo)
                            <div class="combo-card p-5 flex flex-col justify-between" id="combo-card-{{ $combo->id }}">
                                <div>
                                    @if($combo->image)
                                    <img src="{{ $combo->image_url }}"
                                         alt="{{ $combo->combo_name }}"
                                         class="w-full h-44 object-cover rounded-xl mb-4 bg-slate-100">
                                    @else
                                    <div class="w-full h-44 rounded-xl mb-4 bg-gradient-to-br from-amber-50 to-orange-100 border border-amber-200/60 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-5xl text-orange-400">fastfood</span>
                                    </div>
                                    @endif

                                    <h3 class="font-black text-slate-900 text-base mb-1">{{ $combo->combo_name }}</h3>
                                    @if($combo->description)
                                    <p class="text-xs text-slate-500 mb-3 line-clamp-2 leading-relaxed">{{ $combo->description }}</p>
                                    @endif

                                    @if($combo->items->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 mb-4">
                                        @foreach($combo->items as $item)
                                        <span class="px-2.5 py-1 text-[11px] rounded-lg font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                            {{ $item->pivot->quantity }}x {{ $item->name }}
                                        </span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                    <div>
                                        <span class="text-xs text-slate-400 block font-semibold">Giá combo</span>
                                        <span class="text-xl font-black text-red-600">
                                            {{ number_format($combo->price) }}đ
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl">
                                        <button type="button" class="qty-btn qty-btn-minus"
                                                onclick="SHOP.changeCombo({{ $combo->id }}, -1)">−</button>
                                        <span id="combo-qty-{{ $combo->id }}"
                                              class="w-8 text-center font-black text-slate-900 text-sm">
                                            {{ $cart['combos'][$combo->id] ?? 0 }}
                                        </span>
                                        <button type="button" class="qty-btn qty-btn-plus"
                                                onclick="SHOP.changeCombo({{ $combo->id }}, 1)">+</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    {{-- ── Panel: Đồ Ăn Lẻ ───────────────── --}}
                    <div id="panel-items" class="hidden">
                        @if($comboItems->isEmpty())
                        <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 text-slate-400">
                            <span class="material-symbols-outlined text-5xl mb-2 text-slate-300">local_dining</span>
                            <p class="font-bold">Chưa có món bán lẻ nào khả dụng</p>
                        </div>
                        @else
                        <div class="space-y-6">
                            @foreach($comboItems as $type => $items)
                            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
                                <h3 class="text-xs font-black uppercase tracking-widest text-orange-600 mb-4 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                    {{ ucfirst($type) ?: 'Khác' }}
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($items as $item)
                                    <div class="item-card p-4 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-slate-900 text-sm">{{ $item->name }}</p>
                                            <p class="text-xs text-slate-400 font-medium">{{ $item->unit }}</p>
                                            <p class="text-sm font-black text-orange-600 mt-1">{{ number_format($item->price) }}đ</p>
                                        </div>
                                        <div class="flex items-center gap-2 bg-slate-100 p-1 rounded-xl flex-shrink-0">
                                            <button type="button" class="qty-btn qty-btn-minus"
                                                    onclick="SHOP.changeItem({{ $item->id }}, -1)">−</button>
                                            <span id="item-qty-{{ $item->id }}"
                                                  class="w-7 text-center font-black text-slate-900 text-sm">
                                                {{ $cart['items'][$item->id] ?? 0 }}
                                            </span>
                                            <button type="button" class="qty-btn qty-btn-plus"
                                                    onclick="SHOP.changeItem({{ $item->id }}, 1)">+</button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                </div>

                {{-- Cột phải (Giỏ hàng Sidebar) --}}
                <div class="lg:col-span-4">
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm sticky top-24">

                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h2 class="font-black text-slate-900 text-lg flex items-center gap-2">
                                <span class="material-symbols-outlined text-red-500">shopping_bag</span>
                                Giỏ Hàng
                            </h2>
                            <span id="cart-count-badge" class="px-3 py-1 bg-red-50 text-red-600 font-bold text-xs rounded-full border border-red-100">
                                0 món
                            </span>
                        </div>

                        {{-- Dynamic Cart Item List --}}
                        <div id="cart-items-list" class="py-4 space-y-3 min-h-[160px] max-h-[320px] overflow-y-auto">
                            <div id="empty-cart-msg" class="text-center py-10 text-slate-400">
                                <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">shopping_cart</span>
                                <p class="text-xs font-semibold">Giỏ hàng của bạn đang trống</p>
                                <p class="text-[11px] text-slate-400 mt-1">Chọn combo hoặc đồ ăn để thêm vào đơn</p>
                            </div>
                        </div>

                        {{-- Pricing Breakdown --}}
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <div class="flex justify-between text-sm text-slate-500 font-medium">
                                <span>Tạm tính</span>
                                <span id="cart-subtotal" class="font-bold text-slate-900">0đ</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                                <span class="font-black text-slate-900 text-base uppercase tracking-wider">Tổng Tiền</span>
                                <span id="cart-total" class="text-2xl font-black text-red-600">0đ</span>
                            </div>
                        </div>

                        {{-- Chọn rạp --}}
                        <div class="pt-4 border-t border-slate-100" id="cinema-select-wrapper">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                <span class="material-symbols-outlined text-sm align-middle text-red-500">location_on</span>
                                Chọn rạp nhận hàng <span class="text-red-500">*</span>
                            </label>
                            <select id="cinema-select"
                                    class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-2xl px-4 py-3 focus:outline-none focus:border-red-500 transition-colors"
                                    onchange="SHOP.selectCinema(this.value)">
                                <option value="">-- Chọn rạp nhận hàng --</option>
                                @foreach($cinemas as $c)
                                <option value="{{ $c->id }}" {{ $selectedCinemaId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            <p id="cinema-required-msg" class="text-xs text-red-500 font-bold mt-1.5 hidden">⚠️ Vui lòng chọn rạp nhận hàng trước khi tiếp tục.</p>
                        </div>

                        {{-- Action Button (Luôn có thể click để phản hồi trực quan) --}}
                        <div class="mt-4">
                            <input type="hidden" name="cinema_id" id="form-cinema-id" value="{{ $selectedCinemaId ?? '' }}">
                            <button id="btn-checkout" type="button" class="checkout-btn" onclick="SHOP.goToStep(2)">
                                <span class="flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                                    TIẾP TỤC THANH TOÁN
                                </span>
                            </button>
                            <div class="flex items-center justify-center gap-2 mt-3 text-slate-400 text-[11px] font-semibold">
                                <span class="material-symbols-outlined text-sm text-emerald-500">verified_user</span>
                                Thanh toán an toàn bảo mật
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────────────── --}}
        {{-- STEP 2: XÁC NHẬN ĐƠN HÀNG VÀ CHỌN PHƯƠNG THỨC THANH TOÁN (KHÔNG ĐỔI URL) --}}
        {{-- ───────────────────────────────────────────────────────────────────────── --}}
        <div id="step-2-container" class="hidden">
            
            {{-- Header Navigation Quay Lại --}}
            <div class="mb-6 flex items-center justify-between">
                <button type="button"
                        onclick="SHOP.goToStep(1)"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:text-red-600 hover:border-red-200 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    ← Quay lại chọn sản phẩm
                </button>
                <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Thanh toán bảo mật VNPay
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                {{-- ── Cột Trái: Chi tiết đơn hàng & Voucher ── --}}
                <div class="lg:col-span-7 space-y-6">

                    {{-- Chi tiết sản phẩm --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <h2 class="font-black text-slate-900 text-base mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                            <span class="material-symbols-outlined text-red-500">receipt_long</span>
                            Tóm Tắt Đơn Hàng F&B
                        </h2>

                        {{-- Section Combos --}}
                        <div id="step2-combos-section" class="mb-4 hidden">
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">🎁 Combo Ưu Đãi</p>
                            <div id="step2-combos-list" class="space-y-2"></div>
                        </div>

                        {{-- Section Items --}}
                        <div id="step2-items-section" class="mb-4 hidden">
                            <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">🍿 Đồ Ăn Lẻ</p>
                            <div id="step2-items-list" class="space-y-2"></div>
                        </div>

                        {{-- Tổng tính toán --}}
                        <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
                            <div class="flex justify-between text-xs text-slate-500 font-medium">
                                <span>Tạm tính</span>
                                <span id="step2-subtotal" class="font-bold text-slate-800">0đ</span>
                            </div>
                            <div id="step2-discount-row" class="flex justify-between text-xs text-emerald-600 font-bold hidden">
                                <span>Mã giảm giá</span>
                                <span id="step2-discount-val">−0đ</span>
                            </div>
                            <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                                <span class="font-black text-slate-900 text-base uppercase tracking-wider">Tổng Thanh Toán</span>
                                <span id="step2-final-total" class="text-2xl font-black text-red-600">0đ</span>
                            </div>
                        </div>
                    </div>

                    {{-- Mã Giảm Giá --}}
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <h3 class="font-black text-slate-900 text-xs uppercase tracking-widest mb-3 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600">local_offer</span>
                            Mã Giảm Giá / Voucher
                        </h3>

                        <div id="step2-voucher-applied" class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl hidden">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                                <div>
                                    <span id="step2-voucher-code-text" class="text-sm font-black text-emerald-800">Mã ưu đãi</span>
                                    <p id="step2-voucher-desc-text" class="text-xs text-emerald-600">Đã giảm vào tổng đơn</p>
                                </div>
                            </div>
                            <button type="button" onclick="SHOP.removeVoucher()" class="text-xs text-slate-400 hover:text-red-500 font-bold px-2 py-1 cursor-pointer">✕ Xóa</button>
                        </div>

                        <form id="step2-voucher-input-form" onsubmit="SHOP.applyVoucher(event)" class="flex gap-2">
                            <input type="text" id="voucher-code-input" placeholder="Nhập mã ưu đãi..."
                                   class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-sm px-4 py-3 rounded-2xl font-bold uppercase tracking-wider focus:outline-none focus:border-red-500">
                            <button type="submit" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-colors cursor-pointer">
                                Áp Dụng
                            </button>
                        </form>
                        <p id="voucher-error-msg" class="text-xs text-red-500 font-medium mt-2 hidden"></p>
                    </div>

                </div>

                {{-- ── Cột Phải: Thanh toán VNPay ── --}}
                <div class="lg:col-span-5 space-y-6">

                    <form action="{{ route('combo-shop.confirm') }}" method="POST" id="payment-confirm-form" class="space-y-6">
                        @csrf
                        <input type="hidden" name="payment_method" value="vnpay">
                        <input type="hidden" name="cinema_id" id="step2-form-cinema-id" value="{{ $selectedCinemaId ?? '' }}">

                        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                            <h2 class="font-black text-slate-900 text-base mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                                <span class="material-symbols-outlined text-blue-600">payments</span>
                                Cổng Thanh Toán VNPay
                            </h2>

                            <div class="mb-5">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                    Rạp nhận hàng
                                </label>
                                <div class="flex items-center justify-between p-3 bg-emerald-50 border border-emerald-200 rounded-2xl">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-emerald-600 text-sm">location_on</span>
                                        <span id="step2-cinema-name" class="text-sm font-bold text-emerald-800">
                                            {{ $selectedCinemaId ? ($cinemas->firstWhere('id', $selectedCinemaId)?->name ?? 'Rạp đã chọn') : 'Chưa chọn rạp' }}
                                        </span>
                                    </div>
                                    <button type="button" onclick="SHOP.goToStep(1)" class="text-xs text-slate-500 hover:text-red-500 font-bold underline cursor-pointer">
                                        Thay đổi
                                    </button>
                                </div>
                            </div>

                            {{-- Card VNPay duy nhất --}}
                            <div class="p-4 rounded-2xl border-2 border-blue-600 bg-blue-50/60 mb-5 flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-blue-700 flex items-center justify-center text-white font-black text-sm flex-shrink-0 shadow-md">
                                    VNPAY
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 text-sm">Thanh toán qua VNPay</h4>
                                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Hỗ trợ quét mã QR, thẻ ATM, Visa, MasterCard, Mobile Banking</p>
                                </div>
                            </div>

                            {{-- Chọn ngân hàng --}}
                            <div class="mb-4">
                                <label for="bank_code" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">
                                    Chọn ngân hàng thanh toán
                                </label>
                                <select name="bank_code" id="bank_code"
                                        class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-2xl px-4 py-3 focus:outline-none focus:border-blue-600">
                                    <option value="NCB">Ngân hàng NCB</option>
                                    <option value="VNPAYQR">Thanh toán qua VNPAYQR</option>
                                    <option value="VIETCOMBANK">Ngân hàng Vietcombank</option>
                                    <option value="VIETINBANK">Ngân hàng VietinBank</option>
                                    <option value="BIDV">Ngân hàng BIDV</option>
                                    <option value="AGRIBANK">Ngân hàng Agribank</option>
                                    <option value="MBBANK">Ngân hàng MBBank</option>
                                    <option value="TCB">Ngân hàng Techcombank</option>
                                </select>
                            </div>

                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-500 space-y-1">
                                <p class="font-bold text-slate-700">• Lưu ý thanh toán:</p>
                                <p>Bạn sẽ được chuyển hướng sang cổng thanh toán VNPay để hoàn tất giao dịch.</p>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit"
                                class="w-full py-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-base rounded-2xl shadow-lg shadow-red-600/30 transition-all flex items-center justify-center gap-2 tracking-wider cursor-pointer">
                            <span class="material-symbols-outlined text-xl">lock</span>
                            THANH TOÁN NGAY <span id="step2-btn-total">0đ</span>
                        </button>

                        <p class="text-center text-[11px] text-slate-400 font-medium">
                            Khi bấm thanh toán, bạn đồng ý với Quy định mua hàng của FilmGo.
                        </p>
                    </form>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
const COMBO_PRICES = {
    @foreach($combos as $combo)
    {{ $combo->id }}: { name: @json($combo->combo_name), price: {{ $combo->price }} },
    @endforeach
};

const ITEM_PRICES = {};
@foreach($comboItems as $type => $items)
@foreach($items as $item)
ITEM_PRICES[{{ $item->id }}] = { name: @json($item->name), price: {{ $item->price }}, unit: @json($item->unit) };
@endforeach
@endforeach

const initialCombos = @json((object)($cart['combos'] ?? []));
const initialItems  = @json((object)($cart['items'] ?? []));

const SHOP = {
    cart: {
        combos: (typeof initialCombos === 'object' && initialCombos !== null) ? initialCombos : {},
        items:  (typeof initialItems  === 'object' && initialItems  !== null) ? initialItems  : {},
    },
    cinemaId: {{ $selectedCinemaId ? $selectedCinemaId : 'null' }},
    currentStep: 1,

    selectCinema(id) {
        this.cinemaId = id ? parseInt(id) : null;
        const msgEl = document.getElementById('cinema-required-msg');
        const selEl = document.getElementById('cinema-select');
        if (msgEl) msgEl.classList.toggle('hidden', !!id);
        if (selEl) {
            selEl.classList.toggle('border-red-500', !id);
            selEl.classList.toggle('bg-red-50/50', !id);
        }
        document.getElementById('form-cinema-id').value = id || '';
        document.getElementById('step2-form-cinema-id').value = id || '';
        this.render();
    },

    changeCombo(id, delta) {
        const current = this.cart.combos[id] ?? 0;
        const newQty  = Math.max(0, current + delta);
        if (newQty === 0) delete this.cart.combos[id];
        else this.cart.combos[id] = newQty;
        
        const qtyEl = document.getElementById(`combo-qty-${id}`);
        if (qtyEl) qtyEl.textContent = newQty;
        this.render();
    },

    changeItem(id, delta) {
        const current = this.cart.items[id] ?? 0;
        const newQty  = Math.max(0, current + delta);
        if (newQty === 0) delete this.cart.items[id];
        else this.cart.items[id] = newQty;

        const qtyEl = document.getElementById(`item-qty-${id}`);
        if (qtyEl) qtyEl.textContent = newQty;
        this.render();
    },

    render() {
        const list     = document.getElementById('cart-items-list');
        const badge    = document.getElementById('cart-count-badge');
        const subtotal = document.getElementById('cart-subtotal');
        const total    = document.getElementById('cart-total');

        let html = '';
        let totalPrice = 0;
        let totalQty   = 0;

        Object.entries(this.cart.combos).forEach(([id, qty]) => {
            const p = COMBO_PRICES[parseInt(id)];
            if (!p || qty <= 0) return;
            const sub = p.price * qty;
            totalPrice += sub; totalQty += qty;
            html += `
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold text-slate-900 truncate">🎁 ${p.name}</p>
                  <p class="text-[11px] text-slate-500 font-medium">${qty} x ${p.price.toLocaleString('vi')}đ</p>
                </div>
                <span class="text-xs font-black text-red-600 whitespace-nowrap">${sub.toLocaleString('vi')}đ</span>
              </div>`;
        });

        Object.entries(this.cart.items).forEach(([id, qty]) => {
            const p = ITEM_PRICES[parseInt(id)];
            if (!p || qty <= 0) return;
            const sub = p.price * qty;
            totalPrice += sub; totalQty += qty;
            html += `
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold text-slate-900 truncate">🍿 ${p.name}</p>
                  <p class="text-[11px] text-slate-500 font-medium">${qty} x ${p.price.toLocaleString('vi')}đ</p>
                </div>
                <span class="text-xs font-black text-orange-600 whitespace-nowrap">${sub.toLocaleString('vi')}đ</span>
              </div>`;
        });

        if (list) {
            if (html) {
                list.innerHTML = html;
            } else {
                list.innerHTML = `
                    <div id="empty-cart-msg" class="text-center py-10 text-slate-400">
                        <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">shopping_cart</span>
                        <p class="text-xs font-semibold">Giỏ hàng của bạn đang trống</p>
                        <p class="text-[11px] text-slate-400 mt-1">Chọn combo hoặc đồ ăn để thêm vào đơn</p>
                    </div>`;
            }
        }

        if (badge) badge.textContent    = `${totalQty} món`;
        if (subtotal) subtotal.textContent = `${totalPrice.toLocaleString('vi')}đ`;
        if (total) total.textContent    = `${totalPrice.toLocaleString('vi')}đ`;
    },

    async goToStep(step, pushState = true) {
        if (step === 2) {
            let totalQty = 0;
            Object.values(this.cart.combos).forEach(q => totalQty += q);
            Object.values(this.cart.items).forEach(q => totalQty += q);

            if (totalQty <= 0) {
                alert('Vui lòng chọn ít nhất 1 sản phẩm combo hoặc đồ ăn lẻ.');
                return;
            }
            if (!this.cinemaId) {
                const msgEl = document.getElementById('cinema-required-msg');
                const selEl = document.getElementById('cinema-select');
                if (msgEl) msgEl.classList.remove('hidden');
                if (selEl) {
                    selEl.classList.add('border-red-500', 'bg-red-50/50');
                    selEl.focus();
                    selEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Đồng bộ giỏ hàng & rạp vào session backend bằng AJAX
            try {
                const res = await fetch("{{ route('combo-shop.cart') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        combos: this.cart.combos,
                        combo_items: this.cart.items,
                        cinema_id: this.cinemaId
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.updateStep2UI(data);
                }
            } catch (err) {
                console.error('Lỗi đồng bộ giỏ hàng:', err);
            }
        }

        this.currentStep = step;
        document.getElementById('step-1-container').classList.toggle('hidden', step !== 1);
        document.getElementById('step-2-container').classList.toggle('hidden', step !== 2);

        // Cập nhật giao diện thanh tiến trình Step
        const bar    = document.getElementById('step-progress-bar');
        const badge1 = document.getElementById('step-1-badge');
        const text1  = document.getElementById('step-1-text');
        const badge2 = document.getElementById('step-2-badge');
        const text2  = document.getElementById('step-2-text');

        if (step === 1) {
            if (bar) bar.className    = 'absolute left-0 top-5 h-0.5 bg-red-600 z-0 transition-all duration-300 w-1/2';
            if (badge1) badge1.className = 'w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-red-600 text-white ring-4 ring-red-600/20 shadow-md';
            if (text1) text1.className  = 'text-xs font-bold uppercase tracking-wider text-red-600';

            if (badge2) badge2.className = 'w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-white border-2 border-slate-300 text-slate-400';
            if (text2) text2.className  = 'text-xs font-bold uppercase tracking-wider text-slate-400';
        } else {
            if (bar) bar.className    = 'absolute left-0 top-5 h-0.5 bg-red-600 z-0 transition-all duration-300 w-full';
            if (badge1) badge1.className = 'w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-red-600 text-white shadow-md';
            if (text1) text1.className  = 'text-xs font-bold uppercase tracking-wider text-red-600';

            if (badge2) badge2.className = 'w-10 h-10 rounded-full flex items-center justify-center font-black text-sm transition-all duration-200 bg-red-600 text-white ring-4 ring-red-600/20 shadow-md';
            if (text2) text2.className  = 'text-xs font-bold uppercase tracking-wider text-red-600';
        }

        if (pushState) {
            history.pushState({ step: step }, '', window.location.href);
        }

        window.scrollTo({ top: 120, behavior: 'smooth' });
    },

    updateStep2UI(data) {
        // Danh sách Combo đã chọn
        const combosContainer = document.getElementById('step2-combos-list');
        if (combosContainer) {
            if (data.selected_combos && data.selected_combos.length > 0) {
                combosContainer.innerHTML = data.selected_combos.map(c => `
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">🎁 ${c.name}</p>
                            <p class="text-xs text-slate-500 font-medium">${c.price.toLocaleString('vi')}đ × ${c.quantity}</p>
                        </div>
                        <span class="font-black text-red-600 text-sm">${c.subtotal.toLocaleString('vi')}đ</span>
                    </div>
                `).join('');
                document.getElementById('step2-combos-section').classList.remove('hidden');
            } else {
                document.getElementById('step2-combos-section').classList.add('hidden');
            }
        }

        // Danh sách Đồ ăn lẻ đã chọn
        const itemsContainer = document.getElementById('step2-items-list');
        if (itemsContainer) {
            if (data.selected_items && data.selected_items.length > 0) {
                itemsContainer.innerHTML = data.selected_items.map(i => `
                    <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900">🍿 ${i.name}</p>
                            <p class="text-xs text-slate-500 font-medium">${i.price.toLocaleString('vi')}đ × ${i.quantity}</p>
                        </div>
                        <span class="font-black text-orange-600 text-sm">${i.subtotal.toLocaleString('vi')}đ</span>
                    </div>
                `).join('');
                document.getElementById('step2-items-section').classList.remove('hidden');
            } else {
                document.getElementById('step2-items-section').classList.add('hidden');
            }
        }

        // Tổng tiền & Voucher
        const subtotalEl = document.getElementById('step2-subtotal');
        if (subtotalEl) subtotalEl.textContent = `${data.subtotal.toLocaleString('vi')}đ`;
        
        if (data.discount_amount > 0) {
            document.getElementById('step2-discount-row').classList.remove('hidden');
            document.getElementById('step2-discount-val').textContent = `−${data.discount_amount.toLocaleString('vi')}đ`;
        } else {
            document.getElementById('step2-discount-row').classList.add('hidden');
        }

        const finalEl = document.getElementById('step2-final-total');
        if (finalEl) finalEl.textContent = `${data.final_total.toLocaleString('vi')}đ`;

        const btnTotalEl = document.getElementById('step2-btn-total');
        if (btnTotalEl) btnTotalEl.textContent = `${data.final_total.toLocaleString('vi')}đ`;

        // Tên rạp nhận hàng
        const cinemaNameEl = document.getElementById('step2-cinema-name');
        if (cinemaNameEl && data.cinema) {
            cinemaNameEl.textContent = data.cinema.name;
        }

        // Trạng thái Voucher
        if (data.voucher) {
            document.getElementById('step2-voucher-applied').classList.remove('hidden');
            document.getElementById('step2-voucher-input-form').classList.add('hidden');
            document.getElementById('step2-voucher-code-text').textContent = `Đã áp dụng: ${data.voucher.code}`;
            document.getElementById('step2-voucher-desc-text').textContent = `Giảm ${data.discount_amount.toLocaleString('vi')}đ vào tổng đơn`;
        } else {
            document.getElementById('step2-voucher-applied').classList.add('hidden');
            document.getElementById('step2-voucher-input-form').classList.remove('hidden');
        }
    },

    async applyVoucher(e) {
        e.preventDefault();
        const codeInput = document.getElementById('voucher-code-input');
        const errorMsg  = document.getElementById('voucher-error-msg');
        if (errorMsg) errorMsg.classList.add('hidden');
        if (!codeInput || !codeInput.value.trim()) return;

        try {
            const res = await fetch("{{ route('combo-shop.voucher.apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ code: codeInput.value.trim() })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                codeInput.value = '';
                this.syncBackendAndRefreshStep2();
            } else {
                if (errorMsg) {
                    errorMsg.textContent = data.message || 'Mã không hợp lệ.';
                    errorMsg.classList.remove('hidden');
                }
            }
        } catch(err) {
            console.error('Lỗi áp dụng voucher:', err);
        }
    },

    async removeVoucher() {
        try {
            const res = await fetch("{{ route('combo-shop.voucher.remove') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.syncBackendAndRefreshStep2();
            }
        } catch(err) {
            console.error('Lỗi xóa voucher:', err);
        }
    },

    async syncBackendAndRefreshStep2() {
        const res = await fetch("{{ route('combo-shop.cart') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                combos: this.cart.combos,
                combo_items: this.cart.items,
                cinema_id: this.cinemaId
            })
        });
        const data = await res.json();
        if (data.success) {
            this.updateStep2UI(data);
        }
    }
};

function switchTab(tabName, btn) {
    document.getElementById('panel-combos').classList.toggle('hidden', tabName !== 'combos');
    document.getElementById('panel-items').classList.toggle('hidden', tabName !== 'items');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

// Bắt sự kiện back trên trình duyệt
window.addEventListener('popstate', (e) => {
    if (e.state && e.state.step === 2) {
        SHOP.goToStep(2, false);
    } else {
        SHOP.goToStep(1, false);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    SHOP.render();
    SHOP.goToStep(1, false);
});

window.addEventListener('pageshow', (e) => {
    if (e.persisted) {
        window.location.reload();
    } else {
        SHOP.render();
    }
});
</script>
@endsection
