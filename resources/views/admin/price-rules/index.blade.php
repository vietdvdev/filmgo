@extends('layouts.admin')

@section('title', 'Quản Lý Quy Tắc Giá - FilmGo')

@section('content')
<main class="flex-1 overflow-y-auto pt-16 bg-background">
    <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
        <!-- Page Header -->
        <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Quy Tắc Giá Phụ Thu</h2>
                <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý các quy tắc điều chỉnh giá vé theo ngày giờ và tình huống đặc biệt.</p>
            </div>
            <a href="{{ route('admin.price-rules.create') }}" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
                Thêm Quy Tắc
            </a>
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
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.price-rules.index') }}" class="flex gap-2 flex-wrap">
                <div class="relative flex-1 min-w-64">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm tên quy tắc...">
                </div>
                
                <select name="day_of_week" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    <option value="">Tất cả ngày trong tuần</option>
                    <option value="0" {{ request('day_of_week') == '0' ? 'selected' : '' }}>Chủ Nhật</option>
                    <option value="1" {{ request('day_of_week') == '1' ? 'selected' : '' }}>Thứ Hai</option>
                    <option value="2" {{ request('day_of_week') == '2' ? 'selected' : '' }}>Thứ Ba</option>
                    <option value="3" {{ request('day_of_week') == '3' ? 'selected' : '' }}>Thứ Tư</option>
                    <option value="4" {{ request('day_of_week') == '4' ? 'selected' : '' }}>Thứ Năm</option>
                    <option value="5" {{ request('day_of_week') == '5' ? 'selected' : '' }}>Thứ Sáu</option>
                    <option value="6" {{ request('day_of_week') == '6' ? 'selected' : '' }}>Thứ Bảy</option>
                </select>

                <select name="status" class="px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                </select>

                <button type="submit" class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                    Lọc
                </button>
                @if(request('search') || request('day_of_week') || request('status'))
                    <a href="{{ route('admin.price-rules.index') }}" class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                        Xóa lọc
                    </a>
                @endif
            </form>

            @if($priceRules->isEmpty())
                <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">local_offer</span>
                    <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có quy tắc giá nào</p>
                    <p class="font-body-md text-body-md mt-1">Hãy thêm quy tắc giá đầu tiên để bắt đầu quản lý giá vé!</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                <th class="py-3.5 px-6 font-semibold">Tên Quy Tắc</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Ngày</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Khung Giờ</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Điều Chỉnh Giá</th>
                                <th class="py-3.5 px-6 font-semibold whitespace-nowrap">Trạng Thái</th>
                                <th class="py-3.5 px-6 font-semibold text-right" style="width:180px;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                            @php
                                $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
                            @endphp
                            @foreach($priceRules as $rule)
                                <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                    <td class="py-4 px-6 text-on-surface-variant font-medium">{{ $loop->iteration + ($priceRules->currentPage() - 1) * $priceRules->perPage() }}</td>
                                    <td class="py-4 px-6 font-medium">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-sm">
                                            {{ $rule->name }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700">
                                            {{ $days[$rule->day_of_week] }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-mono text-on-surface-variant">
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $rule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::createFromFormat('H:i:s', $rule->end_time)->format('H:i') }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($rule->adjustment_amount > 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                                +{{ number_format($rule->adjustment_amount) }} ₫
                                            </span>
                                        @elseif($rule->adjustment_amount < 0)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-orange-50 text-orange-700">
                                                {{ number_format($rule->adjustment_amount) }} ₫
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-700">
                                                0 ₫
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6">
                                        <form action="{{ route('admin.price-rules.toggle-status', $rule->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold cursor-pointer transition-all duration-200 {{ $rule->is_active ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                                                {{ $rule->is_active ? '✓ Hoạt động' : '✗ Tắt' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.price-rules.edit', $rule->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-blue-700 font-medium text-sm hover:underline transition-colors">
                                                <span class="material-symbols-outlined" style="font-size: 18px;">edit</span>
                                            </a>
                                            <form action="{{ route('admin.price-rules.destroy', $rule->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa quy tắc này không?');">
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
                <div class="mt-6">
                    {{ $priceRules->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
