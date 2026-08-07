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
            <span class="font-medium">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
            <span class="material-symbols-outlined text-red-600">error</span>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">

            {{-- Search --}}
            <form method="GET" action="{{ route('admin.formats.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size:20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm định dạng...">
                </div>
                <button type="submit" class="bg-primary text-on-primary text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                <a href="{{ route('admin.formats.index') }}"
                    class="bg-surface-container-high text-on-surface text-sm font-semibold px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center">
                    Xóa lọc
                </a>
                @endif
            </form>

            @if($formats->isEmpty())
            <div class="text-center py-16 text-on-surface-variant rounded-lg border border-dashed border-outline-variant/60">
                <span class="material-symbols-outlined text-5xl text-outline-variant mb-3 block">theaters</span>
                <p class="font-semibold text-on-surface">Chưa có định dạng nào</p>
                <p class="text-sm mt-1">Hãy thêm định dạng chiếu phim đầu tiên!</p>
            </div>
            @else
            <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container/60 text-xs font-semibold text-on-surface-variant border-b border-outline-variant/60 uppercase tracking-wider">
                            <th class="py-3.5 px-5" style="width:50px;">#</th>
                            <th class="py-3.5 px-5">Tên Định Dạng</th>
                            <th class="py-3.5 px-5">Mô Tả</th>
                            <th class="py-3.5 px-5">Giá Phụ Thu</th>
                            <th class="py-3.5 px-5">Trạng Thái</th>
                            <th class="py-3.5 px-5">Suất Chiếu</th>
                            <th class="py-3.5 px-5 text-right" style="width:160px;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-on-surface divide-y divide-outline-variant/40">
                        @foreach($formats as $format)
                        <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                            <td class="py-4 px-5 text-on-surface-variant font-medium text-xs">
                                {{ $loop->iteration + ($formats->currentPage() - 1) * $formats->perPage() }}
                            </td>
                            <td class="py-4 px-5 font-semibold">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                    <span class="material-symbols-outlined text-[14px]">theaters</span>
                                    {{ $format->name }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-on-surface-variant max-w-[220px] truncate">
                                {{ $format->description ?: '—' }}
                            </td>
                            <td class="py-4 px-5">
                                @if($format->surcharge_price > 0)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <span class="material-symbols-outlined text-[13px]">add_circle</span>
                                    +{{ number_format($format->surcharge_price, 0, ',', '.') }}đ
                                </span>
                                @else
                                <span class="text-xs text-zinc-400 font-medium">Không phụ thu</span>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                @if($format->status === 'active')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                    Hoạt động
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-zinc-100 text-zinc-500 border border-zinc-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-zinc-400 inline-block"></span>
                                    Tạm dừng
                                </span>
                                @endif
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="material-symbols-outlined text-[13px]">movie</span>
                                    {{ $format->showtimes_count }} suất
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right whitespace-nowrap">
                                <div class="flex gap-2 items-center justify-end">
                                    <button type="button"
                                        onclick="openEditModal({{ $format->id }}, '{{ addslashes($format->name) }}', '{{ addslashes($format->description ?? '') }}', {{ $format->surcharge_price }}, '{{ $format->status }}')"
                                        class="inline-flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-200">
                                        <span class="material-symbols-outlined" style="font-size:14px;">edit</span> Sửa
                                    </button>
                                    <button type="button"
                                        onclick="openDeleteModal({{ $format->id }}, '{{ addslashes($format->name) }}', {{ $format->showtimes_count }}, {{ $format->rooms_count }})"
                                        class="inline-flex items-center gap-1 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all duration-200">
                                        <span class="material-symbols-outlined" style="font-size:14px;">delete</span> Xóa
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-4">
                <small class="text-sm text-on-surface-variant">
                    Hiển thị {{ $formats->firstItem() }}–{{ $formats->lastItem() }} / {{ $formats->total() }} định dạng
                </small>
                {{ $formats->links() }}
            </div>
            @endif
        </div>
    </div>
</main>

{{-- ══════════════════════════════════════════════════════
     MODAL THÊM
══════════════════════════════════════════════════════ --}}
<div id="create-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeCreateModal()"></div>
    <div id="create-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-lg mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">

        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">add_circle</span>
                Thêm Định Dạng Mới
            </h3>
            <button onclick="closeCreateModal()" class="text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.formats.store') }}" class="space-y-4" id="create-form">
            @csrf

            {{-- Tên --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                    Tên định dạng <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                    value="{{ old('name') }}"
                    placeholder="VD: 2D, 3D, IMAX, 4DX..."
                    class="w-full border rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:ring-1 transition-colors
                        {{ $errors->has('name') && !session('edit_id') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary' }}">
                @error('name')
                    @if(!session('edit_id'))
                    <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                    </p>
                    @endif
                @enderror
            </div>

            {{-- Mô tả --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Mô tả</label>
                <textarea name="description" rows="2"
                    placeholder="Mô tả ngắn về định dạng chiếu..."
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors resize-none">{{ old('description') }}</textarea>
                @error('description')
                    @if(!session('edit_id'))
                    <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                    </p>
                    @endif
                @enderror
            </div>

            {{-- Giá phụ thu + Trạng thái --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                        Giá phụ thu (đ) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="surcharge_price" min="0"
                        value="{{ old('surcharge_price', 0) }}"
                        class="w-full border rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:ring-1 transition-colors
                            {{ $errors->has('surcharge_price') && !session('edit_id') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary' }}">
                    @error('surcharge_price')
                        @if(!session('edit_id'))
                        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                        </p>
                        @endif
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                    </select>
                </div>
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

{{-- ══════════════════════════════════════════════════════
     MODAL SỬA
══════════════════════════════════════════════════════ --}}
<div id="edit-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeEditModal()"></div>
    <div id="edit-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-lg mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">

        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">edit</span>
                Chỉnh Sửa Định Dạng
            </h3>
            <button onclick="closeEditModal()" class="text-on-surface-variant hover:text-on-surface transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form id="edit-form" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Tên --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                    Tên định dạng <span class="text-red-500">*</span>
                </label>
                <input type="text" id="edit-name" name="name"
                    value="{{ session('edit_id') ? old('name') : '' }}"
                    class="w-full border rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:ring-1 transition-colors
                        {{ $errors->has('name') && session('edit_id') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary' }}"
                    required>
                @error('name')
                    @if(session('edit_id'))
                    <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                    </p>
                    @endif
                @enderror
            </div>

            {{-- Mô tả --}}
            <div>
                <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">Mô tả</label>
                <textarea id="edit-description" name="description" rows="2"
                    placeholder="Mô tả ngắn về định dạng chiếu..."
                    class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors resize-none">{{ session('edit_id') ? old('description') : '' }}</textarea>
                @error('description')
                    @if(session('edit_id'))
                    <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                    </p>
                    @endif
                @enderror
            </div>

            {{-- Giá phụ thu + Trạng thái --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                        Giá phụ thu (đ) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="edit-surcharge" name="surcharge_price" min="0"
                        value="{{ session('edit_id') ? old('surcharge_price') : '' }}"
                        class="w-full border rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:ring-1 transition-colors
                            {{ $errors->has('surcharge_price') && session('edit_id') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : 'border-outline-variant focus:border-primary focus:ring-primary' }}"
                        required>
                    @error('surcharge_price')
                        @if(session('edit_id'))
                        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">error</span>{{ $message }}
                        </p>
                        @endif
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wider">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="edit-status" name="status"
                        class="w-full border border-outline-variant rounded-lg px-3 py-2.5 text-sm text-on-surface bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        <option value="active" {{ session('edit_id') && old('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ session('edit_id') && old('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                    </select>
                </div>
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

{{-- ══════════════════════════════════════════════════════
     MODAL XÓA
══════════════════════════════════════════════════════ --}}
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div id="delete-modal-content"
        class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg w-full max-w-md mx-4 p-6 transform scale-95 opacity-0 transition-all duration-200">
        <div class="flex flex-col items-center text-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center border border-red-200 text-red-600">
                <span class="material-symbols-outlined text-4xl">warning</span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-on-surface">Xác Nhận Xóa</h3>
                <p class="text-sm text-on-surface-variant mt-2">
                    Bạn có chắc muốn xóa định dạng <strong id="delete-name" class="text-red-600"></strong>?
                </p>
                <p id="delete-warning"
                    class="hidden text-xs text-red-600 mt-3 bg-red-50 border border-red-200 rounded-lg p-2.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-sm">block</span>
                    <span id="delete-warning-text"></span>
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
                        class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-bold rounded-lg hover:bg-red-700 shadow-sm transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        Xác nhận xóa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Helpers ──────────────────────────────────────────────────────
    function animateOpen(id) {
        const el = document.getElementById(id);
        setTimeout(() => { el.classList.remove('scale-95','opacity-0'); el.classList.add('scale-100','opacity-100'); }, 10);
    }
    function animateClose(modalId, contentId, cb) {
        const el = document.getElementById(contentId);
        el.classList.remove('scale-100','opacity-100');
        el.classList.add('scale-95','opacity-0');
        setTimeout(() => { document.getElementById(modalId).classList.add('hidden'); cb && cb(); }, 200);
    }

    // ── Create ───────────────────────────────────────────────────────
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
        animateOpen('create-modal-content');
    }
    function closeCreateModal() { animateClose('create-modal','create-modal-content'); }

    // ── Edit ─────────────────────────────────────────────────────────
    function openEditModal(id, name, description, surcharge, status) {
        document.getElementById('edit-form').action = `/admin/formats/${id}`;
        document.getElementById('edit-name').value        = name;
        document.getElementById('edit-description').value = description;
        document.getElementById('edit-surcharge').value   = surcharge;
        document.getElementById('edit-status').value      = status;
        document.getElementById('edit-modal').classList.remove('hidden');
        animateOpen('edit-modal-content');
    }
    function closeEditModal() { animateClose('edit-modal','edit-modal-content'); }

    // ── Delete ───────────────────────────────────────────────────────
    function openDeleteModal(id, name, showtimesCount, roomsCount) {
        document.getElementById('delete-form').action = `/admin/formats/${id}`;
        document.getElementById('delete-name').textContent = `«${name}»`;
        const warning = document.getElementById('delete-warning');
        const submit  = document.getElementById('delete-submit');
        const total   = (showtimesCount || 0) + (roomsCount || 0);
        if (total > 0) {
            const parts = [];
            if (showtimesCount > 0) parts.push(`${showtimesCount} suất chiếu`);
            if (roomsCount > 0)     parts.push(`${roomsCount} phòng chiếu`);
            document.getElementById('delete-warning-text').textContent =
                `Không thể xóa! Định dạng này đang được dùng bởi ${parts.join(' và ')}.`;
            warning.classList.remove('hidden');
            submit.disabled = true;
        } else {
            warning.classList.add('hidden');
            submit.disabled = false;
        }
        document.getElementById('delete-modal').classList.remove('hidden');
        animateOpen('delete-modal-content');
    }
    function closeDeleteModal() { animateClose('delete-modal','delete-modal-content'); }

    // ── Tự động mở lại modal khi có lỗi validation ───────────────────
    @if($errors->any())
        @if(session('edit_id'))
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('edit-modal');
            modal.classList.remove('hidden');
            animateOpen('edit-modal-content');
            document.getElementById('edit-form').action = `/admin/formats/{{ session('edit_id') }}`;
        });
        @else
        document.addEventListener('DOMContentLoaded', () => openCreateModal());
        @endif
    @endif

    // ── Escape key ───────────────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); closeDeleteModal(); }
    });
</script>
@endpush
@endsection
