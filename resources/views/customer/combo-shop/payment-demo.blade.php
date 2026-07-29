@extends('layouts.customer')

@section('title', 'Demo Thanh Toán — FilmGo F&B')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4" style="background:#0a0a12">
<div class="max-w-md w-full">
    <div class="rounded-2xl overflow-hidden" style="background:linear-gradient(145deg,#1a1a2e,#16213e); border:1px solid rgba(255,255,255,0.08)">

        {{-- Header --}}
        <div class="px-6 py-5 text-center" style="background:linear-gradient(135deg,#7c3aed,#4c1d95)">
            <span class="material-symbols-outlined text-white mb-2 block" style="font-size:40px">science</span>
            <h2 class="text-xl font-black text-white">Demo Thanh Toán</h2>
            <p class="text-purple-200 text-sm mt-1">Môi trường phát triển — không phải giao dịch thật</p>
        </div>

        <div class="p-6">
            {{-- Thông tin đơn --}}
            <div class="rounded-xl p-4 mb-5" style="background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08)">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400">Mã đơn</span>
                    <span class="font-bold text-white font-mono">{{ $booking->booking_code }}</span>
                </div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-400">Loại</span>
                    <span class="font-bold text-orange-400">Combo / F&B</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Tổng tiền</span>
                    <span class="font-black text-red-400 text-base">{{ number_format($booking->final_total) }}đ</span>
                </div>
            </div>

            {{-- Nút demo success --}}
            <form action="{{ route('combo-shop.payment.demo.complete', ['id' => $booking->id, 'provider' => $provider]) }}"
                  method="POST">
                @csrf
                <button type="submit"
                        class="w-full py-4 font-black text-white rounded-xl transition-all"
                        style="background:linear-gradient(135deg,#22c55e,#16a34a)"
                        onmouseover="this.style.transform='scale(1.02)'"
                        onmouseout="this.style.transform='scale(1)'">
                    <span class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:22px">check_circle</span>
                        Xác Nhận Thanh Toán Demo
                    </span>
                </button>
            </form>

            <a href="{{ route('combo-shop.index') }}"
               class="mt-3 block text-center text-sm text-gray-400 hover:text-white transition-colors">
                Hủy & quay lại cửa hàng
            </a>
        </div>
    </div>
</div>
</div>
@endsection
