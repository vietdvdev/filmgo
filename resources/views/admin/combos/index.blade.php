@extends('layouts.admin')

@section('title', 'Quản Lý Combo Bắp Nước - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Combo Bắp Nước</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Danh mục các gói bắp rang, nước uống và đồ ăn nhẹ phục vụ khách hàng đặt kèm khi xem phim.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.combo-items.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">category</span>
                    Quản Lý Món Thành Phần
                </a>
                <a href="{{ route('admin.combos.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                    <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                    Thêm Combo Mới
                </a>
            </div>
        </div>

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
            <!-- Search -->
            <form method="GET" action="{{ route('admin.combos.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm combo...">
                </div>
                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.combos.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($combos->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">fastfood</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có combo nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm combo bắp nước đầu tiên để khách hàng có thể chọn lựa!</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold" style="width:100px;">Hình Ảnh</th>
                                <th class="py-3.5 px-6 font-semibold">Tên Combo</th>
                                <th class="py-3.5 px-6 font-semibold">Giá Bán</th>
                                <th class="py-3.5 px-6 font-semibold">Thành Phần & Mô Tả</th>
                                <th class="py-3.5 px-6 font-semibold" style="width:120px;">Trạng Thái</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:150px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($combos as $combo)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($combos->currentPage() - 1) * $combos->perPage() }}</td>
                                    <td class="py-4 px-6">
                                        @if($combo->image)
                                            <div class="w-16 h-16 rounded-lg overflow-hidden border border-outline-variant bg-surface-container-high">
                                                <img src="{{ asset($combo->image) }}" alt="{{ $combo->combo_name }}" class="w-full h-full object-cover">
                                            </div>
                                        @else
                                            <div class="w-16 h-16 rounded-lg border border-dashed border-outline-variant/60 flex items-center justify-center bg-surface-container/40 text-on-surface-variant">
                                                <span class="material-symbols-outlined text-3xl">fastfood</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-on-surface">{{ $combo->combo_name }}</td>
                                    <td class="py-4 px-6">
                                        <span class="font-semibold text-primary">
                                            {{ number_format($combo->price) }} ₫
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-on-surface-variant max-w-sm space-y-1">
                                        @if($combo->items->isNotEmpty())
                                            <div class="flex flex-wrap gap-1.5 mb-1">
                                                @foreach($combo->items as $item)
                                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                        <span class="font-bold">{{ $item->pivot->quantity }}x</span> {{ $item->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                        <p class="text-xs text-on-surface-variant/80 truncate" title="{{ $combo->description }}">
                                            {{ $combo->description ?: 'Không có mô tả chi tiết' }}
                                        </p>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($combo->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200/50">
                                                Ngưng bán
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.combos.edit', $combo->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-blue-700 font-medium text-sm hover:underline transition-colors" title="Chỉnh sửa">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </a>
                                            <form action="{{ route('admin.combos.destroy', $combo->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa combo bắp nước này không? (Có thể khôi phục lại sau)');">
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

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $combos->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
