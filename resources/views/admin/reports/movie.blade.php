@extends('layouts.admin')

@section('title', 'Báo Cáo Doanh Thu - FilmGo Admin')

@php
    function fmtVnd(int $n): string {
        return number_format($n, 0, ',', '.') . 'đ';
    }

    $hasFilter = $movieId || $day || $month || $year || $from || $to;

    // Nhãn khoảng thời gian hiển thị
    $periodLabel = 'Tất cả thời gian';
    if ($from && $to)   $periodLabel = \Carbon\Carbon::parse($from)->format('d/m/Y') . ' → ' . \Carbon\Carbon::parse($to)->format('d/m/Y');
    elseif ($from)      $periodLabel = 'Từ ' . \Carbon\Carbon::parse($from)->format('d/m/Y');
    elseif ($to)        $periodLabel = 'Đến ' . \Carbon\Carbon::parse($to)->format('d/m/Y');
    elseif ($day && $month && $year) $periodLabel = "Ngày $day/$month/$year";
    elseif ($month && $year) $periodLabel = "Tháng $month/$year";
    elseif ($year)      $periodLabel = "Năm $year";
    elseif ($month)     $periodLabel = "Tháng $month";
    elseif ($day)       $periodLabel = "Ngày $day";

    $years = range(date('Y'), 2024, -1);
    $months = range(1, 12);
@endphp

@section('content')
<div class="flex-grow overflow-y-auto bg-[#f8f9fb] text-zinc-700 p-6 md:p-8">

    {{-- ── Header ── --}}
    <div class="mt-6 md:mt-10 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-1">
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-zinc-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600 text-3xl">bar_chart</span>
                Báo Cáo Doanh Thu
            </h1>
            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-500 bg-white border border-zinc-200 rounded-xl px-3 py-1.5">
                <span class="material-symbols-outlined text-sm text-red-500">schedule</span>
                {{ $periodLabel }}
                @if($movieId)
                    &nbsp;·&nbsp;
                    <span class="text-red-600">{{ $allMovies->firstWhere('id', $movieId)?->title }}</span>
                @endif
            </span>
        </div>
        <p class="text-xs text-zinc-400 font-medium">Thống kê doanh thu vé & F&B theo từng rạp chiếu phim</p>
    </div>

    {{-- ── Bộ lọc ── --}}
    <form method="GET" action="{{ route('admin.reports.movie') }}"
          class="bg-white border border-zinc-200/80 rounded-2xl shadow-sm p-4 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

            {{-- Phim --}}
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Phim</label>
                <select name="movie_id"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
                    <option value="">— Tất cả phim —</option>
                    @foreach($allMovies as $m)
                        <option value="{{ $m->id }}" @selected($movieId == $m->id)>{{ $m->title }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Ngày --}}
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Ngày</label>
                <input type="number" name="day" min="1" max="31" placeholder="1–31"
                    value="{{ $day }}"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
            </div>

            {{-- Tháng --}}
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Tháng</label>
                <select name="month"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
                    <option value="">— Tháng —</option>
                    @foreach($months as $m)
                        <option value="{{ $m }}" @selected($month == $m)>Tháng {{ $m }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Năm --}}
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-1">Năm</label>
                <select name="year"
                    class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
                    <option value="">— Năm —</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Nút --}}
            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-sm">search</span>
                    Lọc
                </button>
                @if($hasFilter)
                <a href="{{ route('admin.reports.movie') }}"
                   class="flex items-center justify-center px-3 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-500 text-xs font-bold rounded-xl transition-colors">
                    <span class="material-symbols-outlined text-sm">close</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Khoảng ngày tuỳ chọn --}}
        <div class="mt-3 pt-3 border-t border-zinc-100 flex flex-wrap items-center gap-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Khoảng ngày</span>
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-400 font-semibold">Từ</span>
                <input type="date" name="from" value="{{ $from }}"
                    class="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
                <span class="text-xs text-zinc-400 font-semibold">Đến</span>
                <input type="date" name="to" value="{{ $to }}"
                    class="bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-1.5 text-xs font-semibold text-zinc-700 focus:outline-none focus:border-red-400">
            </div>
            <span class="text-[10px] text-zinc-300 italic">* Khoảng ngày sẽ ghi đè bộ lọc ngày/tháng/năm</span>
        </div>
    </form>

    {{-- ── KPI Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Tổng vé --}}
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Tổng vé bán</p>
                <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-500 text-base">local_activity</span>
                </div>
            </div>
            <p class="text-3xl font-black text-zinc-900">{{ number_format($summary['ticket_count']) }}</p>
            <p class="text-[11px] text-zinc-400">vé đã bán thành công</p>
        </div>

        {{-- Doanh thu vé --}}
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Doanh thu vé</p>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-emerald-500 text-base">confirmation_number</span>
                </div>
            </div>
            <p class="text-2xl font-black text-zinc-900 leading-tight">{{ fmtVnd($summary['ticket_revenue']) }}</p>
            <p class="text-[11px] text-zinc-400">từ bán vé xem phim</p>
        </div>

        {{-- Doanh thu F&B --}}
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 shadow-sm flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Doanh thu F&B</p>
                <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-500 text-base">fastfood</span>
                </div>
            </div>
            <p class="text-2xl font-black text-zinc-900 leading-tight">{{ fmtVnd($summary['fnb_revenue']) }}</p>
            <p class="text-[11px] text-zinc-400">combo & bắp nước</p>
        </div>

        {{-- Tổng doanh thu --}}
        <div class="bg-gradient-to-br from-red-600 to-red-700 rounded-2xl p-5 shadow-md shadow-red-200 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-black uppercase tracking-widest text-red-200">Tổng doanh thu</p>
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-base">payments</span>
                </div>
            </div>
            <p class="text-2xl font-black text-white leading-tight">{{ fmtVnd($summary['total_revenue']) }}</p>
            <p class="text-[11px] text-red-200">
                @if($movieId)
                    {{ $allMovies->firstWhere('id', $movieId)?->title }}
                @else
                    tất cả {{ $allMovies->count() }} phim
                @endif
            </p>
        </div>
    </div>

    {{-- ── Bảng chi tiết ── --}}
    <div class="bg-white border border-zinc-200/80 rounded-3xl shadow-sm overflow-hidden">

        {{-- Table header --}}
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center gap-3">
            <span class="material-symbols-outlined text-red-600 text-xl">movie</span>
            <h2 class="text-sm font-black uppercase tracking-wider text-zinc-800">Chi Tiết Theo Phim</h2>
            <span class="ml-auto text-[11px] text-zinc-400 font-semibold bg-zinc-50 border border-zinc-100 rounded-lg px-2.5 py-1">
                {{ $moviesList->count() }} phim
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-100 text-[10px] font-black uppercase tracking-widest text-zinc-400">
                        <th class="px-6 py-3.5 text-left">#</th>
                        <th class="px-6 py-3.5 text-left">Phim</th>
                        <th class="px-6 py-3.5 text-right">Số vé bán</th>
                        <th class="px-6 py-3.5 text-right">Doanh thu vé</th>
                        <th class="px-6 py-3.5 text-right">Doanh thu F&B</th>
                        <th class="px-6 py-3.5 text-right">Tổng doanh thu</th>
                        <th class="px-6 py-3.5 text-right">Tỷ trọng</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($moviesList as $i => $movie)
                    @php
                        $pct = $summary['total_revenue'] > 0
                            ? round($movie['total_revenue'] / $summary['total_revenue'] * 100, 1)
                            : 0;
                    @endphp
                    <tr class="hover:bg-zinc-50/60 transition-colors group">
                        <td class="px-6 py-4 text-zinc-300 font-bold text-xs">{{ $i + 1 }}</td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-zinc-800 group-hover:text-red-600 transition-colors">{{ $movie['title'] }}</p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($movie['ticket_count'] > 0)
                                <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 font-bold text-xs px-2.5 py-1 rounded-lg">
                                    <span class="material-symbols-outlined text-xs">local_activity</span>
                                    {{ number_format($movie['ticket_count']) }}
                                </span>
                            @else
                                <span class="text-zinc-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-zinc-700">
                            @if($movie['ticket_revenue'] > 0)
                                {{ fmtVnd($movie['ticket_revenue']) }}
                            @else
                                <span class="text-zinc-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($movie['fnb_revenue'] > 0)
                                <span class="font-semibold text-amber-600">{{ fmtVnd($movie['fnb_revenue']) }}</span>
                            @else
                                <span class="text-zinc-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-black text-red-600">{{ fmtVnd($movie['total_revenue']) }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-full bg-red-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-zinc-500 w-10 text-right">{{ $pct }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <span class="material-symbols-outlined text-zinc-200 text-5xl block mb-3">search_off</span>
                            <p class="text-zinc-400 font-semibold text-sm">Không có dữ liệu phù hợp với bộ lọc.</p>
                            @if($hasFilter)
                            <a href="{{ route('admin.reports.movie') }}" class="mt-3 inline-block text-xs text-red-500 hover:underline font-semibold">Xóa bộ lọc</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                @if($moviesList->isNotEmpty())
                <tfoot>
                    <tr class="bg-zinc-50 border-t-2 border-zinc-200">
                        <td colspan="2" class="px-6 py-3.5 text-xs font-black text-zinc-600 uppercase tracking-wider">
                            Tổng cộng
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 font-black text-xs px-2.5 py-1 rounded-lg">
                                {{ number_format($summary['ticket_count']) }} vé
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right text-xs font-black text-zinc-700">
                            {{ fmtVnd($summary['ticket_revenue']) }}
                        </td>
                        <td class="px-6 py-3.5 text-right text-xs font-black text-amber-600">
                            {{ fmtVnd($summary['fnb_revenue']) }}
                        </td>
                        <td class="px-6 py-3.5 text-right text-sm font-black text-red-600">
                            {{ fmtVnd($summary['total_revenue']) }}
                        </td>
                        <td class="px-6 py-3.5 text-right text-xs font-black text-zinc-400">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
