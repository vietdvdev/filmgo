<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Vé & Phiếu Bắp Nước - {{ $booking->booking_code }}</title>
    <style>
        /* CSS DÀNH RIÊNG CHO MÁY IN NHIỆT (THERMAL PRINTER 80MM) */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', Courier, 'DejaVu Sans', monospace;
            font-size: 11px;
            color: #000000;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            width: 80mm;
            max-width: 80mm;
            box-sizing: border-box;
        }

        .thermal-ticket {
            width: 100%;
            padding: 10px 8px;
            box-sizing: border-box;
            page-break-after: always;
            border-bottom: 1px dashed #000000;
        }

        .thermal-ticket:last-child {
            page-break-after: avoid;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }

        .cinema-info {
            font-size: 10px;
            margin-top: 2px;
            margin-bottom: 6px;
        }

        .divider {
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }

        .divider-double {
            border-top: 2px solid #000000;
            margin: 8px 0;
        }

        /* Khối hiển thị vị trí ghế in KHỔ LỚN */
        .seat-large-box {
            text-align: center;
            border: 2px solid #000000;
            padding: 6px;
            margin: 8px 0;
        }

        .seat-large-title {
            font-size: 10px;
            text-transform: uppercase;
        }

        .seat-large-number {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .qr-container {
            text-align: center;
            margin-top: 8px;
            margin-bottom: 4px;
        }

        .qr-container svg, .qr-container img {
            width: 140px;
            height: 140px;
            display: inline-block;
        }

        .fnb-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .combo-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .combo-table th, .combo-table td {
            font-size: 11px;
            padding: 3px 0;
            text-align: left;
        }

        /* Ẩn các phần thừa khi bấm in */
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Thanh điều hướng Hỗ trợ In lại (Ẩn khi bật hộp thoại in) --}}
    <div class="no-print" style="padding: 10px; background: #333; text-align: center; color: #fff; position: sticky; top: 0;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #004be3; color: #fff; border: none; font-weight: bold; border-radius: 4px; cursor: pointer;">
            🖨️ Bấm vào đây để In Vé & Bắp Nước
        </button>
    </div>

    {{-- ── PHẦN A: LÔ VÉ XEM PHIM (MỖI VÉ TRÊN 1 DẢI IN NGẮT TRANG) ── --}}
    @foreach($ticketsData as $index => $item)
        <div class="thermal-ticket">
            
            {{-- Header Rạp phim --}}
            <div class="text-center">
                <div class="header-title">FILMGO CINEMA</div>
                <div class="cinema-info">{{ $cinema->name ?? 'Rạp FilmGo' }}</div>
                <div style="font-size: 9px;">Vé {{ $index + 1 }}/{{ count($ticketsData) }} - Đơn: {{ $booking->booking_code }}</div>
            </div>

            <div class="divider"></div>

            {{-- VỊ TRÍ GHẾ IN KHỔ LỚN BẮT MẮT --}}
            <div class="seat-large-box">
                <div class="seat-large-title">VỊ TRÍ CHỖ NGỒI</div>
                <div class="seat-large-number">GHẾ {{ $item['seat_name'] }}</div>
            </div>

            {{-- Thông tin Phim & Suất chiếu --}}
            <div style="margin: 6px 0;">
                <div class="bold" style="font-size: 13px; margin-bottom: 2px;">{{ $booking->showtime?->movie?->title ?? 'N/A' }}</div>
                <div>Phòng: <span class="bold">{{ $booking->showtime?->room?->room_name ?? 'N/A' }}</span></div>
                <div>Suất: <span class="bold">{{ $booking->showtime?->start_time ? \Carbon\Carbon::parse($booking->showtime->start_time)->format('H:i') : '' }}</span> - Ngày: {{ $booking->showtime?->show_date ? \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m/Y') : '' }}</div>
            </div>

            <div class="divider"></div>

            {{-- Thông tin Khách hàng --}}
            <div style="font-size: 10px;">
                <div>Khách: {{ $booking->user?->full_name ?? 'Khách vãng lai' }}</div>
                <div>SĐT: {{ $booking->user?->phone ?? 'N/A' }}</div>
            </div>

            {{-- MÃ QR CODE KIỂM SOÁT CỔNG RIÊNG TỪNG VÉ --}}
            <div class="qr-container">
                @if(!empty($item['qr_image']))
                    {!! $item['qr_image'] !!}
                @endif
                <div style="font-size: 9px; font-family: monospace; margin-top: 2px;">
                    {{ $item['qr_code'] ?? ($booking->booking_code.'-'.$item['seat_name']) }}
                </div>
            </div>

            <div class="divider"></div>

            <div class="text-center" style="font-size: 9px;">
                Cảm ơn quý khách & Chúc xem phim vui vẻ!<br>
                (Quét mã QR tại cửa kiểm soát vé)
            </div>
        </div>
    @endforeach


    {{-- ── PHẦN B: PHIẾU NHẬN BẮP NƯỚC (F&B RECEIPT) ── --}}
    @if(isset($booking->combos) && $booking->combos->count() > 0)
        <div class="thermal-ticket">
            
            <div class="text-center">
                <div class="cinema-info">{{ $cinema->name ?? 'Rạp FilmGo' }}</div>
                <div class="fnb-title">PHIẾU NHẬN BẮP NƯỚC</div>
                <div style="font-size: 9px;">Mã đơn: <span class="bold">{{ $booking->booking_code }}</span></div>
            </div>

            <div class="divider-double"></div>

            <div style="font-size: 10px; margin-bottom: 4px;">
                <div>Khách hàng: <span class="bold">{{ $booking->user?->full_name ?? 'Khách vãng lai' }}</span></div>
                <div>Ngày tạo: {{ $booking->created_at?->format('H:i d/m/Y') }}</div>
            </div>

            <div class="divider"></div>

            {{-- Danh sách Combo bắp nước --}}
            <div class="bold" style="margin-bottom: 4px; font-size: 11px;">DANH SÁCH MÓN ĐẶT:</div>
            <table class="combo-table">
                <thead>
                    <tr style="border-bottom: 1px solid #000;">
                        <th style="width: 25%;">SL</th>
                        <th>Tên Combo / Sản Phẩm</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->combos as $combo)
                        <tr>
                            <td class="bold" style="font-size: 13px;">{{ $combo->pivot->quantity }}x</td>
                            <td class="bold" style="font-size: 12px;">{{ $combo->combo_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="divider-double"></div>

            <div class="text-center bold" style="font-size: 10px; margin-top: 6px; padding: 4px; border: 1px solid #000;">
                Vui lòng đưa phiếu này cho nhân viên quầy Concession
            </div>

            <div class="text-center" style="font-size: 9px; margin-top: 8px;">
                -- Cảm ơn quý khách --
            </div>

        </div>
    @endif

    {{-- TỰ ĐỘNG BẬT HỘP THOẠI MÁY IN TRÊN TRÌNH DUYỆT --}}
    <script>
        window.onload = function () {
            // Tự động bật hộp thoại in máy in nhiệt khi trang vừa tải xong
            window.print();
        };
    </script>
</body>
</html>
