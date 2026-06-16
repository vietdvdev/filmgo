@extends('layouts.admin')

@section('title', 'Quản Lý Phim - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Phim</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Tổng số: {{ $movies->total() }} bộ phim</p>
            </div>
            <a href="{{ route('admin.movies.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Phim Mới
            </a>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md text-body-md">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="font-body-md text-body-md">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">
            <!-- Filter Bar -->
            <form method="GET" action="{{ route('admin.movies.index') }}" class="flex flex-wrap gap-3 items-center">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên phim...">
                </div>

                <select name="status" class="bg-surface border border-outline-variant text-on-surface text-label-sm font-label-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                    <option value="">Tất cả trạng thái</option>
                    <option value="showing"  {{ request('status') == 'showing'  ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="stopped"  {{ request('status') == 'stopped'  ? 'selected' : '' }}>Ngừng chiếu</option>
                </select>

                <select name="genre" class="bg-surface border border-outline-variant text-on-surface text-label-sm font-label-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                    <option value="">Tất cả thể loại</option>
                    @foreach($genres as $g)
                        <option value="{{ $g->id }}" {{ request('genre') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-primary-container transition-colors">
                    Lọc phim
                </button>

                @if(request()->hasAny(['search','status','genre']))
                    <a href="{{ route('admin.movies.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($movies->isEmpty())
                <div class="text-center py-12 text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3">movie</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có phim nào</p>
                    <p class="font-body-md text-body-md">Hãy thêm phim đầu tiên!</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container font-label-md text-label-md text-on-surface-variant">
                                <th class="py-3 px-4 font-medium" style="width: 50px;">#</th>
                                <th class="py-3 px-4 font-medium" style="width: 80px;">Poster</th>
                                <th class="py-3 px-4 font-medium" style="width: 25%;">Tên Phim</th>
                                <th class="py-3 px-4 font-medium whitespace-nowrap">Thể Loại</th>
                                <th class="py-3 px-4 font-medium whitespace-nowrap" style="width: 110px;">Thời Lượng</th>
                                <th class="py-3 px-4 font-medium whitespace-nowrap" style="width: 120px;">Khởi Chiếu</th>
                                <th class="py-3 px-4 font-medium whitespace-nowrap" style="width: 80px;">Tuổi</th>
                                <th class="py-3 px-4 font-medium whitespace-nowrap" style="width: 130px;">Trạng Thái</th>
                                <th class="py-3 px-4 font-medium text-right whitespace-nowrap" style="width: 190px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant">
                            @foreach($movies as $movie)
                                <tr class="hover:bg-surface-container-low transition-colors duration-150 {{ $movie->trashed() ? 'opacity-50' : '' }}">
                                    <td class="py-3 px-4">{{ $loop->iteration + ($movies->currentPage() - 1) * $movies->perPage() }}</td>
                                    <td class="py-3 px-4">
                                        @if($movie->poster)
                                            <img src="{{ asset($movie->poster) }}" alt="{{ $movie->title }}" class="w-11 h-16 object-cover rounded shadow-sm">
                                        @else
                                            <div class="w-11 h-16 bg-surface-container-highest rounded flex items-center justify-center text-on-surface-variant">
                                                <span class="material-symbols-outlined" style="font-size: 20px;">image</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-on-surface truncate" title="{{ $movie->title }}">{{ $movie->title }}</div>
                                        <div class="text-xs text-on-surface-variant mt-0.5 truncate">
                                            {{ $movie->director ? '🎬 '.$movie->director : '' }}{{ $movie->country ? ' · '.$movie->country : '' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($movie->genres as $genre)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-primary-fixed text-on-primary-fixed-variant">
                                                    {{ $genre->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $movie->duration }} phút</td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $movie->release_date->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center justify-center w-8 h-6 rounded bg-neutral-900 text-white font-bold text-xs">
                                            {{ $movie->age_limit }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($movie->trashed())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-neutral-100 text-neutral-800 whitespace-nowrap">Đã xóa</span>
                                        @elseif($movie->status === 'showing')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 whitespace-nowrap">Đang chiếu</span>
                                        @elseif($movie->status === 'upcoming')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 whitespace-nowrap">Sắp chiếu</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 whitespace-nowrap">Ngừng chiếu</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex gap-2 items-center justify-end whitespace-nowrap">
                                            @if($movie->trashed())
                                                <form action="{{ route('admin.movies.restore', $movie->id) }}" method="POST" class="inline-block align-middle">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 text-[13px] font-semibold px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors whitespace-nowrap">
                                                        <span class="material-symbols-outlined" style="font-size: 16px;">settings_backup_restore</span> Khôi phục
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('admin.movies.edit', $movie) }}" class="inline-flex items-center gap-1 text-[13px] font-semibold px-3 py-1.5 rounded-lg bg-secondary-container text-on-secondary-container hover:bg-secondary-fixed transition-colors whitespace-nowrap">
                                                    <span class="material-symbols-outlined" style="font-size: 16px;">edit</span> Sửa
                                                </a>
                                                <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST" class="inline-block align-middle"
                                                      onsubmit="return confirm('Xóa phim «{{ addslashes($movie->title) }}»?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-1 text-[13px] font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors whitespace-nowrap">
                                                        <span class="material-symbols-outlined" style="font-size: 16px;">trash</span> Xóa
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-center mt-6">
                    <small class="font-body-md text-body-md text-on-surface-variant">Hiển thị {{ $movies->firstItem() }}–{{ $movies->lastItem() }} / {{ $movies->total() }} phim</small>
                    <div>
                        {{ $movies->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
