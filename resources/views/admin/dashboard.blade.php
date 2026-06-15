@extends('layouts.admin')

@section('title', 'Tổng Quan Hệ Thống - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Tổng Quan Hệ Thống</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">Dữ liệu tổng hợp hoạt động kinh doanh hôm nay.</p>
        </div>
        <!-- 4 Stat Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter">
            <!-- Card 1 -->
            <div class="bg-surface-container-lowest rounded-lg p-stack-lg border border-outline-variant shadow-ambient-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Doanh Thu Hôm Nay</p>
                        <h3 class="font-headline-md text-headline-md text-on-surface">124.5M ₫</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">payments</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center text-primary font-label-sm text-label-sm">
                        <span class="material-symbols-outlined" style="font-size: 16px;">trending_up</span> +12.5%
                    </span>
                    <span class="font-body-md text-body-md text-on-surface-variant text-xs">so với hôm qua</span>
                </div>
                <!-- Sparkline -->
                <div class="absolute bottom-0 left-0 w-full h-12 opacity-30">
                    <svg class="w-full h-full stroke-primary fill-none" preserveAspectRatio="none" stroke-width="2" viewBox="0 0 100 30">
                        <path d="M0,25 L10,20 L20,28 L30,15 L40,18 L50,8 L60,12 L70,5 L80,10 L90,2 L100,5"></path>
                    </svg>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="bg-surface-container-lowest rounded-lg p-stack-lg border border-outline-variant shadow-ambient-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Vé Đã Bán</p>
                        <h3 class="font-headline-md text-headline-md text-on-surface">1,432</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">local_activity</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center text-primary font-label-sm text-label-sm">
                        <span class="material-symbols-outlined" style="font-size: 16px;">trending_up</span> +8.2%
                    </span>
                    <span class="font-body-md text-body-md text-on-surface-variant text-xs">so với hôm qua</span>
                </div>
                <!-- Sparkline -->
                <div class="absolute bottom-0 left-0 w-full h-12 opacity-30">
                    <svg class="w-full h-full stroke-primary fill-none" preserveAspectRatio="none" stroke-width="2" viewBox="0 0 100 30">
                        <path d="M0,20 L15,22 L30,15 L45,18 L60,10 L75,12 L90,5 L100,8"></path>
                    </svg>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="bg-surface-container-lowest rounded-lg p-stack-lg border border-outline-variant shadow-ambient-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Phim Đang Chiếu</p>
                        <h3 class="font-headline-md text-headline-md text-on-surface">24</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">movie_creation</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center text-secondary font-label-sm text-label-sm">
                        <span class="material-symbols-outlined" style="font-size: 16px;">trending_flat</span> 0%
                    </span>
                    <span class="font-body-md text-body-md text-on-surface-variant text-xs">trong tuần này</span>
                </div>
                <!-- Sparkline -->
                <div class="absolute bottom-0 left-0 w-full h-12 opacity-20">
                    <svg class="w-full h-full stroke-secondary fill-none" preserveAspectRatio="none" stroke-width="2" viewBox="0 0 100 30">
                        <path d="M0,15 L20,15 L40,15 L60,15 L80,15 L100,15"></path>
                    </svg>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="bg-surface-container-lowest rounded-lg p-stack-lg border border-outline-variant shadow-ambient-sm relative overflow-hidden group hover:-translate-y-1 transition-transform duration-300">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider mb-1">Người Dùng Mới</p>
                        <h3 class="font-headline-md text-headline-md text-on-surface">386</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-primary-fixed flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">person_add</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center text-primary font-label-sm text-label-sm">
                        <span class="material-symbols-outlined" style="font-size: 16px;">trending_up</span> +24.1%
                    </span>
                    <span class="font-body-md text-body-md text-on-surface-variant text-xs">so với tuần trước</span>
                </div>
                <!-- Sparkline -->
                <div class="absolute bottom-0 left-0 w-full h-12 opacity-30">
                    <svg class="w-full h-full stroke-primary fill-none" preserveAspectRatio="none" stroke-width="2" viewBox="0 0 100 30">
                        <path d="M0,28 L15,25 L30,20 L45,15 L60,18 L75,8 L90,10 L100,2"></path>
                    </svg>
                </div>
            </div>
        </div>
        <!-- Main Content Layout (Grid) -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-gutter">
            <!-- Main Chart Area (Spans 2 columns) -->
            <div class="xl:col-span-2 bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-headline-sm text-headline-sm text-on-surface">Xu Hướng Doanh Thu</h3>
                    <select class="bg-surface border border-outline-variant text-on-surface text-label-sm font-label-sm rounded-md px-3 py-1.5 focus:ring-primary focus:border-primary">
                        <option>7 ngày qua</option>
                        <option>30 ngày qua</option>
                        <option>Năm nay</option>
                    </select>
                </div>
                <!-- Simulated Area Chart -->
                <div class="flex-1 min-h-[300px] relative w-full mt-4">
                    <!-- Y-Axis Labels -->
                    <div class="absolute left-0 top-0 bottom-8 w-12 flex flex-col justify-between text-label-sm text-on-surface-variant text-right pr-2">
                        <span class="">150M</span>
                        <span class="">100M</span>
                        <span class="">50M</span>
                        <span class="">0</span>
                    </div>
                    <!-- Chart Area -->
                    <div class="absolute left-12 right-0 top-0 bottom-8 border-b border-l border-outline-variant">
                        <!-- Grid Lines -->
                        <div class="absolute w-full h-full flex flex-col justify-between">
                            <div class="w-full border-t border-outline-variant opacity-30"></div>
                            <div class="w-full border-t border-outline-variant opacity-30"></div>
                            <div class="w-full border-t border-outline-variant opacity-30"></div>
                            <div></div> <!-- Bottom line handled by container border -->
                        </div>
                        <!-- SVG Chart -->
                        <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 1000 300">
                            <defs>
                                <linearGradient id="chartGrad" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#3366ff" stop-opacity="0.2"></stop>
                                    <stop offset="100%" stop-color="#3366ff" stop-opacity="0"></stop>
                                </linearGradient>
                            </defs>
                            <!-- Area Fill -->
                            <path class="chart-gradient" d="M0,250 L100,220 L200,240 L300,180 L400,150 L500,190 L600,120 L700,140 L800,80 L900,100 L1000,40 L1000,300 L0,300 Z"></path>
                            <!-- Line -->
                            <path d="M0,250 L100,220 L200,240 L300,180 L400,150 L500,190 L600,120 L700,140 L800,80 L900,100 L1000,40" fill="none" stroke="#3366ff" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
                            <!-- Data Points -->
                            <circle cx="600" cy="120" fill="#ffffff" r="4" stroke="#3366ff" stroke-width="2"></circle>
                            <circle cx="800" cy="80" fill="#ffffff" r="4" stroke="#3366ff" stroke-width="2"></circle>
                            <circle cx="1000" cy="40" fill="#ffffff" r="4" stroke="#3366ff" stroke-width="2"></circle>
                        </svg>
                    </div>
                    <!-- X-Axis Labels -->
                    <div class="absolute left-12 right-0 bottom-0 h-8 flex justify-between items-end text-label-sm text-on-surface-variant px-2">
                        <span class="">T2</span>
                        <span class="">T3</span>
                        <span class="">T4</span>
                        <span class="">T5</span>
                        <span class="">T6</span>
                        <span class="">T7</span>
                        <span class="">CN</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Recent Orders Table -->
        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden">
            <div class="p-stack-lg border-b border-outline-variant flex justify-between items-center bg-surface-container-lowest">
                <h3 class="font-headline-sm text-headline-sm text-on-surface">Vé Đặt Gần Đây</h3>
                <button class="text-primary font-label-md text-label-md hover:underline flex items-center gap-1">
                    Xem tất cả <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container font-label-md text-label-md text-on-surface-variant">
                            <th class="py-3 px-6 font-medium whitespace-nowrap">Mã Đơn</th>
                            <th class="py-3 px-6 font-medium whitespace-nowrap">Khách Hàng</th>
                            <th class="py-3 px-6 font-medium">Tên Phim</th>
                            <th class="py-3 px-6 font-medium whitespace-nowrap">Suất Chiếu</th>
                            <th class="py-3 px-6 font-medium whitespace-nowrap text-right">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-6 font-medium text-primary">#ORD-8923</td>
                            <td class="py-4 px-6">Nguyễn Văn An</td>
                            <td class="py-4 px-6 truncate max-w-[200px]">Dune: Hành Tinh Cát - Phần 2</td>
                            <td class="py-4 px-6 text-on-surface-variant">19:30 - Rạp 1</td>
                            <td class="py-4 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-primary-container text-primary-fixed-dim border border-primary-fixed-dim/20">
                                    Đã thanh toán
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-6 font-medium text-primary">#ORD-8924</td>
                            <td class="py-4 px-6">Trần Thị Bích</td>
                            <td class="py-4 px-6 truncate max-w-[200px]">Kung Fu Panda 4</td>
                            <td class="py-4 px-6 text-on-surface-variant">20:15 - Rạp 3</td>
                            <td class="py-4 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-surface-variant text-on-surface-variant border border-outline-variant">
                                    Chờ
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-6 font-medium text-primary">#ORD-8925</td>
                            <td class="py-4 px-6">Lê Hoàng Nam</td>
                            <td class="py-4 px-6 truncate max-w-[200px]">Mai</td>
                            <td class="py-4 px-6 text-on-surface-variant">18:00 - Rạp 2</td>
                            <td class="py-4 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-primary-container text-primary-fixed-dim border border-primary-fixed-dim/20">
                                    Đã thanh toán
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-6 font-medium text-primary">#ORD-8926</td>
                            <td class="py-4 px-6">Phạm Tuấn Anh</td>
                            <td class="py-4 px-6 truncate max-w-[200px]">Godzilla x Kong: Đế Chế Mới</td>
                            <td class="py-4 px-6 text-on-surface-variant">21:00 - Rạp 1</td>
                            <td class="py-4 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-primary-container text-primary-fixed-dim border border-primary-fixed-dim/20">
                                    Đã thanh toán
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-6 font-medium text-primary">#ORD-8927</td>
                            <td class="py-4 px-6">Vũ Minh Tâm</td>
                            <td class="py-4 px-6 truncate max-w-[200px]">Dune: Hành Tinh Cát - Phần 2</td>
                            <td class="py-4 px-6 text-on-surface-variant">22:15 - Rạp 4</td>
                            <td class="py-4 px-6 text-right">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold tracking-wide bg-surface-variant text-on-surface-variant border border-outline-variant">
                                    Chờ
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
