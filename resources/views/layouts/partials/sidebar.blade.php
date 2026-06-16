<aside class="bg-surface-container-lowest border-r border-outline-variant shadow-sm w-[280px] h-screen fixed left-0 top-0 flex flex-col py-stack-lg z-20">
    <!-- Brand Logo -->
    <div class="px-gutter mb-stack-xl flex items-center gap-3">
        <div class="w-8 h-8 rounded bg-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-on-primary" style="font-size: 20px;">movie_filter</span>
        </div>
        <div>
            <h1 class="font-headline-md text-headline-md font-bold text-primary">FilmGo</h1>
        </div>
    </div>
    <!-- Navigation Tabs -->
    <nav class="flex-1 overflow-y-auto px-stack-sm flex flex-col gap-1">
        <!-- Tổng Quan -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ route('admin.dashboard') }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? 1 : 0 }};">dashboard</span>
            <span class="font-label-md text-label-md">Tổng Quan</span>
        </a>
        <!-- Quản Lý Phim -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.movies.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ route('admin.movies.index') }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.movies.*') ? 1 : 0 }};">movie</span>
            <span class="font-label-md text-label-md">Quản Lý Phim</span>
        </a>
        <!-- Quản Lý Thể Loại -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.genres.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}" href="{{ route('admin.genres.index') }}">
            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.genres.*') ? 1 : 0 }};">category</span>
            <span class="font-label-md text-label-md">Quản Lý Thể Loại</span>
        </a>
        <!-- Inactive Tabs (các tính năng khác) -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">theater_comedy</span>
            <span class="font-label-md text-label-md">Quản Lý Rạp</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="font-label-md text-label-md">Lịch Chiếu Phim</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">group</span>
            <span class="font-label-md text-label-md">Người Dùng</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">confirmation_number</span>
            <span class="font-label-md text-label-md">Vé &amp; Đơn Hàng</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">fastfood</span>
            <span class="font-label-md text-label-md">Combo Bắp Nước</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">sell</span>
            <span class="font-label-md text-label-md">Khuyến Mãi</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">bar_chart</span>
            <span class="font-label-md text-label-md">Báo Cáo</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">settings</span>
            <span class="font-label-md text-label-md">Cài Đặt</span>
        </a>
    </nav>
    <!-- Footer Action -->
    <div class="px-stack-sm mt-auto pt-4">
        <div class="flex items-center gap-3 px-4 py-4 mb-2 border-b border-outline-variant/30">
            <div class="w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant">
                <img alt="Admin Profile Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8FUWMNC0ffrEhTJJ2QQlZG0NpRn9_rQq5TMl71FLZ3M26VsE49J6rbi3NKBzpZUHIC4psG4hFT954Gr_kJ57mdBvRYbF93dNWatcURYqY3hhtZaK91AiCUbaTq-ti1O8dJ60UhEY7AolLpoL1Xnqmxg4aZo2gqQoHIW8NDYw1Dt0s_5hy-GrgDN3MMWctpiTEAKzG5-gk9c8KlEnvHV2lz3U5F2HPtYzXQrYr763FJlkAustguFhgRRd_w6sJ0CT6Q91LMaNNXY8">
            </div>
            <div class="flex flex-col overflow-hidden">
                <span class="font-label-md text-label-md text-on-surface truncate">Quản trị viên</span>
                <span class="text-[11px] text-on-surface-variant truncate">admin@filmgo.vn</span>
            </div>
        </div>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200" href="#">
            <span class="material-symbols-outlined">logout</span>
            <span class="font-label-md text-label-md">Đăng Xuất</span>
        </a>
    </div>
</aside>
