@extends('layouts.admin')

@section('title', 'Người Dùng Đã Xóa - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

            <!-- Page Header -->
            <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="text-on-surface-variant hover:text-primary transition-colors font-body-md text-body-md flex items-center gap-1">
                            <span class="material-symbols-outlined" style="font-size: 18px;">arrow_back</span>
                            Quản Lý Người Dùng
                        </a>
                        <span class="text-on-surface-variant">/</span>
                        <span class="font-body-md text-body-md text-on-surface">Đã Xóa</span>
                    </div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">Người Dùng Đã Xóa</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">Danh sách tài khoản đã bị xóa mềm. Bạn có thể khôi phục lại.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span class="font-body-md text-body-md font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <span class="font-body-md text-body-md font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">

                <!-- Search -->
                <form method="GET" action="{{ route('admin.users.trashed') }}" class="flex gap-2 flex-wrap">
                    <div class="relative w-64">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" style="font-size: 20px;">search</span>
                        <input class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                            type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc email...">
                    </div>
                    <button type="submit"
                        class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                        Tìm kiếm
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.users.trashed') }}"
                            class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                            Xóa lọc
                        </a>
                    @endif
                </form>

                @if ($trashedUsers->isEmpty())
                    <div class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">restore_from_trash</span>
                        <p class="font-headline-sm text-headline-sm text-on-surface">Không có người dùng nào đã xóa</p>
                        <p class="font-body-md text-body-md mt-1">Tất cả tài khoản đang hoạt động bình thường.</p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                    <th class="py-3.5 px-6 font-semibold" style="width:60px;">#</th>
                                    <th class="py-3.5 px-6 font-semibold">Người Dùng</th>
                                    <th class="py-3.5 px-6 font-semibold">Email</th>
                                    <th class="py-3.5 px-6 font-semibold">Ngày Xóa</th>
                                    <th class="py-3.5 px-6 font-semibold text-right">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                                @foreach ($trashedUsers as $user)
                                    <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                        <td class="py-4 px-6 text-on-surface-variant font-medium">
                                            {{ $loop->iteration + ($trashedUsers->currentPage() - 1) * $trashedUsers->perPage() }}
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant shrink-0">
                                                    @if ($user->avatar)
                                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->full_name }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-on-surface-variant font-bold">
                                                            {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="font-medium text-on-surface-variant">{{ $user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 text-on-surface-variant">{{ $user->email }}</td>
                                        <td class="py-4 px-6 text-on-surface-variant whitespace-nowrap">
                                            {{ $user->deleted_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:shadow-sm transition-all duration-200">
                                                    <span class="material-symbols-outlined" style="font-size: 15px;">restore</span>
                                                    Khôi phục
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <small class="font-body-md text-body-md text-on-surface-variant">
                            Hiển thị {{ $trashedUsers->firstItem() }}–{{ $trashedUsers->lastItem() }} / {{ $trashedUsers->total() }} người dùng
                        </small>
                        <div>{{ $trashedUsers->links() }}</div>
                    </div>
                @endif
            </div>

        </div>
    </main>
@endsection
