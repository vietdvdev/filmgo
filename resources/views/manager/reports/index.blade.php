@extends('layouts.manager')

@section('title', 'Báo Cáo & Thống Kê - FilmGo')

@section('content')
<div class="space-y-6">

    <div class="border-b border-slate-200 pb-4">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Báo Cáo & Thống Kê</h2>
        <p class="text-sm text-slate-500 mt-1">Chỉ tính các giao dịch đã thanh toán thành công.</p>
    </div>

    {{-- Bộ lọc --}}
    <form method="GET" action="{{ route('manager.reports.index') }}"
          class="bg-white border border-slate-200 shadow-sm p-5 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Rạp chiếu</label>
                <select name="cinema_id" class="w-full border border-slate-300 bg-slate-50 text-sm px-3 py-2 rounded-none focus:outline-none focus:border-blue-600">
                    <option value="">Tất cả rạp</option>
                    @foreach($allCinemas as $c)
                        <option value="{{ $c->id }}" @selected($filterCinemaId == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Lọc theo</label>
                <select name="filter_type" id="filter_type" onchange="toggleDateInputs(this.value)"
                        class="w-full border border-slate-300 bg-slate-50 text-sm px-3 py-2 rounded-none focus:outline-none focus:border-blue-600">
                    <option value="all"   @selected($filterType === 'all')>Tất cả thời gian</option>
                    <option value="day"   @selected($filterType === 'day')>Theo ngày</option>
                    <option value="month" @selected($filterType === 'month')>Theo tháng</option>
                    <option value="year"  @selected($filterType === 'year')>Theo năm</option>
                </select>
            </div>

            <div id="input-day" class="{{ $filterType === 'day' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Ngày</label>
                <input type="date" name="filter_date" value="{{ $filterDate }}"
                       class="w-full border border-slate-300 bg-slate-50 text-sm px-3 py-2 rounded-none focus:outline-none focus:border-blue-600">
            </div>

            <div id="input-month" class="{{ $filterType === 'month' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Tháng</label>
                <input type="month" name="filter_month" value="{{ $filterMonth }}"
                       class="w-full border border-slate-300 bg-slate-50 text-sm px-3 py-2 rounded-none focus:outline-none focus:border-blue-600">
            </div>

            <div id="input-year" class="{{ $filterType === 'year' ? '' : 'hidden' }}">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Năm</label>
                <select name="filter_year"
                        class="w-full border border-slate-300 bg-slate-50 text-sm px-3 py-2 rounded-none focus:outline-none focus:border-blue-600">
                    @for($y = now()->year; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" @selected($filterYear == $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>

        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2 rounded-none">
                <span class="material-symbols-outlined text-base">filter_alt</span> Áp dụng
            </button>
            @if($filterType !== 'all' || $filterCinemaId)
            <a href="{{ route('manager.reports.index') }}"
               class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold px-5 py-2 rounded-none">
                <span class="material-symbols-outlined text-base">close</span> Xóa bộ lọc
            </a>
            @endif
        </div>
    </form>

    {{-- ── Khi chọn 1 rạp cụ thể: hiển thị chi tiết theo phim ── --}}
    @if($filterCinemaId)
    @php
        $selectedCinema      = $allCinemas->firstWhere('id', $filterCinemaId);
        $cinemaTotalShowtimes = $movieStats->sum('showtime_count');
        $cinemaTotalTickets   = $movieStats->sum('ticket_count');
        $cinemaTicketRevenue  = $movieStats->sum('ticket_revenue');
        $cinemaFnbRevenue     = $movieStats->sum('fnb_revenue');
        $cinemaTotalRevenue   = $movieStats->sum('total_revenue');
    @endphp

    {{-- Summary cards rạp được chọn --}}
    <div class="border-l-4 border-blue-500 pl-4">
        <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Chi Tiết Rạp</p>
        <p class="text-xl font-extrabold text-slate-900">{{ $selectedCinema?->name }}</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Suất Chiếu</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemaTotalShowtimes, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vé Đã Bán</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemaTotalTickets, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Vé</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemaTicketRevenue, 0, ',', '.') }}<span class="text-sm">đ</span></p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu F&B</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($cinemaFnbRevenue, 0, ',', '.') }}<span class="text-sm">đ</span></p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-4 border-l-4 border-l-blue-500">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Tổng Doanh Thu</p>
            <p class="text-2xl font-extrabold text-blue-700 mt-1">{{ number_format($cinemaTotalRevenue, 0, ',', '.') }}<span class="text-sm">đ</span></p>
        </div>
    </div>

    {{-- Bảng theo phim --}}
    <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500 text-lg">movie</span>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Doanh Thu Theo Phim</h3>
        </div>

        @if($movieStats->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tên Phim</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Suất Chiếu</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Vé Đã Bán</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu Vé</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu F&B</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50">Tổng Doanh Thu</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach($movieStats as $i => $movie)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $movie->title }}</td>
                        <td class="px-5 py-4 text-right text-slate-700">{{ number_format($movie->showtime_count, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right text-slate-800">{{ number_format($movie->ticket_count, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right text-slate-800">{{ number_format($movie->ticket_revenue, 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right text-slate-800">
                            {{ $movie->fnb_revenue > 0 ? number_format($movie->fnb_revenue, 0, ',', '.').'đ' : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold text-blue-700 bg-blue-50">
                            {{ number_format($movie->total_revenue, 0, ',', '.') }}đ
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-800 text-white">
                    <tr>
                        <td colspan="2" class="px-5 py-4 text-xs font-bold uppercase text-slate-300">
                            Tổng Cộng ({{ $movieStats->count() }} phim)
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($cinemaTotalShowtimes, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($cinemaTotalTickets, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($cinemaTicketRevenue, 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($cinemaFnbRevenue, 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right font-extrabold text-blue-300 bg-blue-900/40">
                            {{ number_format($cinemaTotalRevenue, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="px-6 py-10 text-center">
            <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">movie_off</span>
            <p class="text-sm text-slate-500">Không có dữ liệu phim cho bộ lọc đã chọn.</p>
        </div>
        @endif
    </div>

    {{-- ── Khi không chọn rạp: hiển thị bảng tổng hợp tất cả rạp ── --}}
    @else

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng Vé Đã Bán</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['ticket_count'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu Vé</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['ticket_revenue'], 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh Thu F&B</p>
            <p class="text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($summary['fnb_revenue'], 0, ',', '.') }}đ</p>
        </div>
        <div class="bg-white border border-slate-200 shadow-sm p-5 border-l-4 border-l-blue-500">
            <p class="text-xs font-bold text-blue-500 uppercase tracking-wider">Tổng Doanh Thu</p>
            <p class="text-3xl font-extrabold text-blue-700 mt-1">{{ number_format($summary['total_revenue'], 0, ',', '.') }}đ</p>
            <p class="text-xs text-slate-400 mt-0.5">{{ $cinemas->count() }} rạp</p>
        </div>
    </div>

    <div class="bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-500 text-lg">table_chart</span>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Chi Tiết Theo Rạp</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="px-5 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Rạp Chiếu</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Vé Đã Bán</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu Vé</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Doanh Thu F&B</th>
                        <th class="px-5 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50">Tổng Doanh Thu</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($cinemas as $i => $cinema)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-900">{{ $cinema->name }}</div>
                            <div class="text-xs text-slate-400">{{ $cinema->city }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-slate-800">{{ number_format($cinema->ticket_count, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right text-slate-800">{{ number_format($cinema->ticket_revenue, 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right text-slate-800">
                            {{ $cinema->fnb_revenue > 0 ? number_format($cinema->fnb_revenue, 0, ',', '.').'đ' : '—' }}
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold text-blue-700 bg-blue-50">
                            {{ number_format($cinema->total_revenue, 0, ',', '.') }}đ
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">Không có dữ liệu.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($cinemas->count() > 0)
                <tfoot class="bg-slate-800 text-white">
                    <tr>
                        <td colspan="2" class="px-5 py-4 text-xs font-bold uppercase text-slate-300">Tổng Cộng</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($summary['ticket_count'], 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($summary['ticket_revenue'], 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right font-extrabold">{{ number_format($summary['fnb_revenue'], 0, ',', '.') }}đ</td>
                        <td class="px-5 py-4 text-right font-extrabold text-blue-300 bg-blue-900/40">{{ number_format($summary['total_revenue'], 0, ',', '.') }}đ</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @endif

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
