@extends('layouts.admin')

@section('title', 'Quản Lý Rạp - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

        {{-- Page Header --}}
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Rạp Chiếu Phim</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý danh sách các rạp chiếu phim trong hệ thống.</p>
            </div>
            <a href="{{ route('admin.cinemas.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Rạp
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

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.cinemas.index') }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm tên rạp, thành phố...">
                </div>
                <select name="status" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Ngừng hoạt động</option>
                </select>
                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Lọc
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.cinemas.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($cinemas->total() === 0)
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">theater_comedy</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có rạp chiếu nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm rạp chiếu phim đầu tiên vào hệ thống!</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold">Tên Rạp</th>
                                <th class="py-3.5 px-6 font-semibold">Thành Phố</th>
                                <th class="py-3.5 px-6 font-semibold">Địa Chỉ</th>
                                <th class="py-3.5 px-6 font-semibold">Điện Thoại</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Phòng Chiếu</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Trạng Thái</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:120px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($cinemas as $cinema)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($cinemas->currentPage() - 1) * $cinemas->perPage() }}</td>
                                    <td class="py-4 px-6 font-medium">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                            {{ $cinema->name }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
                                            {{ $cinema->city }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-on-surface-variant text-sm max-w-[220px] truncate">{{ $cinema->address }}</td>
                                    <td class="py-4 px-6 text-on-surface-variant text-sm font-mono">{{ $cinema->phone }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            <span class="material-symbols-outlined" style="font-size:13px;">meeting_room</span>
                                            {{ $cinema->rooms_count }} phòng
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($cinema->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">✓ Hoạt động</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700">✗ Ngừng HĐ</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-3 items-center">
                                            <a href="{{ route('admin.cinemas.edit', $cinema) }}"
                                                class="text-primary hover:text-blue-700 transition-colors">
                                                <span class="material-symbols-outlined" style="font-size: 20px;">edit</span>
                                            </a>
                                            <button type="button"
                                                onclick="openDeleteModal('{{ route('admin.cinemas.destroy', $cinema) }}', '{{ addslashes($cinema->name) }}')"
                                                class="text-error hover:text-red-700 transition-colors">
                                                <span class="material-symbols-outlined" style="font-size: 20px;">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $cinemas->links() }}
                </div>
            @endif
        </div>
    </div>
</main>

{{-- Delete Modal --}}
<div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    <div id="delete-modal-content" class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg max-w-md w-full mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300 ease-out">
        <div class="flex flex-col items-center text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center border border-red-200 text-red-600">
                <span class="material-symbols-outlined text-4xl">warning</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Xác Nhận Xóa Rạp Chiếu</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                    Bạn có chắc chắn muốn xóa rạp <strong id="delete-cinema-name" class="text-red-600"></strong>?
                </p>
                <p class="text-xs text-red-500/80 mt-2 italic bg-red-50/50 p-2 rounded border border-red-100">
                    Chỉ có thể xóa rạp chưa có phòng chiếu liên kết.
                </p>
            </div>
            <div class="flex gap-3 w-full">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface font-label-md text-label-md rounded-lg hover:bg-surface-container-highest transition-colors">
                    Hủy bỏ
                </button>
                <form id="delete-confirm-form" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2.5 bg-red-600 text-white font-label-md text-label-md rounded-lg hover:bg-red-700 transition-all duration-200">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(actionUrl, cinemaName) {
        document.getElementById('delete-confirm-form').action = actionUrl;
        document.getElementById('delete-cinema-name').textContent = `«${cinemaName}»`;
        const modal = document.getElementById('delete-confirm-modal');
        const content = document.getElementById('delete-modal-content');
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    function closeDeleteModal() {
        const content = document.getElementById('delete-modal-content');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        setTimeout(() => document.getElementById('delete-confirm-modal').classList.add('hidden'), 300);
    }
</script>
@endsection
