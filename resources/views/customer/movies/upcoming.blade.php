@extends('layouts.customer')

@section('title', 'Phim Sắp Chiếu - FilmGo')

@section('content')
    <div
        class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-purple-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- ================= PAGE HEADER ================= -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-6 mb-10 gap-4">
                <div class="space-y-1">
                    <div
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-600 uppercase tracking-wider mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        Sắp đổ bộ
                    </div>
                    <h1 class="text-3xl font-black text-neutral-900 tracking-tight uppercase sm:text-4xl">
                        Phim Sắp Chiếu
                    </h1>
                    <p class="text-sm text-neutral-400 font-medium">
                        Đón đầu những dự án bom tấn và siêu phẩm điện ảnh sắp ra mắt trong thời gian tới.
                    </p>
                </div>

                @if ($movies->count())
                    <div
                        class="text-xs font-bold text-neutral-400 uppercase tracking-wider bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-sm w-max">
                        Chờ đón: <span class="text-purple-600">{{ $movies->total() }}</span> bộ phim
                    </div>
                @endif
            </div>

            <!-- ================= MOVIES GRID ================= -->
            @if ($movies->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 sm:gap-8">
                    @foreach ($movies as $movie)
                        <div class="group flex flex-col bg-transparent rounded-3xl transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div
                                class="relative aspect-[2/3] bg-slate-200 rounded-[24px] overflow-hidden shadow-sm border border-slate-200/40 group-hover:shadow-xl group-hover:shadow-purple-900/5 transition-all duration-500 group-hover:-translate-y-2">
                                <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                    loading="lazy">

                                <!-- Age Limit Badge (Nếu có thuộc tính age_limit) -->
                                @if (isset($movie->age_limit))
                                    <div
                                        class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold bg-white/90 backdrop-blur-md text-neutral-800 rounded-lg shadow-sm border border-slate-200/50">
                                        {{ $movie->age_limit }}
                                    </div>
                                @endif

                                <!-- Quick Action Overlay on Hover -->
                                <div
                                    class="absolute inset-0 bg-neutral-950/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="#"
                                        class="bg-white text-neutral-900 font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-wider flex items-center gap-1 hover:bg-purple-600 hover:text-white">
                                        Chi tiết phim
                                    </a>
                                </div>
                            </div>

                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-2">
                                <h3
                                    class="font-bold text-neutral-800 text-sm sm:text-base line-clamp-1 group-hover:text-purple-600 transition-colors duration-200">
                                    {{ $movie->title }}
                                </h3>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                    <!-- Release Date Badge -->
                                    <div
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md w-max">
                                        <span class="material-symbols-outlined text-xs">calendar_today</span>
                                        <span>{{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}</span>
                                    </div>

                                    <!-- Duration if available -->
                                    @if ($movie->duration)
                                        <span class="text-neutral-400 text-[11px] font-medium pl-1">
                                            {{ $movie->duration }} phút
                                        </span>
                                    @endif
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
                <div
                    class="text-center py-24 bg-white rounded-[32px] border border-dashed border-slate-200 shadow-sm max-w-md mx-auto">
                    <div
                        class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300 shadow-inner">
                        <span class="material-symbols-outlined text-3xl">upcoming</span>
                    </div>
                    <h3 class="text-base font-bold text-neutral-800 mb-1">Hiện tại trống</h3>
                    <p class="text-xs sm:text-sm text-neutral-400 font-medium px-4">
                        Chưa có lịch phát hành phim mới cho giai đoạn tiếp theo. Quay lại sau bạn nhé!
                    </p>
                </div>
            @endif

        </div>
    </div>
@endsection
