@extends('layouts.customer')

@section('title', 'Kết quả tìm kiếm - FilmGo')

@section('content')
<div class="bg-slate-50 w-full min-h-screen font-sans text-slate-850 antialiased py-12 selection:bg-brand-primary selection:text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

         <!-- ================= PAGE HEADER ================= -->
        <div class="border-b border-slate-200 pb-6 mb-10 space-y-2">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 uppercase tracking-widest">
                <span class="material-symbols-outlined text-sm">search</span>
                Hệ thống tìm kiếm
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase sm:text-4xl">
                Kết quả tìm kiếm
            </h1>
            <div class="text-sm text-slate-500 flex items-center gap-2 pt-1">
                Từ khóa: 
                <span class="inline-flex items-center px-3 py-1 rounded-none bg-white border border-slate-200 text-brand-primary font-bold text-xs shadow-sm">
                    "{{ $keyword }}"
                </span>
                @if($movies->count())
                    <span class="text-slate-350">|</span>
                    <span class="text-xs text-slate-400 font-medium">Tìm thấy <span class="text-brand-primary font-black">{{ $movies->total() }}</span> kết quả</span>
                @endif
            </div>
        </div>

        <!-- ================= SEARCH RESULTS GRID ================= -->
        @if($movies->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8">
                @foreach($movies as $movie)
                    <div class="group flex flex-col bg-transparent transition-all duration-300">
                        
                        <!-- Premium Card Poster -->
                        <div class="relative aspect-[2/3] bg-slate-100 border border-slate-200 group-hover:border-brand-primary transition-all duration-500 rounded-none overflow-hidden group-hover:-translate-y-2 shadow-sm">
                            <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}"
                                alt="{{ $movie->title }}"
                                class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                loading="lazy">

                            <!-- Quick Action Overlay on Hover -->
                            <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                <a href="{{ route('movies.show', $movie->id) }}" class="bg-brand-primary text-white font-black text-[10px] px-6 py-3 rounded-none shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-widest hover:bg-slate-900 hover:text-white">
                                    Chi tiết phim
                                </a>
                            </div>
                        </div>
 
                        <!-- Card Descriptions -->
                        <div class="mt-4 px-1 space-y-1.5">
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-1 group-hover:text-brand-primary transition-colors duration-200 uppercase tracking-tight">
                                <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                            </h3>
                            <div class="flex items-center gap-1.5 text-slate-455 text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                <span>{{ $movie->duration }} phút</span>
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
            <div class="text-center py-24 bg-white border border-dashed border-slate-200 rounded-none shadow-sm max-w-md mx-auto">
                <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-none flex items-center justify-center mx-auto mb-4 text-slate-350 shadow-inner">
                    <span class="material-symbols-outlined text-3xl">search_off</span>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-1">Không tìm thấy kết quả</h3>
                <p class="text-xs sm:text-sm text-slate-400 font-medium px-6 leading-relaxed">
                    Không có phim hoặc diễn viên nào phù hợp với từ khóa của bạn. Hãy thử lại bằng một từ khóa khác nhé!
                </p>
            </div>
        @endif

    </div>
</div>
@endsection