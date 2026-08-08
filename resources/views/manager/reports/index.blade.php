@extends('layouts.manager')

@section('title', 'Báo Cáo & Thống Kê - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Báo Cáo & Thống Kê</h2>
            <p class="text-sm text-slate-500 mt-1">Danh sách các rạp bạn được phép xem báo cáo.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-bold">
            <span class="material-symbols-outlined text-sm">theaters</span>
            {{ $cinemas->count() }} rạp
        </span>
    </div>

    <!-- Cinema List -->
    @forelse($cinemas as $cinema)
        <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">

            <!-- Card Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-800 text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-400 text-2xl">theaters</span>
                    <div>
                        <h3 class="text-base font-bold tracking-tight">{{ $cinema->name }}</h3>
                        <span class="text-xs text-slate-400 font-medium">ID #{{ $cinema->id }}</span>
                    </div>
                </div>
                @if($cinema->status === 'active')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                        Đang hoạt động
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold bg-red-500/20 text-red-300 border border-red-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400 inline-block"></span>
                        Ngừng hoạt động
                    </span>
                @endif
            </div>

            <!-- Card Body: Thông tin cơ bản -->
            <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">location_on</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Địa Chỉ</p>
                        <p class="text-sm text-slate-800 font-medium leading-relaxed">{{ $cinema->address }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">apartment</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Thành Phố</p>
                        <p class="text-sm text-slate-800 font-medium">{{ $cinema->city }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">phone</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Điện Thoại</p>
                        <p class="text-sm text-slate-800 font-medium">{{ $cinema->phone }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-slate-400 mt-0.5">meeting_room</span>
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-0.5">Phòng Chiếu</p>
                        <p class="text-sm text-slate-800 font-medium">{{ $cinema->rooms_count }} phòng</p>
                    </div>
                </div>
            </div>

        </div>
    @empty
        <div class="bg-white border border-slate-200 shadow-sm p-12 text-center">
            <span class="material-symbols-outlined text-5xl text-slate-300 mb-3 block">insert_chart</span>
            <p class="text-base font-semibold text-slate-500">Bạn chưa được phân công quản lý rạp nào.</p>
            <p class="text-sm text-slate-400 mt-1">Vui lòng liên hệ Admin để được phân công.</p>
        </div>
    @endforelse
</div>
@endsection
