@extends('layouts.customer')

@section('title', 'Phim Đang Chiếu - FilmGo')

@section('content')
    <div
        class="bg-neutral-50 w-full min-h-screen font-sans text-neutral-800 antialiased py-12 selection:bg-indigo-500 selection:text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- ================= PAGE HEADER ================= -->
            <div class="flex flex-col md:flex-row md:items-end justify-between border-b border-slate-200 pb-6 mb-10 gap-4">
                <div class="space-y-1">
                    <div
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        Lịch chiếu rạp
                    </div>
                    <h1 class="text-3xl font-black text-neutral-900 tracking-tight uppercase sm:text-4xl">
                        Phim Đang Chiếu
                    </h1>
                    <p class="text-sm text-neutral-400 font-medium">
                        Khám phá và đặt vé ngay những siêu phẩm điện ảnh hot nhất hiện nay.
                    </p>
                </div>

                @if ($movies->count())
                    <div
                        class="text-xs font-bold text-neutral-400 uppercase tracking-wider bg-white border border-slate-200 px-3 py-1.5 rounded-xl shadow-sm w-max">
                        Tổng số: <span class="text-indigo-600">{{ $movies->total() }}</span> bộ phim
                    </div>
                @endif
            </div>

            <!-- ================= FILTERS ================= -->
            <form method="GET" action="{{ route('movies.showing') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 bg-white border border-slate-200 p-4 rounded-2xl shadow-sm text-neutral-800">
                <div>
                    <label for="genre_id" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Thể loại</label>
                    <select id="genre_id" name="genre_id" class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="">-- Tất cả thể loại --</option>
                        @foreach($genres as $genre)
                            <option value="{{ $genre->id }}" {{ request('genre_id') == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="age_limit" class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Giới hạn độ tuổi</label>
                    <select id="age_limit" name="age_limit" class="mt-1 block w-full px-3 py-2 bg-slate-50 border border-slate-300 text-sm rounded-xl focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="">-- Tất cả độ tuổi --</option>
                        @foreach($ageLimits as $limit)
                            <option value="{{ $limit }}" {{ request('age_limit') == $limit ? 'selected' : '' }}>{{ $limit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        Áp dụng
                    </button>
                    @if(request()->anyFilled(['genre_id', 'age_limit']))
                        <a href="{{ route('movies.showing') }}" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors text-center">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>

            <!-- ================= MOVIES GRID ================= -->
            @if ($movies->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 sm:gap-8">
                    @foreach ($movies as $movie)
                        <div class="group flex flex-col bg-transparent rounded-3xl transition-all duration-300">

                            <!-- Premium Card Poster -->
                            <div
                                class="relative aspect-[2/3] bg-slate-200 rounded-[24px] overflow-hidden shadow-sm border border-slate-200/40 group-hover:shadow-xl group-hover:shadow-indigo-900/5 transition-all duration-500 group-hover:-translate-y-2">
                                <img src="{{ $movie->poster ? asset('storage/' . $movie->poster) : asset('images/no-image.jpg') }}"
                                    alt=""
                                    class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                                    loading="lazy">

                                <!-- Age Limit Badge -->
                                <div
                                    class="absolute top-3 left-3 px-2 py-0.5 text-[10px] font-bold bg-white/95 backdrop-blur-md text-neutral-800 rounded-lg shadow-sm border border-slate-200/50">
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
                            <div class="mt-4 px-1 space-y-2">
                                <h3
                                    class="font-bold text-neutral-800 text-sm sm:text-base line-clamp-1 group-hover:text-indigo-600 transition-colors duration-200">
                                    <a href="{{ route('movies.show', $movie->id) }}">{{ $movie->title }}</a>
                                </h3>

                                <div class="flex items-center justify-between gap-2">
                                    <!-- Duration -->
                                    <div class="flex items-center gap-1 text-neutral-400 text-xs font-medium">
                                        <span class="material-symbols-outlined text-sm text-neutral-400">schedule</span>
                                        <span>{{ $movie->duration }} phút</span>
                                    </div>

                                    <!-- Status Badge (Dịu mắt) -->
                                    <span
                                        class="inline-flex items-center text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md uppercase tracking-wider">
                                        Đang chiếu
                                    </span>
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
                        <span class="material-symbols-outlined text-3xl">movie_filter</span>
                    </div>
                    <h3 class="text-base font-bold text-neutral-800 mb-1">Hiện tại trống</h3>
                    <p class="text-xs sm:text-sm text-neutral-400 font-medium px-4">
                        Rạp đang cập nhật danh sách phim mới. Vui lòng quay lại sau nhé!
                    </p>
                </div>
            @endif

        </div>
    </div>
@endsection
