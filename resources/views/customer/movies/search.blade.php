@extends('layouts.customer')

@section('title', 'Kết quả tìm kiếm - FilmGo')

@section('content')
<div class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- ================= PAGE HEADER ================= -->
        <div class="border-b border-slate-200 pb-6 mb-10 space-y-2">
            <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 uppercase tracking-wider">
                <span class="material-symbols-outlined text-sm">search</span>
                Hệ thống tìm kiếm
            </div>
            <h1 class="text-3xl font-black text-neutral-900 tracking-tight uppercase sm:text-4xl">
                Kết quả tìm kiếm
            </h1>
            <p class="text-sm text-neutral-500 flex items-center gap-1.5 pt-1">
                Từ khóa: 
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-200/60 text-neutral-800 font-bold text-xs shadow-sm">
                    "{{ $keyword }}"
                </span>
                @if($movies->count())
                    <span class="text-neutral-300">|</span>
                    <span class="text-xs text-neutral-400 font-medium">Tìm thấy <span class="text-indigo-600 font-bold">{{ $movies->total() }}</span> kết quả</span>
                @endif
            </p>
        </div>

        <!-- ================= SEARCH RESULTS GRID ================= -->
        @if($movies->count())
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 sm:gap-8">
                @foreach($movies as $movie)
                    <div class="group flex flex-col bg-transparent rounded-3xl transition-all duration-300">
                        
                        <!-- Premium Card Poster -->
                        <div class="relative aspect-[2/3] bg-slate-200 rounded-[24px] overflow-hidden shadow-sm border border-slate-200/40 group-hover:shadow-xl group-hover:shadow-indigo-900/5 transition-all duration-500 group-hover:-translate-y-2">
                            <img src="{{ $movie->poster ? asset('storage/'.$movie->poster) : asset('images/no-image.jpg') }}"
                                alt="{{ $movie->title }}"
                                class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                loading="lazy">

                            <!-- Quick Action Overlay on Hover -->
                            <div class="absolute inset-0 bg-neutral-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                <a href="{{ route('movies.show', $movie->id) }}" class="bg-white text-neutral-900 font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-wider hover:bg-indigo-600 hover:text-white">
                                    Chi tiết phim
                                </a>
                            </div>
                        </div>
 
                        <!-- Card Descriptions -->
                        <div class="mt-4 px-1 space-y-1">
                            <h3 class="font-bold text-neutral-800 text-sm sm:text-base line-clamp-1 group-hover:text-indigo-600 transition-colors duration-200">
                                <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                            </h3>
                            <div class="flex items-center gap-1.5 text-neutral-400 text-xs font-medium">
                                <span class="material-symbols-outlined text-sm">schedule</span>
                                <span>{{ $movie->duration }} phút</span>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- ================= PAGINATION ================= -->
            <div class="mt-12 border-t border-slate-200 pt-6 flex justify-center">
                <div class="modern-pagination">
                    {{ $movies->links() }}
                </div>
            </div>

        @else
            <!-- ================= EMPTY STATE ================= -->
            <div class="text-center py-24 bg-white rounded-[32px] border border-dashed border-slate-200 shadow-sm max-w-md mx-auto">
                <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-inner">
                    <span class="material-symbols-outlined text-3xl">search_off</span>
                </div>
                <h3 class="text-base font-bold text-neutral-800 mb-1">Không tìm thấy kết quả</h3>
                <p class="text-xs sm:text-sm text-neutral-400 font-medium px-6 leading-relaxed">
                    Không có phim hoặc diễn viên nào phù hợp với từ khóa của bạn. Hãy thử lại bằng một từ khóa khác nhé!
                </p>
            </div>
        @endif

    </div>
</div>
@endsection