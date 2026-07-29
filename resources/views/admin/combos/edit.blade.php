@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Combo Bắp Nước - FilmGo')

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
                <span class="text-outline">Chỉnh Sửa</span>
            </div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Combo Bắp Nước</h2>
        </div>

        @if($errors->any())
            <div class="flex items-start gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
                <ul class="font-body-md text-body-md font-medium list-disc pl-2 space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg max-w-3xl">
            <form action="{{ route('admin.combos.update', $combo->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Tên combo -->
                <div class="space-y-2">
                    <label for="combo_name" class="block font-label-md text-label-md text-on-surface">
                        Tên Combo Bắp Nước <span class="text-error">*</span>
                    </label>
                    <input
                        type="text"
                        id="combo_name"
                        name="combo_name"
                        value="{{ old('combo_name', $combo->combo_name) }}"
                        class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('combo_name') border-error @enderror"
                        placeholder="Ví dụ: Combo Solo, Combo Couple, Big Family..."
                        maxlength="255"
                        oninput="updateCharCount(this, 'comboNameCount', 255)"
                        required
                    >
                    <div class="flex justify-between items-center text-xs text-on-surface-variant">
                        <span>Nhập tên ngắn gọn, dễ nhớ</span>
                        <div><span id="comboNameCount">{{ strlen(old('combo_name', $combo->combo_name)) }}</span>/255</div>
                    </div>
                    @error('combo_name')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Thành phần chi tiết -->
                @php
                    $attachedItems = $combo->items->keyBy('id');
                @endphp
                <div class="space-y-3 p-4 bg-surface-container-low/60 rounded-xl border border-outline-variant/50">
                    <div class="flex justify-between items-center">
                        <div>
                            <label class="block font-label-md text-label-md text-on-surface font-semibold flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary" style="font-size: 20px;">restaurant</span>
                                Thành Phần Chi Tiết Trong Combo
                            </label>
                            <p class="text-xs text-on-surface-variant mt-0.5">Chọn các thành phần (Bắp lớn, Bắp nhỏ, Nước lớn, Nước nhỏ...) và số lượng.</p>
                        </div>
                        <a href="{{ route('admin.combo-items.index') }}" target="_blank" class="text-xs text-primary hover:underline flex items-center gap-1 font-medium">
                            <span class="material-symbols-outlined" style="font-size: 14px;">open_in_new</span> Quản lý món thành phần
                        </a>
                    </div>

                    @error('items')
                        <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700 font-semibold">
                            <span class="material-symbols-outlined text-sm">error</span> {{ $message }}
                        </div>
                    @enderror

                    @if(isset($comboItems) && $comboItems->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            @foreach($comboItems as $item)
                                @php
                                    $isAttached = $attachedItems->has($item->id);
                                    $qty = old('items.' . $item->id, $isAttached ? $attachedItems[$item->id]->pivot->quantity : '');
                                @endphp
                                <div class="flex items-center justify-between p-3 bg-surface-container-lowest rounded-lg border border-outline-variant/40 hover:border-primary/50 transition-colors">
                                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                        <input
                                            type="checkbox"
                                            id="item_chk_{{ $item->id }}"
                                            class="w-4 h-4 text-primary rounded border-outline-variant focus:ring-primary flex-shrink-0"
                                            {{ ($isAttached || old('items.' . $item->id)) ? 'checked' : '' }}
                                            onchange="toggleItemQty({{ $item->id }}, {{ $item->price }})"
                                        >
                                        <label for="item_chk_{{ $item->id }}" class="cursor-pointer select-none flex-1 min-w-0">
                                            <div class="flex items-center gap-1 text-sm font-medium text-on-surface">
                                                @if($item->type === 'popcorn')
                                                    <span class="material-symbols-outlined text-amber-500 text-base flex-shrink-0">popcorn</span>
                                                @elseif($item->type === 'drink')
                                                    <span class="material-symbols-outlined text-blue-500 text-base flex-shrink-0">local_drink</span>
                                                @elseif($item->type === 'snack')
                                                    <span class="material-symbols-outlined text-orange-500 text-base flex-shrink-0">cookie</span>
                                                @else
                                                    <span class="material-symbols-outlined text-gray-500 text-base flex-shrink-0">restaurant</span>
                                                @endif
                                                <span class="truncate">{{ $item->name }}</span>
                                            </div>
                                            <div class="text-xs text-primary font-semibold mt-0.5 ml-5">{{ number_format($item->price) }} ₫ / {{ $item->unit }}</div>
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                        <span class="text-xs text-on-surface-variant">SL:</span>
                                        <input
                                            type="number"
                                            id="item_qty_{{ $item->id }}"
                                            name="items[{{ $item->id }}]"
                                            value="{{ $qty }}"
                                            {{ ($isAttached || old('items.' . $item->id)) ? '' : 'disabled' }}
                                            min="1"
                                            max="99"
                                            placeholder="0"
                                            data-price="{{ $item->price }}"
                                            class="w-16 px-2 py-1 bg-surface-container border border-outline-variant rounded text-center text-sm font-semibold text-on-surface focus:outline-none focus:border-primary disabled:opacity-40 disabled:bg-surface-container/50"
                                            oninput="recalculateTotal()"
                                        >
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Tổng giá gốc thành phần -->
                        <div class="flex items-center justify-between pt-2 px-1">
                            <div class="text-xs text-on-surface-variant font-medium flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">calculate</span>
                                Tổng giá gốc các thành phần đã chọn:
                            </div>
                            <div class="text-sm font-bold text-on-surface">
                                <span id="totalBasePrice">0</span> ₫
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" onclick="autoFillPrice()" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 rounded-md border border-emerald-200 transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 16px;">price_check</span> Điền giá gốc vào Giá bán thực tế
                            </button>
                            <button type="button" onclick="autoGenerateDescription()" class="text-xs font-semibold text-primary hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md border border-blue-200 transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 16px;">auto_fix_high</span> Tự động tạo Mô tả
                            </button>
                        </div>
                    @else
                        <div class="text-center py-4 text-xs text-on-surface-variant bg-surface-container/30 rounded-lg">
                            Chưa có danh mục món thành phần. <a href="{{ route('admin.combo-items.index') }}" class="text-primary underline">Bấm vào đây để tạo (Bắp lớn, Nước lớn...)</a>
                        </div>
                    @endif
                </div>

                <!-- Giá bán thực tế (bán cho khách) -->
                <div class="space-y-2 p-4 bg-amber-50/60 rounded-xl border border-amber-200/60">
                    <label for="price" class="block font-label-md text-label-md text-on-surface font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-600" style="font-size: 20px;">sell</span>
                        Giá Bán Thực Tế (VNĐ) <span class="text-error">*</span>
                    </label>
                    <p class="text-xs text-on-surface-variant -mt-1">
                        Đây là giá hiển thị và tính tiền khi khách hàng chọn combo. Có thể thấp hơn tổng giá gốc (giá ưu đãi).
                    </p>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="{{ old('price', $combo->price) }}"
                        class="w-full px-4 py-2.5 bg-white border border-amber-300 rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-400 transition-colors text-lg font-bold @error('price') border-error @enderror"
                        placeholder="0"
                        min="0"
                        required
                    >
                    @error('price')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mô tả -->
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
                    >{{ old('description', $combo->description) }}</textarea>
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
                        <div class="flex-1 space-y-3">
                            <input
                                type="file"
                                id="image"
                                name="image"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="w-full px-3 py-1.5 border border-outline-variant rounded-lg font-body-md text-sm text-on-surface-variant file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-surface-container-high file:text-on-surface hover:file:bg-surface-container-highest transition-colors"
                                onchange="previewFile(this)"
                            >
                            <p class="text-xs text-on-surface-variant">Hỗ trợ định dạng JPG, JPEG, PNG, WEBP. Dung lượng tối đa 2MB.</p>
                            @if($combo->image)
                                <div class="flex items-center gap-2 pt-1">
                                    <input
                                        type="checkbox"
                                        id="remove_image"
                                        name="remove_image"
                                        value="1"
                                        class="rounded border-outline-variant text-primary focus:ring-primary cursor-pointer w-4 h-4"
                                        onchange="toggleRemoveImage(this)"
                                    >
                                    <label for="remove_image" class="text-xs font-semibold text-error hover:text-red-700 cursor-pointer select-none">
                                        Xóa hình ảnh hiện tại
                                    </label>
                                </div>
                            @endif
                            @error('image')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Preview Box -->
                        <div class="relative w-28 h-28 border border-outline-variant rounded-lg overflow-hidden bg-surface-container/40 flex items-center justify-center flex-shrink-0">
                            @if($combo->image)
                                <img id="imagePreview" src="{{ asset($combo->image) }}" alt="Xem trước ảnh" class="w-full h-full object-cover">
                                <div id="imagePlaceholder" class="flex flex-col items-center justify-center text-on-surface-variant text-center p-2 hidden">
                                    <span class="material-symbols-outlined text-3xl">image</span>
                                    <span class="text-[10px] mt-1 font-medium">Chưa có ảnh</span>
                                </div>
                            @else
                                <img id="imagePreview" src="#" alt="Xem trước ảnh" class="w-full h-full object-cover hidden">
                                <div id="imagePlaceholder" class="flex flex-col items-center justify-center text-on-surface-variant text-center p-2">
                                    <span class="material-symbols-outlined text-3xl">image</span>
                                    <span class="text-[10px] mt-1 font-medium">Chưa có ảnh</span>
                                </div>
                            @endif
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
                        <option value="active" {{ old('status', $combo->status) === 'active' ? 'selected' : '' }}>Hoạt động (Bán hàng)</option>
                        <option value="inactive" {{ old('status', $combo->status) === 'inactive' ? 'selected' : '' }}>Ngưng bán (Ẩn khỏi trang đặt vé)</option>
                    </select>
                    @error('status')
                        <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                    <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Lưu Thay Đổi
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
    const originalImageSrc = "{{ $combo->image ? asset($combo->image) : '#' }}";

    // Đơn giá của từng món (dùng trong JS tính toán)
    const ITEM_PRICES = {
        @if(isset($comboItems))
            @foreach($comboItems as $item)
                {{ $item->id }}: {{ $item->price }},
            @endforeach
        @endif
    };

    function updateCharCount(input, countId, max) {
        document.getElementById(countId).textContent = input.value.length;
    }

    function toggleItemQty(itemId, itemPrice) {
        const chk = document.getElementById('item_chk_' + itemId);
        const qtyInput = document.getElementById('item_qty_' + itemId);
        if (chk.checked) {
            qtyInput.disabled = false;
            if (!qtyInput.value || parseInt(qtyInput.value) <= 0) {
                qtyInput.value = 1;
            }
        } else {
            qtyInput.disabled = true;
            qtyInput.value = '';
        }
        recalculateTotal();
        autoGenerateDescription();
    }

    function recalculateTotal() {
        let total = 0;
        for (const [itemId, unitPrice] of Object.entries(ITEM_PRICES)) {
            const chk = document.getElementById('item_chk_' + itemId);
            const qty = document.getElementById('item_qty_' + itemId);
            if (chk && chk.checked && qty && parseInt(qty.value) > 0) {
                total += unitPrice * parseInt(qty.value);
            }
        }
        document.getElementById('totalBasePrice').textContent = total.toLocaleString('vi-VN');
        return total;
    }

    function autoFillPrice() {
        const total = recalculateTotal();
        document.getElementById('price').value = total;
    }

    function autoGenerateDescription() {
        const parts = [];
        @if(isset($comboItems))
            @foreach($comboItems as $item)
                (function() {
                    const chk = document.getElementById('item_chk_{{ $item->id }}');
                    const qty = document.getElementById('item_qty_{{ $item->id }}');
                    if (chk && chk.checked && qty && qty.value > 0) {
                        parts.push(qty.value + ' {{ $item->name }}');
                    }
                })();
            @endforeach
        @endif
        if (parts.length > 0) {
            document.getElementById('description').value = parts.join(' + ');
        }
    }

    function previewFile(input) {
        const file = input.files[0];
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const removeCheckbox = document.getElementById('remove_image');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(file);
            if (removeCheckbox) removeCheckbox.checked = false;
        } else {
            resetPreview();
        }
    }

    function toggleRemoveImage(checkbox) {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const fileInput = document.getElementById('image');
        if (checkbox.checked) {
            preview.src = "#";
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            fileInput.value = "";
        } else {
            resetPreview();
        }
    }

    function resetPreview() {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const removeCheckbox = document.getElementById('remove_image');
        if (originalImageSrc !== '#') {
            preview.src = originalImageSrc;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.src = "#";
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
        if (removeCheckbox) removeCheckbox.checked = false;
    }

    // Tính tổng giá gốc ngay khi tải trang (đã có dữ liệu cũ)
    window.addEventListener('DOMContentLoaded', function() {
        recalculateTotal();
    });
</script>
@endsection
