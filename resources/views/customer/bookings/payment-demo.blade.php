@extends('layouts.customer')

@section('title', 'Thanh Toán Demo - FilmGo')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-850 font-sans antialiased py-10 selection:bg-brand-primary selection:text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-slate-200 rounded-none shadow-sm overflow-hidden">
            <div class="bg-brand-primary px-6 py-4">
                <h2 class="text-lg font-black uppercase tracking-widest text-white">Thanh Toán Giả Lập {{ $provider === 'momo' ? 'MoMo' : 'VNPay' }}</h2>
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
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-3">Hướng dẫn demo</h3>
                        <ul class="list-disc list-inside text-sm text-slate-600 space-y-2 font-medium leading-relaxed">
                            <li>Trang này mô phỏng bước quét QR và hoàn tất thanh toán.</li>
                            <li>Bấm "Hoàn tất thanh toán" để giả lập giao dịch thành công.</li>
                            <li>Sau đó hệ thống sẽ chuyển về trang xác nhận.</li>
                        </ul>
                    </div>
                </div>

                <div class="rounded-none border border-slate-200 bg-slate-50 p-6 flex flex-col items-center justify-center gap-4">
                    <div class="w-56 h-56 rounded-none bg-white p-4 flex items-center justify-center border border-slate-200 shadow-sm">
                        <div class="text-center text-slate-800">
                            <div class="text-3xl font-black mb-3 text-slate-900">Demo</div>
                            <div class="text-xs uppercase tracking-[0.25em] text-slate-400 font-bold">Thanh toán</div>
                            <div class="mt-4 text-sm font-semibold text-slate-700">Quét mã QR</div>
                        </div>
                    </div>

                    <form id="demo-payment-form" action="{{ route('booking.payment.demo.complete', ['booking_id' => $booking->id, 'provider' => $provider]) }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="signature_data" id="signature_data" value="" />

                        <div class="rounded-none border border-slate-200 bg-white p-4 mb-5 text-left shadow-sm">
                            <label class="block text-sm font-black uppercase tracking-widest text-slate-600 mb-3">Ký tên xác nhận thanh toán</label>
                            <div class="bg-white rounded-none border border-slate-300 p-2">
                                <canvas id="signatureCanvas" width="320" height="220" class="w-full rounded-none border border-slate-200"></canvas>
                            </div>
                            <div class="flex items-center justify-between gap-3 mt-3 text-xs text-slate-500 font-medium">
                                <span>Kéo chuột hoặc chạm vào vùng trên để ký.</span>
                                <button type="button" id="clearSignature" class="text-brand-primary hover:underline font-bold">Xóa chữ ký</button>
                            </div>
                        </div>

                        <button id="submitDemoPayment" type="submit" disabled class="w-full bg-brand-primary hover:bg-red-700 text-white font-black py-3.5 rounded-none shadow-md shadow-brand-primary/20 transition-all duration-200 uppercase tracking-wider text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                            Hoàn tất thanh toán giả lập
                        </button>
                    </form>

                    <a href="{{ route('booking.checkout', $booking->showtime_id) }}"
                       class="text-xs text-slate-500 hover:text-brand-primary transition-colors font-bold uppercase tracking-wider mt-2">
                        Quay lại trang checkout
                    </a>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const canvas = document.getElementById('signatureCanvas');
                            const ctx = canvas.getContext('2d');
                            const signatureInput = document.getElementById('signature_data');
                            const submitButton = document.getElementById('submitDemoPayment');
                            const clearButton = document.getElementById('clearSignature');
                            let drawing = false;
                            let signed = false;

                            ctx.strokeStyle = '#000';
                            ctx.lineWidth = 2;
                            ctx.lineCap = 'round';

                            function resizeCanvas() {
                                const dataUrl = canvas.toDataURL();
                                canvas.width = canvas.offsetWidth;
                                canvas.height = 220;
                                const img = new Image();
                                img.onload = function () {
                                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                                };
                                img.src = dataUrl;
                            }

                            function updateSubmitState() {
                                submitButton.disabled = !signed;
                            }

                            function startPosition(event) {
                                drawing = true;
                                signed = true;
                                updateSubmitState();
                                ctx.beginPath();
                                ctx.moveTo(getX(event), getY(event));
                                event.preventDefault();
                            }

                            function finishedPosition(event) {
                                if (!drawing) return;
                                drawing = false;
                                ctx.closePath();
                                event.preventDefault();
                            }

                            function draw(event) {
                                if (!drawing) return;
                                ctx.lineTo(getX(event), getY(event));
                                ctx.stroke();
                                event.preventDefault();
                            }

                            function getX(event) {
                                const rect = canvas.getBoundingClientRect();
                                return (event.touches ? event.touches[0].clientX : event.clientX) - rect.left;
                            }

                            function getY(event) {
                                const rect = canvas.getBoundingClientRect();
                                return (event.touches ? event.touches[0].clientY : event.clientY) - rect.top;
                            }

                            function clearSignature() {
                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                                signatureInput.value = '';
                                signed = false;
                                updateSubmitState();
                            }

                            canvas.addEventListener('mousedown', startPosition);
                            canvas.addEventListener('touchstart', startPosition);
                            canvas.addEventListener('mouseup', finishedPosition);
                            canvas.addEventListener('touchend', finishedPosition);
                            canvas.addEventListener('mousemove', draw);
                            canvas.addEventListener('touchmove', draw);
                            canvas.addEventListener('mouseleave', finishedPosition);

                            clearButton.addEventListener('click', clearSignature);

                            document.getElementById('demo-payment-form').addEventListener('submit', function () {
                                signatureInput.value = canvas.toDataURL('image/png');
                            });

                            updateSubmitState();
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
