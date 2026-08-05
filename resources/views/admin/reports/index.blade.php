@extends('layouts.admin')

@section('title', 'Báo Cáo Doanh Thu - FilmGo Admin')

@php
    function fmtVnd(int $n): string {
        return number_format($n, 0, ',', '.') . 'đ';
    }
@endphp

@section('content')
<div class="flex-grow overflow-y-auto bg-[#f8f9fb] text-zinc-700 p-6 md:p-8">

    {{-- Header --}}
    <div class="border-b border-zinc-200 pb-6 mb-8 mt-6 md:mt-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-zinc-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600 text-3xl">bar_chart</span>
                Báo Cáo Doanh Thu
            </h1>
            <p class="text-xs text-zinc-500 font-medium mt-1">Thống kê doanh thu theo từng rạp chiếu phim</p>
        </div>

        {{-- Bộ lọc ngày --}}
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1.5 bg-white border border-zinc-200 rounded-xl px-3 py-2 text-xs">
                <span class="material-symbols-outlined text-zinc-400 text-base">calendar_today</span>
                <span class="text-zinc-400 font-semibold">Từ</span>
                <input type="date" name="from" value="{{ $from }}"
                    class="bg-transparent text-zinc-700 font-semibold focus:outline-none">
            </div>
            <div class="flex items-center gap-1.5 bg-white border border-zinc-200 rounded-xl px-3 py-2 text-xs">
                <span class="material-symbols-outlined text-zinc-400 text-base">calendar_today</span>
                <span class="text-zinc-400 font-semibold">Đến</span>
                <input type="date" name="to" value="{{ $to }}"
                    class="bg-transparent text-zinc-700 font-semibold focus:outline-none">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-colors">
                Lọc
            </button>
            @if($from || $to)
            <a href="{{ route('admin.reports.index') }}"
               class="px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-600 text-xs font-bold rounded-xl transition-colors">
                Xóa lọc
            </a>
            @endif
        </form>
    </div>

    {{-- KPI tổng --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Tổng vé bán</p>
            <p class="text-2xl font-black text-zinc-900">{{ number_format($summary['ticket_count']) }}</p>
            <p class="text-[11px] text-zinc-400 mt-1">vé</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Doanh thu vé</p>
            <p class="text-2xl font-black text-zinc-900">{{ fmtVnd($summary['ticket_revenue']) }}</p>
            <p class="text-[11px] text-zinc-400 mt-1">từ bán vé</p>
        </div>
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm">
            <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Doanh thu F&B</p>
            <p class="text-2xl font-black text-zinc-900">{{ fmtVnd($summary['fnb_revenue']) }}</p>
            <p class="text-[11px] text-zinc-400 mt-1">combo & bắp nước</p>
        </div>
        <div class="bg-white border border-red-100 rounded-2xl p-5 shadow-sm bg-red-50/40">
            <p class="text-[10px] font-black uppercase tracking-widest text-red-400 mb-1">Tổng doanh thu</p>
            <p class="text-2xl font-black text-red-600">{{ fmtVnd($summary['total_revenue']) }}</p>
            <p class="text-[11px] text-red-300 mt-1">tất cả rạp</p>
        </div>
    </div>

    {{-- Bảng chi tiết theo rạp --}}
    <div class="bg-white border border-zinc-200/80 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center gap-2">
            <span class="material-symbols-outlined text-red-600 text-xl">corporate_fare</span>
            <h2 class="text-sm font-black uppercase tracking-wider text-zinc-800">Chi Tiết Theo Rạp</h2>
            @if($from || $to)
            <span class="ml-auto text-[11px] text-zinc-400 font-semibold">
                {{ $from ? \Carbon\Carbon::parse($from)->format('d/m/Y') : '...' }}
                →
                {{ $to ? \Carbon\Carbon::parse($to)->format('d/m/Y') : '...' }}
            </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-100 text-[10px] font-black uppercase tracking-widest text-zinc-400">
                        <th class="px-6 py-3 text-left">Rạp chiếu</th>
                        <th class="px-6 py-3 text-right">Số vé bán</th>
                        <th class="px-6 py-3 text-right">Doanh thu vé</th>
                        <th class="px-6 py-3 text-right">Doanh thu F&B</th>
                        <th class="px-6 py-3 text-right">Tổng doanh thu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($cinemas as $cinema)
                    <tr class="hover:bg-zinc-50/60 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-zinc-800">{{ $cinema['name'] }}</p>
                            <p class="text-[11px] text-zinc-400 mt-0.5">{{ $cinema['address'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-zinc-700">{{ number_format($cinema['ticket_count']) }}</span>
                            <span class="text-[11px] text-zinc-400 ml-1">vé</span>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-zinc-700">
                            {{ fmtVnd($cinema['ticket_revenue']) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($cinema['fnb_revenue'] > 0)
                                <span class="font-semibold text-amber-600">{{ fmtVnd($cinema['fnb_revenue']) }}</span>
                            @else
                                <span class="text-zinc-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-black text-red-600 text-base">{{ fmtVnd($cinema['total_revenue']) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-zinc-400 text-sm">Không có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($cinemas->isNotEmpty())
                <tfoot>
                    <tr class="bg-zinc-50 border-t-2 border-zinc-200 text-xs font-black text-zinc-600 uppercase tracking-wider">
                        <td class="px-6 py-3">Tổng cộng</td>
                        <td class="px-6 py-3 text-right">{{ number_format($summary['ticket_count']) }} vé</td>
                        <td class="px-6 py-3 text-right">{{ fmtVnd($summary['ticket_revenue']) }}</td>
                        <td class="px-6 py-3 text-right text-amber-600">{{ fmtVnd($summary['fnb_revenue']) }}</td>
                        <td class="px-6 py-3 text-right text-red-600 text-sm">{{ fmtVnd($summary['total_revenue']) }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
