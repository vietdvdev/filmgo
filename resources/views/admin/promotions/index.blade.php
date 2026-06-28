@extends('layouts.admin')

@section('title', 'Quản Lý Khuyến Mãi - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Khuyến Mãi</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý danh sách các chiến dịch giảm giá, mã quà tặng và mã voucher khuyến mãi.</p>
            </div>
            <a href="{{ route('admin.promotions.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Khuyến Mãi
            </a>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
                <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.promotions.index') }}" class="flex gap-2">
                <div class="relative w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm mã code...">
                </div>
                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Tìm kiếm
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.promotions.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($promotions->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">sell</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có mã khuyến mãi nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm mã khuyến mãi đầu tiên để áp dụng cho khách hàng!</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold">Mã Code</th>
                                <th class="py-3.5 px-6 font-semibold">Loại Giảm</th>
                                <th class="py-3.5 px-6 font-semibold">Giá Trị Giảm</th>
                                <th class="py-3.5 px-6 font-semibold">Đơn Tối Thiểu</th>
                                <th class="py-3.5 px-6 font-semibold">Tổng Số Lượng</th>
                                <th class="py-3.5 px-6 font-semibold">Thời Gian Áp Dụng</th>
                                <th class="py-3.5 px-6 font-semibold">Trạng Thái</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:180px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @foreach($promotions as $promo)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($promotions->currentPage() - 1) * $promotions->perPage() }}</td>
                                    <td class="py-4 px-6 font-medium">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-black bg-indigo-50 text-indigo-700 border border-indigo-200/60 shadow-sm uppercase tracking-wide">
                                            {{ $promo->code }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-on-surface-variant">
                                        @if($promo->discount_type === 'percent')
                                            Giảm theo %
                                        @else
                                            Giảm tiền mặt
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="font-black text-on-surface">
                                            @if($promo->discount_type === 'percent')
                                                {{ $promo->discount_value }}%
                                            @else
                                                {{ number_format($promo->discount_value) }} ₫
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 font-semibold">
                                        {{ number_format($promo->min_order_amount) }} ₫
                                    </td>
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">
                                        {{ $promo->quantity !== null ? $promo->quantity . ' lượt' : 'Không giới hạn' }}
                                    </td>
                                    <td class="py-4 px-6 text-xs text-on-surface-variant">
                                        <div>BĐ: {{ $promo->start_date ? $promo->start_date->format('d/m/Y H:i') : '-' }}</div>
                                        <div class="mt-0.5">KT: {{ $promo->end_date ? $promo->end_date->format('d/m/Y H:i') : '-' }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($promo->status === 'active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                                Hoạt động
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                                Ngưng bán
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.promotions.edit', $promo->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-blue-700 font-medium text-sm hover:underline transition-colors">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </a>
                                            <form action="{{ route('admin.promotions.destroy', $promo->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa mã khuyến mãi này không?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center gap-1 text-error hover:text-red-700 font-medium text-sm hover:underline transition-colors">
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
                <div class="pt-4">
                    {{ $promotions->links() }}
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
