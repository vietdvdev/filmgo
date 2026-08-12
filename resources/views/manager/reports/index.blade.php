@extends('layouts.manager')

@section('title', 'Báo Cáo & Thống Kê - FilmGo')

@section('content')
<div class="space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Báo Cáo & Thống Kê</h2>
        <p class="text-sm text-slate-500 mt-1">Doanh thu tổng hợp theo từng rạp — chỉ tính đơn đã thanh toán.</p>
    </div>

    {{-- ── Bộ lọc ───────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('manager.reports.index') }}"
          class="bg-white border border-slate-200 shadow-sm p-5 space-y-4" id="filter-form">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Lọc theo rạp --}}
            @if($allCinemas->count() > 1)
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Rạp chiếu</label>
                <select name="cinema_id" class="w-full border border-slate-300 bg-slate-50 text-sm text-slate-800 px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none">
                    <option value="">Tất cả rạp</option>
                    @foreach($allCinemas as $c)
                        <option value="{{ $c->id }}" @selected($filterCinemaId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="cinema_id" value="">
            @endif

            {{-- Loại lọc thời gian --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Lọc theo</label>
                <select name="filter_type" id="filter_type"
                        class="w-full border border-slate-300 bg-slate-50 text-sm text-slate-800 px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none"
                        onchange="toggleDateInputs(this.value)">
                    <option value="all"   @selected($filterType === 'all')>Tất cả thời gian</option>
                    <option value="day"   @selected($filterType === 'day')>Theo ngày</option>
                    <option value="month" @selected($filterType === 'month')>Theo tháng</option>
                    <option value="year"  @selected($filterType === 'year')>Theo năm</option>
                </select>
            </div>

            {{-- Input ngày --}}
            <div id="input-day" class="{{ $filterType === 'day' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Ngày</label>
                <input type="date" name="filter_date" value="{{ $filterDate }}"
                       class="w-full border border-slate-300 bg-slate-50 text-sm text-slate-800 px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none">
            </div>

            {{-- Input tháng --}}
            <div id="input-month" class="{{ $filterType === 'month' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tháng</label>
                <input type="month" name="filter_month" value="{{ $filterMonth }}"
                       class="w-full border border-slate-300 bg-slate-50 text-sm text-slate-800 px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none">
            </div>

            {{-- Input năm --}}
            <div id="input-year" class="{{ $filterType === 'year' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Năm</label>
                <select name="filter_year"
                        class="w-full border border-slate-300 bg-slate-50 text-sm text-slate-800 px-3 py-2 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 rounded-none">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" @selected($filterYear == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

        </div>

        <div class="flex items-center gap-3 pt-1">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2 transition-colors rounded-none">
                <span class="material-symbols-outlined text-base">filter_alt</span>
                Áp dụng bộ lọc
            </button>
            @if($filterType !== 'all' || $filterCinemaId)
            <a href="{{ route('manager.reports.index') }}"
               class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-5 py-2 transition-colors rounded-none">
                <span class="material-symbols-outlined text-base">close</span>
                Xóa bộ lọc
            </a>
            @endif

            {{-- Label mô tả bộ lọc đang áp dụng --}}
            @if($filterType !== 'all' || $filterCinemaId)
            <span class="text-xs text-slate-500 italic">
                Đang lọc:
                @if($filterCinemaId) <strong>{{ $allCinemas->firstWhere('id', $filterCinemaId)?->name }}</strong> @endif
                @if($filterType === 'day' && $filterDate) · Ngày {{ \Carbon\Carbon::parse($filterDate)->format('d/m/Y') }} @endif
                @if($filterType === 'month' && $filterMonth) · Tháng {{ \Carbon\Carbon::parse($filterMonth.'-01')->format('m/Y') }} @endif
                @if($filterType === 'year' && $filterYear) · Năm {{ $filterYear }} @endif
            </span>
            @endif
        </div>
    </form>

    {{-- ── Tổng hợp (Summary Cards) ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng Vé Đã Bán</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['ticket_count'], 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-0.5">vé</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Vé</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['ticket_revenue'], 0, ',', '.') }}<span class="text-base">đ</span></p>
            @if($summary['total_revenue'] > 0)
            <p class="text-xs text-slate-400 mt-0.5">{{ round($summary['ticket_revenue'] / $summary['total_revenue'] * 100) }}% tổng DT</p>
            @endif
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu F&B</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['fnb_revenue'], 0, ',', '.') }}<span class="text-base">đ</span></p>
            @if($summary['total_revenue'] > 0)
            <p class="text-xs text-slate-400 mt-0.5">{{ round($summary['fnb_revenue'] / $summary['total_revenue'] * 100) }}% tổng DT</p>
            @endif
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Tổng Doanh Thu</p>
            <p class="text-3xl font-extrabold text-blue-700 mt-1">{{ number_format($summary['total_revenue'], 0, ',', '.') }}<span class="text-base">đ</span></p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $cinemas->count() }} rạp</p>
        </div>
    </div>

    {{-- ── Doanh thu theo phim (khi chọn 1 rạp cụ thể) ─────────────────────── --}}
    @if($filterCinemaId && $movieStats->count() > 0)
    <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500 text-lg">movie</span>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">
                Doanh Thu Theo Phim — {{ $allCinemas->firstWhere('id', $filterCinemaId)?->name }}
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-8">#</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Phim</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Vé Đã Bán</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50">Doanh Thu Vé</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Tỷ Trọng</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @php $totalMovieRevenue = $movieStats->sum('ticket_revenue'); @endphp
                    @foreach($movieStats as $i => $movie)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 font-semibold text-slate-900">{{ $movie->title }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">
                            {{ number_format($movie->ticket_count, 0, ',', '.') }}
                            <span class="text-xs text-slate-400 font-normal">vé</span>
                        </td>
                        <td class="px-5 py-3 text-right font-extrabold text-blue-700 bg-blue-50">
                            {{ number_format($movie->ticket_revenue, 0, ',', '.') }}<span class="text-xs font-semibold">đ</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($totalMovieRevenue > 0)
                                @php $pct = round($movie->ticket_revenue / $totalMovieRevenue * 100) @endphp
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 w-8 text-right">{{ $pct }}%</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($filterCinemaId && $movieStats->count() === 0)
    <div class="bg-white border border-slate-200 shadow-sm px-6 py-10 text-center">
        <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">movie_off</span>
        <p class="text-sm font-semibold text-slate-500">Không có dữ liệu phim cho rạp và bộ lọc đã chọn.</p>
    </div>
    @endif

    {{-- ── Bảng thống kê chi tiết ───────────────────────────────────────────── --}}
    <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">

        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500 text-lg">table_chart</span>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Chi Tiết Theo Rạp</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-8">#</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rạp Chiếu</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Thành Phố</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Trạng Thái</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Vé Đã Bán</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu Vé</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu F&B</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50">Tổng Doanh Thu</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Tỷ Trọng</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($cinemas as $i => $cinema)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-4 text-slate-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $cinema->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $cinema->rooms_count }} phòng chiếu</div>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $cinema->city }}</td>
                        <td class="px-5 py-4">
                            @if($cinema->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-700 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>Hoạt động
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold bg-red-100 text-red-700 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Ngừng
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right font-semibold text-slate-800">
                            {{ number_format($cinema->ticket_count, 0, ',', '.') }}
                            <span class="text-xs text-slate-400 font-normal">vé</span>
                        </td>
                        <td class="px-5 py-4 text-right font-semibold text-slate-800">
                            {{ number_format($cinema->ticket_revenue, 0, ',', '.') }}<span class="text-xs text-slate-400">đ</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($cinema->fnb_revenue > 0)
                                <span class="font-semibold text-slate-800">{{ number_format($cinema->fnb_revenue, 0, ',', '.') }}<span class="text-xs text-slate-400">đ</span></span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right bg-blue-50">
                            <span class="font-extrabold text-blue-700">{{ number_format($cinema->total_revenue, 0, ',', '.') }}<span class="text-xs font-semibold">đ</span></span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($summary['total_revenue'] > 0)
                                @php $pct = round($cinema->total_revenue / $summary['total_revenue'] * 100) @endphp
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 w-8 text-right">{{ $pct }}%</span>
                                </div>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">insert_chart</span>
                            <p class="text-sm font-semibold text-slate-500">Không có dữ liệu cho bộ lọc đã chọn.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

                {{-- Footer tổng cộng --}}
                @if($cinemas->count() > 0)
                <tfoot class="bg-slate-800 text-white">
                    <tr>
                        <td colspan="4" class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-300">
                            Tổng Cộng ({{ $cinemas->count() }} rạp)
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold">
                            {{ number_format($summary['ticket_count'], 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-400">vé</span>
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold">
                            {{ number_format($summary['ticket_revenue'], 0, ',', '.') }}<span class="text-xs text-slate-400">đ</span>
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold">
                            {{ number_format($summary['fnb_revenue'], 0, ',', '.') }}<span class="text-xs text-slate-400">đ</span>
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold text-blue-300 bg-blue-900/40">
                            {{ number_format($summary['total_revenue'], 0, ',', '.') }}<span class="text-xs font-semibold">đ</span>
                        </td>
                        <td class="px-5 py-4 text-right text-xs font-bold text-slate-400">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function toggleDateInputs(type) {
    ['day', 'month', 'year'].forEach(t => {
        document.getElementById('input-' + t)?.classList.toggle('hidden', t !== type);
    });
}
</script>
@endsection
