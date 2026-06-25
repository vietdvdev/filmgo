@extends('layouts.manager')

@section('title', 'Lịch Suất Chiếu - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Suất Chiếu Hệ Thống</h2>
            <p class="text-sm text-slate-500 mt-1">Quản lý giờ chiếu và phòng chiếu cho từng phim.</p>
        </div>
        <a href="{{ route('manager.showtimes.create') }}" class="bg-blue-600 text-white font-semibold text-sm px-4 py-2.5 hover:bg-blue-700 transition-colors flex items-center gap-1.5 rounded-none">
            <span class="material-symbols-outlined text-sm">add</span> Tạo Suất Chiếu Mới
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-semibold rounded-none flex items-center gap-2">
            <span class="material-symbols-outlined text-base">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if($errors->has('error'))
        <div class="p-4 bg-red-50 text-red-800 border border-red-200 text-sm font-semibold rounded-none flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            {{ $errors->first('error') }}
        </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-white border border-slate-200 shadow-sm p-4 rounded-none">
        <form method="GET" action="{{ route('manager.showtimes.index') }}" class="flex items-center gap-4">
            <div>
                <label for="date" class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Lọc theo ngày</label>
                <input type="date" id="date" name="date" value="{{ request('date', today()->toDateString()) }}"
                       class="mt-1 block px-3 py-2 bg-slate-50 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>
            
            <div class="self-end">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-5 py-2.5 rounded-none transition-colors">
                    Lọc suất chiếu
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 font-semibold text-xs text-slate-500 uppercase border-b border-slate-200">
                    <th class="py-3 px-6" style="width: 60px;">#</th>
                    <th class="py-3 px-6">Phim</th>
                    <th class="py-3 px-6">Phòng Chiếu</th>
                    <th class="py-3 px-6">Ngày Chiếu</th>
                    <th class="py-3 px-6">Thời Gian</th>
                    <th class="py-3 px-6">Giá Vé Cơ Bản</th>
                    <th class="py-3 px-6" style="width: 140px;">Trạng Thái</th>
                    <th class="py-3 px-6 text-right" style="width: 150px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                @forelse($showtimes as $showtime)
                    <tr class="hover:bg-slate-50/50 {{ $showtime->status === 'finished' ? 'opacity-50 bg-slate-50/30' : '' }}">
                        <td class="py-4 px-6 text-slate-500 font-medium">{{ $loop->iteration + ($showtimes->currentPage() - 1) * $showtimes->perPage() }}</td>
                        <td class="py-4 px-6 font-bold text-slate-900">{{ $showtime->movie->title }}</td>
                        <td class="py-4 px-6 font-medium text-slate-700">
                            {{ $showtime->room->room_name }}
                            <div class="text-[10px] text-slate-400 font-normal">{{ $showtime->room->cinema->name }}</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600">{{ $showtime->show_date->format('d/m/Y') }}</td>
                        <td class="py-4 px-6 text-slate-700 font-semibold">
                            {{ Carbon\Carbon::parse($showtime->start_time)->format('H:i') }} - {{ Carbon\Carbon::parse($showtime->end_time)->format('H:i') }}
                        </td>
                        <td class="py-4 px-6 text-slate-900 font-bold">{{ number_format($showtime->base_price) }}đ</td>
                        <td class="py-4 px-6">
                            @if($showtime->status === 'upcoming')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-blue-100 text-blue-800">Sắp chiếu</span>
                            @elseif($showtime->status === 'showing')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800">Đang chiếu</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-600">Đã kết thúc</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('manager.showtimes.seats', $showtime->id) }}" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white transition-all rounded-none">
                                <span class="material-symbols-outlined text-sm">event_seat</span> Xem ghế
                            </a>
                            @if($showtime->status === 'upcoming')
                                <form action="{{ route('manager.showtimes.cancel', $showtime->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy suất chiếu này?')" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all rounded-none">
                                        <span class="material-symbols-outlined text-sm">cancel</span> Hủy
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-slate-400 italic">Không có suất chiếu nào trong ngày này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($showtimes->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $showtimes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
