@extends('layouts.manager')

@section('title', 'Quản Lý Phòng Chiếu - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Phòng Chiếu Chi Nhánh</h2>
            <p class="text-sm text-slate-500 mt-1">Cấu hình phòng chiếu và sơ đồ ghế ngồi.</p>
        </div>
        <button onclick="openCreateModal()" class="bg-blue-600 text-white font-semibold text-sm px-4 py-2.5 hover:bg-blue-700 transition-colors flex items-center gap-1.5 rounded-none">
            <span class="material-symbols-outlined text-sm">add</span> Thêm Phòng Chiếu Mới
        </button>
    </div>

    <!-- Alerts -->
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

    <!-- Search & Filter -->
    <div class="bg-white border border-slate-200 shadow-sm p-4 rounded-none">
        <form method="GET" action="{{ route('manager.rooms.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên phòng chiếu..." 
                   class="w-64 px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-none transition-colors">
                Tìm kiếm
            </button>
            @if(request('search'))
                <a href="{{ route('manager.rooms.index') }}" class="bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-semibold px-4 py-2 rounded-none transition-colors flex items-center justify-center">
                    Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 font-semibold text-xs text-slate-500 uppercase border-b border-slate-200">
                    <th class="py-3 px-6" style="width: 60px;">#</th>
                    <th class="py-3 px-6">Tên Phòng</th>
                    <th class="py-3 px-6">Sức Chứa (Ghế)</th>
                    <th class="py-3 px-6">Loại Phòng</th>
                    <th class="py-3 px-6" style="width: 150px;">Trạng Thái</th>
                    <th class="py-3 px-6 text-right" style="width: 300px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                @forelse($rooms as $room)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-4 px-6 text-slate-500 font-medium">{{ $loop->iteration + ($rooms->currentPage() - 1) * $rooms->perPage() }}</td>
                        <td class="py-4 px-6 font-bold text-slate-900">{{ $room->room_name }}</td>
                        <td class="py-4 px-6 font-medium text-slate-700">{{ $room->capacity }} ghế</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $room->room_type }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            @if($room->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800">Đang hoạt động</span>
                            @elseif($room->status === 'maintenance')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-amber-100 text-amber-800">Đang bảo trì</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-800">Ngừng hoạt động</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="flex gap-2 justify-end items-center">
                                <!-- Seat Map Button -->
                                <a href="{{ route('manager.rooms.seat-map', $room->id) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 border border-blue-300 text-blue-700 bg-white hover:bg-blue-50 transition-all rounded-none">
                                    <span class="material-symbols-outlined text-sm">grid_on</span> Sơ đồ ghế
                                </a>
                                <!-- Edit Button -->
                                <button onclick="openEditModal('{{ $room->id }}', '{{ $room->room_name }}', '{{ $room->capacity }}', '{{ $room->room_type }}', '{{ $room->status }}')" 
                                        class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition-all rounded-none">
                                    <span class="material-symbols-outlined text-sm">edit</span> Sửa
                                </button>
                                <!-- Delete Form -->
                                <form action="{{ route('manager.rooms.destroy', $room->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa phòng chiếu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all rounded-none">
                                        <span class="material-symbols-outlined text-sm">delete</span> Xóa
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400 italic">Không tìm thấy phòng chiếu nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($rooms->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Thêm Phòng Chiếu -->
<div id="create-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <!-- Modal Content -->
    <div class="relative bg-white border border-slate-200 shadow-xl max-w-md w-full mx-4 p-6 rounded-none">
        <h3 class="text-lg font-bold text-slate-900 uppercase border-b border-slate-200 pb-3">Thêm Phòng Chiếu Mới</h3>
        
        <form action="{{ route('manager.rooms.store') }}" method="POST" class="space-y-4 mt-4">
            @csrf
            
            <div>
                <label for="room_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Tên phòng chiếu</label>
                <input id="room_name" name="room_name" type="text" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Ví dụ: Phòng 01">
            </div>

            <div>
                <label for="capacity" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Sức chứa (Ghế)</label>
                <input id="capacity" name="capacity" type="number" required min="1" max="500"
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="room_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Loại phòng chiếu</label>
                <select id="room_type" name="room_type" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    <option value="2D">Phòng chiếu 2D</option>
                    <option value="3D">Phòng chiếu 3D</option>
                    <option value="IMAX">Phòng chiếu IMAX</option>
                    <option value="4DX">Phòng chiếu 4DX</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                    Thêm phòng chiếu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Sửa Phòng Chiếu -->
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <!-- Modal Content -->
    <div class="relative bg-white border border-slate-200 shadow-xl max-w-md w-full mx-4 p-6 rounded-none">
        <h3 class="text-lg font-bold text-slate-900 uppercase border-b border-slate-200 pb-3">Sửa Phòng Chiếu</h3>
        
        <form id="edit-room-form" method="POST" class="space-y-4 mt-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="edit_room_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Tên phòng chiếu</label>
                <input id="edit_room_name" name="room_name" type="text" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="edit_capacity" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Sức chứa (Ghế)</label>
                <input id="edit_capacity" name="capacity" type="number" required min="1" max="500"
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="edit_room_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Loại phòng chiếu</label>
                <select id="edit_room_type" name="room_type" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    <option value="2D">Phòng chiếu 2D</option>
                    <option value="3D">Phòng chiếu 3D</option>
                    <option value="IMAX">Phòng chiếu IMAX</option>
                    <option value="4DX">Phòng chiếu 4DX</option>
                </select>
            </div>

            <div>
                <label for="edit_status" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Trạng thái hoạt động</label>
                <select id="edit_status" name="status" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    <option value="active">Đang hoạt động</option>
                    <option value="maintenance">Đang bảo trì</option>
                    <option value="inactive">Ngừng hoạt động</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }
    
    function openEditModal(roomId, roomName, capacity, roomType, status) {
        document.getElementById('edit_room_name').value = roomName;
        document.getElementById('edit_capacity').value = capacity;
        document.getElementById('edit_room_type').value = roomType;
        document.getElementById('edit_status').value = status;
        document.getElementById('edit-room-form').action = `/manager/rooms/${roomId}`;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
