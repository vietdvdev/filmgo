@extends('layouts.customer')

@section('title', 'FilmGo - Đặt Vé Xem Phim Nhanh Chóng & Tiện Lợi')

@section('content')
    <div
        class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased selection:bg-indigo-500 selection:text-white">

        <!-- ================= HERO BANNER SECTION ================= -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16">
            <div
                class="relative bg-gradient-to-tr from-slate-100/80 via-neutral-50 to-indigo-50/30 rounded-[32px] p-6 sm:p-12 border border-slate-200/50 shadow-sm overflow-hidden">

                <!-- Decorative Subtle Blur Glows -->
                <div class="absolute -top-12 -right-12 w-72 h-72 bg-indigo-200/20 rounded-full blur-3xl pointer-events-none">
                </div>
                <div class="absolute -bottom-12 -left-12 w-72 h-72 bg-rose-200/10 rounded-full blur-3xl pointer-events-none">
                </div>

                <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    <!-- Text Information -->
                    <div class="lg:col-span-7 space-y-6 order-2 lg:order-1 text-center lg:text-left">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 shadow-sm text-indigo-600 text-xs font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-ping"></span>
                            Tiêu điểm điện ảnh
                        </div>

                        <h1
                            class="text-4xl sm:text-5xl lg:text-6xl font-black text-neutral-900 leading-[1.1] tracking-tight">
                            Đại Chiến <br>
                            <span
                                class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent">Vũ
                                Trụ Vô Tận</span>
                        </h1>

                        <p class="text-neutral-500 text-sm sm:text-base leading-relaxed max-w-xl mx-auto lg:mx-0">
                            Hành trình phiêu lưu vượt thời gian đầy kịch tính của các siêu anh hùng. Bộ phim bom tấn khoa
                            học viễn tưởng được mong đợi nhất năm nay đã chính thức cập bến FilmGo.
                        </p>

                        <div class="flex flex-wrap justify-center lg:justify-start gap-4 pt-2">
                            <a href="{{ route('movies.showing') }}"
                                class="inline-flex items-center gap-2 bg-neutral-900 hover:bg-indigo-600 text-white font-semibold text-sm px-7 py-3.5 rounded-2xl transition-all duration-300 shadow-sm hover:shadow-lg hover:shadow-indigo-600/20 transform hover:-translate-y-0.5">
                                <span class="material-symbols-outlined text-lg">confirmation_number</span> Đặt vé ngay
                            </a>
                            <a href="#"
                                class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-200 text-neutral-700 font-semibold text-sm px-7 py-3.5 rounded-2xl transition-all duration-300 shadow-sm transform hover:-translate-y-0.5">
                                <span class="material-symbols-outlined text-lg">play_circle</span> Xem Trailer
                            </a>
                        </div>
                    </div>

                    <!-- Layered Creative Image Layout -->
                    <div class="lg:col-span-5 order-1 lg:order-2 flex justify-center">
                        <div
                            class="relative w-full max-w-[380px] aspect-[4/3] sm:aspect-video lg:aspect-[4/3] rounded-2xl bg-white p-3 shadow-xl shadow-slate-200 border border-slate-200/60 transform rotate-1 hover:rotate-0 transition-transform duration-500">
                            <img src="https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1925&auto=format&fit=crop"
                                alt="Cinematic Banner" class="w-full h-full object-cover rounded-xl">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ================= MAIN CONTENT AREA ================= -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-24 pb-24">

            <!-- SECTION: NOW SHOWING -->
            <div class="space-y-8">
                <!-- Modern Header Structure -->
                <div class="flex items-end justify-between border-b border-slate-200 pb-4">
                    <div class="space-y-1">
                        <h2 class="text-2xl font-black text-neutral-900 tracking-tight uppercase sm:text-3xl">Phim Đang
                            Chiếu</h2>
                        <p class="text-xs sm:text-sm text-neutral-400 font-medium">Thưởng thức những siêu phẩm điện ảnh mới
                            nhất tại rạp</p>
                    </div>
                    <a href="{{ route('movies.showing') }}"
                        class="group text-xs font-bold text-indigo-600 hover:text-indigo-700 transition-colors uppercase tracking-widest flex items-center gap-1">
                        Xem tất cả
                        <span
                            class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </div>

                <!-- Movies Grid Layout -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 sm:gap-8">
                    @forelse($showingMovies as $movie)
                        <div class="group flex flex-col bg-transparent rounded-3xl transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div
                                class="relative aspect-[2/3] bg-slate-200 rounded-[24px] overflow-hidden shadow-sm border border-slate-200/40 group-hover:shadow-xl group-hover:shadow-indigo-900/5 transition-all duration-500 group-hover:-translate-y-2">
                                <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">

                                <!-- Minimalist Age Limit Badge -->
                                <div
                                    class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold bg-white/90 backdrop-blur-md text-neutral-800 rounded-lg shadow-sm border border-slate-200/50">
                                    {{ $movie->age_limit }}
                                </div>

                                <!-- Quick Action Overlay on Hover -->
                                <div
                                    class="absolute inset-0 bg-neutral-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                        class="bg-white text-neutral-900 font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-wider flex items-center gap-1 hover:bg-indigo-600 hover:text-white">
                                        <span class="material-symbols-outlined text-sm">local_activity</span> Đặt vé
                                    </a>
                                </div>
                            </div>
 
                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-1">
                                <h3
                                    class="font-bold text-neutral-800 text-sm sm:text-base line-clamp-1 group-hover:text-indigo-600 transition-colors duration-200">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>
                                <div class="flex items-center gap-1.5 text-neutral-400 text-xs">
                                    <span class="material-symbols-outlined text-sm text-neutral-400">schedule</span>
                                    <span>{{ $movie->duration }} phút</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-slate-200 shadow-sm">
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
                        <h2 class="text-2xl font-black text-neutral-900 tracking-tight uppercase sm:text-3xl">Phim Sắp Chiếu
                        </h2>
                        <p class="text-xs sm:text-sm text-neutral-400 font-medium">Đón đầu các dự án bom tấn chuẩn bị đổ bộ
                            rạp phim</p>
                    </div>
                    <a href="{{ route('movies.upcoming') }}"
                        class="group text-xs font-bold text-neutral-500 hover:text-neutral-800 transition-colors uppercase tracking-widest flex items-center gap-1">
                        Xem tất cả
                        <span
                            class="material-symbols-outlined text-sm transition-transform group-hover:translate-x-1">arrow_forward</span>
                    </a>
                </div>

                <!-- Movies Grid Layout -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 sm:gap-8">
                    @forelse($upcomingMovies as $movie)
                        <div class="group flex flex-col bg-transparent rounded-3xl transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div
                                class="relative aspect-[2/3] bg-slate-200 rounded-[24px] overflow-hidden shadow-sm border border-slate-200/40 group-hover:shadow-xl group-hover:shadow-purple-900/5 transition-all duration-500 group-hover:-translate-y-2">
                                <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105">

                                <!-- Minimalist Age Limit Badge -->
                                <div
                                    class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold bg-white/90 backdrop-blur-md text-neutral-600 rounded-lg shadow-sm border border-slate-200/50">
                                    {{ $movie->age_limit }}
                                </div>

                                <!-- Quick Action Overlay on Hover -->
                                <div
                                    class="absolute inset-0 bg-neutral-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                        class="bg-white text-neutral-900 font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-wider flex items-center gap-1 hover:bg-indigo-600 hover:text-white">
                                        Chi tiết
                                    </a>
                                </div>
                            </div>
 
                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-2">
                                <h3
                                    class="font-bold text-neutral-800 text-sm sm:text-base line-clamp-1 group-hover:text-purple-600 transition-colors duration-200">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <!-- Release Date Badge -->
                                    <span
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">
                                        {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}
                                    </span>

                                    <!-- Soft Trailer Link -->
                                    @if ($movie->trailer_url)
                                        <a href="{{ $movie->trailer_url }}" target="_blank"
                                            class="text-xs text-neutral-400 hover:text-neutral-800 font-bold flex items-center transition-colors">
                                            Trailer
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-slate-200 shadow-sm">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2 block">upcoming</span>
                            <p class="text-slate-400 text-sm font-medium">Chưa có lịch chiếu phim sắp tới.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
