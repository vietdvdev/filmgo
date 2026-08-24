@extends('layouts.customer')

@section('title', 'FilmGo - Đặt Vé Xem Phim Nhanh Chóng & Tiện Lợi')

@section('styles')
<style>
    /* Premium 3D Hover State Outline */
    .card-hover {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent; /* Tránh giật layout khi hover */
    }
    .card-hover:hover {
        box-shadow: 0 30px 60px -15px rgba(225, 29, 72, 0.6), 0 0 30px rgba(225, 29, 72, 0.4);
        transform: translateY(-10px) scale(1.05) !important;
        border-color: rgba(225, 29, 72, 1);
        z-index: 10;
    }
    
    /* Image Hover Overlay with Glassmorphism */
    .hover-overlay {
        backdrop-filter: blur(5px);
        background-color: rgba(2, 6, 23, 0.75); /* bg-slate-950/75 cho độ tương phản nút bấm tốt hơn */
    }
    .group:hover .hover-overlay {
        opacity: 1;
    }

    /* Infinite Marquee Animation */
    @keyframes marquee {
        0% { transform: translateX(0%); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        display: flex;
        width: 200%;
        animation: marquee 25s linear infinite;
    }
    .animate-marquee:hover {
        animation-play-state: paused;
    }

    /* Floating Animations */
    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(5deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    @keyframes float-reverse {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(30px) rotate(-5deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    .animate-float-1 { animation: float 6s ease-in-out infinite; }
    .animate-float-2 { animation: float-reverse 8s ease-in-out infinite; }
    .animate-float-3 { animation: float 10s ease-in-out infinite; }
    .animate-float-4 { animation: float-reverse 7s ease-in-out infinite; }
    .animate-float-5 { animation: float 9s ease-in-out infinite; }

    /* Custom scrollbar for quick booking */
    .custom-select {
        appearance: none;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1em;
    }
</style>
@endsection

@section('content')
<div class="bg-[#F8FAFC] text-slate-800 min-h-screen font-sans antialiased relative overflow-hidden">



    <!-- Gradient Glassmorphism Blobs Background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
        <!-- Top Left Pink/Red Blob -->
        <div class="absolute top-[-10%] left-[-10%] w-[40vw] h-[40vw] bg-rose-500/20 rounded-full blur-[100px] mix-blend-multiply animate-pulse" style="animation-duration: 8s;"></div>
        
        <!-- Middle Right Blue Blob -->
        <div class="absolute top-[30%] right-[-10%] w-[50vw] h-[50vw] bg-blue-500/10 rounded-full blur-[120px] mix-blend-multiply animate-pulse" style="animation-duration: 12s;"></div>
        
        <!-- Bottom Left Rose Blob -->
        <div class="absolute bottom-[-5%] left-[10%] w-[45vw] h-[45vw] bg-rose-400/15 rounded-full blur-[100px] mix-blend-multiply animate-pulse" style="animation-duration: 10s;"></div>
    </div>
    
    <div class="relative z-10">

    <!-- ================= 1. HERO CAROUSEL SECTION ================= -->
    <section class="relative w-full h-[540px] sm:h-[600px] bg-slate-950 overflow-hidden group" id="hero-carousel">
        @php
            $heroMovies = $showingMovies->take(5);
            if ($heroMovies->isEmpty() && $featuredMovie) {
                $heroMovies = collect([$featuredMovie]);
            }
        @endphp

        <!-- Slides Container -->
        <div class="relative w-full h-full">
            @foreach($heroMovies as $index => $movie)
                @php
                    $heroImage = $movie->poster_url ?? 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1925&auto=format&fit=crop';
                    $heroRating = ($movie->reviews->count() > 0) ? number_format($movie->reviews->avg('rating'), 1) : '8.8';
                @endphp
                <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-1000 {{ $index === 0 ? 'opacity-100 z-20' : 'opacity-0 z-0' }}" data-index="{{ $index }}">
                    <!-- Background Image with Ambient Blur -->
                    <div class="absolute inset-0 w-full h-full bg-cover bg-center opacity-40 scale-105 filter blur-xs"
                         style="background-image: url('{{ $heroImage }}');"></div>
                    
                    <!-- Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/70 to-transparent"></div>

                    <div class="relative max-w-7xl mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-8">
                        
                        <!-- Left Side: Text Content -->
                        <div class="max-w-2xl text-white py-12 z-20 transition-all duration-700 transform {{ $index === 0 ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0' }} slide-content">
                            <!-- Badges Row -->
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="bg-rose-600 text-white px-2.5 py-0.5 text-xs font-black rounded uppercase tracking-wider shadow-sm">
                                    {{ $movie->age_limit ?? 'T18' }}
                                </span>
                                @if($movie->genres->count() > 0)
                                    <span class="border border-white/30 bg-white/10 backdrop-blur-md px-2.5 py-0.5 text-xs font-semibold rounded text-slate-200">
                                        {{ $movie->genres->pluck('name')->take(2)->implode(' / ') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Main Display Title -->
                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black mb-4 leading-tight text-white tracking-tight drop-shadow-lg">
                                {{ $movie->title }}
                            </h1>

                            <!-- Brief Description -->
                            <p class="text-slate-300 text-sm sm:text-base mb-8 line-clamp-3 leading-relaxed font-medium max-w-xl drop-shadow-md">
                                {{ $movie->description ?? 'Khi thế giới đứng trên bờ vực diệt vong, một nhóm kháng chiến cuối cùng phải tìm cách kích hoạt lõi năng lượng cổ đại trước khi quân đoàn bóng tối nuốt chửng mọi thứ.' }}
                            </p>

                            <!-- Actions CTA -->
                            <div class="flex flex-wrap items-center gap-4">
                                <a href="{{ route('movies.show', $movie->id) }}"
                                   class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm px-7 py-3.5 rounded-lg flex items-center gap-2 shadow-[0_0_20px_rgba(225,29,72,0.4)] hover:scale-105 transition-all duration-300">
                                    <span class="material-symbols-outlined text-[20px]">local_activity</span>
                                    Đặt vé ngay
                                </a>
                                @if($movie->trailer_url)
                                    <button onclick="openTrailerModal('{{ $movie->trailer_url }}', '{{ addslashes($movie->title) }}')"
                                            class="border border-white/40 hover:border-white bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold text-sm px-6 py-3.5 rounded-lg flex items-center gap-2 transition-all duration-300">
                                        <span class="material-symbols-outlined text-[20px]">play_circle</span>
                                        Xem Trailer
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Right Side: Floating Poster -->
                        <div class="hidden lg:block relative z-20 w-[300px] shrink-0 transition-all duration-1000 transform {{ $index === 0 ? 'translate-x-0 opacity-100' : 'translate-x-12 opacity-0' }} slide-poster" style="animation: float 6s ease-in-out infinite;">
                            <div class="aspect-[2/3] rounded-xl overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.6)] border border-white/20 transform rotate-2 hover:rotate-0 hover:scale-105 transition-all duration-500 cursor-pointer">
                                <img src="{{ $heroImage }}" alt="Hero Poster" class="w-full h-full object-cover">
                                <!-- Subtle glass overlay -->
                                <div class="absolute inset-0 bg-gradient-to-tr from-black/40 via-transparent to-white/10 pointer-events-none"></div>
                            </div>
                            <!-- Decorative Glow Behind Poster -->
                            <div class="absolute inset-0 bg-rose-600/40 blur-[70px] -z-10 rounded-full scale-90 animate-pulse" style="animation-duration: 4s;"></div>
                        </div>
                        
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Carousel Navigation Arrows -->
        @if($heroMovies->count() > 1)
            <button id="hero-prev" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md text-white transition-all duration-300 opacity-0 group-hover:opacity-100 hidden md:flex cursor-pointer hover:scale-110 shadow-lg">
                <span class="material-symbols-outlined text-2xl">chevron_left</span>
            </button>
            <button id="hero-next" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 w-12 h-12 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md text-white transition-all duration-300 opacity-0 group-hover:opacity-100 hidden md:flex cursor-pointer hover:scale-110 shadow-lg">
                <span class="material-symbols-outlined text-2xl">chevron_right</span>
            </button>
        @endif

        <!-- Carousel Indicators -->
        @if($heroMovies->count() > 1)
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2 z-30">
                @foreach($heroMovies as $index => $movie)
                    <button class="carousel-dot transition-all duration-300 rounded-full {{ $index === 0 ? 'w-8 h-1.5 bg-rose-600' : 'w-2.5 h-1.5 bg-white/40 hover:bg-white/70' }}" data-index="{{ $index }}"></button>
                @endforeach
            </div>
        @endif
    </section>

    <!-- ================= 2. INFINITE MARQUEE RIBBON ================= -->
    <div class="bg-gradient-to-r from-rose-700 via-rose-600 to-rose-700 text-white py-3 overflow-hidden relative shadow-md z-30 border-y border-rose-800">
        <div class="animate-marquee whitespace-nowrap flex items-center font-bold text-xs sm:text-sm tracking-widest uppercase">
            <!-- Content Block 1 -->
            <div class="flex items-center justify-around w-full">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-amber-300">local_fire_department</span> HOT DEAL: GIẢM 20% COMBO BẮP NƯỚC KHI ĐẶT VÉ ONLINE</span>
                <span class="mx-8 text-rose-300">|</span>
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-emerald-300">redeem</span> TẶNG VÉ 2D CHO THÀNH VIÊN MỚI</span>
                <span class="mx-8 text-rose-300">|</span>
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-blue-300">movie_filter</span> TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO VỚI IMAX & 4DX</span>
                <span class="mx-8 text-rose-300">|</span>
            </div>
            <!-- Content Block 2 (Duplicate for seamless loop) -->
            <div class="flex items-center justify-around w-full">
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-amber-300">local_fire_department</span> HOT DEAL: GIẢM 20% COMBO BẮP NƯỚC KHI ĐẶT VÉ ONLINE</span>
                <span class="mx-8 text-rose-300">|</span>
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-emerald-300">redeem</span> TẶNG VÉ 2D CHO THÀNH VIÊN MỚI</span>
                <span class="mx-8 text-rose-300">|</span>
                <span class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-blue-300">movie_filter</span> TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO VỚI IMAX & 4DX</span>
                <span class="mx-8 text-rose-300">|</span>
            </div>
        </div>
    </div>


    <!-- ================= 3. KHÁM PHÁ THEO THỂ LOẠI ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2 tracking-tight">Khám Phá Theo Thể Loại</h2>
            <p class="text-slate-500 text-sm font-medium">Tìm kiếm những bộ phim đỉnh cao phù hợp với sở thích của bạn</p>
        </div>

        @php
            // Mapping icon sinh động theo tên thể loại
            $genreIcons = [
                'Hành động' => 'sports_martial_arts',
                'Hài' => 'sentiment_very_satisfied',
                'Hài hước' => 'sentiment_very_satisfied',
                'Kinh dị' => 'visibility',
                'Viễn tưởng' => 'rocket_launch',
                'Khoa học viễn tưởng' => 'rocket_launch',
                'Tình cảm' => 'favorite',
                'Lãng mạn' => 'favorite',
                'Hoạt hình' => 'child_care',
                'Gia đình' => 'family_restroom',
                'Phiêu lưu' => 'explore',
                'Tâm lý' => 'psychology',
                'Tội phạm' => 'local_police',
                'Bí ẩn' => 'search',
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
            @foreach($genres as $genre)
                @php
                    $icon = $genreIcons[$genre->name] ?? 'movie';
                @endphp
                <a href="{{ route('movies.showing', ['genre_id' => $genre->id]) }}"
                   data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}"
                   class="bg-white border border-slate-200/80 rounded-lg p-3 text-center cursor-pointer hover:border-rose-500 hover:bg-rose-50/30 transition-all card-hover group block">
                    <div class="w-8 h-8 mx-auto bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mb-1.5 group-hover:scale-110 group-hover:bg-rose-600 group-hover:text-white transition-all duration-300">
                        <span class="material-symbols-outlined text-[16px]">{{ $icon }}</span>
                    </div>
                    <h3 class="font-bold text-xs text-slate-900 group-hover:text-rose-600 transition-colors leading-tight mb-0.5">{{ $genre->name }}</h3>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $genre->movies_count }} phim</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- ================= 4. PHIM ĐANG CHIẾU (NOW SHOWING) ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex justify-between items-end mb-8 border-b border-slate-200 pb-4">
            <div data-aos="fade-right">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">Phim Đang Chiếu</h2>
                <div class="w-16 h-1 bg-rose-600 mt-2 rounded"></div>
            </div>
            <a href="{{ route('movies.showing') }}" class="text-rose-600 hover:text-rose-700 font-bold text-xs sm:text-sm hover:underline flex items-center gap-1 group">
                Xem tất cả 
                <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
            @forelse($showingMovies->take(5) as $movie)
                @php
                    $formatsText = $movie->formats->pluck('name')->implode(', ') ?: '2D';
                @endphp
                <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}" class="group relative bg-white rounded-xl overflow-hidden border border-slate-200/80 shadow-xs card-hover flex flex-col h-full">
                    
                    <!-- Poster Frame (2/3 Aspect Ratio) -->
                    <div class="relative aspect-[2/3] overflow-hidden bg-slate-100">
                        <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}" 
                             alt="{{ $movie->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                        
                        <!-- Age Limit Tag -->
                        <div class="absolute top-2.5 left-2.5 flex gap-1">
                            <span class="bg-rose-600 text-white px-2 py-0.5 text-[10px] font-black rounded uppercase shadow-sm">
                                {{ $movie->age_limit }}
                            </span>
                        </div>

                        <!-- HOT Badge (Top 3 Latest) -->
                        @if($loop->index < 3)
                        <div class="absolute top-2.5 right-2.5 flex items-center bg-gradient-to-r from-red-600 to-rose-500 text-white px-2 py-0.5 rounded text-[10px] font-black uppercase shadow-[0_0_10px_rgba(225,29,72,0.6)] animate-pulse">
                            <span class="material-symbols-outlined text-[12px] mr-0.5" style='font-variation-settings: "FILL" 1;'>local_fire_department</span>
                            HOT
                        </div>
                        @endif

                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 hover-overlay transition-opacity duration-300 p-4 gap-2">
                            <a href="{{ route('movies.show', $movie->id) }}"
                               class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg w-full text-center shadow-lg transform scale-95 group-hover:scale-100 transition-all duration-300 uppercase tracking-wider">
                                Mua Vé
                            </a>
                            @if($movie->trailer_url)
                                <button onclick="openTrailerModal('{{ $movie->trailer_url }}', '{{ addslashes($movie->title) }}')"
                                        class="bg-white/20 hover:bg-white/30 backdrop-blur-md text-white font-semibold text-xs px-4 py-2 rounded-lg w-full text-center transition-colors">
                                    Trailer
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-3.5 flex flex-col flex-grow">
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base line-clamp-1 mb-1 group-hover:text-rose-600 transition-colors">
                            <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                        </h3>
                        <p class="text-slate-500 text-xs mb-3 line-clamp-1 font-medium">
                            {{ $movie->genres->pluck('name')->implode(', ') ?: 'Điện ảnh' }}
                        </p>
                        
                        <div class="mt-auto pt-2.5 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span> 
                                {{ $movie->duration }}p
                            </span>
                            <span class="font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[11px]">
                                {{ $formatsText }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 bg-white border border-dashed border-slate-200 rounded-xl">
                    <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">movie_filter</span>
                    <p class="text-slate-500 text-sm font-medium">Hiện tại chưa có danh sách phim đang chiếu.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- ================= 5. PHIM SẮP CHIẾU (COMING SOON) ================= -->
    <section class="bg-slate-100/70 border-y border-slate-200 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-end mb-8">
                <div data-aos="fade-right">
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">Phim Sắp Chiếu</h2>
                    <div class="w-16 h-1 bg-rose-600 mt-2 rounded"></div>
                </div>
                <a href="{{ route('movies.upcoming') }}" class="text-rose-600 hover:text-rose-700 font-bold text-xs sm:text-sm hover:underline flex items-center gap-1 group">
                    Xem tất cả 
                    <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @forelse($upcomingMovies->take(5) as $movie)
                    <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}" class="group relative bg-white rounded-xl overflow-hidden border border-slate-200 shadow-xs card-hover flex flex-col h-full">
                        
                        <!-- Poster Frame (2/3 Aspect Ratio) -->
                        <div class="relative aspect-[2/3] overflow-hidden bg-slate-100">
                            <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}" 
                                 alt="{{ $movie->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            
                            <!-- Release Date Badge at Bottom of Poster -->
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-slate-950/90 via-slate-950/60 to-transparent p-3 pt-6">
                                <span class="text-white text-xs font-bold block">
                                    Khởi chiếu: {{ $movie->release_date ? \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') : 'Sắp ra mắt' }}
                                </span>
                            </div>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-slate-950/60 flex flex-col items-center justify-center opacity-0 hover-overlay transition-opacity duration-300 p-4 gap-2">
                                <a href="{{ route('movies.show', $movie->id) }}"
                                   class="bg-white/20 hover:bg-white/30 backdrop-blur-md text-white font-bold text-xs px-4 py-2.5 rounded-lg w-full text-center transition-all duration-300">
                                    Chi Tiết
                                </a>
                                @if($movie->trailer_url)
                                    <button onclick="openTrailerModal('{{ $movie->trailer_url }}', '{{ addslashes($movie->title) }}')"
                                            class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-4 py-2.5 rounded-lg w-full text-center transition-colors">
                                        Xem Trailer
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-3.5 flex flex-col flex-grow">
                            <h3 class="font-bold text-slate-900 text-sm sm:text-base line-clamp-1 mb-1 group-hover:text-rose-600 transition-colors">
                                <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                            </h3>
                            <p class="text-slate-500 text-xs line-clamp-1 font-medium">
                                {{ $movie->genres->pluck('name')->implode(', ') ?: 'Phim bom tấn' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 bg-white border border-dashed border-slate-200 rounded-xl">
                        <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">upcoming</span>
                        <p class="text-slate-500 text-sm font-medium">Chưa có lịch chiếu phim sắp tới.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ================= 6. ƯU ĐÃI & BÁN COMBO (PROMOTIONS) ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex justify-between items-end mb-8 border-b border-slate-200 pb-4">
            <div data-aos="fade-right">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">Ưu Đãi & Sự Kiện</h2>
                <div class="w-16 h-1 bg-rose-600 mt-2 rounded"></div>
            </div>
            <a href="{{ route('combo-shop.index') }}" class="text-rose-600 hover:text-rose-700 font-bold text-xs sm:text-sm hover:underline flex items-center gap-1 group">
                Xem bắp nước
                <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Promo Card 1 -->
            <a href="{{ route('combo-shop.index') }}" data-aos="zoom-in" data-aos-delay="0" class="bg-white rounded-xl overflow-hidden border border-slate-200/80 shadow-xs card-hover group block">
                <div class="h-48 overflow-hidden relative bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1585647347483-22b66260dfff?q=80&w=800&auto=format&fit=crop" 
                         alt="Combo Bắp Nước Ưu Đãi" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase">
                        Combo Ưu Đãi
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-2 line-clamp-1 group-hover:text-rose-600 transition-colors">
                        Combo Bắp Nước Siêu Tiết Kiệm
                    </h3>
                    <p class="text-slate-500 text-xs sm:text-sm line-clamp-2 leading-relaxed">
                        Thưởng thức bắp rang bơ giòn rụm và nước ngọt mát lạnh với mức giá ưu đãi giảm đến 20% khi mua kèm vé xem phim.
                    </p>
                </div>
            </a>

            <!-- Promo Card 2 -->
            <div data-aos="zoom-in" data-aos-delay="150" class="bg-white rounded-xl overflow-hidden border border-slate-200/80 shadow-xs card-hover group cursor-pointer">
                <div class="h-48 overflow-hidden relative bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=800&auto=format&fit=crop" 
                         alt="Ngày Hội Thành Viên" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3 bg-blue-600 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase">
                        Ngày Hội Thành Viên
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-2 line-clamp-1 group-hover:text-blue-600 transition-colors">
                        Thứ 3 Vui Vẻ - Vé Chỉ Từ 45.000đ
                    </h3>
                    <p class="text-slate-500 text-xs sm:text-sm line-clamp-2 leading-relaxed">
                        Áp dụng cho mọi suất chiếu 2D vào ngày thứ 3 hàng tuần tại tất cả các rạp FilmGo. Nhân đôi điểm thưởng thành viên.
                    </p>
                </div>
            </div>

            <!-- Promo Card 3 -->
            <div class="bg-white rounded-xl overflow-hidden border border-slate-200/80 shadow-xs card-hover group cursor-pointer">
                <div class="h-48 overflow-hidden relative bg-slate-100">
                    <img src="https://images.unsplash.com/photo-1559526324-4b87b5e36e44?q=80&w=800&auto=format&fit=crop" 
                         alt="Đối Tác Thanh Toán" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 left-3 bg-emerald-600 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase">
                        Thanh Toán Online
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-base text-slate-900 mb-2 line-clamp-1 group-hover:text-emerald-600 transition-colors">
                        Thanh Toán Tiện Lợi
                    </h3>
                    <p class="text-slate-500 text-xs sm:text-sm line-clamp-2 leading-relaxed">
                        Nhận vé điện tử và mã QR soát vé tự động qua Email chỉ với vài thao tác quét mã thanh toán an toàn, bảo mật.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= 7. TRẢI NGHIỆM ĐIỆN ẢNH ĐỈNH CAO (IMAX, 4DX) ================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="mb-8 border-b border-slate-200 pb-4">
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">Trải Nghiệm Điện Ảnh Đỉnh Cao</h2>
            <div class="w-16 h-1 bg-rose-600 mt-2 rounded"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- IMAX Card -->
            <div class="relative rounded-2xl overflow-hidden h-[280px] sm:h-[320px] group shadow-md">
                <img src="https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?q=80&w=1000&auto=format&fit=crop" 
                     alt="IMAX" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/70 to-transparent flex items-center p-8 sm:p-12">
                    <div class="max-w-sm text-white">
                        <div class="text-3xl sm:text-4xl font-black italic tracking-widest mb-3 text-white">IMAX</div>
                        <h3 class="text-lg sm:text-xl font-bold mb-2">Màn Hình Siêu Lớn, Âm Thanh Sống Động</h3>
                        <p class="text-slate-300 text-xs sm:text-sm mb-5 leading-relaxed">
                            Đắm chìm vào từng khung hình rực rỡ với công nghệ trình chiếu chuẩn điện ảnh tiên tiến nhất.
                        </p>
                        <a href="{{ route('movies.showing') }}" 
                           class="inline-block border border-white text-white font-bold text-xs px-5 py-2.5 rounded-lg hover:bg-white hover:text-slate-950 transition-colors uppercase tracking-wider">
                            Tìm Hiểu Thêm
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4DX Card -->
            <div class="relative rounded-2xl overflow-hidden h-[280px] sm:h-[320px] group shadow-md">
                <img src="https://images.unsplash.com/photo-1478720568477-152d9b164e26?q=80&w=1000&auto=format&fit=crop" 
                     alt="4DX" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/70 to-transparent flex items-center p-8 sm:p-12">
                    <div class="max-w-sm text-white">
                        <div class="text-3xl sm:text-4xl font-black italic tracking-widest mb-3 text-blue-400">4DX</div>
                        <h3 class="text-lg sm:text-xl font-bold mb-2">Đánh Thức Mọi Giác Quan</h3>
                        <p class="text-slate-300 text-xs sm:text-sm mb-5 leading-relaxed">
                            Trải nghiệm ghế chuyển động đa chiều, hiệu ứng gió, sương mù và ánh sáng chân thực ngay tại rạp.
                        </p>
                        <a href="{{ route('movies.showing') }}" 
                           class="inline-block border border-white text-white font-bold text-xs px-5 py-2.5 rounded-lg hover:bg-white hover:text-slate-950 transition-colors uppercase tracking-wider">
                            Khám Phá Ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    </div>
</div>

<!-- ================= MODAL TRAILER VIDEO ================= -->
<div id="trailerModal" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="relative w-full max-w-4xl bg-slate-900 rounded-2xl overflow-hidden shadow-2xl border border-slate-700">
        <div class="flex justify-between items-center px-6 py-4 border-b border-slate-800">
            <h3 id="trailerModalTitle" class="text-base font-bold text-white uppercase tracking-wider truncate">Trailer Phim</h3>
            <button onclick="closeTrailerModal()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>
        <div class="relative aspect-video w-full bg-black">
            <iframe id="trailerIframe" class="w-full h-full" src="" title="Trailer" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Xử lý Quick Booking Form
    function handleQuickBooking(event) {
        event.preventDefault();
        const movieSelect = document.getElementById('quickMovieSelect');
        const selectedMovieId = movieSelect.value;

        if (!selectedMovieId) {
            alert('Vui lòng chọn một bộ phim bạn muốn xem!');
            movieSelect.focus();
            return;
        }

        const selectedOption = movieSelect.options[movieSelect.selectedIndex];
        const movieUrl = selectedOption.getAttribute('data-url');

        if (movieUrl) {
            window.location.href = movieUrl;
        }
    }

    // Xử lý Trailer Modal
    function openTrailerModal(trailerUrl, movieTitle) {
        if (!trailerUrl) return;

        let embedUrl = trailerUrl;
        // Chuyển đổi link YouTube thông thường sang link Embed
        if (trailerUrl.includes('youtube.com/watch?v=')) {
            const videoId = trailerUrl.split('v=')[1].split('&')[0];
            embedUrl = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
        } else if (trailerUrl.includes('youtu.be/')) {
            const videoId = trailerUrl.split('youtu.be/')[1].split('?')[0];
            embedUrl = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1';
        }

        document.getElementById('trailerModalTitle').textContent = 'Trailer: ' + (movieTitle || '');
        document.getElementById('trailerIframe').src = embedUrl;
        
        const modal = document.getElementById('trailerModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeTrailerModal() {
        const modal = document.getElementById('trailerModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('trailerIframe').src = '';
    }

    // Đóng modal khi click ra ngoài backdrop
    document.getElementById('trailerModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTrailerModal();
        }
    });

    // ==========================================
    // Hero Carousel Logic
    // ==========================================
    (function() {
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const prevBtn = document.getElementById('hero-prev');
        const nextBtn = document.getElementById('hero-next');
        if (!slides || slides.length <= 1) return;

        let currentIndex = 0;
        let autoPlayInterval;

        function updateSlide(newIndex) {
            if (newIndex === currentIndex) return;

            // --- Fade out current slide ---
            const curSlide = slides[currentIndex];
            curSlide.classList.remove('opacity-100', 'z-20');
            curSlide.classList.add('opacity-0', 'z-0');

            const curContent = curSlide.querySelector('.slide-content');
            if (curContent) {
                curContent.classList.remove('translate-y-0', 'opacity-100');
                curContent.classList.add('translate-y-8', 'opacity-0');
            }
            const curPoster = curSlide.querySelector('.slide-poster');
            if (curPoster) {
                curPoster.classList.remove('translate-x-0', 'opacity-100');
                curPoster.classList.add('translate-x-12', 'opacity-0');
            }

            // Update dots
            if (dots[currentIndex]) {
                dots[currentIndex].classList.remove('w-8', 'bg-rose-600');
                dots[currentIndex].classList.add('w-2.5', 'bg-white/40');
            }

            // --- Update index ---
            currentIndex = newIndex;

            // --- Fade in new slide ---
            const newSlide = slides[currentIndex];
            newSlide.classList.remove('opacity-0', 'z-0');
            newSlide.classList.add('opacity-100', 'z-20');

            setTimeout(function() {
                const newContent = newSlide.querySelector('.slide-content');
                if (newContent) {
                    newContent.classList.remove('translate-y-8', 'opacity-0');
                    newContent.classList.add('translate-y-0', 'opacity-100');
                }
                const newPoster = newSlide.querySelector('.slide-poster');
                if (newPoster) {
                    newPoster.classList.remove('translate-x-12', 'opacity-0');
                    newPoster.classList.add('translate-x-0', 'opacity-100');
                }
            }, 80);

            // Update dots
            if (dots[currentIndex]) {
                dots[currentIndex].classList.remove('w-2.5', 'bg-white/40');
                dots[currentIndex].classList.add('w-8', 'bg-rose-600');
            }
        }

        function nextSlide() {
            updateSlide((currentIndex + 1) % slides.length);
        }

        function prevSlide() {
            updateSlide((currentIndex - 1 + slides.length) % slides.length);
        }

        function resetAutoPlay() {
            clearInterval(autoPlayInterval);
            autoPlayInterval = setInterval(nextSlide, 5000);
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() { nextSlide(); resetAutoPlay(); });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function() { prevSlide(); resetAutoPlay(); });
        }

        dots.forEach(function(dot, index) {
            dot.addEventListener('click', function() {
                updateSlide(index);
                resetAutoPlay();
            });
        });

        // Tự động chuyển slide mỗi 5s
        resetAutoPlay();
    })();
</script>
@endsection
