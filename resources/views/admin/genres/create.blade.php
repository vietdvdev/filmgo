@extends('layouts.admin')

@section('title', 'Thêm Thể Loại Mới - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.genres.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">category</span> Quản Lý Thể Loại
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Thêm Mới</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Thêm Thể Loại Mới</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl">
            <form action="{{ route('admin.genres.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Tên thể loại -->
                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">
                        Tên Thể Loại <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror"
                        placeholder="Ví dụ: Hành động, Kinh dị, Tình cảm..."
                        maxlength="100"
                        oninput="updateCharCount(this, 'nameCount', 100)"
                    >
                    <div class="flex justify-between items-center text-xs text-on-surface-variant">
                        <span>Hãy chọn gợi ý nhanh bên dưới nếu cần</span>
                        <div><span id="nameCount">{{ strlen(old('name', '')) }}</span>/100</div>
                    </div>
                    @error('name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Gợi ý nhanh -->
                    <div class="pt-2">
                        <small class="text-on-surface-variant font-label-sm text-xs">Gợi ý nhanh:</small>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach(['Hành động','Tình cảm','Kinh dị','Hoạt hình','Viễn tưởng','Hài hước','Tâm lý','Phiêu lưu','Tội phạm','Lịch sử'] as $tag)
                                <span class="cursor-pointer bg-surface-container hover:bg-primary hover:text-on-primary transition-colors duration-150 px-3 py-1 rounded-full text-xs font-medium text-on-surface-variant border border-outline-variant/30" 
                                      onclick="document.getElementById('name').value='{{ $tag }}'; updateCharCount(document.getElementById('name'), 'nameCount', 100)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Mô tả -->
                <div class="space-y-2">
                    <label for="description" class="block font-label-md text-label-md text-on-surface">Mô Tả</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('description') border-error @enderror"
                        placeholder="Mô tả ngắn về thể loại phim này..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Lưu Thể Loại
                    </button>
                    <a href="{{ route('admin.genres.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    function updateCharCount(input, countId, max) {
        document.getElementById(countId).textContent = input.value.length;
    }
</script>
@endsection
