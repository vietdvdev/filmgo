<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Biên Lai Combo - {{ $booking->booking_code }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px; color: #000;
            background: #fff;
            width: 80mm; max-width: 80mm;
        }

        .ticket { width: 100%; padding: 6px 7px 12px; }

        /* ── Header ── */
        .hd-logo  { font-size: 20px; font-weight: 900; letter-spacing: 3px; text-align: center; padding: 6px 0 2px; }
        .hd-sub   { font-size: 8px; text-align: center; letter-spacing: 2px; text-transform: uppercase; color: #444; }
        .hd-title {
            font-size: 10px; font-weight: 900; text-align: center;
            text-transform: uppercase; letter-spacing: 1px;
            border-top: 2px solid #000; border-bottom: 2px solid #000;
            padding: 3px 0; margin: 5px 0 4px;
        }

        /* ── Cinema ── */
        .cinema-name { font-size: 12px; font-weight: 900; }
        .cinema-line { font-size: 9px; color: #333; margin-top: 1px; }

        /* ── Dividers ── */
        .d-dash { border-top: 1px dashed #000; margin: 5px 0; }
        .d-bold { border-top: 2px solid #000; margin: 6px 0; }

        /* ── Meta rows ── */
        .meta { display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 3px; }
        .meta .l { color: #555; white-space: nowrap; }
        .meta .v { font-weight: 700; text-align: right; max-width: 60%; word-break: break-word; }

        /* ── Product table ── */
        .prod-tbl { width: 100%; border-collapse: collapse; font-size: 11px; margin: 4px 0; }
        .prod-tbl th { font-weight: 700; border-bottom: 1px solid #000; padding: 2px 0; text-align: left; }
        .prod-tbl td { padding: 3px 0; border-bottom: 1px dashed #ccc; }
        .prod-tbl td:nth-child(2) { text-align: center; }
        .prod-tbl td:last-child   { text-align: right; font-weight: 700; }

        /* ── Price summary ── */
        .price-tbl { width: 100%; border-collapse: collapse; font-size: 10px; margin: 4px 0; }
        .price-tbl td { padding: 2px 0; }
        .price-tbl td:last-child { text-align: right; font-weight: 700; }
        .price-tbl .total td { font-size: 12px; font-weight: 900; border-top: 1px solid #000; padding-top: 4px; }

        /* ── Booking code ── */
        .bc-wrap  { text-align: center; margin: 5px 0 3px; }
        .bc-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        .bc-val   { font-size: 14px; font-weight: 900; letter-spacing: 2px; font-family: monospace; }

        .notice { text-align: center; font-size: 10px; font-weight: 700; border: 1px solid #000; padding: 5px; margin-top: 6px; }
        .footer { text-align: center; font-size: 9px; color: #555; margin-top: 8px; line-height: 1.6; }

        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print" style="padding:10px;background:#1e293b;text-align:center;position:sticky;top:0;z-index:99;">
    <button id="printButton" style="padding:8px 20px;background:#f59e0b;color:#fff;border:none;font-weight:bold;border-radius:6px;cursor:pointer;font-size:13px;">
        🖨️ In Biên Lai
    </button>
</div>

<div class="ticket">

    {{-- 1. HEADER --}}
    <div class="hd-logo">★ FilmGo ★</div>
    <div class="hd-sub">Cinema &amp; Entertainment</div>
    <div class="hd-title">BIÊN LAI MUA HÀNG F&amp;B</div>

    {{-- 2. THÔNG TIN RẠP --}}
    <div class="cinema-name">{{ $cinema->name ?? 'FilmGo Cinema' }}</div>
    @if(!empty($cinema->address))
        <div class="cinema-line">{{ $cinema->address }}{{ !empty($cinema->city) ? ', '.$cinema->city : '' }}</div>
    @endif
    @if(!empty($cinema->phone))
        <div class="cinema-line">ĐT: {{ $cinema->phone }}</div>
    @endif

    <div class="d-dash"></div>

    {{-- 3. THÔNG TIN ĐƠN --}}
    <div class="meta"><span class="l">POS:</span>          <span class="v">{{ $booking->booking_code }}</span></div>
    <div class="meta"><span class="l">Thời gian:</span>    <span class="v">{{ $booking->created_at?->format('H:i d/m/Y') }}</span></div>
    <div class="meta"><span class="l">Khách hàng:</span>   <span class="v">{{ $booking->user?->full_name ?? 'Khách vãng lai' }}</span></div>
    <div class="meta"><span class="l">Thanh toán:</span>   <span class="v">{{ $booking->payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}</span></div>
    <div class="meta"><span class="l">Nhân viên in:</span> <span class="v">{{ auth()->user()->full_name ?? auth()->user()->name ?? 'Hệ thống' }}</span></div>

    <div class="d-bold"></div>

    {{-- 4. DANH SÁCH SẢN PHẨM --}}
    <table class="prod-tbl">
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
            @foreach($booking->comboItems as $item)
            <tr>
                <td>{{ $item->comboItem?->name ?? 'Món lẻ' }}</td>
                <td style="text-align:center;font-weight:900;font-size:13px;">{{ $item->quantity }}</td>
                <td>{{ number_format($item->subtotal ?? 0) }}đ</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-dash"></div>

    {{-- 5. BẢNG TỔNG TIỀN --}}
    <table class="price-tbl">
        <tr>
            <td class="l">Ticket Price</td>
            <td>{{ number_format($booking->total_amount ?? 0) }}đ</td>
        </tr>
        <tr>
            <td class="l">Service Charge</td>
            <td>0đ</td>
        </tr>
        @if(($booking->discount_amount ?? 0) > 0)
        <tr>
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

    {{-- 6. MÃ ĐẶT CHỖ --}}
    <div class="bc-wrap">
        <div class="bc-label">Booking Code / Mã đơn hàng</div>
        <div class="bc-val">{{ $booking->booking_code }}</div>
    </div>

    <div class="d-bold"></div>

    <div class="notice">Vui lòng đưa phiếu này cho nhân viên quầy Concession</div>

    @if($booking->combo_expires_at)
    <div style="text-align:center;font-size:10px;font-weight:700;border:1px dashed #000;padding:4px;margin-top:5px;">
        HẠN DÙNG: {{ $booking->combo_expires_at->format('H:i d/m/Y') }}
    </div>
    @endif

    <div style="text-align:center;font-size:9.5px;font-weight:700;margin-top:6px;text-transform:uppercase;">
        * Vui lòng bảo quản vé vì không được in lại *
    </div>

    <div class="footer">
        Cảm ơn quý khách đã mua hàng tại FilmGo!<br>
        ★ Chúc quý khách ngon miệng ★
    </div>

</div>

<script>
    const markPrintedUrl = '{{ route('staff.combo-bookings.mark-printed', ['bookingId' => $booking->id]) }}';
    const redirectUrl    = '{{ route('staff.combo-bookings.index') }}';

    function markPrinted() {
        return fetch(markPrintedUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });
    }

    function triggerPrint() {
        window.print();
    }

    window.onafterprint = function () {
        markPrinted()
            .catch(e => console.error(e))
            .finally(() => { window.location.href = redirectUrl; });
    };

    document.getElementById('printButton').addEventListener('click', triggerPrint);

    window.onload = function () { setTimeout(triggerPrint, 250); };
</script>
</body>
</html>
