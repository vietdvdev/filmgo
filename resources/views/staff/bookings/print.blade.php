<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vé - {{ $booking->booking_code }}</title>
    <style>
        /* ═════════════════════════════════════════════════════════════════════
           1. RESET & BASE STYLES FOR 80mm THERMAL RECEIPT PRINTER (K80)
        ═════════════════════════════════════════════════════════════════════ */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        html, body {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 0;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, 'Helvetica Neue', Arial, sans-serif, 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ═════════════════════════════════════════════════════════════════════
           2. STRICT CSS PRINT CONFIGURATION (@media print)
           - Chống lỗi thừa trang trắng khổ A4
           - Đảm bảo in đen trắng sắc nét trên giấy in nhiệt 80mm
        ═════════════════════════════════════════════════════════════════════ */
        @media print {
            /* Ẩn toàn bộ mọi element trên giao diện */
            body * {
                visibility: hidden;
            }

            /* Chỉ hiển thị duy nhất container #ticket-print-area và các thành phần con */
            #ticket-print-area,
            #ticket-print-area * {
                visibility: visible;
            }

            /* Đưa khung in về góc trên cùng bên trái của trang in nhiệt */
            #ticket-print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                max-width: 80mm;
                margin: 0;
                padding: 0;
                background: #ffffff !important;
            }

            /* Ẩn thanh công cụ / nút bấm */
            .no-print {
                display: none !important;
                visibility: hidden !important;
            }
        }

        /* ═════════════════════════════════════════════════════════════════════
           3. TICKET CONTAINER & CARD STYLING (80mm / ~300px)
        ═════════════════════════════════════════════════════════════════════ */
        .ticket-card {
            width: 80mm;
            max-width: 80mm;
            padding: 8px 10px 12px;
            margin: 0 auto;
            background: #ffffff;
            color: #000000;
            box-sizing: border-box;
            page-break-after: always;
            break-after: page;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .ticket-card:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }

        /* ── Header ── */
        .cinema-logo {
            font-size: 20px;
            font-weight: 900;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .cinema-subtext {
            font-size: 8px;
            text-align: center;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .cinema-branch {
            font-size: 11px;
            font-weight: 800;
            text-align: center;
        }

        .cinema-address {
            font-size: 9px;
            text-align: center;
            line-height: 1.2;
            margin-top: 1px;
        }

        .ticket-badge {
            font-size: 10px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 2px solid #000000;
            border-bottom: 2px solid #000000;
            padding: 4px 0;
            margin: 6px 0;
        }

        /* ── Dividers ── */
        .divider-dashed {
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }

        .divider-solid {
            border-top: 2px solid #000000;
            margin: 6px 0;
        }

        /* ── Movie Info & Alignment ── */
        .movie-title {
            font-size: 14px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.25;
            margin: 4px 0;
            word-wrap: break-word;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .info-label {
            font-weight: 600;
            white-space: nowrap;
        }

        .info-value {
            font-weight: 800;
            text-align: right;
            max-width: 62%;
            word-break: break-word;
        }

        /* ── SEAT HIGHLIGHT (CRITICAL: FOR USHERS IN THE DARK) ── */
        .seat-box {
            border: 3px solid #000000;
            border-radius: 4px;
            text-align: center;
            padding: 6px 4px;
            margin: 8px 0;
            background: #ffffff;
        }

        .seat-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .seat-number {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: 2px;
            line-height: 1.1;
            margin: 2px 0;
        }

        .seat-type-tag {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Financial Table ── */
        .price-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 4px 0;
        }

        .price-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .price-table td:last-child {
            text-align: right;
            font-weight: 800;
        }

        .price-table .total-row td {
            font-size: 13px;
            font-weight: 900;
            border-top: 1.5px solid #000000;
            padding-top: 4px;
        }

        /* ── Booking Code & QR ── */
        .booking-code-box {
            text-align: center;
            margin: 6px 0 4px;
        }

        .booking-code-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .booking-code-val {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 2px;
            font-family: monospace;
        }

        .qr-container {
            text-align: center;
            margin: 6px auto 2px;
        }

        .qr-container svg,
        .qr-container img {
            width: 125px;
            height: 125px;
            margin: 0 auto;
            display: block;
        }

        .qr-hint {
            font-size: 8px;
            text-align: center;
            font-weight: 700;
            margin-top: 3px;
        }

        .qr-text {
            font-size: 7.5px;
            text-align: center;
            font-family: monospace;
            font-weight: 600;
            margin-top: 2px;
            word-break: break-all;
        }

        /* ── Footer ── */
        .ticket-footer {
            text-align: center;
            font-size: 9px;
            margin-top: 6px;
            line-height: 1.5;
            font-weight: 600;
        }

        /* ── F&B Receipt Table ── */
        .fnb-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin: 5px 0;
        }

        .fnb-table th {
            font-weight: 800;
            border-bottom: 1.5px solid #000000;
            padding: 3px 0;
            text-align: left;
        }

        .fnb-table td {
            padding: 3px 0;
            border-bottom: 1px dashed #000000;
        }
    </style>
</head>
<body>

{{-- NÚT ĐIỀU KHIỂN (ẨN KHI IN) --}}
<div class="no-print" style="padding: 12px; background: #0f172a; text-align: center; position: sticky; top: 0; z-index: 999; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    <button onclick="printTicket()" style="padding: 10px 24px; background: #2563eb; color: #ffffff; border: none; font-weight: 800; border-radius: 8px; cursor: pointer; font-size: 14px; display: inline-flex; items-center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
        🖨️ In Vé Ngay (K80)
    </button>
    <button onclick="window.close()" style="margin-left: 8px; padding: 10px 18px; background: #475569; color: #ffffff; border: none; font-weight: 700; border-radius: 8px; cursor: pointer; font-size: 13px;">
        Đóng
    </button>
</div>

@php
    $showtime     = $booking->showtime;
    $movie        = $showtime?->movie;
    $room         = $showtime?->room;
    $startTime    = $showtime?->start_time ? \Carbon\Carbon::parse($showtime->start_time)->format('H:i') : '';
    $endTime      = $showtime?->end_time   ? \Carbon\Carbon::parse($showtime->end_time)->format('H:i')   : '';
    $showDate     = $showtime?->show_date  ? \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') : '';
    $totalTickets = count($ticketsData);
@endphp

{{-- ═════════════════════════════════════════════════════════════════════
     KHU VỰC IN CHÍNH: #ticket-print-area (ĐƯỢC BẢO LƯU KHI IN NHIỆT K80)
═════════════════════════════════════════════════════════════════════ --}}
<div id="ticket-print-area">

    {{-- ══════════════════════════════════════
         PHẦN A: VÉ XEM PHIM (1 GHẾ / 1 TỜ VÉ)
    ══════════════════════════════════════ --}}
    @foreach($ticketsData as $index => $item)
    <div class="ticket-card">

        {{-- 1. HEADER --}}
        <div class="cinema-logo">★ FilmGo ★</div>
        <div class="cinema-subtext">CINEMA &amp; ENTERTAINMENT</div>
        
        <div class="cinema-branch">{{ $cinema->name ?? 'FilmGo Cinema' }}</div>
        @if(!empty($cinema->address))
            <div class="cinema-address">{{ $cinema->address }}{{ !empty($cinema->city) ? ', '.$cinema->city : '' }}</div>
        @endif
        @if(!empty($cinema->phone))
            <div class="cinema-address">Hotline: {{ $cinema->phone }}</div>
        @endif

        <div class="ticket-badge">THẺ VÀO PHÒNG CHIẾU PHIM</div>

        {{-- 2. THÔNG TIN PHIM & PHÒNG CHIẾU --}}
        <div class="movie-title">{{ $movie?->title ?? 'N/A' }}</div>
        
        <div class="divider-dashed"></div>

        <div class="info-row">
            <span class="info-label">Ngày chiếu:</span>
            <span class="info-value">{{ $showDate }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Suất chiếu:</span>
            <span class="info-value">{{ $startTime }}{{ $endTime ? ' - '.$endTime : '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phòng chiếu:</span>
            <span class="info-value">{{ $room?->room_name ?? 'N/A' }} {{ !empty($room?->room_type) ? '('.$room->room_type.')' : '' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nhân viên in:</span>
            <span class="info-value">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'Hệ thống' }}</span>
        </div>

        {{-- 3. KHỐI NỔI BẬT VỊ TRÍ GHẾ (SEAT HIGHLIGHT - CRITICAL FOR USHERS) --}}
        <div class="seat-box">
            <div class="seat-label">Ghế / Seat</div>
            <div class="seat-number">{{ $item['seat_name'] }}</div>
            @if(!empty($item['seat_type']))
                <div class="seat-type-tag">[{{ $item['seat_type'] }}]</div>
            @endif
        </div>

        <div class="divider-dashed"></div>

        {{-- 4. BẢNG GIÁ VÉ CHI TIẾT --}}
        <table class="price-table">
            <tr>
                <td class="info-label">Loại vé (Ticket Type):</td>
                <td>{{ $item['seat_type'] ?? 'Vé thường' }}</td>
            </tr>
            <tr>
                <td class="info-label">Giá vé (Price):</td>
                <td>{{ number_format($item['price'] ?? 0) }}đ</td>
            </tr>
            <tr>
                <td class="info-label">Phí dịch vụ (Service):</td>
                <td>0đ</td>
            </tr>
            @if(($booking->discount_amount ?? 0) > 0 && $index === 0)
            <tr>
                <td class="info-label">Khuyến mãi (Discount):</td>
                <td>-{{ number_format($booking->discount_amount) }}đ</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>TỔNG TIỀN (TOTAL):</td>
                <td>{{ number_format($booking->final_total ?? 0) }}đ</td>
            </tr>
        </table>

        <div class="divider-dashed"></div>

        {{-- 5. MÃ ĐẶT CHỖ (BOOKING CODE) --}}
        <div class="booking-code-box">
            <div class="booking-code-label">Booking Code / Mã đặt chỗ</div>
            <div class="booking-code-val">{{ $booking->booking_code }}</div>
            <div style="font-size: 8px; font-weight: 700; margin-top: 1px;">(Vé {{ $index + 1 }} / {{ $totalTickets }})</div>
        </div>

        {{-- 6. MÃ QR CHECK-IN --}}
        @if(!empty($item['qr_image']))
            <div class="qr-container">
                {!! $item['qr_image'] !!}
            </div>
        @endif
        <div class="qr-hint">Quét mã QR tại cửa kiểm soát để vào phòng</div>
        @if(!empty($item['qr_code']))
            <div class="qr-text">{{ $item['qr_code'] }}</div>
        @endif

        <div class="divider-solid"></div>

        {{-- 7. FOOTER --}}
        <div class="ticket-footer">
            Cảm ơn quý khách đã chọn FilmGo!<br>
            Vui lòng giữ vé suốt thời gian xem phim.<br>
            ★ Chúc quý khách xem phim vui vẻ! ★
        </div>

    </div>
    @endforeach

    {{-- ══════════════════════════════════════
         PHẦN B: PHIẾU NHẬN BẮP NƯỚC (F&B) NẾU CÓ
    ══════════════════════════════════════ --}}
    @if(($includeFnb ?? true) && isset($booking->combos) && $booking->combos->count() > 0)
    <div class="ticket-card">

        <div class="cinema-logo">★ FilmGo ★</div>
        <div class="cinema-subtext">CINEMA &amp; ENTERTAINMENT</div>
        
        <div class="cinema-branch">{{ $cinema->name ?? 'FilmGo Cinema' }}</div>
        @if(!empty($cinema->address))
            <div class="cinema-address">{{ $cinema->address }}</div>
        @endif

        <div class="ticket-badge">PHIẾU NHẬN BẮP NƯỚC (F&amp;B)</div>

        <div class="info-row">
            <span class="info-label">Mã đơn:</span>
            <span class="info-value">{{ $booking->booking_code }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Khách hàng:</span>
            <span class="info-value">{{ $booking->user?->name ?? 'Khách vãng lai' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Thời gian:</span>
            <span class="info-value">{{ $booking->created_at?->format('H:i d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phim:</span>
            <span class="info-value">{{ $movie?->title ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Suất chiếu:</span>
            <span class="info-value">{{ $startTime }} ({{ $showDate }})</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nhân viên in:</span>
            <span class="info-value">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'Hệ thống' }}</span>
        </div>

        <div class="divider-solid"></div>

        <table class="fnb-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align: center; width: 30px;">SL</th>
                    <th style="text-align: right;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->combos as $combo)
                <tr>
                    <td style="font-weight: 700;">{{ $combo->combo_name }}</td>
                    <td style="text-align: center; font-weight: 900; font-size: 13px;">{{ $combo->pivot->quantity }}</td>
                    <td>{{ number_format($combo->pivot->subtotal) }}đ</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider-solid"></div>

        <div style="text-align: center; font-size: 10px; font-weight: 800; border: 2px solid #000000; padding: 6px; margin-top: 6px;">
            Vui lòng đưa phiếu này cho nhân viên quầy Concession
        </div>

        <div style="text-align: center; font-size: 9.5px; font-weight: 700; margin-top: 6px; text-transform: uppercase;">
            * Vui lòng bảo quản vé vì không được in lại *
        </div>

        <div class="ticket-footer" style="margin-top: 8px;">
            -- Cảm ơn quý khách đã mua hàng --
        </div>

    </div>
    @endif

</div>

{{-- ═════════════════════════════════════════════════════════════════════
     4. JAVASCRIPT PRINT TRIGGER
═════════════════════════════════════════════════════════════════════ --}}
<script>
    /**
     * Kích hoạt hộp thoại in nhiệt của trình duyệt
     */
    function printTicket() {
        window.print();
    }

    // Tự động mở hộp thoại in sau khi trang load xong
    window.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            printTicket();
        }, 300);
    });
</script>

</body>
</html>
