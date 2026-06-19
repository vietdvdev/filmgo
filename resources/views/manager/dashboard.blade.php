@extends('layouts.manager')

@section('title', 'Tổng Quan Chi Nhánh - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Hệ Thống Vận Hành Rạp</h2>
        <p class="text-sm text-slate-500 mt-1">Xin chào, {{ auth()->user()->full_name }}. Dưới đây là hoạt động hôm nay tại rạp của bạn.</p>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Stat Card 1 -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Phòng Chiếu Đang Chạy</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $roomCount }}</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">meeting_room</span>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng Nhân Sự Rạp</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $staffCount }}</p>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">group</span>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Suất Chiếu Hôm Nay</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $showtimeTodayCount }}</p>
            </div>
            <div class="p-3 bg-purple-50 text-purple-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">schedule</span>
            </div>
        </div>
    </div>

    <!-- Welcome Panel -->
    <div class="bg-white border border-slate-200 shadow-sm p-8 rounded-none">
        <h3 class="text-lg font-bold text-slate-900 uppercase">Quy Trình Quản Lý Rạp Chiếu</h3>
        <p class="text-sm text-slate-600 mt-2 leading-relaxed">
            Với vai trò là Quản lý rạp, bạn có toàn quyền quản trị và chịu trách nhiệm điều hành các mặt hoạt động tại chi nhánh rạp của mình bao gồm: phân công nhân viên bán vé, cấu hình và bảo trì phòng chiếu, lên lịch suất chiếu hàng tuần cho các phim hot, theo dõi báo cáo doanh thu và tỷ lệ lấp đầy ghế ngồi theo thời gian thực.
        </p>
        <div class="mt-6 flex flex-wrap gap-4">
            <a href="{{ route('manager.showtimes.index') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white font-semibold text-sm px-5 py-2.5 hover:bg-blue-700 transition-colors rounded-none">
                <span class="material-symbols-outlined text-sm">schedule</span> Quản lý Lịch Suất Chiếu
            </a>
            <a href="{{ route('manager.staff.index') }}" class="inline-flex items-center gap-2 border border-slate-300 text-slate-700 font-semibold text-sm px-5 py-2.5 hover:bg-slate-50 transition-colors rounded-none">
                <span class="material-symbols-outlined text-sm">group</span> Xem Danh Sách Nhân Sự
            </a>
        </div>
    </div>
</div>
@endsection
