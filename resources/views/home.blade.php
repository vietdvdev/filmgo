@extends('layouts.customer')

@section('title', 'FilmGo - Đặt Vé Xem Phim Nhanh Chóng & Tiện Lợi')

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-800 antialiased selection:bg-brand-primary selection:text-white">

        <!-- ================= HERO BANNER SECTION ================= -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-16">
            <div class="relative bg-white p-8 sm:p-16 border border-slate-200 rounded-none shadow-sm overflow-hidden">

                <!-- Subtle Accent Glow -->
                <div class="absolute -top-12 -right-12 w-96 h-96 bg-brand-primary/5 rounded-full blur-[100px] pointer-events-none"></div>

                <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                    <!-- Text Information -->
                    <div class="lg:col-span-7 space-y-8 order-2 lg:order-1 text-center lg:text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-none bg-slate-50 border border-slate-200 text-brand-primary text-xs font-black uppercase tracking-widest">
                            <span class="w-2 h-2 bg-brand-primary animate-ping"></span>
                            Tiêu điểm điện ảnh
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black text-slate-900 leading-none tracking-tighter uppercase">
                            Đại Chiến <br>
                            <span class="text-brand-primary">Vũ Trụ Vô Tận</span>
                        </h1>

                        <p class="text-slate-500 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0 font-medium">
                            Hành trình phiêu lưu vượt thời gian đầy kịch tính của các siêu anh hùng. Bộ phim bom tấn khoa học viễn tưởng được mong đợi nhất năm nay đã chính thức cập bến FilmGo.
                        </p>

                        <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
                            <a href="{{ route('movies.showing') }}"
                               class="inline-flex items-center gap-2 bg-brand-primary hover:bg-red-700 text-white font-black text-xs px-8 py-4 rounded-none transition-all duration-300 shadow-md hover:shadow-lg hover:shadow-brand-primary/20 uppercase tracking-widest">
                                <span class="material-symbols-outlined text-sm">confirmation_number</span> Đặt vé ngay
                            </a>
                            <a href="#"
                               class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-250 border border-slate-200 text-slate-700 font-black text-xs px-8 py-4 rounded-none transition-all duration-300 uppercase tracking-widest">
                                <span class="material-symbols-outlined text-sm">play_circle</span> Xem Trailer
                            </a>
                        </div>
                    </div>

                    <!-- Layered Image Layout -->
                    <div class="lg:col-span-5 order-1 lg:order-2 flex justify-center">
                        <div class="relative w-full max-w-[380px] aspect-[4/3] sm:aspect-video lg:aspect-[4/3] bg-slate-50 p-2 shadow-md border border-slate-200 rounded-none transition-all duration-300 hover:border-brand-primary">
                            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1925&auto=format&fit=crop"
                                 alt="Cinematic Banner" class="w-full h-full object-cover rounded-none">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-24 pb-32">

            <!-- SECTION: BROWSE BY GENRE -->
            @if($genres->count())
                <div class="space-y-8 bg-white p-8 border border-slate-200 shadow-sm rounded-none">
                    <div class="border-b border-slate-100 pb-4">
                        <h2 class="text-xl font-black text-slate-900 tracking-tighter uppercase">Duyệt theo thể loại</h2>
                        <p class="text-xs text-slate-400 font-medium">Khám phá danh sách phim theo sở thích điện ảnh của bạn</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach($genres as $genre)
                            <a href="{{ route('movies.showing', ['genre_id' => $genre->id]) }}"
                               class="flex items-center justify-between gap-4 bg-slate-50 border border-slate-200 hover:border-brand-primary hover:text-brand-primary px-5 py-3 rounded-none font-bold text-xs uppercase transition-all duration-200 group">
                                <span class="tracking-widest">{{ $genre->name }}</span>
                                <span class="px-2 py-0.5 text-[10px] bg-slate-200 text-slate-655 font-black group-hover:bg-brand-primary group-hover:text-white transition-colors">
                                    {{ $genre->movies_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- SECTION: NOW SHOWING -->
            <div class="space-y-8">
                <!-- Modern Header Structure -->
                <div class="flex items-end justify-between border-b border-slate-200 pb-4">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tighter uppercase sm:text-3xl">Phim Đang Chiếu</h2>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium">Thưởng thức những siêu phẩm điện ảnh mới nhất tại rạp</p>
                    </div>
                    <a href="{{ route('movies.showing') }}"
                       class="group text-xs font-black text-brand-primary hover:text-red-700 transition-colors uppercase tracking-widest flex items-center gap-1">
                        Xem tất cả
                        <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </div>

                <!-- Movies Grid Layout -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    @forelse($showingMovies as $movie)
                        <div class="group flex flex-col bg-transparent transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div class="relative aspect-[2/3] bg-slate-100 border border-slate-200 group-hover:border-brand-primary transition-all duration-500 rounded-none overflow-hidden group-hover:-translate-y-2 shadow-sm">
                                <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}"
                                     alt=""
                                     class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">

                                <!-- Minimalist Age Limit Badge -->
                                <div class="absolute top-3 left-3 px-2 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded-none border border-slate-800">
                                    {{ $movie->age_limit }}
                                </div>

                                <!-- Quick Action Overlay on Hover -->
                                <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                       class="bg-brand-primary text-white font-black text-[10px] px-6 py-3 rounded-none shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-widest hover:bg-slate-900 hover:text-white">
                                        Đặt vé
                                    </a>
                                </div>
                            </div>
 
                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-1.5">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-1 group-hover:text-brand-primary transition-colors duration-200 uppercase tracking-tight">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>
                                <div class="flex items-center gap-1.5 text-slate-400 text-xs">
                                    <span class="material-symbols-outlined text-sm">schedule</span>
                                    <span>{{ $movie->duration }} phút</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 bg-white border border-dashed border-slate-200 rounded-none shadow-sm">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">movie_filter</span>
                            <p class="text-slate-400 text-sm font-medium">Danh sách phim hiện đang trống.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- SECTION: COMING SOON -->
            <div class="space-y-8">
                <!-- Modern Header Structure -->
                <div class="flex items-end justify-between border-b border-slate-200 pb-4">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tighter uppercase sm:text-3xl">Phim Sắp Chiếu</h2>
                        <p class="text-xs sm:text-sm text-slate-400 font-medium">Đón đầu các dự án bom tấn chuẩn bị đổ bộ rạp phim</p>
                    </div>
                    <a href="{{ route('movies.upcoming') }}"
                       class="group text-xs font-black text-slate-455 hover:text-slate-800 transition-colors uppercase tracking-widest flex items-center gap-1">
                        Xem tất cả
                        <span class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </div>

                <!-- Movies Grid Layout -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    @forelse($upcomingMovies as $movie)
                        <div class="group flex flex-col bg-transparent transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div class="relative aspect-[2/3] bg-slate-100 border border-slate-200 group-hover:border-brand-primary transition-all duration-500 rounded-none overflow-hidden group-hover:-translate-y-2 shadow-sm">
                                <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}"
                                     alt=""
                                     class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">

                                <!-- Minimalist Age Limit Badge -->
                                <div class="absolute top-3 left-3 px-2 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded-none border border-slate-800">
                                    {{ $movie->age_limit }}
                                </div>

                                <!-- Quick Action Overlay on Hover -->
                                <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                       class="bg-brand-primary text-white font-black text-[10px] px-6 py-3 rounded-none shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-widest hover:bg-slate-900 hover:text-white">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
 
                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-2">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-1 group-hover:text-brand-primary transition-colors duration-200 uppercase tracking-tight">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <!-- Release Date Badge -->
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-primary bg-brand-primary/5 px-2.5 py-0.5 border border-brand-primary/10 rounded-none uppercase">
                                        {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}
                                    </span>

                                    <!-- Soft Trailer Link -->
                                    @if ($movie->trailer_url)
                                        <a href="{{ $movie->trailer_url }}" target="_blank"
                                           class="text-xs text-slate-400 hover:text-slate-800 font-black flex items-center transition-colors uppercase tracking-widest">
                                            Trailer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-20 bg-white border border-dashed border-slate-200 rounded-none shadow-sm">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">upcoming</span>
                            <p class="text-slate-400 text-sm font-medium">Chưa có lịch chiếu phim sắp tới.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
