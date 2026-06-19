@extends('layouts.manager')

@section('title', 'Báo Cáo & Thống Kê Doanh Thu - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Báo Cáo & Thống Kê</h2>
        <p class="text-sm text-slate-500 mt-1">Phân tích tình hình kinh doanh, doanh thu và hiệu suất lấp đầy phòng chiếu của rạp.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <!-- Revenue Today -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Hôm Nay</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ number_format($revenueToday, 0, ',', '.') }}đ</p>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
        </div>

        <!-- Revenue This Week -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Tuần Này</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ number_format($revenueWeek, 0, ',', '.') }}đ</p>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">monetization_on</span>
            </div>
        </div>

        <!-- Occupancy Rate -->
        <div class="bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between rounded-none">
            <div class="space-y-2">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tỷ Lệ Lấp Đầy Ghế</p>
                <p class="text-3xl font-extrabold text-slate-900">{{ $occupancyRate }}%</p>
                <p class="text-[10px] text-slate-500">Đã đặt {{ $bookedSeats }} / {{ $totalSeats }} ghế tổng cộng</p>
            </div>
            <div class="p-3 bg-purple-50 text-purple-600 rounded-none">
                <span class="material-symbols-outlined text-2xl">percent</span>
            </div>
        </div>
    </div>

    <!-- Revenue by Movie Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-none">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span class="material-symbols-outlined text-lg text-slate-500">movie</span> Doanh Thu Theo Phim
            </h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tên Phim</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Số Lượng Vé Đặt</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng Doanh Thu</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($movieRevenues as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">{{ $item->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ number_format($item->total_bookings, 0, ',', '.') }} vé</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 text-right">{{ number_format($item->total_revenue, 0, ',', '.') }}đ</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">info</span>
                                Chưa ghi nhận dữ liệu doanh thu bán vé cho phim nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
