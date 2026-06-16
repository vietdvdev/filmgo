@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Thể Loại - FilmGo')

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
                <span class="text-outline">Chỉnh Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Thể Loại</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl space-y-6">
            <!-- Info Badge -->
            <div class="flex items-center gap-3 p-4 bg-primary-fixed text-on-primary-fixed rounded-lg border border-primary-fixed-dim/20">
                <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">info</span>
                <span class="font-body-md text-body-md">
                    Đang chỉnh sửa: <strong class="text-primary">{{ $genre->name }}</strong> &nbsp;·&nbsp;
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                        <span class="material-symbols-outlined" style="font-size: 14px;">movie</span>
                        {{ $genre->movies()->count() }} phim liên kết
                    </span>
                </span>
            </div>

            <form action="{{ route('admin.genres.update', $genre) }}" method="POST" class="space-y-6">
                @csrf @method('PUT')

                <!-- Tên thể loại -->
                <div class="space-y-2">
                    <label for="name" class="block font-label-md text-label-md text-on-surface">
                        Tên Thể Loại <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $genre->name) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('name') border-error @enderror"
                        placeholder="Tên thể loại..."
                        maxlength="100"
                        oninput="updateCharCount(this, 'nameCount', 100)"
                    >
                    <div class="flex justify-end text-xs text-on-surface-variant">
                        <div><span id="nameCount">{{ strlen(old('name', $genre->name)) }}</span>/100</div>
                    </div>
                    @error('name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
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
                    >{{ old('description', $genre->description) }}</textarea>
                    @error('description')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span> Cập Nhật
                    </button>
                    <a href="{{ route('admin.genres.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                    </a>
                </div>
            </form>

            <!-- Xóa nhanh nếu không có phim liên kết -->
            <div class="pt-6 border-t border-outline-variant/30">
                @if($genre->movies()->count() === 0)
                    <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST"
                          onsubmit="return confirm('Xóa thể loại «{{ $genre->name }}»? Hành động này không thể hoàn tác!')">
                        @csrf @method('DELETE')
                        <button type="submit" class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors font-label-md text-label-md px-4 py-2.5 rounded-lg flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">trash</span> Xóa Thể Loại Này
                        </button>
                    </form>
                @else
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                        <span class="material-symbols-outlined text-amber-500" style="font-size: 16px;">lock</span>
                        <span>Không thể xóa thể loại đang có <strong>{{ $genre->movies()->count() }} phim</strong> liên kết.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<script>
    function updateCharCount(input, countId, max) {
        document.getElementById(countId).textContent = input.value.length;
    }
</script>
@endsection
