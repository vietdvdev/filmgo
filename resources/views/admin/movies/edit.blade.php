@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Phim - FilmGo')

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
                <span class="text-outline">Chỉnh Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Phim</h2>
        </div>

        <form action="{{ route('admin.movies.update', $movie) }}" method="POST" enctype="multipart/form-data" id="movieForm">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter items-start">
                <!-- Cột trái: Thông tin mô tả & Media (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Info Badge -->
                    <div class="flex items-center gap-3 p-4 bg-primary-fixed text-on-primary-fixed rounded-lg border border-primary-fixed-dim/20">
                        <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">edit_note</span>
                        <span class="font-body-md text-body-md">
                            Đang chỉnh sửa: <strong class="text-primary">{{ $movie->title }}</strong>
                        </span>
                    </div>

                    <!-- Khối 1: Thông tin mô tả chính (General Info) -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                            <h3 class="font-headline-sm text-headline-sm">Thông Tin Cơ Bản</h3>
                        </div>

                        <!-- Tên phim -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Tên Phim <span class="text-error">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $movie->title) }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('title') border-error @enderror"
                                placeholder="Nhập tên phim...">
                            @error('title')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Đạo diễn & Quốc gia -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Đạo Diễn</label>
                                <input type="text" name="director" value="{{ old('director', $movie->director) }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Tên đạo diễn...">
                            </div>
                            <div class="space-y-2">
                                <label class="block font-label-md text-label-md text-on-surface">Quốc Gia</label>
                                <input type="text" name="country" value="{{ old('country', $movie->country) }}"
                                    class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" placeholder="Quốc gia...">
                            </div>
                        </div>

                        <!-- Mô tả -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Mô Tả Nội Dung</label>
                            <textarea name="description" rows="5" class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                                placeholder="Tóm tắt nội dung phim...">{{ old('description', $movie->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Khối 2: Media & Trailer -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">image</span>
                            <h3 class="font-headline-sm text-headline-sm">Media & Trailer</h3>
                        </div>

                        <div class="flex flex-col md:flex-row gap-6 items-start">
                            <!-- Preview Poster ở bên trái -->
                            <div class="flex-shrink-0 mx-auto md:mx-0">
                                @if($movie->poster)
                                    <img id="posterImg" class="w-36 h-52 object-cover rounded-lg border border-outline-variant shadow-sm" src="{{ asset($movie->poster) }}" alt="Poster">
                                    <div class="w-36 h-52 border border-outline-variant border-dashed rounded-lg overflow-hidden relative shadow-sm flex flex-col items-center justify-center text-on-surface-variant gap-2 bg-surface-container-low hidden" id="placeholderDiv">
                                        <span class="material-symbols-outlined" style="font-size: 36px;">image</span>
                                        <span class="text-xs font-label-sm">Xem trước</span>
                                    </div>
                                @else
                                    <img id="posterImg" class="w-36 h-52 object-cover rounded-lg border border-outline-variant shadow-sm hidden" src="" alt="Poster">
                                    <div class="w-36 h-52 border border-outline-variant border-dashed rounded-lg overflow-hidden relative shadow-sm flex flex-col items-center justify-center text-on-surface-variant gap-2 bg-surface-container-low" id="placeholderDiv">
                                        <span class="material-symbols-outlined" style="font-size: 36px;">image</span>
                                        <span class="text-xs font-label-sm">Xem trước</span>
                                    </div>
                                @endif
                            </div>
                            <!-- Input Poster và URL Trailer ở bên phải -->
                            <div class="flex-grow w-full space-y-5">
                                <div class="space-y-2">
                                    <label class="block font-label-md text-label-md text-on-surface">Tải Lên Poster Mới <small class="text-on-surface-variant font-normal">(jpg, png, webp — tối đa 2MB)</small></label>
                                    <input type="file" name="poster" id="posterInput"
                                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('poster') border-error @enderror"
                                        accept="image/jpeg,image/png,image/webp"
                                        onchange="previewPoster(this)">
                                    @error('poster')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror

                                    @if($movie->poster)
                                        <div class="flex items-center gap-2 mt-2">
                                            <input type="checkbox" name="remove_poster" value="1" id="removePoster"
                                                class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-outline-variant"
                                                onchange="toggleRemovePoster(this)">
                                            <label for="removePoster" class="cursor-pointer text-xs font-semibold text-red-600 select-none flex items-center gap-1.5">
                                                <span class="material-symbols-outlined" style="font-size: 16px;">delete</span> Xóa poster hiện tại
                                            </label>
                                        </div>
                                    @endif
                                </div>
                                <div class="space-y-2">
                                    <label class="block font-label-md text-label-md text-on-surface">URL Trailer (YouTube/Vimeo)</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-lg">play_circle</span>
                                        <input type="url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}"
                                            class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('trailer_url') border-error @enderror"
                                            placeholder="https://youtube.com/watch?v=...">
                                    </div>
                                    @error('trailer_url')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Khối 3: Thể Loại & Diễn Viên -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <!-- Thể loại -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">sell</span>
                                <h3 class="font-headline-sm text-headline-sm">Thể Loại</h3>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($genres as $genre)
                                    <label class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 border border-outline-variant rounded-full text-body-md text-on-surface-variant hover:bg-surface-container-low has-[:checked]:border-primary has-[:checked]:bg-primary-container/10 has-[:checked]:text-primary transition-all duration-200 select-none">
                                        <input type="checkbox" name="genres[]" value="{{ $genre->id }}" 
                                            class="w-4 h-4 rounded text-primary focus:ring-primary border-outline-variant"
                                            {{ in_array($genre->id, old('genres', $selectedGenres)) ? 'checked' : '' }}>
                                        <span>{{ $genre->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <hr class="border-outline-variant/20">

                        <!-- Diễn viên -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">group</span>
                                <h3 class="font-headline-sm text-headline-sm">Diễn Viên (Cast)</h3>
                            </div>

                            <div id="actorList" class="flex flex-wrap gap-2"></div>

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

                            <div id="actorInputs"></div>
                        </div>
                    </div>

                </div>

                <!-- Cột phải: Thông tin vận hành & Xuất bản (1/3) -->
                <div class="lg:col-span-1 space-y-6 sticky top-20">
                    
                    <!-- Khối 1: Cấu hình phát hành (Release Settings) -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-6">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">settings</span>
                            <h3 class="font-headline-sm text-headline-sm">Cấu Hình Phát Hành</h3>
                        </div>

                        <!-- Trạng thái (Radio Button Group) -->
                        <div class="space-y-3">
                            <label class="block font-label-md text-label-md text-on-surface">Trạng Thái <span class="text-error">*</span></label>
                            <div class="grid grid-cols-1 gap-2">
                                <!-- Sắp chiếu -->
                                <input type="radio" name="status" id="status_upcoming" value="upcoming" class="hidden peer" {{ old('status', $movie->status) == 'upcoming' ? 'checked' : '' }}>
                                <label for="status_upcoming" class="cursor-pointer flex items-center gap-3 px-4 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-700 transition-all duration-200">
                                    <span class="material-symbols-outlined text-xl">schedule</span>
                                    <span class="text-body-md font-semibold">Sắp chiếu</span>
                                </label>

                                <!-- Đang chiếu -->
                                <input type="radio" name="status" id="status_showing" value="showing" class="hidden peer" {{ old('status', $movie->status) == 'showing' ? 'checked' : '' }}>
                                <label for="status_showing" class="cursor-pointer flex items-center gap-3 px-4 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-700 transition-all duration-200">
                                    <span class="material-symbols-outlined text-xl">play_circle</span>
                                    <span class="text-body-md font-semibold">Đang chiếu</span>
                                </label>

                                <!-- Ngừng chiếu -->
                                <input type="radio" name="status" id="status_stopped" value="stopped" class="hidden peer" {{ old('status', $movie->status) == 'stopped' ? 'checked' : '' }}>
                                <label for="status_stopped" class="cursor-pointer flex items-center gap-3 px-4 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-700 transition-all duration-200">
                                    <span class="material-symbols-outlined text-xl">cancel</span>
                                    <span class="text-body-md font-semibold">Ngừng chiếu</span>
                                </label>
                            </div>
                            @error('status')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Thời lượng phim -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Thời Lượng (phút) <span class="text-error">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-lg">hourglass_empty</span>
                                <input type="number" name="duration" value="{{ old('duration', $movie->duration) }}"
                                    class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('duration') border-error @enderror"
                                    placeholder="90" min="1" max="600">
                            </div>
                            @error('duration')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Ngày khởi chiếu -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Ngày Khởi Chiếu <span class="text-error">*</span></label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-lg">calendar_today</span>
                                <input type="date" name="release_date" value="{{ old('release_date', $movie->release_date->format('Y-m-d')) }}"
                                    class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('release_date') border-error @enderror">
                            </div>
                            @error('release_date')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Khối 2: Giới hạn độ tuổi (Classification) -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">security</span>
                            <h3 class="font-headline-sm text-headline-sm">Phân Loại Độ Tuổi</h3>
                        </div>

                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Giới Hạn Độ Tuổi <span class="text-error">*</span></label>
                            <div class="grid grid-cols-5 gap-2">
                                <!-- P -->
                                <input type="radio" name="age_limit" id="age_P" value="P" class="hidden peer" {{ old('age_limit', $movie->age_limit) == 'P' ? 'checked' : '' }}>
                                <label for="age_P" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white transition-all duration-200" title="P - Mọi lứa tuổi">
                                    P
                                </label>

                                <!-- K -->
                                <input type="radio" name="age_limit" id="age_K" value="K" class="hidden peer" {{ old('age_limit', $movie->age_limit) == 'K' ? 'checked' : '' }}>
                                <label for="age_K" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white transition-all duration-200" title="K - Dưới 13 tuổi">
                                    K
                                </label>

                                <!-- T13 -->
                                <input type="radio" name="age_limit" id="age_T13" value="T13" class="hidden peer" {{ old('age_limit', $movie->age_limit) == 'T13' ? 'checked' : '' }}>
                                <label for="age_T13" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white transition-all duration-200" title="T13 - Từ 13 tuổi">
                                    T13
                                </label>

                                <!-- T16 -->
                                <input type="radio" name="age_limit" id="age_T16" value="T16" class="hidden peer" {{ old('age_limit', $movie->age_limit) == 'T16' ? 'checked' : '' }}>
                                <label for="age_T16" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white transition-all duration-200" title="T16 - Từ 16 tuổi">
                                    T16
                                </label>

                                <!-- T18 -->
                                <input type="radio" name="age_limit" id="age_T18" value="T18" class="hidden peer" {{ old('age_limit', $movie->age_limit) == 'T18' ? 'checked' : '' }}>
                                <label for="age_T18" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-red-600 peer-checked:bg-red-600 peer-checked:text-white transition-all duration-200" title="T18 - Từ 18 tuổi">
                                    T18
                                </label>
                            </div>
                            <p class="text-[11px] text-on-surface-variant italic mt-2 text-center leading-relaxed">
                                <strong>P:</strong> Mọi lứa tuổi • <strong>K:</strong> Dưới 13<br>
                                <strong>T13:</strong> Trên 13 • <strong>T16:</strong> Trên 16 • <strong>T18:</strong> Trên 18
                            </p>
                            @error('age_limit')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Khối 3: Thao tác xuất bản & Xóa (Publish Actions) -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-4">
                        <div class="flex items-center gap-2 pb-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                            <h3 class="font-headline-sm text-headline-sm">Xác Nhận</h3>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Kiểm tra kỹ các thông tin phim trước khi cập nhật.</p>

                        @if($errors->any())
                            <div class="p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg space-y-1">
                                <span class="font-label-md text-label-md text-error block font-semibold">Lỗi nhập liệu:</span>
                                <ul class="list-disc pl-4 text-xs font-body-md space-y-0.5">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="flex flex-col gap-3 pt-2">
                            <button type="submit" class="w-full bg-primary text-on-primary font-label-md text-label-md py-3 rounded-lg hover:bg-primary-container transition-colors flex items-center justify-center gap-2 shadow-md">
                                <span class="material-symbols-outlined" style="font-size: 20px;">save</span> Cập Nhật Phim
                            </button>
                            <a href="{{ route('admin.movies.index') }}" class="w-full bg-surface-container-high text-on-surface font-label-md text-label-md py-3 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 20px;">close</span> Hủy bỏ
                            </a>
                        </div>

                        <hr class="my-4 border-outline-variant/30">
                        <button type="submit" form="deleteForm" class="w-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors font-label-md text-label-md py-3 rounded-lg flex items-center justify-center gap-2"
                            onclick="return confirm('Xóa phim «{{ addslashes($movie->title) }}»? Có thể khôi phục sau.')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">delete</span> Xóa Phim Này
                        </button>
                    </div>

                </div>
            </div>
        </form>

        {{-- Form xóa đặt ngoài form update --}}
        <form id="deleteForm" action="{{ route('admin.movies.destroy', $movie) }}" method="POST">
            @csrf @method('DELETE')
        </form>
    </div>
</main>

<script>
    // Khởi tạo danh sách diễn viên từ dữ liệu có sẵn
    let actors = @json($movie->actors->pluck('name'));

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
        }
    }

    function toggleRemovePoster(checkbox) {
        const img = document.getElementById('posterImg');
        const placeholder = document.getElementById('placeholderDiv');
        if (checkbox.checked) {
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        } else {
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderActors();
        document.getElementById('actorNameInput').addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); confirmActor(); }
            if (e.key === 'Escape') cancelActor();
        });
    });
</script>
@endsection
