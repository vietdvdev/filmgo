<aside class="bg-surface-container-lowest border-r border-outline-variant/50 shadow-md w-[280px] h-screen fixed left-0 top-0 flex flex-col py-6 px-4 z-20 select-none">
    
    <!-- Brand Logo -->
    <div class="px-2 mb-6 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center shadow-sm shadow-primary/30">
            <span class="material-symbols-outlined text-on-primary text-[22px]">movie_filter</span>
        </div>
        <div>
            <h1 class="text-xl font-black tracking-tight text-primary">FilmGo</h1>
            <p class="text-[11px] text-on-surface-variant/70 font-medium -mt-1">Admin Dashboard</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <nav class="flex-1 overflow-y-auto pr-1 -mr-1 space-y-1.5 scrollbar-thin scrollbar-thumb-outline-variant">
        
        <!-- Tổng Quan -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.dashboard') ? 1 : 0 }};">dashboard</span>
            <span>Tổng Quan</span>
        </a>

        <!-- Quản Lý Phim -->
        <a href="{{ route('admin.movies.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.movies.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.movies.*') ? 1 : 0 }};">movie</span>
            <span>Quản Lý Phim</span>
        </a>

        <!-- Quản Lý Thể Loại -->
        <a href="{{ route('admin.genres.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.genres.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.genres.*') ? 1 : 0 }};">category</span>
            <span>Quản Lý Thể Loại</span>
        </a>

        <!-- Quản Lý Loại Ghế -->
        <a href="{{ route('admin.seat-types.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.seat-types.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.seat-types.*') ? 1 : 0 }};">event_seat</span>
            <span>Quản Lý Loại Ghế</span>
        </a>

        <!-- Quản Lý Quy Tắc Giá -->
        <a href="{{ route('admin.price-rules.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.price-rules.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.price-rules.*') ? 1 : 0 }};">local_offer</span>
            <span>Quy Tắc Giá</span>
        </a>

        <!-- Quản Lý Rạp -->
        <a href="{{ route('admin.cinemas.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.cinemas.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.cinemas.*') ? 1 : 0 }};">corporate_fare</span>
            <span>Quản Lý Rạp</span>
        </a>

        

        <!-- Phân Công Rạp (Tách riêng) -->
        <a href="{{ route('admin.user-cinemas.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.user-cinemas.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.user-cinemas.*') ? 1 : 0 }};">theater_comedy</span>
            <span>Phân Công Rạp</span>
        </a>

        <!-- Vé & Đơn Hàng -->
        <a href="{{ route('admin.bookings.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.bookings.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.bookings.*') ? 1 : 0 }};">confirmation_number</span>
            <span>Vé &amp; Đơn Hàng</span>
        </a>

        <!-- Combo Bắp Nước -->
        <a href="{{ route('admin.combos.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.combos.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.combos.*') ? 1 : 0 }};">fastfood</span>
            <span>Combo Bắp Nước</span>
        </a>

        <!-- Định Dạng Phòng Chiếu -->
        <a href="{{ route('admin.formats.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.formats.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.formats.*') ? 1 : 0 }};">theaters</span>
            <span>Định Dạng Phòng Chiếu</span>
        </a>

        <!-- Khuyến Mãi -->
        <a href="{{ route('admin.promotions.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.promotions.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.promotions.*') ? 1 : 0 }};">sell</span>
            <span>Khuyến Mãi</span>
        </a>

        <div class="pt-2 my-2 border-t border-outline-variant/40"></div>
        <!-- Quản Lý Người Dùng (Tách riêng) -->
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.users.*') ? 1 : 0 }};">group</span>
            <span>Người Dùng</span>
        </a>

        <!-- Báo Cáo (Submenu) -->
        <div class="flex flex-col">
            <button type="button" onclick="toggleSubmenu('reports-submenu', 'reports-chevron')"
               class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface' }}">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->routeIs('admin.reports.*') ? 1 : 0 }};">bar_chart</span>
                    <span>Báo Cáo</span>
                </div>
                <span id="reports-chevron" class="material-symbols-outlined text-[18px] transition-transform duration-200 {{ request()->routeIs('admin.reports.*') ? 'rotate-180' : '' }}">expand_more</span>
            </button>
            
            <!-- Danh sách báo cáo con -->
            <div id="reports-submenu" class="flex flex-col gap-1 mt-1 pl-9 pr-2 overflow-hidden transition-all duration-300 {{ request()->routeIs('admin.reports.*') ? 'max-h-64 opacity-100' : 'max-h-0 opacity-0' }}">
                <!-- Báo cáo doanh thu theo Rạp -->
                <a href="{{ route('admin.reports.cinema') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.reports.cinema') ? 'text-primary font-semibold bg-primary/5' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                    <span>Doanh thu theo Rạp</span>
                </a>
                
                <!-- Báo cáo doanh thu theo Phim -->
                <a href="{{ route('admin.reports.movie') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-sm transition-all duration-200 {{ request()->routeIs('admin.reports.movie') ? 'text-primary font-semibold bg-primary/5' : 'text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low' }}">
                    <span class="material-symbols-outlined text-[18px]">movie</span>
                    <span>Doanh thu theo Phim</span>
                </a>
            </div>
        </div>

        <!-- Cài Đặt -->
        <a href="#" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-on-surface transition-all duration-200">
            <span class="material-symbols-outlined text-[20px]">settings</span>
            <span>Cài Đặt</span>
        </a>
    </nav>

    <!-- Footer Profile & Logout Action -->
    <div class="mt-auto pt-4 border-t border-outline-variant/50 space-y-3">
        <!-- User Badge -->
        <div class="flex items-center gap-3 p-2 rounded-xl bg-surface-container-low/60 border border-outline-variant/30">
            <div class="w-9 h-9 rounded-full ring-2 ring-primary/20 overflow-hidden flex-shrink-0">
                <img alt="Admin Profile Avatar" class="w-full h-full object-cover"
                     src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8FUWMNC0ffrEhTJJ2QQlZG0NpRn9_rQq5TMl71FLZ3M26VsE49J6rbi3NKBzpZUHIC4psG4hFT954Gr_kJ57mdBvRYbF93dNWatcURYqY3hhtZaK91AiCUbaTq-ti1O8dJ60UhEY7AolLpoL1Xnqmxg4aZo2gqQoHIW8NDYw1Dt0s_5hy-GrgDN3MMWctpiTEAKzG5-gk9c8KlEnvHV2lz3U5F2HPtYzXQrYr763FJlkAustguFhgRRd_w6sJ0CT6Q91LMaNNXY8">
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-bold text-on-surface truncate">Quản trị viên</span>
                <span class="text-[11px] text-on-surface-variant/80 truncate">admin@filmgo.vn</span>
            </div>
        </div>

        <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <!-- Logout Button -->
        <button type="button" onclick="openLogoutModal()"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-50/80 text-red-600 hover:bg-red-500 hover:text-white transition-all duration-200 font-semibold text-xs shadow-xs group cursor-pointer">
            <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-0.5 transition-transform duration-200">logout</span>
            <span>Đăng Xuất</span>
        </button>
    </div>

    <!-- Logout Modal -->
    <div id="logout-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity duration-300" onclick="closeLogoutModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10 border border-slate-100 transform transition-all duration-200 scale-95 opacity-0" id="logout-modal-content">
            <div class="flex flex-col items-center text-center gap-3">
                <div class="w-12 h-12 rounded-full bg-red-100/70 text-red-600 flex items-center justify-center ring-8 ring-red-50">
                    <span class="material-symbols-outlined text-2xl">logout</span>
                </div>
                <h2 class="text-lg font-bold text-slate-900">Xác nhận đăng xuất</h2>
                <p class="text-xs leading-relaxed text-slate-500">Bạn có chắc chắn muốn rời khỏi phiên làm việc quản trị FilmGo?</p>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeLogoutModal()"
                    class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors duration-150 cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" onclick="document.getElementById('admin-logout-form').submit()"
                    class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 text-white text-xs font-bold hover:bg-red-600 shadow-sm shadow-red-500/30 transition-all duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>Đăng xuất</span>
                </button>
            </div>
        </div>
    </div>
</aside>

<script>
    function openLogoutModal() {
        const modal = document.getElementById('logout-modal');
        const content = document.getElementById('logout-modal-content');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logout-modal');
        const content = document.getElementById('logout-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogoutModal();
    });

    // Hàm toggle submenu cho sidebar
    function toggleSubmenu(submenuId, chevronId) {
        const submenu = document.getElementById(submenuId);
        const chevron = document.getElementById(chevronId);
        
        if (submenu.classList.contains('max-h-0')) {
            // Mở submenu
            submenu.classList.remove('max-h-0', 'opacity-0');
            submenu.classList.add('max-h-64', 'opacity-100');
            chevron.classList.add('rotate-180');
        } else {
            // Đóng submenu
            submenu.classList.remove('max-h-64', 'opacity-100');
            submenu.classList.add('max-h-0', 'opacity-0');
            chevron.classList.remove('rotate-180');
        }
    }
</script>