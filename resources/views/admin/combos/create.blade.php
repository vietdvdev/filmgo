@extends('layouts.admin')

@section('title', 'Thêm Combo Bắp Nước Mới - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="space-y-2">
            <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                <a href="{{ route('admin.combos.index') }}" class="hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined" style="font-size: 16px;">fastfood</span> Quản Lý Combo Bắp Nước
                </a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span class="text-outline">Thêm Mới</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Thêm Combo Bắp Nước Mới</h2>
        </div>

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-2xl">
            <form action="{{ route('admin.combos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Tên combo -->
                <div class="space-y-2">
                    <label for="combo_name" class="block font-label-md text-label-md text-on-surface">
                        Tên Combo Bắp Nước <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="combo_name"
                        name="combo_name"
                        value="{{ old('combo_name') }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('combo_name') border-error @enderror"
                        placeholder="Ví dụ: Combo Solo, Combo Couple, Big Family..."
                        maxlength="255"
                        oninput="updateCharCount(this, 'comboNameCount', 255)"
                        required
                    >
                    <div class="flex justify-between items-center text-xs text-on-surface-variant">
                        <span>Nhập tên ngắn gọn, dễ nhớ</span>
                        <div><span id="comboNameCount">{{ strlen(old('combo_name', '')) }}</span>/255</div>
                    </div>
                    @error('combo_name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Gợi ý nhanh -->
                    <div class="pt-2">
                        <small class="text-on-surface-variant font-label-sm text-xs">Gợi ý nhanh:</small>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @foreach([
                                'Combo Solo (1 Bắp + 1 Nước)',
                                'Combo Couple (1 Bắp + 2 Nước)',
                                'Combo Gia Đình (2 Bắp + 3 Nước)',
                                'Combo Thử Thách (1 Bắp Lớn + 1 Nước Lớn)',
                                'Combo Snack (1 Bắp + 1 Nước + 1 Snack)'
                            ] as $tag)
                                <span class="cursor-pointer bg-surface-container hover:bg-primary hover:text-on-primary transition-colors duration-150 px-3 py-1 rounded-full text-xs font-medium text-on-surface-variant border border-outline-variant/30" 
                                      onclick="document.getElementById('combo_name').value='{{ $tag }}'; updateCharCount(document.getElementById('combo_name'), 'comboNameCount', 255)">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Giá bán -->
                <div class="space-y-2">
                    <label for="price" class="block font-label-md text-label-md text-on-surface">
                        Giá Bán (VNĐ) <span class="text-error">*</span>
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="{{ old('price', 0) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('price') border-error @enderror"
                        placeholder="0"
                        min="0"
                        required
                    >
                    <p class="text-xs text-on-surface-variant">Giá bán hiển thị cho khách hàng (ví dụ: 75000, 115000...)</p>
                    @error('price')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mô tả / Thành phần -->
                <div class="space-y-2">
                    <label for="description" class="block font-label-md text-label-md text-on-surface">
                        Mô Tả / Thành Phần Chi Tiết
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('description') border-error @enderror"
                        placeholder="Ví dụ: 1 bắp ngọt lớn 60oz + 1 nước ngọt Coca-Cola 32oz..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ảnh minh họa và Preview -->
                <div class="space-y-3">
                    <label for="image" class="block font-label-md text-label-md text-on-surface">
                        Hình Ảnh Minh Họa
                    </label>
                    <div class="flex items-start gap-4">
                        <div class="flex-1">
                            <input
                                type="file"
                                id="image"
                                name="image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full px-3 py-1.5 border border-outline-variant rounded-lg font-body-md text-sm text-on-surface-variant file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest transition-colors"
                                onchange="previewFile(this)"
                            >
                            <p class="text-xs text-on-surface-variant mt-1.5">Hỗ trợ định dạng JPG, JPEG, PNG, WEBP. Dung lượng tối đa 2MB.</p>
                            @error('image')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Preview Box -->
                        <div class="relative w-28 h-28 border border-outline-variant rounded-lg overflow-hidden bg-surface-container/40 flex items-center justify-center flex-shrink-0">
                            <img id="imagePreview" src="#" alt="Xem trước ảnh" class="w-full h-full object-cover hidden">
                            <div id="imagePlaceholder" class="flex flex-col items-center justify-center text-on-surface-variant text-center p-2">
                                <span class="material-symbols-outlined text-3xl">image</span>
                                <span class="text-[10px] mt-1 font-medium">Chưa có ảnh</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trạng thái hoạt động -->
                <div class="space-y-2">
                    <label for="status" class="block font-label-md text-label-md text-on-surface">
                        Trạng Thái Kinh Doanh <span class="text-error">*</span>
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('status') border-error @enderror"
                        required
                    >
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Hoạt động (Bán hàng)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Ngưng bán (Ẩn khỏi trang đặt vé)</option>
                    </select>
                    @error('status')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Lưu Combo
                    </button>
                    <a href="{{ route('admin.combos.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
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

    function previewFile(input) {
        const file = input.files[0];
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            preview.src = "#";
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
</script>
@endsection
