@extends('layouts.customer')

@section('title', 'Thanh Toán - FilmGo')

@section('content')
    <div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Progress Bar -->
            <div class="max-w-3xl mx-auto mb-10">
                <div class="flex items-center justify-between relative">
                    <!-- Lines -->
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-200 z-0"></div>
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-600 z-0"></div>
                    
                    <!-- Step 1 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Chọn Ghế</span>
                    </div>
                    <!-- Step 2 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">
                            <span class="material-symbols-outlined text-sm">check</span>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Chọn Combo</span>
                    </div>
                    <!-- Step 3 -->
                    <div class="z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-indigo-600/30">3</div>
                        <span class="text-xs font-bold text-indigo-600 mt-2">Thanh Toán</span>
                    </div>
                </div>
            </div>

            <!-- Main Content Layout Grid -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-sm overflow-hidden p-6 md:p-8">
                    
                    <!-- Header Title -->
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h2 class="text-xl font-bold text-neutral-900 uppercase tracking-tight flex items-center gap-2">
                            <span class="material-symbols-outlined text-indigo-600">receipt_long</span>
                            Xác Nhận Đơn Hàng & Thanh Toán
                        </h2>
                        <p class="text-xs text-neutral-400 font-medium mt-1">Vui lòng kiểm tra kỹ lại toàn bộ thông tin đơn hàng trước khi xác nhận đặt vé.</p>
                    </div>

                    <!-- Layout: Showtime & Items Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        <!-- Movie Details Poster & Info (1/3 width) -->
                        <div class="md:col-span-1 border-r border-slate-100 pr-0 md:pr-8 flex flex-col gap-4">
                            <div class="w-full aspect-[2/3] rounded-2xl overflow-hidden bg-slate-100 shadow-sm">
                                <img src="{{ $showtime->movie->poster ? asset('storage/' . $showtime->movie->poster) : asset('images/no-image.jpg') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div class="space-y-1">
                                <span class="px-2.5 py-0.5 text-[9px] font-black bg-indigo-600 text-white rounded-md uppercase tracking-wider">{{ $showtime->movie->age_limit }}</span>
                                <h3 class="font-extrabold text-neutral-800 text-base uppercase mt-1 leading-tight">{{ $showtime->movie->title }}</h3>
                            </div>
                            
                            <div class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                                <div>
                                    <span class="block text-neutral-400 font-medium mb-0.5">Rạp Chiếu</span>
                                    <span class="font-bold text-neutral-800">{{ $showtime->room->cinema->name }}</span>
                                </div>
                                <div>
                                    <span class="block text-neutral-400 font-medium mb-0.5">Phòng / Loại Suất</span>
                                    <span class="font-bold text-neutral-800">{{ $showtime->room->room_name }} ({{ $showtime->room->room_type }})</span>
                                </div>
                                <div>
                                    <span class="block text-neutral-400 font-medium mb-0.5">Thời Gian Chiếu</span>
                                    <span class="font-bold text-indigo-600">{{ \Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} | {{ $showtime->show_date->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket details breakdown (2/3 width) -->
                        <div class="md:col-span-2 space-y-6">
                            <!-- Selected Seats Table -->
                            <div>
                                <h4 class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3">Thông Tin Vé Ghế</h4>
                                <div class="bg-neutral-50 border border-slate-150 rounded-2xl overflow-hidden p-4 space-y-3">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="text-neutral-400 font-bold border-b border-slate-200/60 pb-2">
                                                <th class="text-left pb-2">Vị Trí</th>
                                                <th class="text-left pb-2">Loại Ghế</th>
                                                <th class="text-right pb-2">Giá Vé</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedSeats as $ss)
                                                <tr class="text-neutral-800 font-semibold border-b border-slate-100/50 last:border-0">
                                                    <td class="py-2.5">
                                                        <span class="bg-indigo-600 text-white px-2 py-0.5 rounded font-black">{{ $ss->seat->seat_row . $ss->seat->seat_number }}</span>
                                                    </td>
                                                    <td class="py-2.5 text-neutral-500">
                                                        {{ $ss->seat->seatType->name }}
                                                    </td>
                                                    <td class="py-2.5 text-right font-bold">
                                                        {{ number_format($showtime->base_price + ($ss->seat->seatType->surcharge_price ?? 0)) }}đ
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-xs">
                                        <span class="font-bold text-neutral-400">Tổng tiền ghế</span>
                                        <span class="font-black text-neutral-800">{{ number_format($totalSeatPrice) }}đ</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Selected Combos list -->
                            <div>
                                <h4 class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3">Bắp Nước Đi Kèm</h4>
                                <div class="bg-neutral-50 border border-slate-150 rounded-2xl overflow-hidden p-4 space-y-3" id="selectedCombosContainer">
                                    <!-- Sẽ được điền bằng Javascript động -->
                                </div>
                            </div>

                            <!-- Upsell Combos suggestion -->
                            @if($allCombos->count() > 0)
                                <div>
                                    <h4 class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-sm text-indigo-600">local_activity</span>
                                        Gợi ý bắp nước ngon - mua kèm tiết kiệm
                                    </h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @foreach($allCombos as $combo)
                                            <div class="flex items-center bg-white border border-slate-200 rounded-2xl p-3 gap-3 hover:shadow-md hover:border-indigo-100 transition-all duration-200">
                                                <!-- image -->
                                                <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0 border border-slate-200">
                                                    <img src="{{ $combo->image ? asset('storage/' . $combo->image) : asset('images/no-image.jpg') }}" 
                                                         alt="{{ $combo->combo_name }}" 
                                                         class="w-full h-full object-cover">
                                                </div>
                                                <!-- info -->
                                                <div class="flex-grow min-w-0">
                                                    <h5 class="font-bold text-neutral-800 text-xs truncate mb-0.5">{{ $combo->combo_name }}</h5>
                                                    <p class="text-[10px] text-neutral-400 line-clamp-1 leading-normal mb-1">{{ $combo->description }}</p>
                                                    <div class="flex justify-between items-center mt-1">
                                                        <span class="text-xs font-black text-indigo-600">{{ number_format($combo->price) }}đ</span>
                                                        <!-- qty adjuster -->
                                                        <div class="flex items-center border border-slate-200 bg-neutral-50 rounded-xl p-0.5">
                                                            <button type="button" 
                                                                    class="w-6 h-6 rounded-lg text-neutral-500 hover:bg-slate-200 flex items-center justify-center font-bold text-xs transition-colors btn-upsell-dec" 
                                                                    data-id="{{ $combo->id }}">-</button>
                                                            <span class="w-6 text-center text-xs font-black text-neutral-800" 
                                                                  id="upsell-qty-{{ $combo->id }}">0</span>
                                                            <button type="button" 
                                                                    class="w-6 h-6 rounded-lg text-neutral-500 hover:bg-slate-200 flex items-center justify-center font-bold text-xs transition-colors btn-upsell-inc" 
                                                                    data-id="{{ $combo->id }}">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Grand total payment info -->
                            <div class="border-t border-slate-150 pt-4 flex justify-between items-center">
                                <div class="flex flex-col">
                                    <span class="text-neutral-400 text-xs font-bold uppercase tracking-wider">Tổng Cộng Thanh Toán</span>
                                    <span class="text-[10px] text-neutral-400 font-semibold mt-0.5">*Đã bao gồm tất cả các thuế phí</span>
                                </div>
                                <span class="text-2xl font-black text-indigo-600" id="grandTotalPriceLabel">
                                    {{ number_format($grandTotal) }}đ
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- Submission buttons panel -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-10 pt-6 border-t border-slate-100">
                        <a href="{{ route('booking.select-combos', $showtime->id) }}" 
                           class="w-full sm:w-1/3 bg-white border border-slate-200 hover:border-indigo-600 hover:text-indigo-600 text-neutral-500 font-bold py-4 rounded-2xl flex items-center justify-center transition-all duration-200 text-sm uppercase tracking-wider">
                            <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
                            Quay Lại
                        </a>
                        
                        <form action="{{ route('booking.confirm', $showtime->id) }}" method="POST" class="w-full sm:w-2/3" id="confirmForm">
                            @csrf
                            <div id="hiddenCombosContainer"></div>
                            <button type="submit" 
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-600/25 transition-all duration-200 flex items-center justify-center gap-2 uppercase tracking-wider text-sm">
                                <span class="material-symbols-outlined text-sm">payments</span>
                                Xác Nhận & Đặt Vé
                            </button>
                        </form>
                    </div>

                </div>
            </div>
            
        </div>
    </div>

    <!-- Script điều khiển luồng Upsell bắp nước động tại Checkout -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Khởi tạo trạng thái combo từ backend pre-populated
            const combosState = {
                @foreach($allCombos as $combo)
                    @php
                        $qty = 0;
                        if (!empty($selectedCombos)) {
                            foreach ($selectedCombos as $sc) {
                                if ($sc['combo']->id === $combo->id) {
                                    $qty = $sc['quantity'];
                                    break;
                                }
                            }
                        }
                    @endphp
                    "{{ $combo->id }}": {
                        id: "{{ $combo->id }}",
                        name: "{{ $combo->combo_name }}",
                        price: {{ $combo->price }},
                        qty: {{ $qty }}
                    },
                @endforeach
            };

            // Hàm cập nhật giao diện
            function updateUI() {
                let comboTotal = 0;
                let tableBodyHtml = '';
                let hasCombos = false;

                for (const id in combosState) {
                    const item = combosState[id];
                    
                    // Cập nhật số lượng trên nhãn thẻ gợi ý
                    const label = document.getElementById('upsell-qty-' + id);
                    if (label) {
                        label.textContent = item.qty;
                    }

                    // Nếu số lượng > 0 thì thêm vào danh sách đã chọn
                    if (item.qty > 0) {
                        hasCombos = true;
                        const subtotal = item.price * item.qty;
                        comboTotal += subtotal;

                        tableBodyHtml += `
                            <tr class="text-neutral-800 font-semibold border-b border-slate-100/50 last:border-0">
                                <td class="py-2.5 max-w-[150px] truncate">${item.name}</td>
                                <td class="py-2.5 text-center font-bold">x${item.qty}</td>
                                <td class="py-2.5 text-right font-bold">${new Intl.NumberFormat('vi-VN').format(subtotal)}đ</td>
                            </tr>
                        `;
                    }
                }

                // Cập nhật bảng bắp nước đã chọn
                const tableContainer = document.getElementById('selectedCombosContainer');
                if (hasCombos) {
                    tableContainer.innerHTML = `
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-neutral-400 font-bold border-b border-slate-200/60 pb-2">
                                    <th class="text-left pb-2">Combo</th>
                                    <th class="text-center pb-2">Số Lượng</th>
                                    <th class="text-right pb-2">Thành Tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableBodyHtml}
                            </tbody>
                        </table>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200 text-xs">
                            <span class="font-bold text-neutral-400">Tổng tiền combo</span>
                            <span class="font-black text-neutral-800">${new Intl.NumberFormat('vi-VN').format(comboTotal)}đ</span>
                        </div>
                    `;
                } else {
                    tableContainer.innerHTML = `
                        <div class="text-center py-4 text-xs font-semibold text-neutral-400">
                            Không đặt mua Combo bắp nước nào.
                        </div>
                    `;
                }

                // Cập nhật tổng cộng thanh toán
                const seatTotal = {{ $totalSeatPrice }};
                const grandTotal = seatTotal + comboTotal;
                const grandTotalLabel = document.getElementById('grandTotalPriceLabel');
                if (grandTotalLabel) {
                    grandTotalLabel.textContent = new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
                }

                // Cập nhật các input ẩn của form submit
                const hiddenContainer = document.getElementById('hiddenCombosContainer');
                if (hiddenContainer) {
                    let inputsHtml = '';
                    for (const id in combosState) {
                        const item = combosState[id];
                        if (item.qty > 0) {
                            inputsHtml += `<input type="hidden" name="combos[${id}]" value="${item.qty}">`;
                        }
                    }
                    hiddenContainer.innerHTML = inputsHtml;
                }
            }

            // Gắn sự kiện nút giảm
            document.querySelectorAll('.btn-upsell-dec').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    if (combosState[id] && combosState[id].qty > 0) {
                        combosState[id].qty--;
                        updateUI();
                    }
                });
            });

            // Gắn sự kiện nút tăng
            document.querySelectorAll('.btn-upsell-inc').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    if (combosState[id] && combosState[id].qty < 99) {
                        combosState[id].qty++;
                        updateUI();
                    }
                });
            });

            // Gọi cập nhật lần đầu
            updateUI();
        });
    </script>
@endsection
