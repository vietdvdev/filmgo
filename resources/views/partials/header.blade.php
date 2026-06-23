<header class="sticky top-0 z-50 bg-brand-dark/90 backdrop-blur-md border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="/" class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-brand-primary text-4xl font-bold">movie_filter</span>
                    <span class="text-2xl font-black tracking-wider text-white">FILM<span
                            class="text-brand-primary">GO</span></span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="hidden md:flex space-x-8">
                <a href="/"
                    class="text-white hover:text-brand-primary font-medium text-sm transition-colors duration-200">Trang
                    Chủ</a>
                <a href="#"
                    class="text-gray-300 hover:text-brand-primary font-medium text-sm transition-colors duration-200">Lịch
                    Chiếu</a>
                <a href="#"
                    class="text-gray-300 hover:text-brand-primary font-medium text-sm transition-colors duration-200">Phim</a>
                <a href="#"
                    class="text-gray-300 hover:text-brand-primary font-medium text-sm transition-colors duration-200">Khuyến
                    Mãi</a>
                <a href="#"
                    class="text-gray-300 hover:text-brand-primary font-medium text-sm transition-colors duration-200">Tin
                    Điện Ảnh</a>
            </nav>

            <!-- Search Bar & Auth -->
            <div class="flex items-center gap-4">
                <!-- Search Box -->
                <form action="{{ route('movies.search') }}" method="GET" class="relative hidden sm:block">
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                        placeholder="Tìm phim hoặc diễn viên..."
                        class="w-60 pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-full text-sm text-white placeholder-gray-400 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary" />
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
                </form>

                <!-- Auth State -->
                @if (Auth::guard('web')->check())
                    <!-- Dropdown User -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 focus:outline-none py-2">
                            @if (Auth::guard('web')->user()->avatar)
                                <img src="{{ asset(Auth::guard('web')->user()->avatar) }}" alt="Avatar"
                                    class="w-8 h-8 rounded-full object-cover border border-brand-primary/40">
                            @else
                                <div
                                    class="w-8 h-8 rounded-full bg-brand-primary/20 border border-brand-primary/40 flex items-center justify-center text-white">
                                    <span class="material-symbols-outlined text-lg">person</span>
                                </div>
                            @endif
                            <span
                                class="text-sm font-medium text-gray-200 group-hover:text-white hidden md:block">{{ Auth::guard('web')->user()->full_name }}</span>
                            <span
                                class="material-symbols-outlined text-sm text-gray-400 group-hover:text-white">keyboard_arrow_down</span>
                        </button>
                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-1 w-48 bg-brand-secondary border border-white/10 rounded-lg shadow-xl py-1 hidden group-hover:block transition-all duration-300">
                            <a href="{{ route('profile.edit') }}"
                                class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">account_circle</span> Tài khoản
                            </a>
                            <a href="#"
                                class="block px-4 py-2.5 text-sm text-gray-300 hover:bg-white/5 hover:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-lg">history</span> Lịch sử đặt vé
                            </a>
                            <hr class="border-white/5 my-1">
                            <form action="{{ route('logout') }}" method="POST" class="block">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-white/5 hover:text-red-300 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-lg">logout</span> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Login / Register Buttons -->
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-300 hover:text-white transition-colors duration-200">Đăng
                            Nhập</a>
                        <span class="text-white/20">|</span>
                        <a href="{{ route('register') }}"
                            class="bg-brand-primary text-white text-xs font-semibold px-4 py-2 rounded-full hover:bg-red-700 hover:shadow-lg transition-all duration-200">Đăng
                            Ký</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
