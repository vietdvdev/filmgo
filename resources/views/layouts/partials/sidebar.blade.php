<aside
    class="bg-surface-container-lowest border-r border-outline-variant shadow-sm w-[280px] h-screen fixed left-0 top-0 flex flex-col py-stack-lg z-20">
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
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.dashboard') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? 1 : 0 }};">dashboard</span>
            <span class="font-label-md text-label-md">Tổng Quan</span>
        </a>
        <!-- Quản Lý Phim -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.movies.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.movies.index') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.movies.*') ? 1 : 0 }};">movie</span>
            <span class="font-label-md text-label-md">Quản Lý Phim</span>
        </a>
        <!-- Quản Lý Thể Loại -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.genres.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.genres.index') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.genres.*') ? 1 : 0 }};">category</span>
            <span class="font-label-md text-label-md">Quản Lý Thể Loại</span>
        </a>
        <!-- Quản Lý Loại Ghế -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.seat-types.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.seat-types.index') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.seat-types.*') ? 1 : 0 }};">event_seat</span>
            <span class="font-label-md text-label-md">Quản Lý Loại Ghế</span>
        </a>
        <!-- Quản Lý Quy Tắc Giá -->
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.price-rules.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.price-rules.index') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.price-rules.*') ? 1 : 0 }};">local_offer</span>
            <span class="font-label-md text-label-md">Quản Lý Quy Tắc Giá</span>
        </a>

        <a class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.cinemas.*') ? 'bg-surface-container-low text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}"
            href="{{ route('admin.cinemas.index') }}">
            <span class="material-symbols-outlined"
                style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.cinemas.*') ? 1 : 0 }};">corporate_fare</span>
            <span class="font-label-md text-label-md">Quản lý rạp</span>
        </a>
        <div>
            <button type="button" onclick="toggleUserMenu()"
                class="w-full flex items-center justify-between px-4 py-3 rounded-lg transition-colors duration-200
        {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.user-cinemas.*')
            ? 'bg-surface-container-low text-primary font-bold'
            : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">group</span>
                    <span class="font-label-md text-label-md">Người Dùng</span>
                </div>

                <span id="user-menu-icon" class="material-symbols-outlined transition-transform duration-200">
                    expand_more
                </span>
            </button>

            <div id="user-menu"
                class="ml-6 mt-1 space-y-1
        {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.user-cinemas.*') ? '' : 'hidden' }}">
                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm
            {{ request()->routeIs('admin.users.*')
                ? 'bg-primary text-white'
                : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    Danh sách người dùng
                </a>

                <a href="{{ route('admin.user-cinemas.index') }}"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm
            {{ request()->routeIs('admin.user-cinemas.*')
                ? 'bg-primary text-white'
                : 'text-on-surface-variant hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[18px]">theater_comedy</span>
                    Phân công rạp
                </a>
            </div>
        </div>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200"
            href="#">
            <span class="material-symbols-outlined">confirmation_number</span>
            <span class="font-label-md text-label-md">Vé &amp; Đơn Hàng</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200"
            href="#">
            <span class="material-symbols-outlined">fastfood</span>
            <span class="font-label-md text-label-md">Combo Bắp Nước</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200"
            href="#">
            <span class="material-symbols-outlined">sell</span>
            <span class="font-label-md text-label-md">Khuyến Mãi</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200"
            href="#">
            <span class="material-symbols-outlined">bar_chart</span>
            <span class="font-label-md text-label-md">Báo Cáo</span>
        </a>
        <a class="flex items-center gap-3 px-4 py-3 rounded-lg text-on-surface-variant hover:bg-surface-container-low transition-colors duration-200"
            href="#">
            <span class="material-symbols-outlined">settings</span>
            <span class="font-label-md text-label-md">Cài Đặt</span>
        </a>
    </nav>
    <!-- Footer Action -->
    <div class="px-stack-sm mt-auto pt-4">
        <div class="flex items-center gap-3 px-4 py-4 mb-2 border-b border-outline-variant/30">
            <div
                class="w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant">
                <img alt="Admin Profile Avatar" class="w-full h-full object-cover"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8FUWMNC0ffrEhTJJ2QQlZG0NpRn9_rQq5TMl71FLZ3M26VsE49J6rbi3NKBzpZUHIC4psG4hFT954Gr_kJ57mdBvRYbF93dNWatcURYqY3hhtZaK91AiCUbaTq-ti1O8dJ60UhEY7AolLpoL1Xnqmxg4aZo2gqQoHIW8NDYw1Dt0s_5hy-GrgDN3MMWctpiTEAKzG5-gk9c8KlEnvHV2lz3U5F2HPtYzXQrYr763FJlkAustguFhgRRd_w6sJ0CT6Q91LMaNNXY8">
            </div>
            <div class="flex flex-col overflow-hidden">
                <span class="font-label-md text-label-md text-on-surface truncate">Quản trị viên</span>
                <span class="text-[11px] text-on-surface-variant truncate">admin@filmgo.vn</span>
            </div>
        </div>
        <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        {{-- Nút Đăng Xuất --}}
        <button
            type="button"
            onclick="openLogoutModal()"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 transition-all duration-200 cursor-pointer font-semibold group">
            <span class="material-symbols-outlined text-xl group-hover:translate-x-0.5 transition-transform duration-200">logout</span>
            <span class="font-label-md text-label-md">Đăng Xuất</span>
        </button>
    </div>

    {{-- Modal xác nhận đăng xuất --}}
    <div id="logout-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeLogoutModal()"></div>
        {{-- Dialog --}}
        <div class="relative bg-white rounded-xl shadow-2xl w-[380px] mx-4 p-6 z-10 animate-in">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-500 text-3xl">logout</span>
                </div>
                <h2 class="text-lg font-bold text-gray-900">Xác nhận đăng xuất</h2>
                <p class="text-sm text-gray-500">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống quản trị FilmGo không?</p>
            </div>
            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    onclick="closeLogoutModal()"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition-colors duration-200">
                    Hủy bỏ
                </button>
                <button
                    type="button"
                    onclick="document.getElementById('admin-logout-form').submit()"
                    class="flex-1 px-4 py-2.5 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600 transition-colors duration-200 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">logout</span>
                    Đăng xuất
                </button>
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleUserMenu() {
        document.getElementById('user-menu').classList.toggle('hidden');
    }

    function openLogoutModal() {
        const modal = document.getElementById('logout-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logout-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Đóng modal khi nhấn phím Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogoutModal();
    });
</script>
