@extends('layouts.staff')

@section('title', 'Quét Mã QR In Vé - FilmGo')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-outline-variant shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-2xl">qr_code_scanner</span>
                <h1 class="text-xl font-bold text-on-surface">Quét Mã QR In Vé</h1>
            </div>
            <p class="text-xs text-on-surface-variant mt-1">Sử dụng Camera thiết bị hoặc nhập mã đơn để kiểm tra và in vé cho khách hàng tại rạp <strong>{{ $cinema->name }}</strong>.</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Rạp: {{ $cinema->name }}
            </span>
        </div>
    </div>

    {{-- Grid Layout: Camera/Form (Trái) & Kết quả Tra Cứu (Phải) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Cột Trái: Camera QR Scanner & Tra Cứu Thủ Công --}}
        <div class="lg:col-span-5 space-y-6">

            {{-- Camera Scanner Card --}}
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">videocam</span>
                        Camera Quét Mã QR
                    </h2>
                    <span id="camera-status" class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600">
                        Sẵn sàng
                    </span>
                </div>

                {{-- Khung hiển thị Camera --}}
                <div class="relative bg-slate-900 rounded-xl overflow-hidden min-h-[260px] flex items-center justify-center border border-slate-700 shadow-inner">
                    <div id="reader" class="w-full h-full"></div>
                    <div id="camera-placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-center p-6 text-slate-400 space-y-2 pointer-events-none">
                        <span class="material-symbols-outlined text-5xl text-slate-500 animate-pulse">qr_code_2</span>
                        <p class="text-xs font-medium max-w-[240px]">Bấm "Mở Camera" để bắt đầu quét mã QR của khách hàng</p>
                    </div>
                </div>

                {{-- Nút bật/tắt camera --}}
                <div class="flex gap-2">
                    <button id="btn-toggle-camera" type="button"
                            class="flex-1 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-sm">videocam</span>
                        <span id="btn-camera-text">Mở Camera Quét QR</span>
                    </button>
                </div>
            </div>

            {{-- Tra Cứu Bằng Mã Đơn / Barcode --}}
            <div class="bg-white rounded-2xl border border-outline-variant p-5 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-lg">keyboard</span>
                    Nhập Mã Đơn / Mã QR Thủ Công
                </h2>
                <form id="form-manual-lookup" class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-on-surface-variant uppercase tracking-wider mb-1">Mã Đơn / Chuỗi QR</label>
                        <div class="relative">
                            <input type="text" id="input-code"
                                   placeholder="VD: FG-ABC12345 hoặc TKT-..."
                                   class="w-full px-4 py-2.5 uppercase font-mono font-bold text-sm bg-surface border border-outline-variant rounded-xl focus:ring-2 focus:ring-primary focus:border-primary pr-10">
                            <button type="button" id="btn-clear-input" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 hidden">
                                <span class="material-symbols-outlined text-lg">cancel</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-sm">search</span>
                        Tra Cứu Đơn Hàng
                    </button>
                </form>
            </div>

        </div>

        {{-- Cột Phải: Thông Tin Đơn Hàng & Thao Tác In Vé --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-outline-variant p-6 shadow-sm min-h-[500px] flex flex-col justify-between" id="result-container">

                {{-- Trạng thái ban đầu --}}
                <div id="state-empty" class="my-auto text-center py-16 space-y-3">
                    <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl">qr_code_scanner</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-700">Chưa quét mã đơn hàng nào</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Vui lòng hướng camera vào mã QR trên điện thoại của khách hàng hoặc nhập mã đơn hàng ở cột bên trái để tra cứu.</p>
                </div>

                {{-- Trạng thái đang tải --}}
                <div id="state-loading" class="my-auto text-center py-16 space-y-3 hidden">
                    <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs font-bold text-primary">Đang tra cứu thông tin đơn hàng…</p>
                </div>

                {{-- Trạng thái tìm thấy kết quả --}}
                <div id="state-success" class="space-y-6 hidden">
                    
                    {{-- Header thẻ kết quả --}}
                    <div class="flex flex-wrap justify-between items-center pb-4 border-b border-slate-100 gap-2">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">Mã Đơn Hàng</span>
                            <span id="res-code" class="text-2xl font-black text-primary font-mono tracking-wider">#FG-000000</span>
                        </div>
                        <div class="flex items-center gap-2" id="res-badges">
                            {{-- Badges injected dynamically --}}
                        </div>
                    </div>

                    {{-- Trạng thái in vé nổi bật --}}
                    <div id="res-print-banner" class="p-4 rounded-xl border flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span id="banner-icon" class="material-symbols-outlined text-xl">info</span>
                            <div>
                                <p id="banner-title" class="text-xs font-extrabold uppercase tracking-wider"></p>
                                <p id="banner-sub" class="text-[11px] font-medium"></p>
                            </div>
                        </div>
                        <span id="banner-tag" class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full"></span>
                    </div>

                    {{-- Thông tin khách hàng & Phim --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-150 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Khách Hàng</span>
                            <p id="res-customer" class="font-bold text-slate-800 text-sm"></p>
                            <p id="res-phone" class="text-slate-500 font-mono"></p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-150 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Phim / Sản Phẩm</span>
                            <p id="res-movie" class="font-extrabold text-slate-900 text-sm uppercase leading-snug"></p>
                            <p id="res-showtime" class="text-indigo-600 font-bold"></p>
                        </div>
                    </div>

                    {{-- Chi tiết Ghế ngồi --}}
                    <div id="res-seats-box" class="space-y-2">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">🪑 Vị Trí Ghế</span>
                        <div id="res-seats-list" class="flex flex-wrap gap-1.5"></div>
                    </div>

                    {{-- Chi tiết Combo bắp nước --}}
                    <div id="res-combos-box" class="space-y-2 hidden">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block">🍿 Bắp Nước Kèm Theo</span>
                        <div id="res-combos-list" class="space-y-1.5"></div>
                    </div>

                    {{-- Tổng tiền --}}
                    <div class="pt-4 border-t border-slate-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Giá Trị Đơn</span>
                        <span id="res-total" class="text-xl font-black text-slate-900">0đ</span>
                    </div>

                    {{-- Action buttons --}}
                    <div class="pt-4 space-y-2">
                        {{-- Thông báo chuyển sang Quản lý khi đơn đã in --}}
                        <div id="res-reprint-notice" class="hidden p-3.5 bg-sky-50 border border-sky-200 text-sky-900 rounded-xl text-xs space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-sky-800">
                                <span class="material-symbols-outlined text-lg text-sky-600">info</span>
                                <span id="reprint-notice-title">Đơn hàng này đã được in trước đó</span>
                            </div>
                            <p id="reprint-notice-desc" class="text-sky-700 leading-relaxed"></p>
                        </div>

                        {{-- Thông báo suất chiếu đã hết hạn --}}
                        <div id="res-expired-notice" class="hidden p-3.5 bg-red-50 border border-red-200 text-red-900 rounded-xl text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg text-red-600">block</span>
                            <span class="font-bold text-red-800">Suất chiếu đã kết thúc hoặc hết hạn — Không thể in vé.</span>
                        </div>

                        <button id="btn-do-print" type="button"
                                class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">print</span>
                            <span>IN VÉ VÀ ĐÁNH DẤU ĐÃ IN VÉ</span>
                        </button>

                        <button id="btn-reset-scan" type="button"
                                class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider rounded-xl transition-colors">
                            Quét Đơn Tiếp Theo
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        let html5QrCode = null;
        let isScanning = false;
        let currentBookingData = null;

        const readerElem = document.getElementById('reader');
        const placeholderElem = document.getElementById('camera-placeholder');
        const btnToggleCam = document.getElementById('btn-toggle-camera');
        const btnCamText = document.getElementById('btn-camera-text');
        const camStatus = document.getElementById('camera-status');

        const inputCode = document.getElementById('input-code');
        const btnClearInput = document.getElementById('btn-clear-input');
        const formManual = document.getElementById('form-manual-lookup');

        const stateEmpty = document.getElementById('state-empty');
        const stateLoading = document.getElementById('state-loading');
        const stateSuccess = document.getElementById('state-success');

        const btnDoPrint = document.getElementById('btn-do-print');
        const btnDoPrintIcon = btnDoPrint.querySelector('span:first-child');
        const btnDoPrintText = btnDoPrint.querySelector('span:last-child');
        const btnResetScan = document.getElementById('btn-reset-scan');
        const reprintNotice = document.getElementById('res-reprint-notice');
        const reprintNoticeTitle = document.getElementById('reprint-notice-title');
        const reprintNoticeDesc = document.getElementById('reprint-notice-desc');
        const expiredNotice = document.getElementById('res-expired-notice');

        // Toggle clear button on input
        inputCode.addEventListener('input', function() {
            btnClearInput.classList.toggle('hidden', !this.value.trim());
        });
        btnClearInput.addEventListener('click', function() {
            inputCode.value = '';
            this.classList.add('hidden');
            inputCode.focus();
        });

        // Handle Manual Form Submit
        formManual.addEventListener('submit', function(e) {
            e.preventDefault();
            const code = inputCode.value.trim();
            if (code) {
                lookupCode(code);
            }
        });

        // Lookup Function
        function lookupCode(code) {
            showState('loading');

            fetch('{{ route("staff.scan-qr.lookup") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentBookingData = data.booking;
                    renderResult(data.booking);
                    showState('success');
                } else {
                    alert('Lỗi: ' + (data.message || 'Không tìm thấy đơn hàng.'));
                    showState('empty');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Không thể kết nối tới máy chủ tra cứu.');
                showState('empty');
            });
        }

        // Render Lookup Result
        function renderResult(b) {
            document.getElementById('res-code').textContent = '#' + b.booking_code;
            document.getElementById('res-customer').textContent = b.customer_name;
            document.getElementById('res-phone').textContent = b.customer_phone;
            document.getElementById('res-movie').textContent = b.movie_title;
            
            const showtimeElem = document.getElementById('res-showtime');
            if (b.is_combo_only) {
                showtimeElem.innerHTML = '<span class="inline-flex items-center gap-1 text-purple-700 font-bold"><span class="material-symbols-outlined text-sm">fastfood</span> Đơn Hàng F&B (Bắp Nước)</span>';
            } else {
                let showtimeHtml = `${b.show_time} | ${b.show_date} (${b.room_name})`;
                if (b.is_expired) {
                    showtimeHtml += ` <span class="inline-flex items-center text-xs font-bold text-red-600 ml-1.5">🚫 Đã hết hạn</span>`;
                }
                showtimeElem.innerHTML = showtimeHtml;
            }
            document.getElementById('res-total').textContent = b.total_amount + 'đ';

            // Header Badges
            const badgesContainer = document.getElementById('res-badges');
            if (badgesContainer) {
                badgesContainer.innerHTML = '';
                if (b.is_combo_only) {
                    badgesContainer.innerHTML += `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-purple-100 text-purple-700 font-extrabold text-[11px] rounded-full border border-purple-200">
                        <span class="material-symbols-outlined text-[13px]">fastfood</span> ĐƠN COMBO
                    </span>`;
                }
                if (b.is_expired) {
                    badgesContainer.innerHTML += `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 font-extrabold text-[11px] rounded-full border border-red-200">
                        <span class="material-symbols-outlined text-[13px]">timer_off</span> ĐÃ HẾT HẠN
                    </span>`;
                }
                if (b.is_printed) {
                    badgesContainer.innerHTML += `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 font-extrabold text-[11px] rounded-full border border-emerald-200">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span> ${b.is_combo_only ? 'ĐÃ IN PHIẾU' : 'ĐÃ IN VÉ'}
                    </span>`;
                } else if (!b.is_expired || b.is_combo_only) {
                    badgesContainer.innerHTML += `<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-700 font-extrabold text-[11px] rounded-full border border-amber-200">
                        <span class="material-symbols-outlined text-[13px]">schedule</span> ${b.is_combo_only ? 'CHƯA IN PHIẾU' : 'CHƯA IN VÉ'}
                    </span>`;
                }
            }

            // Print status banner
            const banner = document.getElementById('res-print-banner');
            const bIcon = document.getElementById('banner-icon');
            const bTitle = document.getElementById('banner-title');
            const bSub = document.getElementById('banner-sub');
            const bTag = document.getElementById('banner-tag');

            if (b.is_combo_only) {
                // Đơn hàng Combo bắp nước riêng lẻ — kiểm tra hạn sử dụng trước
                if (b.is_expired) {
                    // Combo đã hết hạn
                    banner.className = 'p-4 rounded-xl border bg-red-50 border-red-200 text-red-900 flex items-center justify-between';
                    bIcon.textContent = 'cancel';
                    bTitle.textContent = 'ĐƠN BẮP NƯỚC ĐÃ HẾT HẠN Sờ DỤNG';
                    const expiryInfo = b.combo_expires_at ? ` Hạn dùng đến ngày ${b.combo_expires_at} đã qua.` : '';
                    bSub.textContent = 'Khách hàng không thể sử dụng đơn hàng này nữa.' + expiryInfo;
                    bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-red-200 text-red-800';
                    bTag.textContent = 'HẾT HẠN';

                    btnDoPrint.classList.add('hidden');
                    reprintNotice.classList.add('hidden');
                    expiredNotice.classList.remove('hidden');
                } else if (b.is_printed) {
                    // Combo đã in biên lai
                    banner.className = 'p-4 rounded-xl border bg-emerald-50 border-emerald-200 text-emerald-900 flex items-center justify-between';
                    bIcon.textContent = 'check_circle';
                    bTitle.textContent = 'ĐÃ IN BIÊN LAI BẮP NƯỚC';
                    const expiryText = b.combo_expires_at ? ` Hạn dùng: ${b.combo_expires_at}.` : '';
                    bSub.textContent = 'Đơn bắp nước này đã được in biên lai lúc ' + (b.printed_at || '') + '.' + expiryText;
                    bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-200 text-emerald-800';
                    bTag.textContent = 'ĐÃ IN';

                    reprintNoticeTitle.textContent = 'Đơn bắp nước này đã được in biên lai trước đó';
                    reprintNoticeDesc.innerHTML = `Để <strong>in lại biên lai</strong>, vui lòng truy cập chức năng <a href="{{ route('staff.combo-bookings.index') }}" class="underline font-bold hover:text-sky-900 text-primary">Quản lý đơn bắp nước</a>.`;

                    btnDoPrint.classList.add('hidden');
                    reprintNotice.classList.remove('hidden');
                    expiredNotice.classList.add('hidden');
                } else {
                    // Combo chưa in, còn hạn
                    const daysRemaining = b.combo_days_remaining !== null ? ` Còn ${b.combo_days_remaining} ngày.` : '';
                    const expiryLine = b.combo_expires_at ? ` Hạn dùng: ${b.combo_expires_at}.` : '';
                    banner.className = 'p-4 rounded-xl border bg-amber-50 border-amber-200 text-amber-900 flex items-center justify-between';
                    bIcon.textContent = 'receipt_long';
                    bTitle.textContent = 'CHƯA IN BIÊN LAI BẮP NƯỚC';
                    bSub.textContent = 'Sẵn sàng in biên lai bắp nước và trả món cho khách hàng.' + expiryLine + daysRemaining;
                    bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-200 text-amber-800';
                    bTag.textContent = 'CHƯA IN';

                    btnDoPrintIcon.textContent = 'receipt_long';
                    btnDoPrintText.textContent = 'IN BIÊN LAI BẮP NƯỚC VÀ TRẢ MÓN';
                    btnDoPrint.classList.remove('hidden');
                    reprintNotice.classList.add('hidden');
                    expiredNotice.classList.add('hidden');
                }
            } else if (b.is_expired) {
                // Đơn vé xem phim đã hết hạn
                banner.className = 'p-4 rounded-xl border bg-red-50 border-red-200 text-red-900 flex items-center justify-between';
                bIcon.textContent = 'cancel';
                bTitle.textContent = 'SUẤT CHIẾU ĐÃ HẾT HẠN';
                bSub.textContent = b.is_printed 
                    ? `Suất chiếu đã kết thúc (Đã in vé lúc ${b.printed_at || ''}). Không thể in lại.`
                    : 'Suất chiếu của đơn hàng này đã kết thúc hoặc hết hạn. Không thể thực hiện in vé.';
                bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-red-200 text-red-800';
                bTag.textContent = 'ĐÃ HẾT HẠN';

                btnDoPrint.classList.add('hidden');
                reprintNotice.classList.add('hidden');
                expiredNotice.classList.remove('hidden');
            } else if (b.is_printed) {
                // Đơn vé xem phim đã in vé
                banner.className = 'p-4 rounded-xl border bg-emerald-50 border-emerald-200 text-emerald-900 flex items-center justify-between';
                bIcon.textContent = 'check_circle';
                bTitle.textContent = 'ĐÃ IN VÉ';
                bSub.textContent = 'Đơn hàng này đã được in vé lúc ' + (b.printed_at || '');
                bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-200 text-emerald-800';
                bTag.textContent = 'ĐÃ IN';

                reprintNoticeTitle.textContent = 'Đơn hàng này đã được in vé trước đó';
                reprintNoticeDesc.innerHTML = `Để <strong>in lại vé</strong>, vui lòng truy cập chức năng <a href="{{ route('staff.bookings.index') }}" class="underline font-bold hover:text-sky-900 text-primary">Quản lý vé đặt</a> (chỉ hỗ trợ khi suất chiếu chưa hết hạn).`;

                btnDoPrint.classList.add('hidden');
                reprintNotice.classList.remove('hidden');
                expiredNotice.classList.add('hidden');
            } else {
                // Đơn vé xem phim chưa in
                banner.className = 'p-4 rounded-xl border bg-amber-50 border-amber-200 text-amber-900 flex items-center justify-between';
                bIcon.textContent = 'confirmation_number';
                bTitle.textContent = 'CHƯA IN VÉ';
                bSub.textContent = 'Sẵn sàng in vé và phát cho khách hàng tại quầy.';
                bTag.className = 'text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-amber-200 text-amber-800';
                bTag.textContent = 'CHƯA IN';

                btnDoPrintIcon.textContent = 'print';
                btnDoPrintText.textContent = 'IN VÉ VÀ ĐÁNH DẤU ĐÃ IN VÉ';
                btnDoPrint.classList.remove('hidden');
                reprintNotice.classList.add('hidden');
                expiredNotice.classList.add('hidden');
            }

            // Seats list
            const seatsBox = document.getElementById('res-seats-box');
            const seatsList = document.getElementById('res-seats-list');
            seatsList.innerHTML = '';
            if (b.seats && b.seats.length > 0) {
                seatsBox.classList.remove('hidden');
                b.seats.forEach(s => {
                    const span = document.createElement('span');
                    span.className = 'px-2.5 py-1 bg-indigo-50 border border-indigo-100 text-indigo-700 font-black text-xs rounded-lg';
                    span.textContent = s;
                    seatsList.appendChild(span);
                });
            } else {
                seatsBox.classList.add('hidden');
            }

            // Combos list
            const combosBox = document.getElementById('res-combos-box');
            const combosList = document.getElementById('res-combos-list');
            combosList.innerHTML = '';
            let hasCombo = false;
            if (b.combos && b.combos.length > 0) {
                hasCombo = true;
                b.combos.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between items-center text-xs p-2 bg-slate-50 rounded-lg border border-slate-100';
                    div.innerHTML = `<span class="font-bold text-slate-800">${c.name}</span><span class="font-black text-primary">x${c.qty}</span>`;
                    combosList.appendChild(div);
                });
            }
            if (b.combo_items && b.combo_items.length > 0) {
                hasCombo = true;
                b.combo_items.forEach(ci => {
                    const div = document.createElement('div');
                    div.className = 'flex justify-between items-center text-xs p-2 bg-slate-50 rounded-lg border border-slate-100';
                    div.innerHTML = `<span class="font-bold text-slate-800">${ci.name}</span><span class="font-black text-primary">x${ci.qty}</span>`;
                    combosList.appendChild(div);
                });
            }
            combosBox.classList.toggle('hidden', !hasCombo);
        }

        // State switcher helper
        function showState(state) {
            stateEmpty.classList.toggle('hidden', state !== 'empty');
            stateLoading.classList.toggle('hidden', state !== 'loading');
            stateSuccess.classList.toggle('hidden', state !== 'success');
        }

        // Print Action Button Click
        btnDoPrint.addEventListener('click', function() {
            if (!currentBookingData) return;

            // 1. Mở cửa sổ in vé
            const printWin = window.open(currentBookingData.print_url, '_blank', 'width=900,height=700');

            // 2. Gọi API đánh dấu đã in vé
            const markUrl = currentBookingData.is_combo_only
                ? `/staff/combo-bookings/${currentBookingData.id}/mark-printed`
                : `/staff/bookings/${currentBookingData.id}/mark-printed`;

            fetch(markUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(res => {
                currentBookingData.is_printed = true;
                currentBookingData.printed_at = 'Vừa xong';
                renderResult(currentBookingData);
            })
            .catch(err => console.error(err));
        });

        // Reset Scan Button Click
        btnResetScan.addEventListener('click', function() {
            currentBookingData = null;
            inputCode.value = '';
            btnClearInput.classList.add('hidden');
            showState('empty');
            if (isScanning && html5QrCode) {
                html5QrCode.resume();
            }
        });

        // ── Camera HTML5 QR Scanner Controls ──
        btnToggleCam.addEventListener('click', function() {
            if (isScanning) {
                stopCamera();
            } else {
                startCamera();
            }
        });

        function startCamera() {
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }
            placeholderElem.classList.add('hidden');
            camStatus.textContent = 'Đang bật Camera…';
            camStatus.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700';

            const config = { fps: 10, qrbox: { width: 220, height: 220 } };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            )
            .then(() => {
                isScanning = true;
                btnCamText.textContent = 'Tắt Camera';
                btnToggleCam.className = 'flex-1 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm';
                camStatus.textContent = '● Camera Online';
                camStatus.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700';
            })
            .catch(err => {
                console.error("Không thể mở Camera:", err);
                alert("Không thể truy cập Camera. Vui lòng cho phép quyền sử dụng camera trong trình duyệt hoặc sử dụng ô nhập mã thủ công.");
                stopCamera();
            });
        }

        function stopCamera() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    resetCamUI();
                }).catch(err => {
                    console.error("Lỗi tắt camera:", err);
                    resetCamUI();
                });
            } else {
                resetCamUI();
            }
        }

        function resetCamUI() {
            isScanning = false;
            placeholderElem.classList.remove('hidden');
            btnCamText.textContent = 'Mở Camera Quét QR';
            btnToggleCam.className = 'flex-1 py-2.5 bg-primary hover:bg-primary/90 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all flex items-center justify-center gap-2 shadow-sm';
            camStatus.textContent = 'Sẵn sàng';
            camStatus.className = 'text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600';
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log("Quét thành công QR:", decodedText);
            inputCode.value = decodedText;
            btnClearInput.classList.remove('hidden');
            
            // Pauses scanning while processing lookup
            if (html5QrCode) {
                try { html5QrCode.pause(); } catch(e){}
            }

            lookupCode(decodedText);
        }

        function onScanFailure(error) {
            // Silence scan frame errors
        }
    });
</script>
@endpush
