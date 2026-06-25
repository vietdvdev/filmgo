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
    @if(session('error'))
        <div class="p-4 bg-red-50 text-red-800 border border-red-200 text-sm font-semibold rounded-none flex items-center gap-2">
            <span class="material-symbols-outlined text-base">error</span>
            {{ session('error') }}
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
                    <th class="py-3 px-6 text-right" style="width: 200px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                @forelse($showtimes as $showtime)
                    <tr class="hover:bg-slate-50/50 {{ in_array($showtime->status, ['finished', 'cancelled']) ? 'opacity-50 bg-slate-50/30' : '' }}">
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
                            @elseif($showtime->status === 'finished')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-slate-100 text-slate-600">Đã kết thúc</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-red-100 text-red-800">Đã hủy</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('manager.showtimes.seats', $showtime->id) }}"
                               class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white transition-all rounded-none">
                                <span class="material-symbols-outlined text-sm">event_seat</span> Xem ghế
                            </a>
                            @if($showtime->status === 'upcoming')
                                <button
                                    type="button"
                                    onclick="openCancelModal({{ $showtime->id }}, '{{ addslashes($showtime->movie->title) }}', '{{ Carbon\Carbon::parse($showtime->start_time)->format('H:i') }}')"
                                    class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all rounded-none">
                                    <span class="material-symbols-outlined text-sm">cancel</span> Hủy suất
                                </button>
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

        @if($showtimes->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $showtimes->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Xác Nhận Hủy Suất Chiếu -->
<div id="cancelModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60">
    <div class="bg-white w-full max-w-md shadow-2xl rounded-none p-6 space-y-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-red-600 text-3xl">warning</span>
            <h3 class="text-lg font-bold text-slate-900">Xác Nhận Hủy Suất Chiếu</h3>
        </div>
        <p class="text-sm text-slate-600">
            Bạn sắp hủy suất chiếu <strong id="modalMovieTitle" class="text-slate-900"></strong>
            lúc <strong id="modalStartTime" class="text-slate-900"></strong>.
            Hành động này <span class="text-red-600 font-bold">không thể hoàn tác</span>.
        </p>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">
                Nhập <span class="text-red-600">HUY</span> để xác nhận
            </label>
            <input type="text" id="cancelConfirmInput" placeholder="Gõ HUY" autocomplete="off"
                   class="w-full px-3 py-2 border border-slate-300 text-sm focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 rounded-none">
        </div>
        <div class="flex justify-end gap-3 pt-2">
            <button type="button" onclick="closeCancelModal()"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 border border-slate-300 hover:bg-slate-50 rounded-none transition-colors">
                Quay lại
            </button>
            <button type="button" id="confirmCancelBtn" onclick="submitCancel()" disabled
                    class="px-4 py-2 text-sm font-bold text-white bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed rounded-none transition-all">
                Hủy Suất Chiếu
            </button>
        </div>
    </div>
</div>

<form id="cancelForm" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>

@section('scripts')
<script>
    let cancelTargetUrl = '';

    function openCancelModal(id, title, time) {
        cancelTargetUrl = `/manager/showtimes/${id}/cancel`;
        document.getElementById('modalMovieTitle').textContent = title;
        document.getElementById('modalStartTime').textContent = time;
        document.getElementById('cancelConfirmInput').value = '';
        document.getElementById('confirmCancelBtn').disabled = true;
        const modal = document.getElementById('cancelModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('cancelConfirmInput').focus();
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function submitCancel() {
        document.getElementById('cancelForm').action = cancelTargetUrl;
        document.getElementById('cancelForm').submit();
    }

    document.getElementById('cancelConfirmInput').addEventListener('input', function () {
        document.getElementById('confirmCancelBtn').disabled = this.value.trim() !== 'HUY';
    });

    document.getElementById('cancelModal').addEventListener('click', function (e) {
        if (e.target === this) closeCancelModal();
    });
</script>
@endsection
@endsection
