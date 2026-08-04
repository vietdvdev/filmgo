@extends('layouts.customer')

@section('title', 'Phim Sắp Chiếu - FilmGo')

@section('content')
    <div class="bg-slate-50 w-full min-h-screen font-sans text-slate-850 antialiased py-12 selection:bg-brand-primary selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- ================= PAGE HEADER ================= -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-6 mb-10 gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-primary uppercase tracking-widest mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-primary"></span>
                        Sắp đổ bộ
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter uppercase sm:text-4xl">
                        Phim Sắp Chiếu
                    </h1>
                    <p class="text-sm text-slate-400 font-medium">
                        Đón đầu những dự án bom tấn và siêu phẩm điện ảnh sắp ra mắt trong thời gian tới.
                    </p>
                </div>

                @if ($movies->count())
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-widest bg-white border border-slate-200 px-4 py-2 rounded-none shadow-sm w-max">
                        Chờ đón: <span class="text-brand-primary font-black">{{ $movies->total() }}</span> bộ phim
                    </div>
                @endif
            </div>

            <!-- ================= FILTERS ================= -->
            <form method="GET" action="{{ route('movies.upcoming') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-12 bg-white border border-slate-200 p-5 rounded-none shadow-sm text-slate-800">
                <div>
                    <label for="genre_id" class="block text-xs font-black text-slate-400 uppercase tracking-widest">Thể loại</label>
                    <select id="genre_id" name="genre_id" class="mt-2 block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-sm rounded-none text-slate-750 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50">
                        <option value="">-- Tất cả thể loại --</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}" {{ request('genre_id') == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="age_limit" class="block text-xs font-black text-slate-400 uppercase tracking-widest">Giới hạn độ tuổi</label>
                    <select id="age_limit" name="age_limit" class="mt-2 block w-full px-4 py-3 bg-slate-50 border border-slate-200 text-sm rounded-none text-slate-750 focus:outline-none focus:border-brand-primary focus:ring-1 focus:ring-brand-primary/50">
                        <option value="">-- Tất cả độ tuổi --</option>
                        @foreach($ageLimits as $limit)
                            <option value="{{ $limit }}" {{ request('age_limit') == $limit ? 'selected' : '' }}>{{ $limit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-brand-primary hover:bg-red-700 text-white text-xs font-black uppercase tracking-widest px-5 py-3.5 rounded-none transition-colors">
                        Áp dụng
                    </button>
                    @if(request()->anyFilled(['genre_id', 'age_limit']))
                        <a href="{{ route('movies.upcoming') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black uppercase tracking-widest px-5 py-3.5 rounded-none transition-colors text-center">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>

            <!-- ================= MOVIES GRID ================= -->
            @if ($movies->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8">
                    @foreach ($movies as $movie)
                        <div class="group flex flex-col bg-transparent transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div class="relative aspect-[2/3] bg-slate-100 border border-slate-200 group-hover:border-brand-primary transition-all duration-500 rounded-none overflow-hidden group-hover:-translate-y-2 shadow-sm">
                                <img src="{{ $movie->poster_url ?? asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                    loading="lazy">

                                <!-- Age Limit Badge -->
                                @if (isset($movie->age_limit))
                                    <div class="absolute top-3 left-3 px-2 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded-none border border-slate-800">
                                        {{ $movie->age_limit }}
                                    </div>
                                @endif

                                <!-- Quick Action Overlay on Hover -->
                                <div class="absolute inset-0 bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center p-4">
                                    <a href="{{ route('movies.show', $movie->id) }}"
                                        class="bg-brand-primary text-white font-black text-[10px] px-6 py-3 rounded-none shadow-lg transform scale-90 group-hover:scale-100 transition-all duration-300 uppercase tracking-widest hover:bg-slate-900 hover:text-white">
                                        Chi tiết phim
                                    </a>
                                </div>
                            </div>

                            <!-- Card Descriptions -->
                            <div class="mt-4 px-1 space-y-2">
                                <h3 class="font-bold text-slate-800 text-sm sm:text-base line-clamp-1 group-hover:text-brand-primary transition-colors duration-200 uppercase tracking-tight">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 pt-1">
                                    <!-- Release Date Badge -->
                                    <div class="inline-flex items-center gap-1 text-[11px] font-bold text-brand-primary bg-brand-primary/5 border border-brand-primary/10 px-2 py-0.5 rounded-none uppercase tracking-widest w-max">
                                        <span class="material-symbols-outlined text-xs">calendar_today</span>
                                        <span>{{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}</span>
                                    </div>

                                    <!-- Duration if available -->
                                    @if ($movie->duration)
                                        <span class="text-slate-400 text-[11px] font-medium pl-1">
                                            {{ $movie->duration }} phút
                                        </span>
                                    @endif
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
                <div class="text-center py-24 bg-white border border-dashed border-slate-200 shadow-sm max-w-md mx-auto">
                    <div class="w-16 h-16 bg-slate-50 border border-slate-100 rounded-none flex items-center justify-center mx-auto mb-4 text-slate-350 shadow-inner">
                        <span class="material-symbols-outlined text-3xl">upcoming</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-800 mb-1">Hiện tại trống</h3>
                    <p class="text-xs sm:text-sm text-slate-400 font-medium px-4">
                        Chưa có lịch phát hành phim mới cho giai đoạn tiếp theo. Quay lại sau bạn nhé!
                    </p>
                </div>
            @endif

        </div>
    </div>
@endsection
