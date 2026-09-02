<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Vé xem phim của bạn - FilmGo</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
    </style>
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                "on-background": "#0b1c30",
                "on-error": "#ffffff",
                "outline": "#777587",
                "inverse-surface": "#213145",
                "on-primary": "#ffffff",
                "secondary-fixed-dim": "#c0c1ff",
                "tertiary-fixed": "#ffdbcc",
                "outline-variant": "#c7c4d8",
                "on-surface-variant": "#464555",
                "on-secondary": "#ffffff",
                "on-surface": "#0b1c30",
                "primary-container": "#4f46e5",
                "tertiary-fixed-dim": "#ffb695",
                "primary": "#3525cd",
                "surface-dim": "#cbdbf5",
                "inverse-primary": "#c3c0ff",
                "primary-fixed-dim": "#c3c0ff",
                "primary-fixed": "#e2dfff",
                "surface-variant": "#d3e4fe",
                "error-container": "#ffdad6",
                "on-primary-fixed-variant": "#3323cc",
                "surface-container-lowest": "#ffffff",
                "on-secondary-fixed": "#07006c",
                "secondary": "#4648d4",
                "on-tertiary-fixed": "#351000",
                "surface-tint": "#4d44e3",
                "background": "#f8f9ff",
                "on-primary-container": "#dad7ff",
                "surface-bright": "#f8f9ff",
                "surface-container-low": "#eff4ff",
                "tertiary-container": "#a44100",
                "tertiary": "#7e3000",
                "on-tertiary": "#ffffff",
                "on-secondary-fixed-variant": "#2f2ebe",
                "on-tertiary-fixed-variant": "#7b2f00",
                "on-primary-fixed": "#0f0069",
                "secondary-container": "#6063ee",
                "surface-container": "#e5eeff",
                "secondary-fixed": "#e1e0ff",
                "surface-container-high": "#dce9ff",
                "on-tertiary-container": "#ffd2be",
                "on-secondary-container": "#fffbff",
                "inverse-on-surface": "#eaf1ff",
                "surface-container-highest": "#d3e4fe",
                "surface": "#f8f9ff",
                "on-error-container": "#93000a",
                "error": "#ba1a1a"
            },
            "fontFamily": {
                "sans": ["Inter", "Helvetica Neue", "Arial", "sans-serif"],
            }
          }
        }
      }
    </script>
</head>
<body class="bg-background font-sans text-on-surface m-0 p-0 antialiased" style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0b1c30;">

@php
    $orderTicket = $booking->bookingDetails->first()?->ticket;
    $orderQrCode = $orderTicket?->qr_code ?? $booking->booking_code;
    $qrImage     = app(\App\Services\TicketQrCodeService::class)->getQrImageUrl($orderQrCode);
    $seatsCount  = $booking->bookingDetails->count();
    $seatsList   = $booking->bookingDetails->map(function($d) {
        return optional(optional($d->showtimeSeat)->seat)->seat_row . optional(optional($d->showtimeSeat)->seat)->seat_number;
    })->filter()->implode(', ');
    $isComboOnly = ($booking->booking_type === 'combo_only') || !$booking->showtime_id;
@endphp

<div class="max-w-[600px] mx-auto bg-white my-4 md:my-8 rounded-lg shadow-sm overflow-hidden border border-slate-200" style="max-width: 600px; margin: 24px auto; background-color: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); overflow: hidden;">
    
    <!-- HEADER -->
    <div class="text-center p-8 bg-white border-b border-slate-200/80" style="text-align: center; padding: 32px 24px; background-color: #ffffff; border-bottom: 1px solid #f1f5f9;">
        <h1 class="text-2xl font-black text-indigo-600 tracking-wider mb-3 uppercase" style="font-size: 26px; font-weight: 900; color: #4f46e5; letter-spacing: 2px; margin: 0 0 12px 0; text-transform: uppercase;">FILMGO</h1>
        <div class="flex items-center justify-center gap-2 text-emerald-600 mb-2" style="display: flex; align-items: center; justify-content: center; color: #16a34a; margin-bottom: 8px;">
            <svg style="width: 36px; height: 36px; vertical-align: middle; display: inline-block; fill: #16a34a;" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
            <h2 class="text-2xl font-bold text-slate-900 m-0" style="font-size: 24px; font-weight: 700; color: #0f172a; margin: 0 0 0 8px; display: inline-block;">Đặt vé thành công!</h2>
        </div>
        <p class="text-sm text-slate-500 max-w-[85%] mx-auto leading-relaxed m-0" style="font-size: 14px; color: #64748b; line-height: 1.5; margin: 0 auto; max-width: 85%;">
            Vui lòng xuất trình mã QR bên dưới tại quầy vé hoặc máy tự động để nhận vé cứng hoặc trực tiếp quét qua cổng soát vé.
        </p>
    </div>

    <!-- DIGITAL TICKET CARD -->
    <div class="p-6" style="padding: 24px;">
        <div class="border border-slate-200 rounded-lg overflow-hidden bg-white" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background-color: #ffffff;">
            
            <!-- Ticket Header -->
            <div class="bg-slate-50 p-4 border-b border-slate-200" style="background-color: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0;">
                <h3 class="text-lg font-bold text-slate-900 mb-1 uppercase" style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; text-transform: uppercase;">
                    @if($isComboOnly)
                        ĐƠN HÀNG COMBO BẮP NƯỚC
                    @else
                        {{ optional($booking->showtime->movie)->title ?? 'PHIM CHIẾU RẠP' }}
                    @endif
                </h3>
                <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: center;">
                    <p class="text-xs text-slate-500 m-0" style="font-size: 13px; color: #64748b; margin: 0;">
                        <span class="font-semibold text-slate-800" style="font-weight: 600; color: #1e293b;">Mã vé:</span>
                        <strong style="color: #4f46e5; font-size: 14px;">{{ $booking->booking_code }}</strong>
                    </p>
                </div>
            </div>

            <!-- QR SECTION -->
            <div class="bg-[#F8FAFC] p-6 flex flex-col items-center justify-center border-b border-dashed border-slate-200 text-center" style="background-color: #f8fafc; padding: 24px; text-align: center; border-bottom: 1px dashed #cbd5e1;">
                <div class="w-48 h-48 bg-white border border-slate-200 rounded-lg p-2 shadow-sm mb-3 mx-auto flex items-center justify-center" style="width: 190px; height: 190px; margin: 0 auto 12px auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px; text-align: center;">
                    <img alt="QR Code {{ $booking->booking_code }}" class="w-full h-full object-contain" src="{{ $qrImage }}" style="width: 100%; height: 100%; object-fit: contain; display: block;">
                </div>
                <p class="text-xs text-slate-500 max-w-[80%] mx-auto m-0" style="font-size: 13px; color: #64748b; margin: 0 auto; max-width: 80%;">
                    Quét mã tại quầy vé hoặc cổng vào rạp để in vé / check-in
                </p>
            </div>

            <!-- DETAILS GRID -->
            @if(!$isComboOnly && $booking->showtime)
            <div class="p-6" style="padding: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; padding: 8px 12px 8px 0; vertical-align: top;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 2px 0;">Rạp chiếu</p>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">{{ optional(optional($booking->showtime->room)->cinema)->name ?? 'FilmGo Cinema' }}</p>
                        </td>
                        <td style="width: 50%; padding: 8px 0 8px 12px; vertical-align: top;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 2px 0;">Phòng chiếu</p>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">{{ optional($booking->showtime->room)->room_name ?? optional($booking->showtime->room)->name ?? 'Phòng chiếu' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; padding: 8px 12px 8px 0; vertical-align: top;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 2px 0;">Suất chiếu</p>
                            <p style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">
                                {{ $booking->showtime->show_date ? \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m/Y') : '' }} - {{ $booking->showtime->start_time ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '' }}
                            </p>
                        </td>
                        <td style="width: 50%; padding: 8px 0 8px 12px; vertical-align: top;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 2px 0;">Định dạng</p>
                            <span style="display: inline-block; background-color: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; border: 1px solid #dbeafe;">
                                {{ optional($booking->showtime->format)->name ?? '2D Phụ đề' }}
                            </span>
                        </td>
                    </tr>
                    @if(!empty($seatsList))
                    <tr>
                        <td colspan="2" style="padding-top: 12px; border-top: 1px solid #f1f5f9; margin-top: 6px;">
                            <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0;">Ghế ngồi</p>
                            <p style="font-size: 18px; font-weight: 800; color: #4f46e5; margin: 0; letter-spacing: 0.5px;">{{ $seatsList }}</p>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif

            <!-- COMBOS (NẾU CÓ) -->
            @if($booking->combos && $booking->combos->count() > 0)
            <div class="p-6 border-t border-slate-200" style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background-color: #ffffff;">
                <p style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0 0 10px 0; letter-spacing: 0.5px;">🍿 Combo Bắp Nước Kèm Theo</p>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    @foreach($booking->combos as $combo)
                    <tr style="border-bottom: 1px dashed #f1f5f9;">
                        <td style="padding: 6px 0; color: #1e293b; font-weight: 600;">{{ $combo->name ?? $combo->combo_name }}</td>
                        <td style="padding: 6px 12px; color: #4f46e5; font-weight: 700; text-align: center;">x{{ $combo->pivot->quantity }}</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: 700; text-align: right;">{{ number_format($combo->pivot->subtotal, 0, ',', '.') }}đ</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            {{-- HẠN SỬ DỤNG BẮP NƯỚC (chỉ hiển thị cho đơn combo_only) --}}
            @if($isComboOnly && $booking->combo_expires_at)
            <div style="margin: 0 20px 16px; padding: 14px 16px; background-color: #fff7ed; border: 1px solid #fdba74; border-radius: 8px; border-left: 4px solid #f97316;">
                <p style="font-size: 13px; font-weight: 800; color: #c2410c; margin: 0 0 6px 0; display: flex; align-items: center; gap: 6px;">
                    ⏰ Hạn Sử Dụng Đơn Bắp Nước
                </p>
                <p style="font-size: 13px; color: #9a3412; margin: 0 0 4px 0; line-height: 1.5;">
                    Đơn hàng bắp nước của bạn có hiệu lực nhận hàng trong vòng <strong>3 ngày</strong> kể từ ngày đặt.
                </p>
                <div style="font-size: 14px; color: #7c2d12; margin: 8px 0; background-color: #ffedd5; padding: 8px 12px; border-radius: 6px;">
                    <p style="margin: 0 0 4px 0;"><strong>Ngày đặt:</strong> {{ $booking->created_at->format('H:i — d/m/Y') }}</p>
                    <p style="margin: 0;"><strong>Hạn cuối:</strong> <span style="font-weight: 800; color: #b91c1c;">{{ $booking->combo_expires_at->format('H:i — d/m/Y') }}</span></p>
                </div>
                <p style="font-size: 11px; color: #b45309; margin: 6px 0 0 0;">
                    ⚠️ Vui lòng đến quầy F&amp;B trước thời hạn trên. Quá hạn sẽ không được đổi hoặc hoàn tiền.
                </p>
            </div>
            @endif

        </div>

        <!-- BILLING -->
        <div class="mt-6 border border-slate-200 rounded-lg p-4 bg-white" style="margin-top: 20px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; background-color: #ffffff;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px 0; font-size: 13px; color: #64748b;">
                        Giá vé @if($seatsCount > 0) (x{{ $seatsCount }}) @endif
                    </td>
                    <td style="padding: 4px 0; font-size: 14px; font-weight: 600; color: #0f172a; text-align: right;">
                        {{ number_format($booking->subtotal ?? $booking->total_amount, 0, ',', '.') }}đ
                    </td>
                </tr>
                <tr style="border-bottom: 1px dashed #e2e8f0;">
                    <td style="padding: 4px 0 12px 0; font-size: 13px; color: #64748b;">Giảm giá</td>
                    <td style="padding: 4px 0 12px 0; font-size: 14px; font-weight: 600; color: #16a34a; text-align: right;">
                        {{ number_format($booking->discount_amount ?? 0, 0, ',', '.') }}đ
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px 0 0 0; font-size: 15px; font-weight: 700; color: #0f172a;">Tổng thanh toán:</td>
                    <td style="padding: 12px 0 0 0; font-size: 20px; font-weight: 900; color: #4f46e5; text-align: right;">
                        {{ number_format($booking->final_total ?? $booking->total_amount, 0, ',', '.') }}đ
                    </td>
                </tr>
            </table>
        </div>

        <!-- REMINDERS -->
        <div class="mt-6 bg-[#F1F5F9] rounded-lg p-4 border border-slate-200/60" style="margin-top: 24px; background-color: #f1f5f9; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0;">
            <h4 class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-1.5" style="font-size: 14px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
                <span style="display: inline-block; vertical-align: middle; margin-right: 4px;">ℹ️</span>
                Lưu ý quan trọng
            </h4>
            <ul class="m-0 pl-5 text-xs text-slate-600 space-y-1.5 list-disc" style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.6;">
                <li>Vui lòng có mặt tại rạp chiếu ít nhất 15 phút trước giờ chiếu để thực hiện thủ tục nhận vé/check-in.</li>
                <li>Rạp có quyền từ chối khán giả nếu không đáp ứng quy định về độ tuổi xem phim của Cục Điện Ảnh.</li>
                <li>Vé đã mua không thể hoàn hoặc đổi trong mọi trường hợp (ngoại trừ sự cố kỹ thuật từ rạp).</li>
            </ul>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="bg-slate-50 border-t border-slate-200 p-6 text-center" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 16px; text-align: center;">
        <p class="text-xs text-slate-600 mb-1" style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">
            Cần hỗ trợ? Gọi hotline: <strong class="text-slate-900" style="color: #0f172a;">1900 1234</strong>
        </p>
        <p class="text-xs text-slate-600 mb-3" style="font-size: 13px; color: #64748b; margin: 0 0 12px 0;">
            Email: <a href="mailto:support@filmgo.vn" style="color: #4f46e5; text-decoration: none; font-weight: 600;">support@filmgo.vn</a>
        </p>
        <p class="text-[11px] text-slate-400 m-0" style="font-size: 11px; color: #94a3b8; margin: 0; line-height: 1.4;">
            &copy; {{ date('Y') }} FilmGo. All rights reserved.<br>
            Hệ thống đặt vé xem phim trực tuyến hàng đầu
        </p>
    </div>
</div>

</body>
</html>
