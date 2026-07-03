@extends('layouts.customer')

@section('title', 'Thanh Toán - FilmGo')

@section('content')
<div class="min-h-screen bg-[#0F0F0F] text-white font-sans antialiased py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-[#1A1A1A] border border-zinc-800 rounded-3xl overflow-hidden">
            <div class="bg-brand-primary px-6 py-4">
                <h2 class="text-lg font-black uppercase tracking-widest">Thanh Toán {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</h2>
                <p class="text-sm text-white/80 mt-1">Vui lòng quét mã QR hoặc tiếp tục chuyển hướng để hoàn tất thanh toán</p>
            </div>

            <div class="p-6 md:p-8 grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8">
                <div class="space-y-4">
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-zinc-400 mb-3">Thông tin đơn hàng</h3>
                        <div class="space-y-2 text-sm text-zinc-300">
                            <div class="flex justify-between"><span>Mã đơn</span><span class="font-semibold text-white">{{ $booking->booking_code }}</span></div>
                            <div class="flex justify-between"><span>Phim</span><span class="font-semibold text-white">{{ $booking->showtime->movie->title }}</span></div>
                            <div class="flex justify-between"><span>Rạp</span><span class="font-semibold text-white">{{ $booking->showtime->room->cinema->name }}</span></div>
                            <div class="flex justify-between"><span>Tổng tiền</span><span class="font-semibold text-brand-primary">{{ number_format($booking->total_amount) }}đ</span></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-5">
                        <h3 class="text-sm font-black uppercase tracking-widest text-zinc-400 mb-3">Hướng dẫn</h3>
                        <ul class="list-disc list-inside text-sm text-zinc-300 space-y-2">
                            <li>Quét mã QR bằng ứng dụng {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }} trên điện thoại.</li>
                            <li>Nếu trình duyệt không tự chuyển, bấm nút tiếp tục bên dưới (nếu có).</li>
                            <li>Sau khi thanh toán thành công, hệ thống sẽ tự quay lại trang xác nhận.</li>
                        </ul>
                    </div>
                </div>

                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/70 p-6 flex flex-col items-center justify-center gap-4">
                    @php
                        $qrText = "FilmGo Payment\n" .
                            "Mã đơn: {$booking->booking_code}\n" .
                            "Số tiền: " . number_format($booking->total_amount) . "đ\n";
                        if ($paymentUrl) {
                            $qrText .= "Link: " . $paymentUrl;
                        }
                    @endphp
                    @if($paymentUrl)
                        <div class="w-56 h-56 rounded-2xl bg-white p-4 flex items-center justify-center">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&data={{ urlencode($qrText) }}"
                                 alt="QR code thanh toán {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}"
                                 class="max-w-full max-h-full object-contain" />
                        </div>
                        <div class="text-center text-sm text-white font-semibold">
                            <div class="text-2xl font-black mb-2">{{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</div>
                            <div class="text-xs uppercase tracking-[0.25em] text-zinc-500">QR Thanh Toán</div>
                        </div>

                        <a href="{{ $paymentUrl }}" target="_blank" rel="noopener"
                           class="w-full text-center bg-brand-primary hover:bg-red-700 text-white font-black py-3 rounded-xl transition-all duration-200 uppercase tracking-wider">
                            Tiếp tục thanh toán
                        </a>
                    @else
                        <div class="w-56 h-56 rounded-2xl bg-white p-4 flex items-center justify-center">
                            <div class="text-center text-black text-sm font-semibold">
                                <div class="text-2xl font-black mb-2">{{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</div>
                                <div class="text-xs uppercase tracking-[0.25em] text-zinc-500">QR Thanh Toán</div>
                                <div class="mt-4 text-xs text-zinc-600">Mã QR sẽ được hiển thị tại đây khi cổng thanh toán được cấu hình.</div>
                            </div>
                        </div>
                        <div class="text-sm text-zinc-400">Không có liên kết thanh toán lúc này. Vui lòng thử lại sau.</div>
                    @endif

                    <a href="{{ route('booking.checkout', $booking->showtime_id) }}"
                       class="text-sm text-zinc-500 hover:text-white transition-colors">
                        Quay lại trang checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
