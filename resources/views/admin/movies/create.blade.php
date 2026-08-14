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
                <!-- Cột trái: Thông tin mô tả & Media (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Khối 1: Thông tin mô tả chính (General Info) -->
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

                        <!-- Mô tả -->
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Mô Tả Nội Dung</label>
                            <textarea name="description" rows="5" class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                                placeholder="Tóm tắt nội dung cốt truyện, thông điệp chính của bộ phim...">{{ old('description') }}</textarea>
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
                                <div class="w-36 h-52 border border-outline-variant border-dashed rounded-lg overflow-hidden relative shadow-sm flex flex-col items-center justify-center text-on-surface-variant gap-2 bg-surface-container-low" id="placeholderDiv">
                                    <span class="material-symbols-outlined" style="font-size: 36px;">image</span>
                                    <span class="text-xs font-label-sm">Xem trước</span>
                                </div>
                                <img id="posterImg" class="w-36 h-52 object-cover rounded-lg border border-outline-variant shadow-sm hidden" src="" alt="Poster">
                            </div>
                            <!-- Input Poster và URL Trailer ở bên phải -->
                            <div class="flex-grow w-full space-y-5">
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
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-on-surface-variant text-lg">play_circle</span>
                                        <input type="url" name="trailer_url" value="{{ old('trailer_url') }}"
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
                                            {{ in_array($genre->id, old('genres', [])) ? 'checked' : '' }}>
                                        <span>{{ $genre->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('genres')<p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <hr class="border-outline-variant/20">

                        <!-- Diễn viên -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">group</span>
                                <h3 class="font-headline-sm text-headline-sm">Diễn Viên (Cast)</h3>
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
                                <input type="radio" name="status" id="status_upcoming" value="upcoming" class="hidden peer" {{ old('status', 'upcoming') == 'upcoming' ? 'checked' : '' }}>
                                <label for="status_upcoming" class="cursor-pointer flex items-center gap-3 px-4 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low peer-checked:border-amber-500 peer-checked:bg-amber-500/10 peer-checked:text-amber-700 transition-all duration-200">
                                    <span class="material-symbols-outlined text-xl">schedule</span>
                                    <span class="text-body-md font-semibold">Sắp chiếu</span>
                                </label>

                                <!-- Đang chiếu -->
                                <input type="radio" name="status" id="status_showing" value="showing" class="hidden peer" {{ old('status') == 'showing' ? 'checked' : '' }}>
                                <label for="status_showing" class="cursor-pointer flex items-center gap-3 px-4 py-3 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-container-low peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-700 transition-all duration-200">
                                    <span class="material-symbols-outlined text-xl">play_circle</span>
                                    <span class="text-body-md font-semibold">Đang chiếu</span>
                                </label>

                                <!-- Ngừng chiếu -->
                                <input type="radio" name="status" id="status_stopped" value="stopped" class="hidden peer" {{ old('status') == 'stopped' ? 'checked' : '' }}>
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
                                <input type="number" name="duration" value="{{ old('duration') }}"
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
                                <input type="date" name="release_date" value="{{ old('release_date') }}"
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
                                <input type="radio" name="age_limit" id="age_P" value="P" class="hidden peer" {{ old('age_limit', 'P') == 'P' ? 'checked' : '' }}>
                                <label for="age_P" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-emerald-600 peer-checked:bg-emerald-600 peer-checked:text-white transition-all duration-200" title="P - Mọi lứa tuổi">
                                    P
                                </label>

                                <!-- K -->
                                <input type="radio" name="age_limit" id="age_K" value="K" class="hidden peer" {{ old('age_limit') == 'K' ? 'checked' : '' }}>
                                <label for="age_K" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white transition-all duration-200" title="K - Dưới 13 tuổi">
                                    K
                                </label>

                                <!-- T13 -->
                                <input type="radio" name="age_limit" id="age_T13" value="T13" class="hidden peer" {{ old('age_limit') == 'T13' ? 'checked' : '' }}>
                                <label for="age_T13" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-amber-500 peer-checked:bg-amber-500 peer-checked:text-white transition-all duration-200" title="T13 - Từ 13 tuổi">
                                    T13
                                </label>

                                <!-- T16 -->
                                <input type="radio" name="age_limit" id="age_T16" value="T16" class="hidden peer" {{ old('age_limit') == 'T16' ? 'checked' : '' }}>
                                <label for="age_T16" class="cursor-pointer flex flex-col items-center justify-center py-2.5 border border-outline-variant rounded-lg font-bold text-xs text-on-surface-variant hover:bg-surface-container-low peer-checked:border-orange-500 peer-checked:bg-orange-500 peer-checked:text-white transition-all duration-200" title="T16 - Từ 16 tuổi">
                                    T16
                                </label>

                                <!-- T18 -->
                                <input type="radio" name="age_limit" id="age_T18" value="T18" class="hidden peer" {{ old('age_limit') == 'T18' ? 'checked' : '' }}>
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

                    <!-- Khối 3: Định Dạng Chiếu -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-4">
                        <div class="flex items-center gap-2 pb-3 border-b border-outline-variant/30 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">hd</span>
                            <h3 class="font-headline-sm text-headline-sm">Định Dạng Chiếu <span class="text-error">*</span></h3>
                        </div>

                        <p class="text-xs text-on-surface-variant">Chọn một hoặc nhiều định dạng chiếu cho bộ phim này.</p>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach($formats as $format)
                                @php
                                    $fmtColors = [
                                        '2D'   => 'border-blue-500 bg-blue-500/10 text-blue-700',
                                        '3D'   => 'border-purple-500 bg-purple-500/10 text-purple-700',
                                        'IMAX' => 'border-amber-500 bg-amber-500/10 text-amber-700',
                                        '4DX'  => 'border-emerald-500 bg-emerald-500/10 text-emerald-700',
                                    ];
                                    $activeClasses = $fmtColors[$format->name] ?? 'border-primary bg-primary/10 text-primary';
                                    $isChecked = in_array($format->id, old('format_ids', []));
                                @endphp
                                
                                <label class="format-label cursor-pointer flex flex-col items-center justify-center gap-1 px-3 py-3 border rounded-lg transition-all duration-200 select-none {{ $isChecked ? $activeClasses . ' font-semibold' : 'border-outline-variant text-on-surface-variant hover:bg-surface-container-low' }}"
                                       data-active-classes="{{ $activeClasses }}">
                                    <input type="checkbox" name="format_ids[]" value="{{ $format->id }}" class="hidden format-checkbox" {{ $isChecked ? 'checked' : '' }}>
                                    <span class="font-black text-lg leading-none">{{ $format->name }}</span>
                                    @if($format->surcharge_price > 0)
                                        <span class="text-[10px] font-medium opacity-70">+{{ number_format($format->surcharge_price) }}₫</span>
                                    @else
                                        <span class="text-[10px] font-medium opacity-70">Tiêu chuẩn</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('format_ids')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Khối 4: Thao tác xuất bản (Publish Actions) -->
                    <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg space-y-4">
                        <div class="flex items-center gap-2 pb-2 text-on-surface">
                            <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">task_alt</span>
                            <h3 class="font-headline-sm text-headline-sm">Xác Nhận</h3>
                        </div>
                        <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">Kiểm tra kỹ thông tin phim trước khi lưu. Trường có dấu <span class="text-error">*</span> là bắt buộc nhập.</p>

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
                                <span class="material-symbols-outlined" style="font-size: 20px;">save</span> Lưu Phim Mới
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

    // Xử lý click cho Định Dạng Chiếu (Multi Selection - checkbox)
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.format-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = this.closest('.format-label');
                const activeClasses = label.getAttribute('data-active-classes').split(' ');
                if (this.checked) {
                    label.classList.remove('border-outline-variant', 'text-on-surface-variant', 'hover:bg-surface-container-low');
                    label.classList.add(...activeClasses, 'font-semibold');
                } else {
                    label.classList.remove(...activeClasses, 'font-semibold');
                    label.classList.add('border-outline-variant', 'text-on-surface-variant', 'hover:bg-surface-container-low');
                }
            });
        });
    });
</script>
@endsection
