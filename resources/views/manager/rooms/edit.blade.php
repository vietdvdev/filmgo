@extends('layouts.manager')

@section('title', 'Sửa Phòng Chiếu - FilmGo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Sửa Phòng Chiếu</h2>
                <p class="text-sm text-slate-500 mt-1">Cập nhật thông tin phòng chiếu: <strong>{{ $room->room_name }}</strong></p>
            </div>
            <a href="{{ route('manager.rooms.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
                <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại
            </a>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 text-red-800 border border-red-200 text-sm rounded-none">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <div class="bg-white border border-slate-200 shadow-sm rounded-none p-6">
            <form action="{{ route('manager.rooms.update', $room->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Rạp chiếu</label>
                    <div class="px-3 py-2 bg-slate-50 border border-slate-200 text-sm text-slate-600 rounded-none">
                        {{ $room->cinema->name ?? 'N/A' }}
                    </div>
                </div>

                <div>
                    <label for="room_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Tên phòng chiếu</label>
                    <input id="room_name" name="room_name" type="text" required value="{{ old('room_name', $room->room_name) }}"
                           class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>

                <div>
                    <label for="capacity" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Sức chứa (Ghế)</label>
                    <input id="capacity" name="capacity" type="number" required min="1" max="500" value="{{ old('capacity', $room->capacity) }}"
                           class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                </div>

                <div>
                    <label for="room_type" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Loại phòng chiếu</label>
                    <select id="room_type" name="room_type" required
                            class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        @foreach(['2D', '3D', 'IMAX', '4DX'] as $type)
                            <option value="{{ $type }}" {{ old('room_type', $room->room_type) == $type ? 'selected' : '' }}>Phòng chiếu {{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Trạng thái hoạt động</label>
                    <select id="status" name="status" required
                            class="block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <option value="active" {{ old('status', $room->status) == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Đang bảo trì</option>
                        <option value="inactive" {{ old('status', $room->status) == 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('manager.rooms.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
    </div>
</div>
@endsection
