@extends('layouts.manager')

@section('title', 'Báo Cáo & Thống Kê - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Báo Cáo & Thống Kê</h2>
            <p class="text-sm text-slate-500 mt-1">Doanh thu tổng hợp theo từng rạp (tính trên đơn đã thanh toán).</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-bold">
            <span class="material-symbols-outlined text-sm">theaters</span>
            {{ $cinemas->count() }} rạp
        </span>
    </div>

    <!-- Summary Row -->
    @if($cinemas->count() > 1)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng Vé Đã Bán</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemas->sum('ticket_count'), 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Vé</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemas->sum('ticket_revenue'), 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu F&B</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemas->sum('fnb_revenue'), 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng Doanh Thu</p>
            <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ number_format($cinemas->sum('total_revenue'), 0, ',', '.') }}đ</p>
        </div>
    </div>
    @endif

    <!-- Cinema Cards -->
    @forelse($cinemas as $cinema)
        <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">

            <!-- Card Header -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-800 text-white">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-blue-400 text-2xl">theaters</span>
                    <div>
                        <h3 class="text-base font-bold tracking-tight">{{ $cinema->name }}</h3>
                        <span class="text-xs text-slate-400">{{ $cinema->city }} · {{ $cinema->phone }}</span>
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

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-slate-100">

                <!-- Số vé đã bán -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-slate-400 text-base">confirmation_number</span>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vé Đã Bán</p>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($cinema->ticket_count, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">vé</p>
                </div>

                <!-- Doanh thu vé -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-slate-400 text-base">local_activity</span>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Vé</p>
                    </div>
                    <p class="text-2xl font-extrabold text-slate-900">{{ number_format($cinema->ticket_revenue, 0, ',', '.') }}<span class="text-sm font-semibold">đ</span></p>
                    @if($cinema->total_revenue > 0)
                        <p class="text-xs text-slate-400 mt-0.5">{{ round($cinema->ticket_revenue / $cinema->total_revenue * 100) }}% tổng DT</p>
                    @endif
                </div>

                <!-- Doanh thu F&B -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-slate-400 text-base">fastfood</span>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu F&B</p>
                    </div>
                    @if($cinema->fnb_revenue > 0)
                        <p class="text-2xl font-extrabold text-slate-900">{{ number_format($cinema->fnb_revenue, 0, ',', '.') }}<span class="text-sm font-semibold">đ</span></p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ round($cinema->fnb_revenue / $cinema->total_revenue * 100) }}% tổng DT</p>
                    @else
                        <p class="text-2xl font-extrabold text-slate-400">—</p>
                        <p class="text-xs text-slate-400 mt-0.5">Chưa có dữ liệu</p>
                    @endif
                </div>

                <!-- Tổng doanh thu -->
                <div class="px-6 py-5 bg-blue-50">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-blue-500 text-base">payments</span>
                        <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Tổng Doanh Thu</p>
                    </div>
                    <p class="text-2xl font-extrabold text-blue-700">{{ number_format($cinema->total_revenue, 0, ',', '.') }}<span class="text-sm font-semibold">đ</span></p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $cinema->rooms_count }} phòng chiếu</p>
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
