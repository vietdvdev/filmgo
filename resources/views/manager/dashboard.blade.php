@extends('layouts.manager')

@section('title', 'Tổng Quan Chi Nhánh - FilmGo Manager')

@push('head')
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>[v-cloak]{display:none}</style>
    <script>
        // Gắn CSRF token cho mọi request axios
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['Accept'] = 'application/json';
    </script>
@endpush

@section('content')
<div id="managerDashboardApp" v-cloak>

    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-slate-500 font-medium tracking-wide">Đang tải dữ liệu dashboard...</p>
        </div>
    </div>

    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b border-slate-200 pb-6 mb-8 gap-4 mt-2">
        <div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600 text-3xl">analytics</span>
                Tổng Quan Rạp — {{ $cinema->name }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 font-medium mt-1">Dữ liệu vận hành và doanh thu chi nhánh theo thời gian thực</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button @click="setFilter('today')"
                    :class="filterType === 'today' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">Hôm nay</button>
            <button @click="setFilter('week')"
                    :class="filterType === 'week' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">Tuần này</button>
            <button @click="setFilter('month')"
                    :class="filterType === 'month' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">Tháng này</button>
            <button @click="setFilter('custom')"
                    :class="filterType === 'custom' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-xs">calendar_month</span>Tùy chọn ngày
            </button>
            <div v-if="filterType === 'custom'" class="flex items-center gap-2 bg-white border border-slate-200 p-1.5 rounded-xl ml-2 shadow-sm">
                <input type="date" v-model="customStartDate" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                <span class="text-slate-400 text-xs">đến</span>
                <input type="date" v-model="customEndDate" class="bg-slate-50 border border-slate-200 rounded-lg px-2 py-1 text-xs text-slate-800 focus:outline-none focus:border-blue-600">
                <button @click="applyCustomFilter" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all">Áp dụng</button>
            </div>
        </div>
    </div>

    <!-- Active Filter Info -->
    <div class="mb-6 bg-slate-100 border border-slate-200/80 px-4 py-3 rounded-2xl flex items-center gap-2 text-xs text-slate-500 font-semibold shadow-sm">
        <span class="material-symbols-outlined text-sm text-blue-600">info</span>
        Đang hiển thị thống kê từ ngày
        <span class="text-slate-800 font-bold bg-white px-2 py-0.5 rounded border border-slate-200">@{{ formatDateDisplay(startDate) }}</span>
        đến ngày
        <span class="text-slate-800 font-bold bg-white px-2 py-0.5 rounded border border-slate-200">@{{ formatDateDisplay(endDate) }}</span>
    </div>

    <!-- ── KPI Cards ── -->
    <div v-if="kpis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <!-- Doanh Thu -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Doanh Thu</p>
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">@{{ formatShortMoney(kpis.revenue.today.total) }}</h3>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl border border-blue-100">
                    <span class="material-symbols-outlined">payments</span>
                </div>
            </div>
            <div class="flex items-center pt-4 border-t border-slate-100">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.revenue.growth.total_pct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm">@{{ kpis.revenue.growth.total_pct >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
                    @{{ Math.abs(kpis.revenue.growth.total_pct) }}%
                    <span class="text-slate-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 flex justify-between text-[11px] text-slate-500 font-semibold">
                <span>Vé: @{{ formatMoney(kpis.revenue.today.ticket) }}</span>
                <span>Bắp nước: @{{ formatMoney(kpis.revenue.today.combo) }}</span>
            </div>
        </div>

        <!-- Vé Đã Bán -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Vé Đã Bán</p>
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">@{{ kpis.tickets.today }} <span class="text-xs text-slate-500 font-bold">vé</span></h3>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100">
                    <span class="material-symbols-outlined">local_activity</span>
                </div>
            </div>
            <div class="flex items-center pt-4 border-t border-slate-100">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.tickets.growth_pct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm">@{{ kpis.tickets.growth_pct >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
                    @{{ Math.abs(kpis.tickets.growth_pct) }}%
                    <span class="text-slate-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 font-semibold">
                <span>Kỳ trước: @{{ kpis.tickets.yesterday }} vé</span>
            </div>
        </div>

        <!-- Tỷ Lệ Lấp Đầy -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Tỷ Lệ Lấp Đầy</p>
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 mt-1">@{{ kpis.occupancy.today_rate }}%</h3>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100">
                    <span class="material-symbols-outlined">event_seat</span>
                </div>
            </div>
            <div class="flex items-center pt-4 border-t border-slate-100">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.occupancy.growth_points >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm">@{{ kpis.occupancy.growth_points >= 0 ? 'arrow_upward' : 'arrow_downward' }}</span>
                    @{{ Math.abs(kpis.occupancy.growth_points) }}%
                    <span class="text-slate-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 text-[11px] text-slate-500 font-semibold">
                <span>Ghế bán: @{{ kpis.occupancy.today_booked_seats }}/@{{ kpis.occupancy.today_total_seats }}</span>
            </div>
        </div>

        <!-- Phương Thức Thanh Toán -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4 gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-400 font-bold uppercase tracking-wider truncate">Phương Thức Thanh Toán</p>
                    <h3 class="text-base md:text-lg font-black text-slate-900 mt-2">Online: @{{ kpis.payment_methods.online_pct }}%</h3>
                </div>
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl border border-indigo-100 shrink-0">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                </div>
            </div>
            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mt-4">
                <div class="bg-indigo-600 h-full rounded-full transition-all duration-500"
                     :style="{ width: kpis.payment_methods.online_pct + '%' }"></div>
            </div>
            <div class="mt-4 flex justify-between text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                <span>Online: @{{ formatShortMoney(kpis.payment_methods.online_revenue) }}</span>
                <span>Tại quầy: @{{ formatShortMoney(kpis.payment_methods.counter_revenue) }}</span>
            </div>
        </div>

    </div>

    <!-- ── Biểu Đồ ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Biểu đồ Doanh thu -->
        <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-black uppercase tracking-wider text-slate-800">Biểu Đồ Doanh Thu</h3>
                <span class="text-xs text-slate-400 font-bold">Lọc theo ngày chọn</span>
            </div>
            <div class="h-[320px] relative w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Phim Thị Phần -->
        <div class="lg:col-span-1 bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-black uppercase tracking-wider text-slate-800 mb-6">Top 5 Phim Thị Phần</h3>
                <div class="h-[230px] relative w-full flex items-center justify-center">
                    <canvas id="movieChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 text-[10px] text-slate-400 font-semibold flex justify-between uppercase">
                <span>Dữ liệu vé kỳ này</span>
                <span>Thống kê phân bổ %</span>
            </div>
        </div>

    </div>

    <!-- ── Doanh Thu Theo Phim ── -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm mb-8">
        <h3 class="text-base font-black uppercase tracking-wider text-slate-800 mb-6 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-600">movie</span>
            Doanh Thu Theo Phim Tại Rạp
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-semibold text-slate-500">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-black tracking-wider">
                        <th class="py-4 w-8">#</th>
                        <th class="py-4">Tên Phim</th>
                        <th class="py-4 text-right">Số Vé</th>
                        <th class="py-4 text-right">Doanh Thu</th>
                        <th class="py-4 text-right">Thị Phần</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(movie, index) in topMovies" :key="index" class="border-b border-slate-100/80 hover:bg-slate-50/50 transition-all">
                        <td class="py-4">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black text-white"
                                  :style="{ backgroundColor: movieColors[index] }">@{{ index + 1 }}</span>
                        </td>
                        <td class="py-4 font-bold text-slate-800">@{{ movie.title }}</td>
                        <td class="py-4 text-right text-slate-600">@{{ movie.tickets_count }} vé</td>
                        <td class="py-4 text-right font-bold text-slate-900">@{{ formatMoney(movie.revenue) }}</td>
                        <td class="py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-20 bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" :style="{ width: movie.percentage + '%', backgroundColor: movieColors[index] }"></div>
                                </div>
                                <span class="font-black text-slate-700 w-10 text-right">@{{ movie.percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="topMovies.length === 0">
                        <td colspan="5" class="py-8 text-center text-slate-400 italic">Không có dữ liệu phim trong kỳ này.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Suất Chiếu Hôm Nay ── -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm">
        <h3 class="text-base font-black uppercase tracking-wider text-slate-800 mb-6 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            Suất Chiếu Đang Vận Hành (Hôm Nay)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-semibold text-slate-500">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-black tracking-wider">
                        <th class="py-4">Phim</th>
                        <th class="py-4">Phòng chiếu</th>
                        <th class="py-4">Giờ chiếu</th>
                        <th class="py-4 text-right">Lấp đầy rạp</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="st in showtimes" :key="st.id" class="border-b border-slate-100/80 hover:bg-slate-50/50 transition-all">
                        <td class="py-4 font-bold text-slate-800">@{{ st.movie_title }}</td>
                        <td class="py-4 text-slate-600">@{{ st.room_name }}</td>
                        <td class="py-4 text-blue-600 font-bold">@{{ st.start_time }}</td>
                        <td class="py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <div class="w-24 bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         :class="st.occupancy_percentage >= 90 ? 'bg-red-500 animate-pulse' : 'bg-emerald-500'"
                                         :style="{ width: st.occupancy_percentage + '%' }"></div>
                                </div>
                                <span class="w-12 font-black text-slate-800">@{{ st.occupancy_percentage }}%</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="showtimes.length === 0">
                        <td colspan="4" class="py-8 text-center text-slate-400 italic">Không có suất chiếu nào hoạt động trong hôm nay.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Đơn Đặt Vé & Đặt Bắp Nước Gần Đây ── -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        <!-- Đơn Đặt Vé Gần Đây -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm">
            <h3 class="text-base font-black uppercase tracking-wider text-slate-800 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">local_activity</span>
                Đơn Đặt Vé Gần Đây
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold text-slate-500">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-black tracking-wider">
                            <th class="py-3">Mã đơn</th>
                            <th class="py-3">Khách hàng</th>
                            <th class="py-3">Phim</th>
                            <th class="py-3 text-center">Vé</th>
                            <th class="py-3 text-right">Tổng tiền</th>
                            <th class="py-3 text-right">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in recentBookings" :key="b.id" class="border-b border-slate-100/80 hover:bg-slate-50/50 transition-all">
                            <td class="py-3">
                                <span class="font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">@{{ b.booking_code }}</span>
                            </td>
                            <td class="py-3 font-bold text-slate-800 max-w-[100px] truncate">@{{ b.customer_name }}</td>
                            <td class="py-3 text-slate-600 max-w-[120px] truncate">@{{ b.movie_title }}</td>
                            <td class="py-3 text-center">
                                <span class="bg-blue-50 text-blue-700 font-black px-2 py-0.5 rounded-full text-[10px]">@{{ b.ticket_count }} vé</span>
                            </td>
                            <td class="py-3 text-right font-bold text-slate-900">@{{ formatMoney(b.final_total) }}</td>
                            <td class="py-3 text-right text-slate-400">@{{ b.created_at }}</td>
                        </tr>
                        <tr v-if="recentBookings.length === 0">
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">Không có đơn đặt vé trong kỳ này.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Đơn Đặt Bắp Nước Gần Đây -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-sm">
            <h3 class="text-base font-black uppercase tracking-wider text-slate-800 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-amber-500">fastfood</span>
                Đơn Bắp Nước Gần Đây
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold text-slate-500">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 uppercase font-black tracking-wider">
                            <th class="py-3">Mã đơn</th>
                            <th class="py-3">Khách hàng</th>
                            <th class="py-3 text-center">SL combo</th>
                            <th class="py-3 text-center">Kênh</th>
                            <th class="py-3 text-right">Tổng tiền</th>
                            <th class="py-3 text-right">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="b in recentComboBookings" :key="b.id" class="border-b border-slate-100/80 hover:bg-slate-50/50 transition-all">
                            <td class="py-3">
                                <span class="font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">@{{ b.booking_code }}</span>
                            </td>
                            <td class="py-3 font-bold text-slate-800 max-w-[100px] truncate">@{{ b.customer_name }}</td>
                            <td class="py-3 text-center">
                                <span class="bg-amber-50 text-amber-700 font-black px-2 py-0.5 rounded-full text-[10px]">@{{ b.combo_qty }} món</span>
                            </td>
                            <td class="py-3 text-center">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                      :class="b.channel === 'counter' ? 'bg-slate-100 text-slate-600' : 'bg-indigo-50 text-indigo-600'">
                                    @{{ b.channel === 'counter' ? 'Tại quầy' : 'Online' }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-bold text-slate-900">@{{ formatMoney(b.final_total) }}</td>
                            <td class="py-3 text-right text-slate-400">@{{ b.created_at }}</td>
                        </tr>
                        <tr v-if="recentComboBookings.length === 0">
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">Không có đơn bắp nước trong kỳ này.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
const { createApp, ref, onMounted } = Vue;

const getTodayString = () => new Date().toISOString().split('T')[0];
const getStartOfWeek = () => {
    const d = new Date(), day = d.getDay(), diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff)).toISOString().split('T')[0];
};
const getEndOfWeek = () => {
    const d = new Date(), day = d.getDay(), diff = d.getDate() - day + (day === 0 ? 0 : 7);
    return new Date(d.setDate(diff)).toISOString().split('T')[0];
};
const getStartOfMonth = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0]; };
const getEndOfMonth   = () => { const d = new Date(); return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().split('T')[0]; };

createApp({
    setup() {
        const kpis                = ref(null);
        const showtimes            = ref([]);
        const topMovies            = ref([]);
        const recentBookings       = ref([]);
        const recentComboBookings  = ref([]);
        const loading         = ref(true);
        const filterType      = ref('today');
        const startDate       = ref(getTodayString());
        const endDate         = ref(getTodayString());
        const customStartDate = ref(getTodayString());
        const customEndDate   = ref(getTodayString());

        const movieColors = ['#3b82f6','#fbbf24','#10b981','#e50914','#a855f7'];

        let revenueChartInstance = null;
        let movieChartInstance   = null;

        const formatMoney = (val) => new Intl.NumberFormat('vi-VN').format(val) + 'đ';
        const formatShortMoney = (val) => val >= 1000000 ? (val / 1000000).toFixed(1) + 'M đ' : formatMoney(val);
        const formatDateDisplay = (d) => { if (!d) return ''; const p = d.split('-'); return `${p[2]}/${p[1]}/${p[0]}`; };

        const fetchData = async () => {
            loading.value = true;
            try {
                const params = { start_date: startDate.value, end_date: endDate.value };

                const kpisRes = await axios.get('/manager/dashboard/api/kpis', { params });
                kpis.value = kpisRes.data;

                const showtimesRes = await axios.get('/manager/dashboard/api/ops/today-showtimes');
                showtimes.value = showtimesRes.data;

                const topMoviesRes = await axios.get('/manager/dashboard/api/charts/top-movies', { params });
                topMovies.value = topMoviesRes.data.top_movies || [];

                const [recentBookingsRes, recentComboRes] = await Promise.all([
                    axios.get('/manager/dashboard/api/recent-bookings', { params }),
                    axios.get('/manager/dashboard/api/recent-combo-bookings', { params }),
                ]);
                recentBookings.value      = recentBookingsRes.data;
                recentComboBookings.value = recentComboRes.data;

                await initCharts(params);
            } catch (e) {
                console.error('Lỗi tải dashboard:', e.response?.status, e.response?.data ?? e.message);
            } finally {
                loading.value = false;
            }
        };

        const initCharts = async (params) => {
            // Biểu đồ doanh thu
            try {
                const res  = await axios.get('/manager/dashboard/api/charts/revenue', { params });
                const data = res.data;
                const ctx  = document.getElementById('revenueChart').getContext('2d');
                if (revenueChartInstance) revenueChartInstance.destroy();
                revenueChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Doanh thu Vé', data: data.ticket_revenue, backgroundColor: '#3b82f6', borderColor: '#2563eb', borderWidth: 1, borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.5 },
                            { label: 'Bắp Nước',     data: data.combo_revenue,  backgroundColor: '#fbbf24', borderColor: '#d97706', borderWidth: 1, borderRadius: 6, barPercentage: 0.6, categoryPercentage: 0.5 }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: '#4b5563', font: { family: 'Inter', weight: 'bold', size: 11 } } },
                            tooltip: {
                                backgroundColor: '#fff', titleColor: '#111827', bodyColor: '#374151', borderColor: '#e5e7eb', borderWidth: 1,
                                callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${formatMoney(ctx.raw)}` }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 10 } } },
                            y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { color: '#6b7280', font: { size: 10 }, callback: (v) => Number.isInteger(v) ? formatShortMoney(v) : '' } }
                        }
                    }
                });
            } catch (e) { console.error('Lỗi biểu đồ doanh thu:', e); }

            // Biểu đồ donut top phim
            try {
                const movies = topMovies.value;
                const ctx    = document.getElementById('movieChart').getContext('2d');
                if (movieChartInstance) movieChartInstance.destroy();
                movieChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: movies.map(m => m.title),
                        datasets: [{ data: movies.map(m => m.tickets_count), backgroundColor: movieColors, borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#4b5563', boxWidth: 10, padding: 15, font: { size: 10, weight: 'bold' } } },
                            tooltip: {
                                backgroundColor: '#fff', borderColor: '#e5e7eb', titleColor: '#111827', bodyColor: '#374151', borderWidth: 1,
                                callbacks: { label: (ctx) => ` ${movies[ctx.dataIndex].title}: ${movies[ctx.dataIndex].tickets_count} vé (${movies[ctx.dataIndex].percentage}%)` }
                            }
                        },
                        cutout: '70%'
                    }
                });
            } catch (e) { console.error('Lỗi biểu đồ phim:', e); }
        };

        const setFilter = (type) => {
            filterType.value = type;
            if (type === 'today')  { startDate.value = getTodayString();    endDate.value = getTodayString();    fetchData(); }
            if (type === 'week')   { startDate.value = getStartOfWeek();    endDate.value = getEndOfWeek();      fetchData(); }
            if (type === 'month')  { startDate.value = getStartOfMonth();   endDate.value = getEndOfMonth();     fetchData(); }
        };

        const applyCustomFilter = () => {
            if (!customStartDate.value || !customEndDate.value) return alert('Vui lòng nhập đầy đủ khoảng ngày.');
            if (new Date(customStartDate.value) > new Date(customEndDate.value)) return alert('Ngày bắt đầu không được lớn hơn ngày kết thúc.');
            startDate.value = customStartDate.value;
            endDate.value   = customEndDate.value;
            fetchData();
        };

        onMounted(fetchData);

        return { kpis, showtimes, topMovies, recentBookings, recentComboBookings, loading, filterType, startDate, endDate, customStartDate, customEndDate, movieColors, formatMoney, formatShortMoney, formatDateDisplay, setFilter, applyCustomFilter };
    }
}).mount('#managerDashboardApp');
</script>
@endpush
