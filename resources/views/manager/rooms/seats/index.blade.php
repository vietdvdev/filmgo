@extends('layouts.manager')

@section('title', 'Thiết Lập Sơ Đồ Ghế - ' . $room->room_name)

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b border-slate-200 pb-4 gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-semibold uppercase tracking-wider">
                <a href="{{ route('manager.rooms.index') }}" class="hover:text-blue-600 transition-colors">Phòng Chiếu</a>
                <span class="material-symbols-outlined text-[10px]">arrow_forward_ios</span>
                <span>{{ $room->room_name }}</span>
                <span class="material-symbols-outlined text-[10px]">arrow_forward_ios</span>
                <span class="text-slate-800">Thiết Lập Sơ Đồ Ghế</span>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase mt-1">Sơ Đồ Ghế: {{ $room->room_name }}</h2>
            <p class="text-sm text-slate-500">Loại phòng: <span class="font-semibold text-blue-600">{{ $room->room_type }}</span> | Trạng thái: 
                @if($room->status === 'active')
                    <span class="text-emerald-600 font-semibold">Hoạt động</span>
                @elseif($room->status === 'maintenance')
                    <span class="text-amber-600 font-semibold">Bảo trì</span>
                @else
                    <span class="text-slate-600 font-semibold">Ngừng hoạt động</span>
                @endif
            </p>
        </div>
        <a href="{{ route('manager.rooms.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay Lại
        </a>
    </div>

    <!-- Alert Thông báo nhanh -->
    <div id="toast-message" class="hidden p-4 text-sm font-semibold rounded-none border"></div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-semibold rounded-none">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-800 border border-red-200 text-sm font-semibold rounded-none">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Cột Trái: Trình tạo ghế hàng loạt -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-slate-200 p-6 rounded-none shadow-sm">
                <h3 class="text-sm font-bold text-slate-950 uppercase border-b border-slate-200 pb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-lg text-blue-600">grid_view</span>
                    Tạo Hàng Ghế Nhanh
                </h3>
                
                <form action="{{ route('manager.rooms.seats.bulk', $room->id) }}" method="POST" class="space-y-4 mt-4">
                    @csrf
                    <div>
                        <label for="seat_row" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Ký hiệu hàng ghế</label>
                        <input id="seat_row" name="seat_row" type="text" required maxlength="2" value="{{ old('seat_row') }}"
                               class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 uppercase"
                               placeholder="Ví dụ: A, B, C">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Số bắt đầu</label>
                            <input id="start_number" name="start_number" type="number" required min="1" value="{{ old('start_number', 1) }}"
                                   class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                        <div>
                            <label for="end_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Số kết thúc</label>
                            <input id="end_number" name="end_number" type="number" required min="1" value="{{ old('end_number', 12) }}"
                                   class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        </div>
                    </div>

                    <div>
                        <label for="seat_type_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Loại ghế</label>
                        <select id="seat_type_id" name="seat_type_id" required
                                class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            @foreach($seatTypes as $type)
                                <option value="{{ $type->id }}" {{ old('seat_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (+{{ number_format($type->surcharge_price) }} đ)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 rounded-none transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">apps</span>
                            Sinh hàng ghế
                        </button>
                    </div>
                </form>
            </div>

            <!-- Chú thích loại ghế (Legend) -->
            <div class="bg-white border border-slate-200 p-6 rounded-none shadow-sm">
                <h3 class="text-sm font-bold text-slate-950 uppercase border-b border-slate-200 pb-3">Chú Thích</h3>
                <div class="space-y-3 mt-4">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 border border-slate-300 bg-slate-200 rounded-none"></div>
                        <span class="text-xs font-semibold text-slate-700">Ghế Thường (Standard)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 border border-purple-700 bg-purple-600 rounded-none"></div>
                        <span class="text-xs font-semibold text-slate-700">Ghế VIP</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 border border-pink-600 bg-pink-500 rounded-none"></div>
                        <span class="text-xs font-semibold text-slate-700">Ghế Sweetbox / Đôi</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 border border-red-300 bg-red-100 flex items-center justify-center rounded-none text-red-600 font-bold text-[10px]">
                            <span class="material-symbols-outlined text-xs">build</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-700">Ghế Đang Bảo Trì</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Sơ đồ ghế tương tác -->
        <div class="lg:col-span-3 bg-white border border-slate-200 p-6 rounded-none shadow-sm overflow-x-auto flex flex-col items-center">
            
            <!-- Màn hình chiếu phim ảo -->
            <div class="w-full max-w-2xl text-center mb-12">
                <div class="bg-slate-700 text-white font-bold text-xs uppercase tracking-widest py-2 rounded-none border border-slate-800">
                    MÀN HÌNH CHẾU PHIM
                </div>
                <div class="h-1 bg-slate-400 mt-0.5 w-11/12 mx-auto"></div>
            </div>

            <!-- Sơ đồ ghế -->
            <div class="w-full flex flex-col items-center gap-4 min-w-[700px] pb-6">
                @forelse($groupedSeats as $row => $rowSeats)
                    <div class="flex items-center gap-3 w-full justify-center">
                        <!-- Tên hàng bên trái -->
                        <span class="w-6 text-center font-bold text-slate-500 text-sm uppercase">{{ $row }}</span>
                        
                        <!-- Danh sách ghế trong hàng -->
                        <div class="flex gap-2 justify-center">
                            @php
                                $maxSeatNum = $rowSeats->max('seat_number');
                                $seatsByNum = $rowSeats->keyBy('seat_number');
                            @endphp
                            
                            {{-- Lặp từ 1 đến số ghế lớn nhất để giữ cấu trúc cột đồng đều --}}
                            @for($i = 1; $i <= $maxSeatNum; $i++)
                                @if(isset($seatsByNum[$i]))
                                    @php
                                        $seat = $seatsByNum[$i];
                                        
                                        // Thiết lập màu sắc theo loại ghế
                                        $bgClass = 'bg-slate-200 text-slate-800 border-slate-300';
                                        if ($seat->seat_type_id == 2) {
                                            $bgClass = 'bg-purple-600 text-white border-purple-700';
                                        } elseif ($seat->seat_type_id == 3) {
                                            $bgClass = 'bg-pink-500 text-white border-pink-600';
                                        }
                                        
                                        // Nếu đang bảo trì
                                        if ($seat->status === 'maintenance') {
                                            $bgClass = 'bg-red-100 text-red-600 border-red-300';
                                        }
                                    @endphp
                                    
                                    <button type="button" 
                                            id="seat-btn-{{ $seat->id }}"
                                            data-seat-id="{{ $seat->id }}"
                                            data-seat-row="{{ $seat->seat_row }}"
                                            data-seat-number="{{ $seat->seat_number }}"
                                            data-seat-type-id="{{ $seat->seat_type_id }}"
                                            data-seat-status="{{ $seat->status }}"
                                            onclick="openSeatModal(this)"
                                            class="w-10 h-10 border text-xs font-bold transition-all hover:scale-105 flex items-center justify-center rounded-none cursor-pointer {{ $bgClass }}">
                                        @if($seat->status === 'maintenance')
                                            <span class="material-symbols-outlined text-xs">build</span>
                                        @else
                                            {{ $seat->seat_row }}{{ $seat->seat_number }}
                                        @endif
                                    </button>
                                @else
                                    {{-- Nếu không có ghế tại cột này (đã bị xóa/lối đi) --}}
                                    <div class="w-10 h-10 bg-transparent border-none pointer-events-none rounded-none"></div>
                                @endif
                            @endfor
                        </div>

                        <!-- Tên hàng bên phải -->
                        <span class="w-6 text-center font-bold text-slate-500 text-sm uppercase">{{ $row }}</span>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 italic">
                        Phòng chiếu này chưa có ghế nào. Hãy sử dụng bảng bên trái để khởi tạo hàng ghế.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Chỉnh Sửa Ghế Đơn Lẻ -->
<div id="seat-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeSeatModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white border border-slate-200 shadow-xl max-w-sm w-full mx-4 p-6 rounded-none">
        <div class="flex justify-between items-center border-b border-slate-200 pb-3">
            <h3 class="text-sm font-bold text-slate-900 uppercase">Cấu hình ghế: <span id="modal-seat-name" class="text-blue-600">A5</span></h3>
            <button type="button" onclick="closeSeatModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
        
        <form id="update-seat-form" class="space-y-4 mt-4">
            <input type="hidden" id="modal-seat-id" name="seat_id">
            
            <div>
                <label for="modal_seat_type_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Loại ghế</label>
                <select id="modal_seat_type_id" name="seat_type_id" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-slate-50">
                    @foreach($seatTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} (+{{ number_format($type->surcharge_price) }} đ)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Trạng thái vật lý</label>
                <select id="modal_status" name="status" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 bg-slate-50">
                    <option value="active">Hoạt động bình thường</option>
                    <option value="maintenance">Đang bảo trì / Hỏng</option>
                </select>
            </div>

            <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2 rounded-none transition-colors flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">save</span>
                    Cập Nhật Ghế
                </button>
                <button type="button" onclick="deleteSeatAjax()" class="w-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white text-sm font-bold py-2 rounded-none transition-colors flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-base">delete</span>
                    Xóa Ghế Khỏi Sơ Đồ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const roomId = "{{ $room->id }}";
    const csrfToken = "{{ csrf_token() }}";
    
    // Mở Modal chỉnh sửa ghế
    function openSeatModal(button) {
        const seatId = button.getAttribute('data-seat-id');
        const seatRow = button.getAttribute('data-seat-row');
        const seatNumber = button.getAttribute('data-seat-number');
        const seatTypeId = button.getAttribute('data-seat-type-id');
        const seatStatus = button.getAttribute('data-seat-status');

        document.getElementById('modal-seat-id').value = seatId;
        document.getElementById('modal-seat-name').innerText = `${seatRow}${seatNumber}`;
        document.getElementById('modal_seat_type_id').value = seatTypeId;
        document.getElementById('modal_status').value = seatStatus;

        const modal = document.getElementById('seat-modal');
        modal.classList.remove('hidden');
    }

    // Đóng Modal
    function closeSeatModal() {
        document.getElementById('seat-modal').classList.add('hidden');
    }

    // Hiển thị thông báo nhanh dạng Toast
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast-message');
        toast.innerText = message;
        toast.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'bg-red-50', 'text-red-800', 'border-red-200');
        
        if (type === 'success') {
            toast.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-200');
        } else {
            toast.classList.add('bg-red-50', 'text-red-800', 'border-red-200');
        }
        
        // Cuộn lên đầu trang để nhìn thấy thông báo dễ dàng
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 5000);
    }

    // Gửi AJAX Cập nhật Ghế
    document.getElementById('update-seat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const seatId = document.getElementById('modal-seat-id').value;
        const seatTypeId = document.getElementById('modal_seat_type_id').value;
        const status = document.getElementById('modal_status').value;

        fetch(`/manager/rooms/${roomId}/seats/${seatId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                seat_type_id: seatTypeId,
                status: status
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const seatBtn = document.getElementById(`seat-btn-${seatId}`);
                if (seatBtn) {
                    // Cập nhật lại các thuộc tính dữ liệu trên nút ghế
                    seatBtn.setAttribute('data-seat-type-id', data.seat.seat_type_id);
                    seatBtn.setAttribute('data-seat-status', data.seat.status);

                    // Reset class màu sắc
                    seatBtn.className = "w-10 h-10 border text-xs font-bold transition-all hover:scale-105 flex items-center justify-center rounded-none cursor-pointer";
                    
                    let bgClass = 'bg-slate-200 text-slate-800 border-slate-300';
                    if (data.seat.status === 'maintenance') {
                        bgClass = 'bg-red-100 text-red-600 border-red-300';
                        seatBtn.innerHTML = '<span class="material-symbols-outlined text-xs">build</span>';
                    } else {
                        if (data.seat.seat_type_id == 2) {
                            bgClass = 'bg-purple-600 text-white border-purple-700';
                        } else if (data.seat.seat_type_id == 3) {
                            bgClass = 'bg-pink-500 text-white border-pink-600';
                        }
                        const row = seatBtn.getAttribute('data-seat-row');
                        const num = seatBtn.getAttribute('data-seat-number');
                        seatBtn.innerHTML = `${row}${num}`;
                    }
                    
                    seatBtn.classList.add(...bgClass.split(' '));
                }

                closeSeatModal();
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error(error);
            showToast(error.message || 'Lỗi không xác định khi cập nhật ghế.', 'error');
            closeSeatModal();
        });
    });

    // Gửi AJAX Xóa Ghế
    function deleteSeatAjax() {
        const seatId = document.getElementById('modal-seat-id').value;
        const seatName = document.getElementById('modal-seat-name').innerText;
        
        if (!confirm(`Bạn có chắc chắn muốn xóa ghế ${seatName} khỏi sơ đồ phòng chiếu không? Thao tác này sẽ tạo một khoảng trống tại vị trí này.`)) {
            return;
        }

        fetch(`/manager/rooms/${roomId}/seats/${seatId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const seatBtn = document.getElementById(`seat-btn-${seatId}`);
                if (seatBtn) {
                    // Biến nút ghế thành ô trống trong suốt để giữ bố cục cấu trúc hàng ghế
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = "w-10 h-10 bg-transparent border-none pointer-events-none rounded-none";
                    seatBtn.parentNode.replaceChild(emptyDiv, seatBtn);
                }

                closeSeatModal();
                showToast(data.message, 'success');
            }
        })
        .catch(error => {
            console.error(error);
            showToast(error.message || 'Lỗi không xác định khi xóa ghế.', 'error');
            closeSeatModal();
        });
    }

    // Đóng modal khi nhấn ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSeatModal();
    });
</script>
@endsection
