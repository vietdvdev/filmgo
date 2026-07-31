<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đơn hàng - FilmGo</title>
    <style>
        body {
            background-color: #0F0F0F;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #FFFFFF;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #1A1A1A;
            border: 1px solid #2A2A2A;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #E50914;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #FFFFFF;
            text-decoration: none;
            text-transform: uppercase;
        }
        .brand-primary {
            color: #E50914;
        }
        .content {
            font-size: 15px;
            line-height: 1.6;
            color: #CCCCCC;
            margin-bottom: 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: bold;
            color: #FFFFFF;
            margin-bottom: 15px;
        }
        /* Badge */
        .badge-success {
            display: inline-block;
            background-color: #14532d;
            color: #4ade80;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        /* Booking Code */
        .code-box {
            border: 2px dashed #E50914;
            padding: 16px;
            text-align: center;
            margin: 20px 0;
        }
        .code-label {
            font-size: 11px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .code-value {
            font-size: 26px;
            font-weight: 900;
            color: #E50914;
            letter-spacing: 4px;
            margin-top: 6px;
        }
        /* Section Title */
        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: #FFFFFF;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #2A2A2A;
            padding-bottom: 8px;
            margin-top: 28px;
            margin-bottom: 14px;
        }
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-table td {
            padding: 8px 0;
            font-size: 14px;
            vertical-align: top;
            border-bottom: 1px solid #2A2A2A;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .label-col {
            color: #888888;
            width: 38%;
            font-weight: 500;
        }
        .value-col {
            color: #FFFFFF;
            font-weight: 600;
            width: 62%;
        }
        /* Seat Badge */
        .seat-badge {
            display: inline-block;
            background-color: #2A2A2A;
            color: #E50914;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 4px;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
        /* Combo Table */
        .item-list {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .item-list th {
            background-color: #111111;
            color: #888888;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px;
            text-align: left;
        }
        .item-list td {
            padding: 10px;
            border-bottom: 1px solid #2A2A2A;
            font-size: 13px;
            color: #CCCCCC;
        }
        .item-list tr:last-child td {
            border-bottom: none;
        }
        /* Total Box */
        .total-box {
            background-color: #111111;
            border: 1px solid #2A2A2A;
            padding: 16px 20px;
            margin-top: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 5px 0;
            color: #888888;
        }
        .final-total {
            font-size: 18px;
            font-weight: 900;
            color: #E50914;
            border-top: 1px solid #2A2A2A;
            padding-top: 10px;
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        /* Notice */
        .notice-box {
            border-left: 3px solid #E50914;
            background-color: #1f0a0a;
            padding: 12px 16px;
            margin-top: 24px;
            font-size: 13px;
            color: #CCCCCC;
            line-height: 1.6;
        }
        /* Footer */
        .footer {
            font-size: 12px;
            color: #666666;
            text-align: center;
            border-top: 1px solid #2A2A2A;
            padding-top: 20px;
            margin-top: 40px;
            line-height: 1.5;
        }
        .footer a {
            color: #E50914;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <span class="logo-text">FILM<span class="brand-primary">GO</span></span>
        </div>

        <!-- Content -->
        <div class="content">
            <span class="badge-success">✓ Thanh toán thành công</span>

            <div class="greeting">Xin chào {{ optional($booking->user)->full_name ?? 'Quý khách' }},</div>

            <p>Cảm ơn bạn đã tin tưởng và sử dụng dịch vụ tại <strong style="color:#FFFFFF;">FilmGo</strong>. Đơn hàng của bạn đã được xác nhận thành công. Vui lòng lưu lại mã đơn hàng bên dưới để sử dụng khi đến rạp.</p>

            <!-- Booking Code -->
            <div class="code-box">
                <div class="code-label">Mã đơn hàng xác nhận</div>
                <div class="code-value">{{ $booking->booking_code }}</div>
            </div>

            <!-- Movie & Showtime -->
            @if($booking->showtime)
            <div class="section-title">🎬 Thông tin vé xem phim</div>
            <table class="info-table">
                <tr>
                    <td class="label-col">Tên phim:</td>
                    <td class="value-col">{{ optional($booking->showtime->movie)->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Rạp chiếu:</td>
                    <td class="value-col">{{ optional(optional($booking->showtime->room)->cinema)->name ?? 'FilmGo Cinema' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Phòng chiếu:</td>
                    <td class="value-col">{{ optional($booking->showtime->room)->name ?? 'Phòng chiếu' }}</td>
                </tr>
                <tr>
                    <td class="label-col">Suất chiếu:</td>
                    <td class="value-col">
                        {{ \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m/Y') }}
                        &nbsp;|&nbsp;
                        {{ \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') }} &ndash; {{ \Carbon\Carbon::parse($booking->showtime->end_time)->format('H:i') }}
                    </td>
                </tr>
                @if($booking->bookingDetails->count() > 0)
                <tr>
                    <td class="label-col">Ghế đã chọn:</td>
                    <td class="value-col">
                        @foreach($booking->bookingDetails as $detail)
                            @if($detail->showtimeSeat && $detail->showtimeSeat->seat)
                                <span class="seat-badge">
                                    {{ $detail->showtimeSeat->seat->seat_row }}{{ $detail->showtimeSeat->seat->seat_number }}
                                </span>
                            @endif
                        @endforeach
                    </td>
                </tr>
                @endif
            </table>
            @endif

            <!-- Combo F&B -->
            @if($booking->combos && $booking->combos->count() > 0)
            <div class="section-title">🍿 Combo bắp nước đã đặt</div>
            <table class="item-list">
                <thead>
                    <tr>
                        <th>Tên combo</th>
                        <th style="text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->combos as $combo)
                    <tr>
                        <td><strong style="color:#FFFFFF;">{{ $combo->name }}</strong></td>
                        <td style="text-align: center; color:#FFFFFF;">x{{ $combo->pivot->quantity }}</td>
                        <td style="text-align: right; color:#E50914; font-weight:700;">{{ number_format($combo->pivot->subtotal, 0, ',', '.') }} đ</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

            <!-- Payment Summary -->
            <div class="section-title">💳 Chi tiết thanh toán</div>
            <div class="total-box">
                <div class="total-row">
                    <span>Tạm tính:</span>
                    <span>{{ number_format($booking->subtotal ?? $booking->total_amount, 0, ',', '.') }} đ</span>
                </div>
                @if(($booking->discount_amount ?? 0) > 0)
                <div class="total-row" style="color: #4ade80;">
                    <span>Giảm giá (Voucher):</span>
                    <span>-{{ number_format($booking->discount_amount, 0, ',', '.') }} đ</span>
                </div>
                @endif
                <div class="final-total">
                    <span>Tổng thanh toán:</span>
                    <span>{{ number_format($booking->final_total ?? $booking->total_amount, 0, ',', '.') }} đ</span>
                </div>
            </div>

            <!-- Notice -->
            <div class="notice-box">
                📌 <strong style="color:#FFFFFF;">Hướng dẫn nhận vé / bắp nước:</strong>
                Vui lòng xuất trình mã đơn hàng <strong style="color:#E50914;">{{ $booking->booking_code }}</strong>
                cho nhân viên tại quầy rạp để làm thủ tục in vé cứng và nhận combo bắp nước trước giờ chiếu phim.
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ hotline <strong>1900 1234</strong> hoặc email <a href="mailto:support@filmgo.vn">support@filmgo.vn</a>.</p>
            <p>Đây là email tự động từ hệ thống FilmGo. Vui lòng không phản hồi email này.</p>
            <p>&copy; {{ date('Y') }} FilmGo Project. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
