@extends('layouts.admin')

@section('title', 'Tổng Quan Hệ Thống - FilmGo Admin')

@push('head')
    <!-- Vue.js 3 CDN (Production) -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    <!-- Axios CDN -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="flex-grow overflow-y-auto bg-[#f8f9fb] text-zinc-700 p-6 md:p-8" id="dashboardApp" v-cloak>
    
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-red-600 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-zinc-500 font-medium tracking-wide">Đang tải dữ liệu dashboard...</p>
        </div>
    </div>

    <!-- Header Title & Date Range Filter -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center border-b border-zinc-200 pb-6 mb-8 gap-4 mt-6 md:mt-10">
        <div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-zinc-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-600 text-3xl">analytics</span>
                FilmGo Dashboard
            </h1>
            <p class="text-xs md:text-sm text-zinc-500 font-medium mt-1">Dữ liệu phân tích và quản trị rạp chiếu phim thời gian thực</p>
        </div>

        <!-- Date Range Filter Buttons -->
        <div class="flex flex-wrap items-center gap-2 mt-4 md:mt-0">
            <!-- Button Hôm nay -->
            <button @click="setFilter('today')" 
                    :class="filterType === 'today' ? 'bg-red-600 text-white border-red-600 shadow-md shadow-red-200' : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50 hover:border-zinc-300'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">
                Hôm nay
            </button>
            <!-- Button Tuần này -->
            <button @click="setFilter('week')" 
                    :class="filterType === 'week' ? 'bg-red-600 text-white border-red-600 shadow-md shadow-red-200' : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50 hover:border-zinc-300'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">
                Tuần này
            </button>
            <!-- Button Tháng này -->
            <button @click="setFilter('month')" 
                    :class="filterType === 'month' ? 'bg-red-600 text-white border-red-600 shadow-md shadow-red-200' : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50 hover:border-zinc-300'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150">
                Tháng này
            </button>
            <!-- Button Tùy chọn ngày -->
            <button @click="setFilter('custom')" 
                    :class="filterType === 'custom' ? 'bg-red-600 text-white border-red-600 shadow-md shadow-red-200' : 'bg-white text-zinc-600 border-zinc-200 hover:bg-zinc-50 hover:border-zinc-300'"
                    class="px-3.5 py-2 border rounded-xl text-xs font-bold transition-all duration-150 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-xs">calendar_month</span>
                Tùy chọn ngày
            </button>

            <!-- Custom Date Inputs -->
            <div v-if="filterType === 'custom'" class="flex items-center gap-2 bg-white border border-zinc-200 p-1.5 rounded-xl ml-2 shadow-sm animate-fadeIn">
                <input type="date" v-model="customStartDate" class="bg-zinc-50 border border-zinc-200 rounded-lg px-2 py-1 text-xs text-zinc-800 focus:outline-none focus:border-red-600">
                <span class="text-zinc-400 text-xs">đến</span>
                <input type="date" v-model="customEndDate" class="bg-zinc-50 border border-zinc-200 rounded-lg px-2 py-1 text-xs text-zinc-800 focus:outline-none focus:border-red-600">
                <button @click="applyCustomFilter" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition-all">
                    Áp dụng
                </button>
            </div>
        </div>
    </div>

    <!-- Active Filter Info Bar -->
    <div class="mb-6 bg-zinc-100 border border-zinc-200/80 px-4 py-3 rounded-2xl flex items-center gap-2 text-xs text-zinc-500 font-semibold shadow-sm">
        <span class="material-symbols-outlined text-sm text-red-600">info</span>
        Đang hiển thị thống kê từ ngày 
        <span class="text-zinc-800 font-bold bg-white px-2 py-0.5 rounded border border-zinc-200">@{{ formatDateDisplay(startDate) }}</span>
        đến ngày 
        <span class="text-zinc-800 font-bold bg-white px-2 py-0.5 rounded border border-zinc-200">@{{ formatDateDisplay(endDate) }}</span>
    </div>

    <!-- ── 1. KPI Cards (Grid 4 cột) ── -->
    <div v-if="kpis" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1: Doanh Thu -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Doanh Thu</p>
                    <h3 class="text-2xl md:text-3xl font-black text-zinc-900 mt-1">@{{ formatShortMoney(kpis.revenue.today.total) }}</h3>
                </div>
                <div class="p-3 bg-red-50 text-red-600 rounded-2xl border border-red-100 flex items-center justify-center">
                    <span class="material-symbols-outlined">payments</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between pt-4 border-t border-zinc-100 gap-2">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.revenue.growth.total_pct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm font-bold">
                        @{{ kpis.revenue.growth.total_pct >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                    </span>
                    @{{ Math.abs(kpis.revenue.growth.total_pct) }}%
                    <span class="text-zinc-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 flex justify-between text-[11px] text-zinc-500 font-semibold">
                <span>Vé: @{{ formatMoney(kpis.revenue.today.ticket) }}</span>
                <span>Bắp nước: @{{ formatMoney(kpis.revenue.today.combo) }}</span>
            </div>
        </div>

        <!-- Card 2: Số Vé Đã Bán -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Vé Đã Bán</p>
                    <h3 class="text-2xl md:text-3xl font-black text-zinc-900 mt-1">@{{ kpis.tickets.today }} <span class="text-xs text-zinc-500 font-bold">vé</span></h3>
                </div>
                <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl border border-amber-100 flex items-center justify-center">
                    <span class="material-symbols-outlined">local_activity</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between pt-4 border-t border-zinc-100 gap-2">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.tickets.growth_pct >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm font-bold">
                        @{{ kpis.tickets.growth_pct >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                    </span>
                    @{{ Math.abs(kpis.tickets.growth_pct) }}%
                    <span class="text-zinc-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 flex justify-between text-[11px] text-zinc-500 font-semibold">
                <span>Kỳ trước: @{{ kpis.tickets.yesterday }} vé</span>
            </div>
        </div>

        <!-- Card 3: Tỷ Lệ Lấp Đầy -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider">Tỷ Lệ Lấp Đầy</p>
                    <h3 class="text-2xl md:text-3xl font-black text-zinc-900 mt-1">@{{ kpis.occupancy.today_rate }}%</h3>
                </div>
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 flex items-center justify-center">
                    <span class="material-symbols-outlined">event_seat</span>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between pt-4 border-t border-zinc-100 gap-2">
                <span class="text-xs font-semibold flex items-center gap-1"
                      :class="kpis.occupancy.growth_points >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    <span class="material-symbols-outlined text-sm font-bold">
                        @{{ kpis.occupancy.growth_points >= 0 ? 'arrow_upward' : 'arrow_downward' }}
                    </span>
                    @{{ Math.abs(kpis.occupancy.growth_points) }}%
                    <span class="text-zinc-400 font-medium">so với kỳ trước</span>
                </span>
            </div>
            <div class="mt-3 flex justify-between text-[11px] text-zinc-500 font-semibold">
                <span>Ghế bán: @{{ kpis.occupancy.today_booked_seats }}/@{{ kpis.occupancy.today_total_seats }}</span>
            </div>
        </div>

        <!-- Card 4: Phương Thức Thanh Toán -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 relative overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-md shadow-sm">
            <div class="flex justify-between items-start mb-4 gap-2">
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-zinc-400 font-bold uppercase tracking-wider truncate">Phương Thức Thanh Toán</p>
                    <h3 class="text-base md:text-lg font-black text-zinc-900 mt-2 truncate">
                        Online: @{{ kpis.payment_methods.online_pct }}%
                    </h3>
                </div>
                <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl border border-indigo-100 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                </div>
            </div>
            <!-- Progress Bar phân bổ phương thức -->
            <div class="w-full bg-zinc-100 h-2 rounded-full overflow-hidden mt-4">
                <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" 
                     :style="{ width: kpis.payment_methods.online_pct + '%' }"></div>
            </div>
            <div class="mt-4 flex justify-between items-center gap-2 text-[10px] text-zinc-400 font-bold uppercase tracking-wider w-full">
                <span class="truncate">Online: @{{ formatShortMoney(kpis.payment_methods.online_revenue) }}</span>
                <span class="truncate text-right">Tại quầy: @{{ formatShortMoney(kpis.payment_methods.counter_revenue) }}</span>
            </div>
        </div>

    </div>

    <!-- ── 2. Biểu Đồ (Grid 3 cột) ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Cột 2/3: Biểu đồ Doanh thu -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-3xl p-6 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-base font-black uppercase tracking-wider text-zinc-800">Biểu Đồ Doanh Thu</h3>
                <span class="text-xs text-zinc-400 font-bold">Lọc theo ngày chọn</span>
            </div>
            <div class="h-[320px] relative w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Cột 1/3: Biểu đồ Top 5 Phim -->
        <div class="lg:col-span-1 bg-white border border-zinc-200/80 rounded-3xl p-6 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-black uppercase tracking-wider text-zinc-800 mb-6">Top 5 Phim Thị Phần</h3>
                <div class="h-[230px] relative w-full flex items-center justify-center">
                    <canvas id="movieChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-zinc-100 text-[10px] text-zinc-400 font-semibold flex justify-between uppercase">
                <span>Dữ liệu vé kỳ này</span>
                <span>Thống kê phân bổ %</span>
            </div>
        </div>

    </div>

    <!-- ── 3. Bảng Vận Hành & Cảnh Báo ── -->
    <div class="grid grid-cols-1 gap-6">
        
        <!-- Bảng Theo dõi suất chiếu hôm nay -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 shadow-sm">
            <h3 class="text-base font-black uppercase tracking-wider text-zinc-800 mb-6 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Suất Chiếu Đang Vận Hành (Hôm Nay)
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold text-zinc-500">
                    <thead>
                        <tr class="border-b border-zinc-100 text-[10px] text-zinc-400 uppercase font-black tracking-wider">
                            <th class="py-4">Phim</th>
                            <th class="py-4">Phòng chiếu</th>
                            <th class="py-4">Giờ chiếu</th>
                            <th class="py-4 text-right">Lấp đầy rạp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="st in showtimes" :key="st.id" class="border-b border-zinc-100/80 hover:bg-zinc-50/50 transition-all">
                            <td class="py-4 font-bold text-zinc-800">@{{ st.movie_title }}</td>
                            <td class="py-4 text-zinc-600">@{{ st.room_name }}</td>
                            <td class="py-4 text-red-600 font-bold">@{{ st.start_time }}</td>
                            <td class="py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="w-24 bg-zinc-100 h-2 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300"
                                             :class="st.occupancy_percentage >= 90 ? 'bg-red-600 animate-pulse' : 'bg-emerald-500'"
                                             :style="{ width: st.occupancy_percentage + '%' }"></div>
                                    </div>
                                    <span class="w-12 font-black text-zinc-800">@{{ st.occupancy_percentage }}%</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="showtimes.length === 0">
                            <td colspan="4" class="py-8 text-center text-zinc-400 italic">Không có suất chiếu nào hoạt động trong hôm nay.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Bảng Thống kê doanh thu phim -->
        <div class="bg-white border border-zinc-200/80 rounded-3xl p-6 shadow-sm">
            <h3 class="text-base font-black uppercase tracking-wider text-zinc-800 mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-red-500">payments</span>
                Thống Kê Doanh Thu Phim
            </h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-semibold text-zinc-500">
                    <thead>
                        <tr class="border-b border-zinc-100 text-[10px] text-zinc-400 uppercase font-black tracking-wider">
                            <th class="py-4">Tên Phim</th>
                            <th class="py-4 text-center">Số Vé Bán Ra</th>
                            <th class="py-4 text-right">Tổng Doanh Thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="movie in movieRevenues" :key="movie.id" class="border-b border-zinc-100/80 hover:bg-zinc-50/50 transition-all">
                            <td class="py-4 font-bold text-zinc-800">@{{ movie.title }}</td>
                            <td class="py-4 text-center text-zinc-600">@{{ movie.tickets_count }} <span class="text-[10px] text-zinc-400 font-normal">vé</span></td>
                            <td class="py-4 text-right text-red-600 font-black tracking-wide">@{{ formatMoney(movie.total_revenue) }}</td>
                        </tr>
                        <tr v-if="movieRevenues.length === 0">
                            <td colspan="3" class="py-8 text-center text-zinc-400 italic">Không có dữ liệu doanh thu trong khoảng thời gian này.</td>
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

    // Helper sinh ngày định dạng YYYY-MM-DD
    const getTodayString = () => {
        const d = new Date();
        return d.toISOString().split('T')[0];
    };

    const getStartOfWeekString = () => {
        const d = new Date();
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Điều chỉnh sang Thứ 2
        return new Date(d.setDate(diff)).toISOString().split('T')[0];
    };

    const getEndOfWeekString = () => {
        const d = new Date();
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? 0 : 7); // Điều chỉnh sang Chủ nhật
        return new Date(d.setDate(diff)).toISOString().split('T')[0];
    };

    const getStartOfMonthString = () => {
        const d = new Date();
        return new Date(d.getFullYear(), d.getMonth(), 1).toISOString().split('T')[0];
    };

    const getEndOfMonthString = () => {
        const d = new Date();
        return new Date(d.getFullYear(), d.getMonth() + 1, 0).toISOString().split('T')[0];
    };

    createApp({
        setup() {
            const kpis = ref(null);
            const showtimes = ref([]);
            const movieRevenues = ref([]);
            const loading = ref(true);

            // Các biến lọc Date Range
            const filterType = ref('today');
            const startDate = ref(getTodayString());
            const endDate = ref(getTodayString());

            const customStartDate = ref(getTodayString());
            const customEndDate = ref(getTodayString());

            // Lưu trữ các đối tượng biểu đồ
            let revenueChartInstance = null;
            let movieChartInstance = null;

            const formatMoney = (val) => {
                return new Intl.NumberFormat('vi-VN').format(val) + 'đ';
            };

            const formatShortMoney = (val) => {
                if (val >= 1000000) {
                    return (val / 1000000).toFixed(1) + 'M đ';
                }
                return formatMoney(val);
            };

            const formatDateDisplay = (dateStr) => {
                if (!dateStr) return '';
                const parts = dateStr.split('-');
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            };

            const fetchDashboardData = async () => {
                loading.value = true;
                try {
                    const params = {
                        start_date: startDate.value,
                        end_date: endDate.value
                    };

                    // 1. Load KPIs
                    const kpisRes = await axios.get('/api/admin/dashboard/kpis', { params });
                    kpis.value = kpisRes.data;

                    // 2. Load showtimes hôm nay
                    const showtimesRes = await axios.get('/api/admin/dashboard/ops/today-showtimes');
                    showtimes.value = showtimesRes.data;

                    // 3. Load thống kê doanh thu phim
                    const movieRevRes = await axios.get('/api/admin/dashboard/stats/movie-revenue', { params });
                    movieRevenues.value = movieRevRes.data;

                    // 4. Render charts
                    await initCharts();

                } catch (error) {
                    console.error('Lỗi khi tải dữ liệu dashboard:', error);
                } finally {
                    loading.value = false;
                }
            };

            const setFilter = (type) => {
                filterType.value = type;
                if (type === 'today') {
                    startDate.value = getTodayString();
                    endDate.value = getTodayString();
                    fetchDashboardData();
                } else if (type === 'week') {
                    startDate.value = getStartOfWeekString();
                    endDate.value = getEndOfWeekString();
                    fetchDashboardData();
                } else if (type === 'month') {
                    startDate.value = getStartOfMonthString();
                    endDate.value = getEndOfMonthString();
                    fetchDashboardData();
                }
                // Nếu là 'custom' thì chờ admin ấn nút Áp dụng
            };

            const applyCustomFilter = () => {
                if (!customStartDate.value || !customEndDate.value) {
                    alert('Vui lòng nhập đầy đủ khoảng ngày.');
                    return;
                }
                if (new Date(customStartDate.value) > new Date(customEndDate.value)) {
                    alert('Ngày bắt đầu không được lớn hơn ngày kết thúc.');
                    return;
                }
                startDate.value = customStartDate.value;
                endDate.value = customEndDate.value;
                fetchDashboardData();
            };

            const initCharts = async () => {
                const params = {
                    start_date: startDate.value,
                    end_date: endDate.value
                };

                // Biểu đồ cột kép doanh thu
                try {
                    const res = await axios.get('/api/admin/dashboard/charts/revenue', { params });
                    const data = res.data;

                    const ctx = document.getElementById('revenueChart').getContext('2d');
                    if (revenueChartInstance) revenueChartInstance.destroy();

                    revenueChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: 'Doanh thu Vé',
                                    data: data.ticket_revenue,
                                    backgroundColor: '#ef4444', 
                                    borderColor: '#dc2626',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.5
                                },
                                {
                                    label: 'Bắp Nước',
                                    data: data.combo_revenue,
                                    backgroundColor: '#f59e0b', 
                                    borderColor: '#d97706',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    barPercentage: 0.6,
                                    categoryPercentage: 0.5
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: {
                                        color: '#4b5563', // Gray-600
                                        font: { family: 'Inter', weight: 'bold', size: 11 }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#ffffff',
                                    titleColor: '#111827',
                                    bodyColor: '#374151',
                                    borderColor: '#e5e7eb',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            return ` ${context.dataset.label}: ${formatMoney(context.raw)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#6b7280', font: { family: 'Inter', size: 10 } }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f3f4f6' }, // Light grid lines
                                    ticks: {
                                        color: '#6b7280',
                                        font: { family: 'Inter', size: 10 },
                                        callback: function(value) {
                                            if (Math.floor(value) !== value) return;
                                            return formatShortMoney(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (err) {
                    console.error('Lỗi khi tạo biểu đồ doanh thu:', err);
                }

                // Biểu đồ donut phân bố top 5 phim
                try {
                    const res = await axios.get('/api/admin/dashboard/charts/top-movies', { params });
                    const data = res.data.top_movies;

                    const ctx = document.getElementById('movieChart').getContext('2d');
                    if (movieChartInstance) movieChartInstance.destroy();

                    const labels = data.map(m => m.title);
                    const counts = data.map(m => m.tickets_count);

                    movieChartInstance = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: counts,
                                backgroundColor: [
                                    '#e50914', 
                                    '#fbbf24', 
                                    '#10b981', 
                                    '#3b82f6', 
                                    '#a855f7'  
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#4b5563', // Gray-600
                                        boxWidth: 10,
                                        padding: 15,
                                        font: { family: 'Inter', size: 10, weight: 'bold' }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: '#ffffff',
                                    borderColor: '#e5e7eb',
                                    titleColor: '#111827',
                                    bodyColor: '#374151',
                                    borderWidth: 1,
                                    callbacks: {
                                        label: function(context) {
                                            const movie = data[context.dataIndex];
                                            return ` ${movie.title}: ${movie.tickets_count} vé (${movie.percentage}%)`;
                                        }
                                    }
                                }
                            },
                            cutout: '70%'
                        }
                    });
                } catch (err) {
                    console.error('Lỗi khi tạo biểu đồ phim:', err);
                }
            };

            onMounted(() => {
                fetchDashboardData();
            });

            return {
                kpis,
                showtimes,
                movieRevenues,
                loading,
                filterType,
                startDate,
                endDate,
                customStartDate,
                customEndDate,
                formatMoney,
                formatShortMoney,
                formatDateDisplay,
                setFilter,
                applyCustomFilter,
                fetchDashboardData
            };
        }
    }).mount('#dashboardApp');
</script>
@endpush