@extends('layouts.customer')

@section('title', 'Phim Đang Chiếu - FilmGo')

@section('styles')
<style>
    /* Premium 3D Hover State Outline (Copied from Home) */
    .card-hover {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 2px solid transparent; 
    }
    .card-hover:hover {
        box-shadow: 0 30px 60px -15px rgba(225, 29, 72, 0.4), 0 0 20px rgba(225, 29, 72, 0.2);
        transform: translateY(-10px) scale(1.05) !important;
        border-color: rgba(225, 29, 72, 1);
        z-index: 10;
    }
    
    /* Image Hover Overlay with Glassmorphism */
    .hover-overlay {
        backdrop-filter: blur(5px);
        background-color: rgba(2, 6, 23, 0.65); /* Tối đủ để thấy nút Đặt vé */
    }
    .group:hover .hover-overlay {
        opacity: 1;
    }
</style>
@endsection

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-800 antialiased py-12 selection:bg-brand-primary selection:text-white relative overflow-hidden">
        <!-- Ambient Background Lights (Light Version) -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-primary/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute top-40 right-10 w-[400px] h-[400px] bg-sky-200/40 rounded-full blur-[100px] pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- ================= PAGE HEADER ================= -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-6 mb-10 gap-4" data-aos="fade-down">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary uppercase tracking-widest mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-primary animate-pulse"></span>
                        Lịch chiếu rạp
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase sm:text-4xl">
                        Phim Đang Chiếu
                    </h1>
                    <p class="text-sm text-slate-500 font-medium">
                        Khám phá và đặt vé ngay những siêu phẩm điện ảnh hot nhất hiện nay.
                    </p>
                </div>

                @if ($movies->count())
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-white border border-slate-200 px-4 py-2 rounded-lg shadow-sm w-max">
                        Tổng số: <span class="text-brand-primary font-black">{{ $movies->total() }}</span> bộ phim
                    </div>
                @endif
            </div>

            <!-- ================= FILTERS ================= -->
            <form method="GET" action="{{ route('movies.showing') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12 bg-white/70 border border-slate-200 p-5 rounded-2xl shadow-sm backdrop-blur-xl" data-aos="fade-up" data-aos-delay="100">
                <div>
                    <label for="genre_id" class="block text-xs font-black text-slate-400 uppercase tracking-widest">Thể loại</label>
                    <select id="genre_id" name="genre_id" class="mt-2 block w-full px-4 py-3 bg-white border border-slate-200 text-sm rounded-xl text-slate-800 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50 transition-colors shadow-sm">
                        <option value="">-- Tất cả thể loại --</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}" {{ request('genre_id') == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="age_limit" class="block text-xs font-black text-slate-400 uppercase tracking-widest">Giới hạn độ tuổi</label>
                    <select id="age_limit" name="age_limit" class="mt-2 block w-full px-4 py-3 bg-white border border-slate-200 text-sm rounded-xl text-slate-800 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50 transition-colors shadow-sm">
                        <option value="">-- Tất cả độ tuổi --</option>
                        @foreach($ageLimits as $limit)
                            <option value="{{ $limit }}" {{ request('age_limit') == $limit ? 'selected' : '' }}>{{ $limit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-brand-primary hover:bg-red-700 text-white text-xs font-black uppercase tracking-widest px-5 py-3.5 rounded-xl transition-all shadow-sm hover:shadow-[0_4px_15px_rgba(225,29,72,0.4)]">
                        Áp dụng
                    </button>
                    @if(request()->anyFilled(['genre_id', 'age_limit']))
                        <a href="{{ route('movies.showing') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-widest px-5 py-3.5 rounded-xl transition-colors text-center border border-slate-200 shadow-sm">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>

            <!-- ================= MOVIES GRID ================= -->
            @if ($movies->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach ($movies as $movie)
                        <div class="group flex flex-col bg-transparent transition-all duration-300" data-aos="fade-up" data-aos-delay="{{ $loop->index * 50 }}">

                            <!-- Premium Card Poster -->
                            <div class="relative aspect-[2/3] bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm card-hover">
                                <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                    loading="lazy">

                                <!-- Age Limit Badge -->
                                <div class="absolute top-2.5 left-2.5 px-2 py-0.5 text-[9px] font-black bg-brand-primary text-white rounded uppercase shadow-sm">
                                    {{ $movie->age_limit }}
                                </div>

                                <!-- Quick Action Overlay on Hover -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 hover-overlay transition-opacity duration-300 p-4 gap-2">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                        class="bg-brand-primary text-white font-black text-[10px] px-6 py-3 rounded-lg shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-widest hover:bg-red-700 w-full text-center">
                                        Đặt vé
                                    </a>
                                </div>
                            </div>

                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-2">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-1 group-hover:text-brand-primary transition-colors duration-200 uppercase tracking-tight">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>

                                <div class="flex items-center justify-between gap-2">
                                    <!-- Duration -->
                                    <div class="flex items-center gap-1 text-slate-500 text-xs font-medium">
                                        <span class="material-symbols-outlined text-sm">schedule</span>
                                        <span>{{ $movie->duration }} phút</span>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center text-[9px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded uppercase tracking-widest shadow-sm">
                                        Đang chiếu
                                    </span>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- ================= PAGINATION ================= -->
                <div class="mt-16 border-t border-slate-200 pt-6 flex justify-center">
                    <div class="modern-pagination">
                        {{ $movies->links() }}
                    </div>
                </div>
            @else
                <!-- ================= EMPTY STATE ================= -->
                <div class="text-center py-24 bg-white border border-dashed border-slate-200 rounded-2xl shadow-sm max-w-md mx-auto">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400 shadow-inner">
                        <span class="material-symbols-outlined text-3xl">movie_filter</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">Hiện tại trống</h3>
                    <p class="text-xs sm:text-sm text-slate-500 font-medium px-4">
                        Rạp đang cập nhật danh sách phim mới. Vui lòng quay lại sau nhé!
                    </p>
                </div>
            @endif

        </div>
    </div>
@endsection
