@extends('layouts.customer')

@section('title', 'Thanh Toán Đơn F&B — FilmGo')

@section('content')
<div class="bg-slate-50 min-h-screen py-10 px-4 sm:px-6 lg:px-8 text-slate-800">
    <div class="max-w-4xl mx-auto">

        {{-- Header Navigation --}}
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('combo-shop.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Quay lại chọn sản phẩm
            </a>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Thanh toán bảo mật VNPay
            </div>
        </div>

        @if(session('error'))
        <div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-sm font-semibold text-red-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-500">error</span>
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            {{-- ── Cột Trái: Chi tiết đơn hàng & Voucher ── --}}
            <div class="lg:col-span-7 space-y-6">

                {{-- Chi tiết sản phẩm --}}
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <h2 class="font-black text-slate-900 text-base mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <span class="material-symbols-outlined text-red-500">receipt_long</span>
                        Tóm Tắt Đơn Hàng F&B
                    </h2>

                    @if($selectedCombos->isNotEmpty())
                    <div class="mb-4">
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">🎁 Combo Ưu Đãi</p>
                        <div class="space-y-2">
                            @foreach($selectedCombos as $row)
                            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $row['combo']->combo_name }}</p>
                                    <p class="text-xs text-slate-500 font-medium">{{ number_format($row['combo']->price) }}đ × {{ $row['quantity'] }}</p>
                                </div>
                                <span class="font-black text-red-600 text-sm">{{ number_format($row['subtotal']) }}đ</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($selectedItems->isNotEmpty())
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">🍿 Đồ Ăn Lẻ</p>
                        <div class="space-y-2">
                            @foreach($selectedItems as $row)
                            <div class="flex justify-between items-center p-3 bg-slate-50 rounded-2xl border border-slate-100">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">{{ $row['item']->name }}</p>
                                    <p class="text-xs text-slate-500 font-medium">{{ number_format($row['item']->price) }}đ × {{ $row['quantity'] }}</p>
                                </div>
                                <span class="font-black text-orange-600 text-sm">{{ number_format($row['subtotal']) }}đ</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Tổng tính toán --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 space-y-2">
                        <div class="flex justify-between text-xs text-slate-500 font-medium">
                            <span>Tạm tính</span>
                            <span class="font-bold text-slate-800">{{ number_format($subtotal) }}đ</span>
                        </div>
                        @if($appliedVoucher)
                        <div class="flex justify-between text-xs text-emerald-600 font-bold">
                            <span>Mã giảm giá ({{ $appliedVoucher['code'] }})</span>
                            <span>−{{ number_format($discountAmount) }}đ</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                            <span class="font-black text-slate-900 text-base uppercase tracking-wider">Tổng Thanh Toán</span>
                            <span class="text-2xl font-black text-red-600">{{ number_format($finalTotal) }}đ</span>
                        </div>
                    </div>
                </div>

                {{-- Mã Giảm Giá --}}
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-widest mb-3 flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm text-emerald-600">local_offer</span>
                        Mã Giảm Giá / Voucher
                    </h3>

                    @if($appliedVoucher)
                    <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            <div>
                                <span class="text-sm font-black text-emerald-800">Đã áp dụng: {{ $appliedVoucher['code'] }}</span>
                                <p class="text-xs text-emerald-600">Giảm {{ number_format($discountAmount) }}đ vào tổng đơn</p>
                            </div>
                        </div>
                        <form action="{{ route('combo-shop.voucher.remove') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-slate-400 hover:text-red-500 font-bold px-2 py-1">✕ Xóa</button>
                        </form>
                    </div>
                    @else
                    <form action="{{ route('combo-shop.voucher.apply') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="text" name="code" placeholder="Nhập mã ưu đãi..."
                               class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-sm px-4 py-3 rounded-2xl font-bold uppercase tracking-wider focus:outline-none focus:border-red-500">
                        <button type="submit" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-2xl transition-colors">
                            Áp Dụng
                        </button>
                    </form>
                    @if(session('voucher_error'))
                    <p class="text-xs text-red-500 font-medium mt-2">{{ session('voucher_error') }}</p>
                    @endif
                    @endif
                </div>

            </div>

            {{-- ── Cột Phải: Thanh toán VNPay ── --}}
            <div class="lg:col-span-5 space-y-6">

                <form action="{{ route('combo-shop.confirm') }}" method="POST" id="payment-form" class="space-y-6">
                    @csrf
                    <input type="hidden" name="payment_method" value="vnpay">

                    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                        <h2 class="font-black text-slate-900 text-base mb-4 flex items-center gap-2 pb-3 border-b border-slate-100">
                            <span class="material-symbols-outlined text-blue-600">payments</span>
                            Cổng Thanh Toán VNPay
                        </h2>

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
                            class="w-full py-4 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-black text-base rounded-2xl shadow-lg shadow-red-600/30 transition-all flex items-center justify-center gap-2 tracking-wider">
                        <span class="material-symbols-outlined text-xl">lock</span>
                        THANH TOÁN NGAY {{ number_format($finalTotal) }}đ
                    </button>

                    <p class="text-center text-[11px] text-slate-400 font-medium">
                        Khi bấm thanh toán, bạn đồng ý với Quy định mua hàng của FilmGo.
                    </p>
                </form>

            </div>

        </div>
    </div>
</div>
@endsection
