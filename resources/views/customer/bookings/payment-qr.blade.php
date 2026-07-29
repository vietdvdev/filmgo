@extends('layouts.customer')

@section('title', 'Thanh Toán - FilmGo')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-850 font-sans antialiased py-10 selection:bg-brand-primary selection:text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
            <div class="bg-brand-primary px-6 py-4">
                <h2 class="text-lg font-black uppercase tracking-widest text-white">Thanh Toán {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</h2>
                <p class="text-sm text-white/80 mt-1">Vui lòng quét mã QR hoặc tiếp tục chuyển hướng để hoàn tất thanh toán</p>
            </div>

            <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8">
                <div class="space-y-4">
                    <div class="rounded-none border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3">Thông tin đơn hàng</h3>
                        <div class="space-y-2 text-sm text-slate-700 font-medium">
                            <div class="flex justify-between"><span>Mã đơn</span><span class="font-bold text-slate-900">{{ $booking->booking_code }}</span></div>
                            <div class="flex justify-between"><span>Phim / Loại</span><span class="font-bold text-slate-900">{{ optional(optional($booking->showtime)->movie)->title ?? 'Combo / F&B' }}</span></div>
                            <div class="flex justify-between"><span>Rạp</span><span class="font-bold text-slate-900">{{ optional(optional(optional($booking->showtime)->room)->cinema)->name ?? 'FilmGo Cinema' }}</span></div>
                            <div class="flex justify-between"><span>Tổng tiền</span><span class="font-black text-brand-primary text-base">{{ number_format($booking->total_amount) }}đ</span></div>
                        </div>
                    </div>

                    <div class="rounded-none border border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3">Hướng dẫn</h3>
                        <ul class="list-disc list-inside text-sm text-slate-600 space-y-2 font-medium leading-relaxed">
                            <li>Đây là mã QR mở liên kết thanh toán từ cổng {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}.</li>
                            <li>Nếu trình duyệt không tự chuyển, bấm nút tiếp tục bên dưới.</li>
                            <li>Sau khi thanh toán thành công, hệ thống sẽ tự quay lại trang xác nhận.</li>
                        </ul>
                    </div>
                </div>

                <div class="rounded-none border border-slate-200 bg-slate-50 p-6 flex flex-col items-center justify-center gap-4">
                    @php
                        $qrText = $paymentUrl ?: "FilmGo Payment\n" .
                            "Mã đơn: {$booking->booking_code}\n" .
                            "Số tiền: " . number_format($booking->total_amount) . "đ";
                    @endphp
                    @if($paymentUrl)
                        <div class="w-56 h-56 rounded-none bg-white p-4 flex items-center justify-center border border-slate-200 shadow-sm">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={{ urlencode($qrText) }}"
                                 alt="QR code thanh toán {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}"
                                 class="max-w-full max-h-full object-contain" />
                        </div>
                        <div class="text-center text-sm text-slate-800 font-semibold">
                            <div class="text-2xl font-black mb-2 text-slate-900">{{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</div>
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400 font-bold">QR Thanh Toán</div>
                        </div>

                        <a href="{{ $paymentUrl }}" target="_blank" rel="noopener"
                           class="w-full text-center bg-brand-primary hover:bg-red-700 text-white font-black py-3.5 rounded-none shadow-md shadow-brand-primary/20 transition-all duration-200 uppercase tracking-wider text-sm">
                            Tiếp tục thanh toán
                        </a>
                    @else
                        <div class="w-56 h-56 rounded-none bg-white p-4 flex items-center justify-center border border-slate-200 shadow-sm">
                            <div class="text-center text-slate-800 text-sm font-semibold">
                                <div class="text-2xl font-black mb-2 text-slate-900">{{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</div>
                                <div class="text-xs uppercase tracking-[0.25em] text-slate-400 font-bold">QR Thanh Toán</div>
                                <div class="mt-4 text-xs text-slate-500">Mã QR sẽ hiển thị khi cổng thanh toán trả về liên kết hợp lệ.</div>
                            </div>
                        </div>
                        <div class="text-sm text-slate-500 font-medium">Không có liên kết thanh toán lúc này. Vui lòng thử lại sau.</div>
                    @endif

                    <a href="{{ route('booking.checkout', $booking->showtime_id) }}"
                       class="text-xs text-slate-500 hover:text-brand-primary transition-colors font-bold uppercase tracking-wider mt-2">
                        Quay lại trang checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
