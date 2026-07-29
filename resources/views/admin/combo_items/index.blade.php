@extends('layouts.admin')

@section('title', 'Danh Mục Món Thành Phần Bắp Nước - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                    <a href="{{ route('admin.combos.index') }}" class="hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 16px;">fastfood</span> Quản Lý Combo Bắp Nước
                    </a>
                    <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                    <span class="text-outline">Món Thành Phần</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Danh Mục Món Thành Phần (Bắp Lớn, Bắp Nhỏ, Nước...)</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Các món bắp rang, nước uống đơn lẻ được dùng để ghép thành các Combo bán hàng.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.combos.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                    Quản Lý Combo
                </a>
                <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200 flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                    Thêm Món Thành Phần
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-red-600">error</span>
                <div class="font-body-md text-body-md font-medium">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.combo-items.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm món thành phần...">
                </div>
                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.combo-items.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($items->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">category</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có món thành phần nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm món như Bắp lớn, Bắp nhỏ, Nước lớn, Nước nhỏ...</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold">Tên Thành Phần</th>
                                <th class="py-3.5 px-6 font-semibold">Loại</th>
                                <th class="py-3.5 px-6 font-semibold">Đơn Vị Tính</th>
                                <th class="py-3.5 px-6 font-semibold" style="width:130px;">Đơn Giá (VNĐ)</th>
                                <th class="py-3.5 px-6 font-semibold" style="width:140px;">Trạng Thái</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:160px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($items as $item)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                                    <td class="py-4 px-6 font-semibold text-on-surface flex items-center gap-2">
                                        @if($item->type === 'popcorn')
                                            <span class="material-symbols-outlined text-amber-500">popcorn</span>
                                        @elseif($item->type === 'drink')
                                            <span class="material-symbols-outlined text-blue-500">local_drink</span>
                                        @elseif($item->type === 'snack')
                                            <span class="material-symbols-outlined text-orange-500">cookie</span>
                                        @else
                                            <span class="material-symbols-outlined text-gray-500">restaurant</span>
                                        @endif
                                        {{ $item->name }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="capitalize text-xs font-semibold px-2.5 py-1 rounded-md bg-surface-container-high border border-outline-variant/40">
                                            @if($item->type === 'popcorn') Bắp rang
                                            @elseif($item->type === 'drink') Nước uống
                                            @elseif($item->type === 'snack') Đồ ăn nhẹ
                                            @else Khác
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $item->unit }}</td>
                                    <td class="py-4 px-6 font-semibold text-primary">{{ number_format($item->price) }} ₫</td>
                                    <td class="py-4 px-6">
                                        <form action="{{ route('admin.combo-items.toggle-status', $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold cursor-pointer transition-colors {{ $item->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/50 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border border-red-200/50 hover:bg-red-100' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $item->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                                {{ $item->status === 'active' ? 'Hoạt động' : 'Ngưng dùng' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button onclick="openEditModal({{ json_encode($item) }})" class="inline-flex items-center gap-1 text-primary hover:text-blue-700 font-medium text-sm hover:underline transition-colors" title="Chỉnh sửa">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </button>
                                            <form action="{{ route('admin.combo-items.destroy', $item->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa thành phần này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 text-error hover:text-red-700 font-medium text-sm hover:underline transition-colors" title="Xóa">
                                                    <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $items->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</main>

<!-- Modal Thêm Mới -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-xl max-w-md w-full p-6 space-y-4 shadow-xl border border-outline-variant">
        <div class="flex justify-between items-center border-b border-outline-variant/30 pb-3">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Thêm Món Thành Phần Mới</h3>
            <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.combo-items.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Tên Thành Phần <span class="text-error">*</span></label>
                <input type="text" name="name" placeholder="Ví dụ: Bắp lớn, Bắp nhỏ, Nước lớn..." required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-label-md text-sm text-on-surface mb-1">Loại Món</label>
                    <select name="type" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="popcorn">Bắp rang</option>
                        <option value="drink">Nước uống</option>
                        <option value="snack">Đồ ăn nhẹ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-md text-sm text-on-surface mb-1">Đơn Vị Tính</label>
                    <input type="text" name="unit" value="Hộp" placeholder="Hộp, Ly, Gói..." required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
            </div>
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Đơn Giá Niêm Yết (VNĐ) <span class="text-error">*</span></label>
                <input type="number" name="price" value="0" min="0" required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                <p class="text-xs text-on-surface-variant mt-1">Đơn giá gốc của 1 món (VD: 50000 = 50.000 ₫)</p>
            </div>
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Trạng Thái</label>
                <select name="status" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Ngưng dùng</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant/30">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="px-4 py-2 bg-surface-container-high rounded-lg text-sm font-medium">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-blue-700">Lưu Thành Phần</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Chỉnh Sửa -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/50 hidden flex items-center justify-center p-4">
    <div class="bg-surface-container-lowest rounded-xl max-w-md w-full p-6 space-y-4 shadow-xl border border-outline-variant">
        <div class="flex justify-between items-center border-b border-outline-variant/30 pb-3">
            <h3 class="font-headline-sm text-headline-sm text-on-surface">Chỉnh Sửa Thành Phần</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')" class="text-on-surface-variant hover:text-on-surface">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Tên Thành Phần <span class="text-error">*</span></label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-label-md text-sm text-on-surface mb-1">Loại Món</label>
                    <select id="edit_type" name="type" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                        <option value="popcorn">Bắp rang</option>
                        <option value="drink">Nước uống</option>
                        <option value="snack">Đồ ăn nhẹ</option>
                        <option value="other">Khác</option>
                    </select>
                </div>
                <div>
                    <label class="block font-label-md text-sm text-on-surface mb-1">Đơn Vị Tính</label>
                    <input type="text" id="edit_unit" name="unit" required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                </div>
            </div>
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Đơn Giá Niêm Yết (VNĐ) <span class="text-error">*</span></label>
                <input type="number" id="edit_price" name="price" min="0" required class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                <p class="text-xs text-on-surface-variant mt-1">Đơn giá gốc của 1 món</p>
            </div>
            <div>
                <label class="block font-label-md text-sm text-on-surface mb-1">Trạng Thái</label>
                <select id="edit_status" name="status" class="w-full px-3 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg text-sm focus:outline-none focus:border-primary">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Ngưng dùng</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-outline-variant/30">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 bg-surface-container-high rounded-lg text-sm font-medium">Hủy</button>
                <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg text-sm font-medium hover:bg-blue-700">Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(item) {
        const form = document.getElementById('editForm');
        form.action = '/admin/combo-items/' + item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_type').value = item.type;
        document.getElementById('edit_unit').value = item.unit;
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_status').value = item.status;
        document.getElementById('editModal').classList.remove('hidden');
    }
</script>
@endsection
