@extends('layouts.customer')

@section('title', 'Đặt Hàng Thành Công — FilmGo')

@section('content')
<div class="bg-slate-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8 text-slate-800 flex items-center justify-center">
    <div class="max-w-md w-full">

        {{-- Icon Thành Công --}}
        <div class="text-center mb-6">
            <div class="w-20 h-20 bg-emerald-100 border border-emerald-200 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm animate-bounce">
                <span class="material-symbols-outlined text-4xl">check_circle</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Đặt Hàng Thành Công!</h1>
            <p class="text-slate-500 text-xs font-medium mt-1">
                Đơn hàng F&B của bạn đã được xác nhận thanh toán thành công qua VNPay.
            </p>
        </div>

        {{-- Phiếu Đơn Hàng Card --}}
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm mb-6">

            {{-- Mã đơn --}}
            <div class="text-center pb-5 mb-5 border-b border-slate-100">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">Mã Nhận Hàng Tại Quầy F&B</span>
                <span class="text-2xl font-black text-red-600 font-mono tracking-widest bg-red-50 border border-red-100 px-4 py-2 rounded-2xl inline-block">
                    {{ $booking->booking_code }}
                </span>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Xuất trình mã này cho nhân viên tại quầy</p>
            </div>

            {{-- Danh sách combo --}}
            @if($booking->combos->isNotEmpty())
            <div class="mb-4">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">🎁 Combo Đã Mua</p>
                <div class="space-y-2">
                    @foreach($booking->combos as $combo)
                    <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <p class="font-bold text-slate-900">{{ $combo->combo_name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $combo->pivot->quantity }} phần</p>
                        </div>
                        <span class="font-bold text-red-600">{{ number_format($combo->pivot->subtotal) }}đ</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Đồ ăn lẻ --}}
            @if($booking->comboItems->isNotEmpty())
            <div class="mb-4">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-2">🍿 Đồ Ăn Lẻ</p>
                <div class="space-y-2">
                    @foreach($booking->comboItems as $row)
                    <div class="flex justify-between items-center text-xs p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <p class="font-bold text-slate-900">{{ $row->comboItem->name ?? 'Món ăn' }}</p>
                            <p class="text-[11px] text-slate-500">{{ $row->quantity }} × {{ number_format($row->unit_price) }}đ</p>
                        </div>
                        <span class="font-bold text-orange-600">{{ number_format($row->subtotal) }}đ</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Tổng cộng --}}
            <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Đã Thanh Toán</span>
                <span class="text-xl font-black text-red-600">{{ number_format($booking->final_total) }}đ</span>
            </div>
        </div>

        {{-- Mã QR Đơn Combo --}}
        @php
            $comboTicket = $booking->bookingDetails->first()?->ticket;
            $comboQrSrc  = app(\App\Services\TicketQrCodeService::class)->getQrImageUrl($comboTicket?->qr_code ?? $booking->booking_code);
        @endphp

        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm mb-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-4">
                <span class="material-symbols-outlined text-red-500 text-xl">qr_code_2</span>
                <h3 class="text-xs font-black text-slate-600 uppercase tracking-widest">Mã QR Nhận Hàng</h3>
            </div>

            <div class="flex flex-col items-center gap-3">
                <img src="{{ $comboQrSrc }}"
                     alt="QR đơn combo {{ $booking->booking_code }}"
                     class="w-48 h-48 object-contain rounded-2xl border border-slate-100 p-2 shadow-sm">
                <p class="text-[11px] text-slate-500 font-semibold">Xuất trình mã QR này tại quầy F&amp;B để nhận bắp nước</p>
                <span class="text-xs font-mono font-bold text-slate-700 bg-slate-50 px-3 py-1 rounded-xl border border-slate-200">
                    Mã đơn: #{{ $booking->booking_code }}
                </span>
            </div>
        </div>

        {{-- Hướng dẫn --}}
        <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-2xl mb-6 text-xs text-amber-800 space-y-1">
            <p class="font-bold flex items-center gap-1 text-amber-900">
                <span class="material-symbols-outlined text-sm">info</span> Hướng dẫn nhận hàng
            </p>
            <p class="leading-relaxed text-[11px]">
                Đến quầy F&B của rạp FilmGo bất kỳ trong ngày hôm nay và đọc mã <strong>{{ $booking->booking_code }}</strong> để nhận bắp nước nóng hổi lập tức!
            </p>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            <a href="{{ route('combo-shop.index') }}"
               class="w-full py-3.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">shopping_cart</span>
                Mua Thêm Combo Khác
            </a>
            <a href="{{ route('home') }}"
               class="w-full py-3 bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-bold text-xs uppercase tracking-wider rounded-2xl transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">home</span>
                Về Trang Chủ
            </a>
        </div>

    </div>
</div>
@endsection
