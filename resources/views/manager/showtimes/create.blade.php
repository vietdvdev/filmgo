@extends('layouts.manager')

@section('title', 'Tạo Suất Chiếu Mới - FilmGo')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Tạo Suất Chiếu Mới</h2>
            <p class="text-sm text-slate-500 mt-1">Lên lịch chiếu phim và cấu hình giá vé cơ bản.</p>
        </div>
        <a href="{{ route('manager.showtimes.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-sm rounded-none transition-colors">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Quay lại
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none">
        <form action="{{ route('manager.showtimes.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Phim -->
            <div>
                <label for="movie_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Chọn Phim</label>
                <select id="movie_id" name="movie_id" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    <option value="">-- Chọn phim --</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}" {{ old('movie_id') == $movie->id ? 'selected' : '' }}>
                            {{ $movie->title }} ({{ $movie->duration }} phút)
                        </option>
                    @endforeach
                </select>
                @error('movie_id')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Phòng Chiếu -->
            <div>
                <label for="room_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Chọn Phòng Chiếu</label>
                <select id="room_id" name="room_id" required
                        class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    <option value="">-- Chọn phòng chiếu --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            {{ $room->room_name }} ({{ $room->room_type }} - {{ $room->capacity }} ghế)
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Ngày Chiếu -->
                <div>
                    <label for="show_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Ngày Chiếu</label>
                    <input id="show_date" name="show_date" type="date" required value="{{ old('show_date', today()->toDateString()) }}"
                           class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    @error('show_date')
                        <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Giờ Bắt Đầu -->
                <div>
                    <label for="start_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Giờ Bắt Đầu</label>
                    <input id="start_time" name="start_time" type="time" required value="{{ old('start_time') }}"
                           class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                    @error('start_time')
                        <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Giá Vé Cơ Bản -->
            <div>
                <label for="base_price" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Giá Vé Cơ Bản (VNĐ)</label>
                <input id="base_price" name="base_price" type="number" required min="0" value="{{ old('base_price', 80000) }}"
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                @error('base_price')
                    <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('manager.showtimes.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                    Lên Lịch Suất Chiếu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
