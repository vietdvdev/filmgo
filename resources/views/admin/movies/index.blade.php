@extends('layouts.admin')

@section('title', 'Quản Lý Phim - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Phim</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Tổng số: {{ $movies->total() }} bộ phim đang quản lý trong hệ thống</p>
            </div>
            <a href="{{ route('admin.movies.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Phim Mới
            </a>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-red-600">error</span>
                <span class="font-body-md text-body-md font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">
            <!-- Navigation Tabs -->
            <div class="flex border-b border-outline-variant/30">
                <a href="{{ route('admin.movies.index', array_merge(request()->query(), ['tab' => 'active', 'page' => 1])) }}" 
                   class="px-5 py-3 border-b-2 font-label-md text-label-md transition-all duration-200 flex items-center gap-2 {{ $tabFilter === 'active' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                    <span class="material-symbols-outlined" style="font-size: 18px;">movie</span>
                    Đang Hoạt Động
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $tabFilter === 'active' ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">
                        {{ $activeCount }}
                    </span>
                </a>
                <a href="{{ route('admin.movies.index', array_merge(request()->query(), ['tab' => 'trash', 'page' => 1])) }}" 
                   class="px-5 py-3 border-b-2 font-label-md text-label-md transition-all duration-200 flex items-center gap-2 {{ $tabFilter === 'trash' ? 'border-primary text-primary font-semibold' : 'border-transparent text-on-surface-variant hover:text-on-surface' }}">
                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                    Thùng Rác
                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full {{ $tabFilter === 'trash' ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">
                        {{ $trashCount }}
                    </span>
                </a>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('admin.movies.index') }}" class="flex flex-wrap gap-3 items-center pt-2">
                <input type="hidden" name="tab" value="{{ $tabFilter }}">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm tên phim...">
                </div>

                <select name="status" class="w-48 pr-8 bg-surface border border-outline-variant text-on-surface text-label-sm font-label-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                    <option value="">Tất cả trạng thái</option>
                    <option value="showing"  {{ request('status') == 'showing'  ? 'selected' : '' }}>Đang chiếu</option>
                    <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                    <option value="stopped"  {{ request('status') == 'stopped'  ? 'selected' : '' }}>Ngừng chiếu</option>
                </select>

                <select name="genre" class="w-48 pr-8 bg-surface border border-outline-variant text-on-surface text-label-sm font-label-sm rounded-lg px-3 py-2 focus:ring-primary focus:border-primary">
                    <option value="">Tất cả thể loại</option>
                    @foreach($genres as $g)
                        <option value="{{ $g->id }}" {{ request('genre') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200">
                    Lọc phim
                </button>

                @if(request()->hasAny(['search','status','genre']))
                    <a href="{{ route('admin.movies.index', ['tab' => $tabFilter]) }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($movies->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">movie</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Không tìm thấy phim nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy điều chỉnh bộ lọc hoặc thêm phim mới vào hệ thống.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-4 font-semibold" style="width: 50px;">#</th>
                                <th class="py-3.5 px-4 font-semibold" style="width: 80px;">Poster</th>
                                <th class="py-3.5 px-4 font-semibold" style="width: 25%;">Thông Tin Phim</th>
                                <th class="py-3.5 px-4 font-semibold whitespace-nowrap">Thể Loại</th>
                                <th class="py-3.5 px-4 font-semibold whitespace-nowrap" style="width: 110px;">Thời Lượng</th>
                                <th class="py-3.5 px-4 font-semibold whitespace-nowrap" style="width: 120px;">Khởi Chiếu</th>
                                <th class="py-3.5 px-4 font-semibold whitespace-nowrap" style="width: 80px;">Độ Tuổi</th>
                                <th class="py-3.5 px-4 font-semibold whitespace-nowrap" style="width: 130px;">Trạng Thái</th>
                                <th class="py-3.5 px-4 font-semibold text-right whitespace-nowrap" style="width: 190px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($movies as $movie)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200 {{ $movie->trashed() ? 'opacity-50' : '' }}">
                                    <td class="py-4 px-4 text-on-surface-variant font-medium">{{ $loop->iteration + ($movies->currentPage() - 1) * $movies->perPage() }}</td>
                                    <td class="py-4 px-4">
                                        <div class="w-12 h-18 rounded overflow-hidden shadow-sm border border-outline-variant/30 bg-surface-container-high flex-shrink-0">
                                            @if($movie->poster)
                                                <img src="{{ asset($movie->poster) }}" alt="{{ $movie->title }}" class="w-full h-full object-cover transition-transform duration-200 hover:scale-110">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-on-surface-variant">
                                                    <span class="material-symbols-outlined" style="font-size: 20px;">image</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-semibold text-on-surface text-body-lg leading-snug truncate max-w-[280px]" title="{{ $movie->title }}">{{ $movie->title }}</div>
                                        <div class="text-xs text-on-surface-variant mt-1 truncate max-w-[280px] flex items-center gap-1.5">
                                            @if($movie->director)
                                                <span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-xs" style="font-size:12px;">person</span> {{ $movie->director }}</span>
                                            @endif
                                            @if($movie->country)
                                                <span class="text-outline-variant">•</span>
                                                <span class="flex items-center gap-0.5"><span class="material-symbols-outlined text-xs" style="font-size:12px;">public</span> {{ $movie->country }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex flex-wrap gap-1 max-w-[180px]">
                                            @foreach($movie->genres as $genre)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/50">
                                                    {{ $genre->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap font-medium text-on-surface-variant">
                                        {{ $movie->duration }} phút
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-on-surface-variant">
                                        {{ $movie->release_date->format('d/m/Y') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @php
                                            $ageBg = match($movie->age_limit) {
                                                'P' => 'bg-emerald-500/10 text-emerald-700 border-emerald-300/60',
                                                'K' => 'bg-blue-500/10 text-blue-700 border-blue-300/60',
                                                'T13' => 'bg-amber-500/10 text-amber-700 border-amber-300/60',
                                                'T16' => 'bg-orange-500/10 text-orange-700 border-orange-300/60',
                                                'T18' => 'bg-red-500/10 text-red-700 border-red-300/60',
                                                default => 'bg-neutral-100 text-neutral-800 border-neutral-300',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center justify-center w-9 h-6.5 rounded-md font-bold text-xs border {{ $ageBg }}" title="Giới hạn độ tuổi: {{ $movie->age_limit }}">
                                            {{ $movie->age_limit }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($movie->trashed())
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-neutral-100 text-neutral-700 border border-neutral-300 whitespace-nowrap">Đã xóa</span>
                                        @elseif($movie->status === 'showing')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 whitespace-nowrap">Đang chiếu</span>
                                        @elseif($movie->status === 'upcoming')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300 whitespace-nowrap">Sắp chiếu</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300 whitespace-nowrap">Ngừng chiếu</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 text-right whitespace-nowrap">
                                        <div class="flex gap-2 items-center justify-end whitespace-nowrap">
                                            @if($movie->trashed())
                                                <form action="{{ route('admin.movies.restore', $movie->id) }}" method="POST" class="inline-block align-middle">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                        <span class="material-symbols-outlined" style="font-size: 15px;">settings_backup_restore</span> Khôi phục
                                                    </button>
                                                </form>
                                            @else
                                                <a href="{{ route('admin.movies.edit', $movie) }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                    <span class="material-symbols-outlined" style="font-size: 15px;">edit</span> Sửa
                                                </a>
                                                <button type="button" 
                                                        onclick="openDeleteModal('{{ route('admin.movies.destroy', $movie) }}', '{{ addslashes($movie->title) }}')"
                                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                    <span class="material-symbols-outlined" style="font-size: 15px;">delete</span> Xóa
                                                </button>
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

<!-- Custom Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg max-w-md w-full mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300 ease-out" id="delete-modal-content">
        <div class="flex flex-col items-center text-center space-y-4">
            <!-- Icon -->
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center border border-red-200 text-red-600">
                <span class="material-symbols-outlined text-4xl">warning</span>
            </div>
            
            <!-- Title & Description -->
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Xác Nhận Xóa Phim</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                    Bạn có chắc chắn muốn xóa bộ phim <strong id="delete-movie-title" class="text-red-600 font-semibold"></strong>?
                </p>
                <p class="text-xs text-red-500/80 mt-2 italic bg-red-50/50 p-2 rounded border border-red-100">
                    Lưu ý: Phim sẽ được chuyển vào Thùng rác. Bạn có thể khôi phục lại sau.
                </p>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 w-full mt-4">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface font-label-md text-label-md rounded-lg hover:bg-surface-container-highest transition-colors">
                    Hủy bỏ
                </button>
                <form id="delete-confirm-form" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white font-label-md text-label-md rounded-lg hover:bg-red-700 shadow-sm hover:shadow-md transition-all duration-200">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(actionUrl, movieTitle) {
        const modal = document.getElementById('delete-confirm-modal');
        const content = document.getElementById('delete-modal-content');
        const form = document.getElementById('delete-confirm-form');
        const titleSpan = document.getElementById('delete-movie-title');
        
        form.action = actionUrl;
        titleSpan.textContent = `«${movieTitle}»`;
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-confirm-modal');
        const content = document.getElementById('delete-modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endsection
