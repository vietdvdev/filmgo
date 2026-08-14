<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>In Vé - {{ $booking->booking_code }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px; color: #000;
            background: #fff;
            width: 80mm; max-width: 80mm;
        }

        /* ── Ticket wrapper ── */
        .ticket { width: 100%; padding: 6px 7px 10px; page-break-after: always; }
        .ticket:last-child { page-break-after: avoid; }

        /* ── 1. Header ── */
        .hd-logo  { font-size: 20px; font-weight: 900; letter-spacing: 3px; text-align: center; padding: 6px 0 2px; }
        .hd-sub   { font-size: 8px; text-align: center; letter-spacing: 2px; text-transform: uppercase; color: #444; }
        .hd-title {
            font-size: 10px; font-weight: 900; text-align: center;
            text-transform: uppercase; letter-spacing: 1px;
            border-top: 2px solid #000; border-bottom: 2px solid #000;
            padding: 3px 0; margin: 5px 0 4px;
        }

        /* ── 2. Cinema block ── */
        .cinema-name { font-size: 12px; font-weight: 900; }
        .cinema-line { font-size: 9px; color: #333; margin-top: 1px; }

        /* ── Dividers ── */
        .d-dash { border-top: 1px dashed #000; margin: 5px 0; }
        .d-bold { border-top: 2px solid #000; margin: 6px 0; }

        /* ── Meta rows (label : value) ── */
        .meta { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 3px; }
        .meta .l { color: #555; white-space: nowrap; }
        .meta .v { font-weight: 700; text-align: right; max-width: 58%; word-break: break-word; }

        /* ── 3. Movie title ── */
        .movie-title {
            font-size: 13px; font-weight: 900; text-align: center;
            text-transform: uppercase; line-height: 1.3; margin: 5px 0 4px;
        }

        /* ── Seat box ── */
        .seat-box { border: 2px solid #000; text-align: center; padding: 5px 4px; margin: 6px 0; }
        .seat-box .sl { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .seat-box .sn { font-size: 30px; font-weight: 900; letter-spacing: 3px; line-height: 1.1; }
        .seat-box .st { font-size: 9px; color: #666; margin-top: 2px; }

        /* ── 4. Price table ── */
        .price-tbl { width: 100%; border-collapse: collapse; font-size: 10px; margin: 4px 0; }
        .price-tbl td { padding: 2px 0; vertical-align: top; }
        .price-tbl td:last-child { text-align: right; font-weight: 700; }
        .price-tbl .sep td { border-top: 1px dashed #000; padding-top: 4px; }
        .price-tbl .total td { font-size: 12px; font-weight: 900; border-top: 1px solid #000; padding-top: 4px; }

        /* ── 5. Booking code ── */
        .bc-wrap  { text-align: center; margin: 5px 0 3px; }
        .bc-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .bc-val   { font-size: 14px; font-weight: 900; letter-spacing: 2px; font-family: monospace; }
        .bc-sub   { font-size: 8px; color: #777; margin-top: 1px; }

        /* ── 6. QR ── */
        .qr-wrap { text-align: center; margin: 6px 0 2px; }
        .qr-wrap svg, .qr-wrap img { width: 130px; height: 130px; display: inline-block; }
        .qr-text { font-size: 7px; text-align: center; color: #666; margin-top: 2px; word-break: break-all; font-family: monospace; }
        .qr-hint { font-size: 8px; text-align: center; color: #555; margin-top: 3px; }

        /* ── Footer ── */
        .footer { text-align: center; font-size: 9px; color: #555; margin-top: 6px; line-height: 1.6; }

        /* ── F&B receipt ── */
        .fnb-tbl { width: 100%; border-collapse: collapse; font-size: 11px; margin: 4px 0; }
        .fnb-tbl th { font-weight: 700; border-bottom: 1px solid #000; padding: 2px 0; text-align: left; }
        .fnb-tbl td { padding: 3px 0; border-bottom: 1px dashed #ccc; }
        .fnb-tbl td:nth-child(2) { text-align: center; }
        .fnb-tbl td:last-child   { text-align: right; font-weight: 700; }

        .notice { text-align: center; font-size: 10px; font-weight: 700; border: 1px solid #000; padding: 5px; margin-top: 6px; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print" style="padding:10px;background:#1e293b;text-align:center;position:sticky;top:0;z-index:99;">
    <button onclick="window.print()" style="padding:8px 20px;background:#3b82f6;color:#fff;border:none;font-weight:bold;border-radius:6px;cursor:pointer;font-size:13px;">
        🖨️ In Vé & Bắp Nước
    </button>
</div>

@php
    $showtime  = $booking->showtime;
    $movie     = $showtime?->movie;
    $room      = $showtime?->room;
    $startTime = $showtime?->start_time ? \Carbon\Carbon::parse($showtime->start_time)->format('H:i') : '';
    $endTime   = $showtime?->end_time   ? \Carbon\Carbon::parse($showtime->end_time)->format('H:i')   : '';
    $showDate  = $showtime?->show_date  ? \Carbon\Carbon::parse($showtime->show_date)->format('d/m/Y') : '';
    $totalTickets = count($ticketsData);
@endphp

{{-- ══════════════════════════════════════
     PHẦN A: VÉ XEM PHIM — 1 GHẾ / 1 TỜ
══════════════════════════════════════ --}}
@foreach($ticketsData as $index => $item)
<div class="ticket">

    {{-- 1. HEADER --}}
    <div class="hd-logo">★ FilmGo ★</div>
    <div class="hd-sub">Cinema &amp; Entertainment</div>
    <div class="hd-title">THẺ VÀO PHÒNG CHIẾU PHIM</div>

    {{-- 2. THÔNG TIN RẠP --}}
    <div class="cinema-name">{{ $cinema->name ?? 'FilmGo Cinema' }}</div>
    @if(!empty($cinema->address))
        <div class="cinema-line">{{ $cinema->address }}{{ !empty($cinema->city) ? ', '.$cinema->city : '' }}</div>
    @endif
    @if(!empty($cinema->phone))
        <div class="cinema-line">ĐT: {{ $cinema->phone }}</div>
    @endif

    <div class="d-dash"></div>

    <div class="meta"><span class="l">POS:</span>          <span class="v">{{ $booking->booking_code }}</span></div>
    <div class="meta"><span class="l">Studio:</span>       <span class="v">{{ $room?->room_name ?? 'N/A' }}</span></div>
    @if(!empty($room?->room_type))
    <div class="meta"><span class="l">Loại phòng:</span>   <span class="v">{{ $room->room_type }}</span></div>
    @endif

    <div class="d-bold"></div>

    {{-- 3. THÔNG TIN SUẤT CHIẾU --}}
    <div class="movie-title">{{ $movie?->title ?? 'N/A' }}</div>
    <div class="d-dash"></div>

    <div class="meta"><span class="l">Ngày chiếu:</span>   <span class="v">{{ $showDate }}</span></div>
    <div class="meta"><span class="l">Giờ chiếu:</span>    <span class="v">{{ $startTime }}{{ $endTime ? ' - '.$endTime : '' }}</span></div>
    <div class="meta"><span class="l">Phòng:</span>        <span class="v">{{ $room?->room_name ?? 'N/A' }}</span></div>

    {{-- GHẾ NGỒI --}}
    <div class="seat-box">
        <div class="sl">Ghế / Seat</div>
        <div class="sn">{{ $item['seat_name'] }}</div>
        @if(!empty($item['seat_type']))
            <div class="st">{{ $item['seat_type'] }}</div>
        @endif
    </div>

    <div class="d-dash"></div>

    {{-- 4. BẢNG GIÁ VÉ --}}
    <table class="price-tbl">
        <tr>
            <td class="l">Ticket Type</td>
            <td>{{ $item['seat_type'] ?? 'Vé thường' }}</td>
        </tr>
        <tr>
            <td class="l">Ticket Price</td>
            <td>{{ number_format($item['price'] ?? 0) }}đ</td>
        </tr>
        <tr>
            <td class="l">Service Charge</td>
            <td>0đ</td>
        </tr>
        @if(($booking->discount_amount ?? 0) > 0 && $index === 0)
        <tr class="sep">
            <td class="l">Discount</td>
            <td>-{{ number_format($booking->discount_amount) }}đ</td>
        </tr>
        @endif
        <tr class="total">
            <td>TOTAL</td>
            <td>{{ number_format($booking->final_total ?? 0) }}đ</td>
        </tr>
    </table>

    <div class="d-dash"></div>

    {{-- 5. MÃ ĐẶT CHỖ --}}
    <div class="bc-wrap">
        <div class="bc-label">Booking Code / Mã đặt chỗ</div>
        <div class="bc-val">{{ $booking->booking_code }}</div>
        <div class="bc-sub">Vé {{ $index + 1 }} / {{ $totalTickets }}</div>
    </div>

    {{-- 6. QR CODE --}}
    @if(!empty($item['qr_image']))
    <div class="qr-wrap">{!! $item['qr_image'] !!}</div>
    @endif
    <div class="qr-hint">Quét mã QR tại cửa kiểm soát vé</div>
    @if(!empty($item['qr_code']))
    <div class="qr-text">{{ $item['qr_code'] }}</div>
    @endif

    <div class="d-bold"></div>

    {{-- FOOTER --}}
    <div class="footer">
        Cảm ơn quý khách đã chọn FilmGo!<br>
        Vui lòng giữ vé khi vào rạp.<br>
        ★ Chúc quý khách xem phim vui vẻ ★
    </div>

</div>
@endforeach


{{-- ══════════════════════════════════════
     PHẦN B: PHIẾU NHẬN BẮP NƯỚC (F&B)
══════════════════════════════════════ --}}
@if(($includeFnb ?? true) && isset($booking->combos) && $booking->combos->count() > 0)
<div class="ticket">

    <div class="hd-logo">★ FilmGo ★</div>
    <div class="hd-sub">Cinema &amp; Entertainment</div>
    <div class="hd-title">PHIẾU NHẬN BẮP NƯỚC</div>

    <div class="cinema-name">{{ $cinema->name ?? 'FilmGo Cinema' }}</div>
    @if(!empty($cinema->address))
        <div class="cinema-line">{{ $cinema->address }}</div>
    @endif

    <div class="d-dash"></div>

    <div class="meta"><span class="l">Mã đơn:</span>       <span class="v">{{ $booking->booking_code }}</span></div>
    <div class="meta"><span class="l">Khách hàng:</span>   <span class="v">{{ $booking->user?->full_name ?? 'Khách vãng lai' }}</span></div>
    <div class="meta"><span class="l">Thời gian:</span>    <span class="v">{{ $booking->created_at?->format('H:i d/m/Y') }}</span></div>
    <div class="meta"><span class="l">Phim:</span>         <span class="v">{{ $movie?->title ?? 'N/A' }}</span></div>
    <div class="meta"><span class="l">Suất chiếu:</span>   <span class="v">{{ $startTime }} - {{ $showDate }}</span></div>

    <div class="d-bold"></div>

    <table class="fnb-tbl">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th style="text-align:center;width:24px;">SL</th>
                <th style="text-align:right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($booking->combos as $combo)
            <tr>
                <td style="font-weight:700;">{{ $combo->combo_name }}</td>
                <td style="text-align:center;font-weight:900;font-size:13px;">{{ $combo->pivot->quantity }}</td>
                <td>{{ number_format($combo->pivot->subtotal) }}đ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-bold"></div>

    <div class="notice">Vui lòng đưa phiếu này cho nhân viên quầy Concession</div>

    <div class="footer" style="margin-top:8px;">-- Cảm ơn quý khách --</div>

</div>
@endif

<script>
    window.onload = function () { window.print(); };
</script>
</body>
</html>
