@extends('layouts.admin')

@section('title', 'Định Dạng Phòng Chiếu - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

        {{-- Page Header --}}
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Định Dạng Phòng Chiếu</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý các định dạng chiếu phim (2D, 3D, IMAX, ...) và giá phụ thu.</p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size:18px;">add</span>
                Thêm Định Dạng
            </button>
        </div>

        {{-- Flash messages --}}
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

            {{-- Search --}}
            <form method="GET" action="{{ route('admin.formats.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size:20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm định dạng...">
                </div>
                <button type="submit"
                    class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                <a href="{{ route('admin.formats.index') }}"
                    class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                    Xóa lọc
                </a>
                @endif
            </form>

            @if($formats->isEmpty())
            <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">theaters</span>
                <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có định dạng nào</p>
                <p class="font-body-md text-body-md mt-1">Hãy thêm định dạng chiếu phim đầu tiên!</p>
            </div>
            @else
            <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                            <th class="py-3.5 px-6 font-semibold" style="width:60px;">#</th>
                            <th class="py-3.5 px-6 font-semibold">Tên Định Dạng</th>
                            <th class="py-3.5 px-6 font-semibold">Giá Phụ Thu</th>
                            <th class="py-3.5 px-6 font-semibold">Suất Chiếu</th>
                            <th class="py-3.5 px-6 font-semibold text-right" style="width:180px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                        @foreach($formats as $format)
                        <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                            <td class="py-4 px-6 text-on-surface-variant font-medium">
                                {{ $loop->iteration + ($formats->currentPage() - 1) * $formats->perPage() }}
                            </td>
                            <td class="py-4 px-6 font-medium">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    <span class="material-symbols-outlined text-[14px]">theaters</span>
                                    {{ $format->name }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                @if($format->surcharge_price > 0)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <span class="material-symbols-outlined text-[14px]">add_circle</span>
                                    +{{ number_format($format->surcharge_price, 0, ',', '.') }}đ
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-zinc-100 text-zinc-500 border border-zinc-200">
                                    Không phụ thu
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    <span class="material-symbols-outlined text-[15px]">movie</span>
                                    {{ $format->showtimes_count }} suất
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="flex gap-2 items-center justify-end">
                                    <button type="button"
                                        onclick="openEditModal({{ $format->id }}, '{{ addslashes($format->name) }}', {{ $format->surcharge_price }})"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-sm transition-all duration-200">
                                        <span class="material-symbols-outlined" style="font-size:15px;">edit</span> Sửa
                                    </button>
                                    <button type="button"
                                        onclick="openDeleteModal({{ $format->id }}, '{{ addslashes($format->name) }}', {{ $format->showtimes_count }})"
                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white hover:shadow-sm transition-all duration-200">
                                        <span class="material-symbols-outlined" style="font-size:15px;">delete</span> Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="flex justify-between items-center mt-6">
                <small class="font-body-md text-body-md text-on-surface-variant">
                    Hiển thị {{ $formats->firstItem() }}–{{ $formats->lastItem() }} / {{ $formats->total() }} định dạng
                </small>
                {{ $formats->links() }}
            </div>
            @endif
        </div>
    </div>
</main>

{{-- ── Modal Thêm ── --}}
<div id="create-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCreateModal()"></div>
    <div id="create-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-md mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">
        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">add_circle</span>
            Thêm Định Dạng Mới
        </h3>
        <form method="POST" action="{{ route('admin.formats.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Tên định dạng <span class="text-red-500">*</span></label>
                <input type="text" name="name" placeholder="VD: 2D, 3D, IMAX, 4DX..."
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                    required>
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Giá phụ thu (đ) <span class="text-red-500">*</span></label>
                <input type="number" name="surcharge_price" placeholder="0" min="0" value="0"
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                    required>
                <p class="text-[11px] text-on-surface-variant mt-1">Nhập 0 nếu không có phụ thu thêm.</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface text-sm font-semibold rounded-lg hover:bg-surface-container-highest transition-colors">
                    Hủy
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all duration-200">
                    Thêm định dạng
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Sửa ── --}}
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div id="edit-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-md mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">
        <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-blue-600">edit</span>
            Chỉnh Sửa Định Dạng
        </h3>
        <form id="edit-form" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Tên định dạng <span class="text-red-500">*</span></label>
                <input type="text" id="edit-name" name="name"
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                    required>
            </div>
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Giá phụ thu (đ) <span class="text-red-500">*</span></label>
                <input type="number" id="edit-surcharge" name="surcharge_price" min="0"
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                    required>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface text-sm font-semibold rounded-lg hover:bg-surface-container-highest transition-colors">
                    Hủy
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-lg hover:bg-blue-700 shadow-sm transition-all duration-200">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Xóa ── --}}
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div id="delete-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-md mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">
        <div class="flex flex-col items-center text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center border border-red-200 text-red-600">
                <span class="material-symbols-outlined text-4xl">warning</span>
            </div>
            <div>
                <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Xác Nhận Xóa</h3>
                <p class="font-body-md text-body-md text-on-surface-variant mt-2">
                    Bạn có chắc muốn xóa định dạng <strong id="delete-name" class="text-red-600"></strong>?
                </p>
                <p id="delete-warning" class="hidden text-xs text-red-500 mt-2 bg-red-50 p-2 rounded border border-red-100">
                    ⚠️ Định dạng này đang được sử dụng bởi suất chiếu, không thể xóa.
                </p>
            </div>
            <div class="flex gap-3 w-full">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface text-sm font-semibold rounded-lg hover:bg-surface-container-highest transition-colors">
                    Hủy bỏ
                </button>
                <form id="delete-form" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button id="delete-submit" type="submit"
                        class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 shadow-sm transition-all duration-200">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const routes = {
        update: (id) => `/admin/formats/${id}`,
        destroy: (id) => `/admin/formats/${id}`,
    };

    function animateOpen(contentId) {
        const el = document.getElementById(contentId);
        setTimeout(() => {
            el.classList.remove('scale-95', 'opacity-0');
            el.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function animateClose(modalId, contentId, cb) {
        const el = document.getElementById(contentId);
        el.classList.remove('scale-100', 'opacity-100');
        el.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { document.getElementById(modalId).classList.add('hidden'); if(cb) cb(); }, 200);
    }

    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
        animateOpen('create-modal-content');
    }
    function closeCreateModal() {
        animateClose('create-modal', 'create-modal-content');
    }

    function openEditModal(id, name, surcharge) {
        document.getElementById('edit-form').action = routes.update(id);
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-surcharge').value = surcharge;
        document.getElementById('edit-modal').classList.remove('hidden');
        animateOpen('edit-modal-content');
    }
    function closeEditModal() {
        animateClose('edit-modal', 'edit-modal-content');
    }

    function openDeleteModal(id, name, showtimesCount) {
        document.getElementById('delete-form').action = routes.destroy(id);
        document.getElementById('delete-name').textContent = `«${name}»`;
        const warning = document.getElementById('delete-warning');
        const submit  = document.getElementById('delete-submit');
        if (showtimesCount > 0) {
            warning.classList.remove('hidden');
            submit.disabled = true;
            submit.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            submit.disabled = false;
            submit.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        document.getElementById('delete-modal').classList.remove('hidden');
        animateOpen('delete-modal-content');
    }
    function closeDeleteModal() {
        animateClose('delete-modal', 'delete-modal-content');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateModal(); closeEditModal(); closeDeleteModal();
        }
    });
</script>
@endpush
@endsection
