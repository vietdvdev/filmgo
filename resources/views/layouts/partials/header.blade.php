<header class="bg-surface border-b border-outline-variant h-16 w-full fixed top-0 pl-[280px] z-10 flex justify-between items-center pl-8 pr-10 transition-all duration-200 ease-in-out">
    <!-- Left: Menu Breadcrumb động -->
    <div class="flex-1 flex items-center">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant font-medium">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[20px]">dashboard</span>
                Dashboard
            </a>
            @if(request()->routeIs('admin.movies.*'))
                <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                <a href="{{ route('admin.movies.index') }}" class="hover:text-primary transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.movies.index') ? 'text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">movie</span>
                    Quản lý Phim
                </a>
                @if(request()->routeIs('admin.movies.create'))
                    <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                    <span class="text-on-surface font-semibold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Thêm mới
                    </span>
                @elseif(request()->routeIs('admin.movies.edit'))
                    <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                    <span class="text-on-surface font-semibold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                        Chỉnh sửa
                    </span>
                @endif
            @elseif(request()->routeIs('admin.genres.*'))
                <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                <a href="{{ route('admin.genres.index') }}" class="hover:text-primary transition-colors flex items-center gap-1.5 {{ request()->routeIs('admin.genres.index') ? 'text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">sell</span>
                    Thể Loại Phim
                </a>
                @if(request()->routeIs('admin.genres.create'))
                    <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                    <span class="text-on-surface font-semibold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        Thêm mới
                    </span>
                @elseif(request()->routeIs('admin.genres.edit'))
                    <span class="material-symbols-outlined text-sm text-outline-variant">chevron_right</span>
                    <span class="text-on-surface font-semibold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                        Chỉnh sửa
                    </span>
                @endif
            @endif
        </div>
    </div>
    
    <!-- Right: Actions & Profile -->
    <div class="flex items-center gap-4">
        <!-- Nút thông báo -->
        <button class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center text-on-surface-variant hover:text-primary relative transition-colors">
            <span class="material-symbols-outlined">notifications</span>
            <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <!-- Nút thư -->
        <button class="w-10 h-10 rounded-full hover:bg-surface-container-low flex items-center justify-center text-on-surface-variant hover:text-primary relative transition-colors">
            <span class="material-symbols-outlined">mail</span>
            <span class="absolute top-2.5 right-2.5 w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
        </button>
        
        <div class="h-6 w-[1px] bg-outline-variant mx-2"></div>
        
        <!-- Nút hành động nhanh -->
        <a href="{{ route('admin.movies.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200 flex items-center gap-1.5">
            <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
            Đăng Phim
        </a>
        
        <!-- Avatar profile -->
        <div class="ml-2 w-9 h-9 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant/60 cursor-pointer shadow-sm hover:border-primary transition-all duration-200">
            <img alt="Admin Profile Avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD8FUWMNC0ffrEhTJJ2QQlZG0NpRn9_rQq5TMl71FLZ3M26VsE49J6rbi3NKBzpZUHIC4psG4hFT954Gr_kJ57mdBvRYbF93dNWatcURYqY3hhtZaK91AiCUbaTq-ti1O8dJ60UhEY7AolLpoL1Xnqmxg4aZo2gqQoHIW8NDYw1Dt0s_5hy-GrgDN3MMWctpiTEAKzG5-gk9c8KlEnvHV2lz3U5F2HPtYzXQrYr763FJlkAustguFhgRRd_w6sJ0CT6Q91LMaNNXY8">
        </div>
    </div>
</header>
