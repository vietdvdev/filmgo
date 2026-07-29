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
    .checkout-btn:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(239,68,68,0.4);
    }
    .checkout-btn:disabled {
        opacity: 0.4; cursor: not-allowed; box-shadow: none;
    }
</style>
@endsection

@section('content')
<div class="bg-slate-50 min-h-screen text-slate-800">

    {{-- Hero Banner (Tông màu sáng tươi bứt phá) --}}
    <div class="bg-gradient-to-r from-red-600 via-rose-600 to-amber-500 text-white py-10 px-6 shadow-md">
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

        {{-- Section Switcher --}}
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4 border-b border-slate-200 pb-4">
            <div class="flex gap-3">
                <button class="tab-btn active" onclick="switchTab('combos', this)" id="tab-combos">
                    🎁 Combo Ưu Đãi
                </button>
                <button class="tab-btn" onclick="switchTab('items', this)" id="tab-items">
                    🍿 Đồ Ăn Lẻ
                </button>
            </div>
            <span class="text-xs font-semibold text-slate-500">
                Giao dịch an toàn qua cổng thanh toán VNPay
            </span>
        </div>

        @if(session('error'))
        <div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-sm font-semibold text-red-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-500">error</span>
            {{ session('error') }}
        </div>
        @endif

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
                                <img src="{{ asset('storage/' . $combo->image) }}"
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
                                    <button class="qty-btn qty-btn-minus"
                                            onclick="SHOP.changeCombo({{ $combo->id }}, -1)">−</button>
                                    <span id="combo-qty-{{ $combo->id }}"
                                          class="w-8 text-center font-black text-slate-900 text-sm">
                                        {{ $cart['combos'][$combo->id] ?? 0 }}
                                    </span>
                                    <button class="qty-btn qty-btn-plus"
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
                                        <button class="qty-btn qty-btn-minus"
                                                onclick="SHOP.changeItem({{ $item->id }}, -1)">−</button>
                                        <span id="item-qty-{{ $item->id }}"
                                              class="w-7 text-center font-black text-slate-900 text-sm">
                                            {{ $cart['items'][$item->id] ?? 0 }}
                                        </span>
                                        <button class="qty-btn qty-btn-plus"
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

                    {{-- Action Button --}}
                    <div class="mt-6">
                        <button id="btn-checkout" class="checkout-btn" disabled onclick="SHOP.goToCheckout()">
                            <span class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined">shopping_cart_checkout</span>
                                THANH TOÁN VNPay
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
</div>

<form id="cart-form" action="{{ route('combo-shop.cart') }}" method="POST" class="hidden">
    @csrf
    <div id="cart-form-inputs"></div>
</form>
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

const SHOP = {
    cart: {
        combos: {{ json_encode((object)($cart['combos'] ?? [])) }},
        items:  {{ json_encode((object)($cart['items'] ?? [])) }},
    },

    changeCombo(id, delta) {
        const current = this.cart.combos[id] ?? 0;
        const newQty  = Math.max(0, current + delta);
        if (newQty === 0) delete this.cart.combos[id];
        else this.cart.combos[id] = newQty;
        document.getElementById(`combo-qty-${id}`).textContent = newQty;
        this.render();
    },

    changeItem(id, delta) {
        const current = this.cart.items[id] ?? 0;
        const newQty  = Math.max(0, current + delta);
        if (newQty === 0) delete this.cart.items[id];
        else this.cart.items[id] = newQty;
        document.getElementById(`item-qty-${id}`).textContent = newQty;
        this.render();
    },

    render() {
        const list     = document.getElementById('cart-items-list');
        const emptyMsg = document.getElementById('empty-cart-msg');
        const badge    = document.getElementById('cart-count-badge');
        const subtotal = document.getElementById('cart-subtotal');
        const total    = document.getElementById('cart-total');
        const btnCO    = document.getElementById('btn-checkout');

        let html = '';
        let totalPrice = 0;
        let totalQty   = 0;

        Object.entries(this.cart.combos).forEach(([id, qty]) => {
            const p = COMBO_PRICES[id];
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
            const p = ITEM_PRICES[id];
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

        badge.textContent    = `${totalQty} món`;
        subtotal.textContent = `${totalPrice.toLocaleString('vi')}đ`;
        total.textContent    = `${totalPrice.toLocaleString('vi')}đ`;
        btnCO.disabled       = totalQty === 0;
    },

    goToCheckout() {
        const form   = document.getElementById('cart-form');
        const inputs = document.getElementById('cart-form-inputs');
        inputs.innerHTML = '';

        Object.entries(this.cart.combos).forEach(([id, qty]) => {
            const el = document.createElement('input');
            el.type = 'hidden'; el.name = `combos[${id}]`; el.value = qty;
            inputs.appendChild(el);
        });

        Object.entries(this.cart.items).forEach(([id, qty]) => {
            const el = document.createElement('input');
            el.type = 'hidden'; el.name = `combo_items[${id}]`; el.value = qty;
            inputs.appendChild(el);
        });

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then(() => {
            window.location.href = '{{ route("combo-shop.checkout") }}';
        }).catch(() => {
            window.location.href = '{{ route("combo-shop.checkout") }}';
        });
    },
};

function switchTab(tabName, btn) {
    document.getElementById('panel-combos').classList.toggle('hidden', tabName !== 'combos');
    document.getElementById('panel-items').classList.toggle('hidden', tabName !== 'items');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

document.addEventListener('DOMContentLoaded', () => SHOP.render());
</script>
@endsection
