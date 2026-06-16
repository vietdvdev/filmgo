@extends('layouts.admin')

@section('title', 'Thêm Phim Mới - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.movies.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">movie</span> Quản Lý Phim
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Thêm Mới</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Thêm Phim Mới</h2>
        </div>

        <form action="{{ route('admin.movies.store') }}" method="POST" enctype="multipart/form-data" id="movieForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter items-start">
                <!-- Cột trái: Form nhập liệu -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Thông tin cơ bản -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                            <h3 class="font-headline-sm text-headline-sm">Thông Tin Cơ Bản</h3>
                        </div>

                        <!-- Tên phim -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Tên Phim <span class="text-error">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('title') border-error @enderror"
                                placeholder="Nhập tên phim...">
                            @error('title')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Đạo diễn & Quốc gia -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Đạo Diễn</label>
                                <input type="text" name="director" value="{{ old('director') }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Tên đạo diễn...">
                            </div>
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Quốc Gia</label>
                                <input type="text" name="country" value="{{ old('country') }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Việt Nam, Mỹ, Hàn Quốc...">
                            </div>
                        </div>

                        <!-- Thời lượng, Ngày khởi chiếu, Trạng thái -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Thời Lượng (phút) <span class="text-error">*</span></label>
                                <input type="number" name="duration" value="{{ old('duration') }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('duration') border-error @enderror"
                                    placeholder="90" min="1" max="600">
                                @error('duration')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Ngày Khởi Chiếu <span class="text-error">*</span></label>
                                <input type="date" name="release_date" value="{{ old('release_date') }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('release_date') border-error @enderror">
                                @error('release_date')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Trạng Thái <span class="text-error">*</span></label>
                                <select name="status" class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('status') border-error @enderror">
                                    <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Sắp chiếu</option>
                                    <option value="showing"  {{ old('status') == 'showing'  ? 'selected' : '' }}>Đang chiếu</option>
                                    <option value="stopped"  {{ old('status') == 'stopped'  ? 'selected' : '' }}>Ngừng chiếu</option>
                                </select>
                                @error('status')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <!-- Giới hạn tuổi -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Giới Hạn Độ Tuổi <span class="text-error">*</span></label>
                            <div class="flex flex-wrap gap-3">
                                @foreach(['P' => 'P - Mọi lứa tuổi', 'K' => 'K - Dưới 13', 'T13' => 'T13 - Từ 13 tuổi', 'T16' => 'T16 - Từ 16 tuổi', 'T18' => 'T18 - Từ 18 tuổi'] as $val => $label)
                                    <input type="radio" name="age_limit" id="age_{{ $val }}" value="{{ $val }}" class="hidden peer"
                                        {{ old('age_limit', 'P') == $val ? 'checked' : '' }}>
                                    <label for="age_{{ $val }}" class="cursor-pointer inline-flex items-center justify-center px-4 py-2 font-bold border border-outline-variant peer-checked:border-primary peer-checked:bg-primary peer-checked:text-on-primary rounded-lg text-xs text-on-surface-variant transition-all duration-200" title="{{ $label }}">
                                        {{ $val }}
                                    </label>
                                @endforeach
                            </div>
                            @error('age_limit')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Mô tả -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Mô Tả Nội Dung</label>
                            <textarea name="description" rows="4" class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                                placeholder="Tóm tắt nội dung phim...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <!-- Poster & Trailer -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">image</span>
                            <h3 class="font-headline-sm text-headline-sm">Poster & Trailer</h3>
                        </div>

                        <div class="flex flex-col md:flex-row gap-6 items-start">
                            <!-- Preview -->
                            <div class="flex-shrink-0 mx-auto md:mx-0">
                                <div class="w-32 h-44 border border-outline-variant border-dashed rounded-lg overflow-hidden relative shadow-sm flex flex-col items-center justify-center text-on-surface-variant gap-2 bg-surface-container-low" id="placeholderDiv">
                                    <span class="material-symbols-outlined" style="font-size: 32px;">image</span>
                                    <span class="text-[11px] font-label-sm">Xem trước</span>
                                </div>
                                <img id="posterImg" class="w-32 h-44 object-cover rounded-lg border border-outline-variant shadow-sm hidden" src="" alt="Poster">
                            </div>
                            <!-- Input -->
                            <div class="flex-grow w-full space-y-4">
                                <div class="space-y-2">
                                    <label class="block font-label-md text-label-md text-on-surface">Tải Lên Poster <small class="text-on-surface-variant font-normal">(jpg, png, webp — tối đa 2MB)</small></label>
                                    <input type="file" name="poster" id="posterInput"
                                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('poster') border-error @enderror"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewPoster(this)">
                                    @error('poster')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div class="space-y-2">
                                    <label class="block font-label-md text-label-md text-on-surface">URL Trailer (YouTube/Vimeo)</label>
                                    <input type="url" name="trailer_url" value="{{ old('trailer_url') }}"
                                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('trailer_url') border-error @enderror"
                                        placeholder="https://youtube.com/watch?v=...">
                                    @error('trailer_url')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thể loại -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">sell</span>
                            <h3 class="font-headline-sm text-headline-sm">Thể Loại</h3>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($genres as $genre)
                                <div class="relative flex items-center gap-3 px-4 py-3 border border-outline-variant/50 rounded-lg hover:bg-surface-container-low transition-colors">
                                    <input type="checkbox" name="genres[]" id="genre_{{ $genre->id }}"
                                        value="{{ $genre->id }}" class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant"
                                        {{ in_array($genre->id, old('genres', [])) ? 'checked' : '' }}>
                                    <label for="genre_{{ $genre->id }}" class="cursor-pointer font-label-md text-label-md text-on-surface select-none">{{ $genre->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('genres')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Diễn viên -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">group</span>
                            <h3 class="font-headline-sm text-headline-sm">Diễn Viên</h3>
                        </div>

                        {{-- Danh sách diễn viên đã thêm --}}
                        <div id="actorList" class="flex flex-wrap gap-2"></div>

                        {{-- Inline popup thêm diễn viên --}}
                        <div id="addActorBox" class="p-4 bg-surface-container rounded-lg border border-outline-variant/30 space-y-3 hidden">
                            <input type="text" id="actorNameInput" class="w-full max-w-xs px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none" placeholder="Nhập tên diễn viên...">
                            <div class="flex gap-2">
                                <button type="button" class="bg-primary text-on-primary font-label-sm text-xs px-3 py-1.5 rounded-lg hover:bg-primary-container transition-colors" onclick="confirmActor()">Xác nhận</button>
                                <button type="button" class="bg-surface-container-high text-on-surface font-label-sm text-xs px-3 py-1.5 rounded-lg hover:bg-surface-container-highest transition-colors" onclick="cancelActor()">Hủy</button>
                            </div>
                        </div>

                        <button type="button" class="inline-flex items-center gap-1.5 px-4 py-2 border border-dashed border-outline-variant rounded-lg text-xs font-semibold text-on-surface-variant hover:border-primary hover:text-primary transition-all duration-200" id="addActorBtn" onclick="showAddActor()">
                            <span class="material-symbols-outlined" style="font-size: 16px;">add</span> Thêm Diễn Viên
                        </button>

                        {{-- Hidden inputs chứa tên diễn viên --}}
                        <div id="actorInputs"></div>
                    </div>

                </div>

                <!-- Cột phải: Xác nhận lưu trữ -->
                <div class="lg:col-span-1">
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-4 sticky top-20">
                        <div class="flex items-center gap-2 pb-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                            <h3 class="font-headline-sm text-headline-sm">Xác Nhận</h3>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant">Kiểm tra kỹ thông tin phim trước khi lưu. Trường có dấu <span class="text-error">*</span> là bắt buộc nhập.</p>

                        @if($errors->any())
                            <div class="p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg space-y-1">
                                <span class="font-label-md text-label-md text-error block">Lỗi nhập liệu:</span>
                                <ul class="list-disc pl-4 text-xs font-body-md space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 pt-2">
                            <button type="submit" class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-primary-container transition-colors flex items-center justify-center gap-2 shadow-md">
                                <span class="material-symbols-outlined" style="font-size: 20px;">save</span> Lưu Phim
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="w-full bg-surface-container-high text-on-surface font-label-md text-label-md py-3 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 20px;">close</span> Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    let actors = [];

    function showAddActor() {
        document.getElementById('addActorBox').classList.remove('hidden');
        document.getElementById('addActorBtn').classList.add('hidden');
        document.getElementById('actorNameInput').value = '';
        document.getElementById('actorNameInput').focus();
    }

    function cancelActor() {
        document.getElementById('addActorBox').classList.add('hidden');
        document.getElementById('addActorBtn').classList.remove('hidden');
    }

    function confirmActor() {
        const name = document.getElementById('actorNameInput').value.trim();
        if (!name) { document.getElementById('actorNameInput').focus(); return; }
        if (actors.find(a => a.toLowerCase() === name.toLowerCase())) {
            alert('Diễn viên này đã được thêm!'); return;
        }
        actors.push(name);
        renderActors();
        cancelActor();
    }

    function removeActor(name) {
        actors = actors.filter(a => a !== name);
        renderActors();
    }

    function renderActors() {
        const list = document.getElementById('actorList');
        const inputs = document.getElementById('actorInputs');
        list.innerHTML = actors.map(name =>
            `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-container text-primary-fixed-dim border border-primary-fixed-dim/20">${name}<button type="button" onclick="removeActor('${name.replace(/'/g, "\\'")}')" class="text-primary-fixed-dim/70 hover:text-red-600 transition-colors flex items-center"><span class="material-symbols-outlined" style="font-size: 14px;">close</span></button></span>`
        ).join('');
        inputs.innerHTML = actors.map(name =>
            `<input type="hidden" name="actor_names[]" value="${name}">`
        ).join('');
        document.getElementById('actorList').style.marginBottom = actors.length ? '12px' : '0';
    }

    // Enter để xác nhận
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('actorNameInput').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); confirmActor(); }
            if (e.key === 'Escape') cancelActor();
        });
    });

    function previewPoster(input) {
        const img = document.getElementById('posterImg');
        const placeholder = document.getElementById('placeholderDiv');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
</script>
@endsection
