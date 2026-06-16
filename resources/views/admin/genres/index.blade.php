@extends('layouts.admin')

@section('title', 'Quản Lý Thể Loại - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Thể Loại Phim</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý danh sách các thể loại phim trong hệ thống.</p>
            </div>
            <a href="{{ route('admin.genres.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Thể Loại
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
            <!-- Search -->
            <form method="GET" action="{{ route('admin.genres.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm thể loại...">
                </div>
                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.genres.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($genres->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">sell</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có thể loại nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm thể loại phim đầu tiên để phân loại nội dung!</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold">Tên Thể Loại</th>
                                <th class="py-3.5 px-6 font-semibold">Mô Tả Chi Tiết</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:140px;">Số Phim</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:180px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($genres as $genre)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($genres->currentPage() - 1) * $genres->perPage() }}</td>
                                    <td class="py-4 px-6 font-medium">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-sm">
                                            {{ $genre->name }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 max-w-[350px] truncate">
                                        <span class="text-on-surface-variant leading-relaxed">{{ $genre->description ?: '—' }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span class="material-symbols-outlined text-[15px]">movie</span>
                                            {{ $genre->movies_count }} phim
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right whitespace-nowrap">
                                        <div class="flex gap-2 items-center justify-end whitespace-nowrap">
                                            <a href="{{ route('admin.genres.edit', $genre) }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                <span class="material-symbols-outlined" style="font-size: 15px;">edit</span> Sửa
                                            </a>
                                            <button type="button" 
                                                    onclick="openDeleteModal('{{ route('admin.genres.destroy', $genre) }}', '{{ addslashes($genre->name) }}')"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                <span class="material-symbols-outlined" style="font-size: 15px;">delete</span> Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-6">
                    <small class="font-body-md text-body-md text-on-surface-variant">
                        Hiển thị {{ $genres->firstItem() }}–{{ $genres->lastItem() }} / {{ $genres->total() }} thể loại
                    </small>
                    <div>
                        {{ $genres->links() }}
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
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Xác Nhận Xóa Thể Loại</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                    Bạn có chắc chắn muốn xóa thể loại <strong id="delete-genre-name" class="text-red-600 font-semibold"></strong>?
                </p>
                <p class="text-xs text-red-500/80 mt-2 italic bg-red-50/50 p-2 rounded border border-red-100">
                    Lưu ý: Hành động này có thể ảnh hưởng đến việc phân loại các phim đang sử dụng thể loại này.
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
    function openDeleteModal(actionUrl, genreName) {
        const modal = document.getElementById('delete-confirm-modal');
        const content = document.getElementById('delete-modal-content');
        const form = document.getElementById('delete-confirm-form');
        const nameSpan = document.getElementById('delete-genre-name');
        
        form.action = actionUrl;
        nameSpan.textContent = `«${genreName}»`;
        
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
