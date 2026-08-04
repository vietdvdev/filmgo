<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biên Lai Combo - {{ $booking->booking_code }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        html, body { margin: 0; padding: 0; width: 80mm; max-width: 80mm; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; background: #fff; }
        .wrap { width: 100%; padding: 8px 6px; box-sizing: border-box; }
        .header-title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 4px; letter-spacing: 1px; }
        .sub { text-align: center; font-size: 10px; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; font-size: 11px; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { padding: 4px 0; border-bottom: 1px dashed #000; font-size: 11px; text-align: left; }
        th { font-weight: 700; }
        .total { font-weight: bold; font-size: 13px; margin-top: 8px; }
        .footer { text-align: center; font-size: 10px; margin-top: 8px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; margin-bottom:10px;">
        <button onclick="window.print()" style="padding:8px 12px; background:#f59e0b; color:#fff; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">🖨️ In biên lai</button>
    </div>
    <div class="wrap">
        <div class="header-title">FILMGO CINEMA</div>
        <div class="sub">{{ $cinema->name ?? 'Rạp FilmGo' }} • {{ $booking->created_at?->format('H:i d/m/Y') }}</div>
        <div class="row"><span>Mã đơn</span><span>{{ $booking->booking_code }}</span></div>
        <div class="row"><span>Khách hàng</span><span>{{ $booking->user?->full_name ?? 'Khách vãng lai' }}</span></div>
        <div class="row"><span>Thanh toán</span><span>{{ $booking->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span></div>
        <table>
            <thead>
                <tr><th>Sản phẩm</th><th>SL</th><th>Thành tiền</th></tr>
            </thead>
            <tbody>
                @foreach($booking->combos as $combo)
                    <tr>
                        <td>{{ $combo->combo_name }}</td>
                        <td>{{ $combo->pivot->quantity }}</td>
                        <td>{{ number_format($combo->pivot->subtotal) }}đ</td>
                    </tr>
                @endforeach
                @foreach($booking->comboItems as $item)
                    <tr>
                        <td>{{ $item->comboItem?->name ?? 'Món lẻ' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->subtotal ?? 0) }}đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="row total"><span>Tổng</span><span>{{ number_format($booking->final_total ?? 0) }}đ</span></div>
        <div style="margin-top:12px; text-align:center; font-size:12px; color:#6b7280;">Cảm ơn quý khách đã mua hàng tại FilmGo</div>
    </div>
    <script>window.onload = function(){ window.print(); }</script>
</body>
</html>
