@extends('layouts.admin')

@section('title', 'Quản Lý Người Dùng - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
            <!-- Page Header -->
            <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">Quản Lý Người Dùng</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">Quản lý tài khoản khách hàng, nhân viên
                        và quản trị viên trong hệ thống.</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($trashedCount > 0)
                        <a href="{{ route('admin.users.trashed') }}"
                            class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-outline-variant bg-surface-container text-on-surface-variant font-label-md text-label-md hover:bg-surface-container-high transition-all duration-200">
                            <span class="material-symbols-outlined" style="font-size: 18px;">restore_from_trash</span>
                            Đã Xóa
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold">{{ $trashedCount }}</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.users.create') }}"
                        class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person_add</span>
                        Thêm Người Dùng
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div
                    class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg shadow-sm">
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

            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">
                <!-- Search & Filter -->
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2 flex-wrap">
                    <div class="relative w-64">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
                            style="font-size: 20px;">search</span>
                        <input
                            class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                            type="text" name="search" value="{{ request('search') }}"
                            placeholder="Tìm theo tên hoặc email...">
                    </div>
                    <select name="status"
                        class="px-4 pr-10 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors appearance-none min-w-[180px]"
                        style="
                        background-image:url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;20&quot; height=&quot;20&quot; fill=&quot;none&quot; stroke=&quot;%236b7280&quot; stroke-width=&quot;2&quot; viewBox=&quot;0 0 24 24&quot;><polyline points=&quot;6 9 12 15 18 9&quot;/></svg>');
                        background-repeat:no-repeat;
                        background-position:right 12px center;
                        background-size:16px;
                    ">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Đã khóa</option>
                    </select>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($roles as $role)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="peer sr-only"
                                    {{ in_array($role->id, request('roles', [])) ? 'checked' : '' }}>

                                <span
                                    class="
                                inline-flex items-center px-3 py-2 rounded-lg text-sm
                                border border-outline-variant
                                bg-surface-container
                                peer-checked:bg-primary
                                peer-checked:text-white
                                peer-checked:border-primary
                                transition-all
                            ">
                                    {{ $role->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit"
                        class="bg-primary text-on-primary font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-blue-700 hover:shadow-sm transition-all duration-200">
                        Tìm kiếm
                    </button>
                    @if (request('search') || request('status') || request()->filled('roles'))
                        <a href="{{ route('admin.users.index') }}"
                            class="bg-surface-container-high text-on-surface font-label-md text-label-md px-4 py-2 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center justify-center">
                            Xóa lọc
                        </a>
                    @endif
                </form>

                @if ($users->isEmpty())
                    <div
                        class="text-center py-16 text-on-surface-variant bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/60">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">group_off</span>
                        <p class="font-headline-sm text-headline-sm text-on-surface">Chưa có người dùng nào</p>
                        <p class="font-body-md text-body-md mt-1">Hãy thêm người dùng đầu tiên để bắt đầu quản lý hệ thống!
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-outline-variant/40">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-surface-container/60 font-label-md text-label-md text-on-surface-variant border-b border-outline-variant/60">
                                    <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:60px;">#</th>
                                    <th class="py-3.5 px-6 font-semibold">Người Dùng</th>
                                    <th class="py-3.5 px-6 font-semibold">Liên Hệ</th>
                                    <th class="py-3.5 px-6 font-semibold">Vai Trò</th>
                                    <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:120px;">Trạng Thái
                                    </th>
                                    <th class="py-3.5 px-6 font-semibold whitespace-nowrap" style="width:110px;">Ngày Tạo
                                    </th>
                                    <th class="py-3.5 px-6 font-semibold text-right" style="width:180px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody class="font-body-md text-body-md text-on-surface divide-y divide-outline-variant/40">
                                @foreach ($users as $user)
                                    <tr class="hover:bg-surface-container-low/60 transition-all duration-200">
                                        <td class="py-4 px-6 text-on-surface-variant font-medium">
                                            {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant shrink-0">
                                                    @if ($user->avatar)
                                                        <img src="{{ $user->avatar_url }}"
                                                            alt="{{ $user->full_name }}"
                                                            class="w-full h-full object-cover">
                                                    @else
                                                        <div
                                                            class="w-full h-full flex items-center justify-center text-on-surface-variant font-bold">
                                                            {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="font-medium">{{ $user->full_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="text-on-surface">{{ $user->email }}</div>
                                            @if ($user->phone)
                                                <div class="text-on-surface-variant text-xs mt-0.5">{{ $user->phone }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex flex-wrap gap-1.5">
                                                @forelse($user->roles as $role)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/60 shadow-sm">
                                                        {{ $role->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-on-surface-variant text-xs">—</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            @if ($user->status === 'active')
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Hoạt
                                                    động</span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Đã
                                                    khóa</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-on-surface-variant whitespace-nowrap">
                                            {{ $user->created_at?->format('d/m/Y') }}</td>
                                        <td class="py-4 px-6 text-right whitespace-nowrap">
                                            <div class="flex gap-2 items-center justify-end whitespace-nowrap">
                                                <a href="{{ route('admin.users.edit', [
                                                    'user' => $user,
                                                    'return' => urlencode(request()->fullUrl()),
                                                ]) }}"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                    <span class="material-symbols-outlined"
                                                        style="font-size: 15px;">edit</span> Sửa
                                                </a>
                                                <button type="button"
                                                    onclick="openDeleteModal(
                                                        '{{ route('admin.users.destroy', [
                                                            'user' => $user,
                                                            'return' => urlencode(request()->fullUrl()),
                                                        ]) }}',
                                                        '{{ addslashes($user->full_name) }}'
                                                    )"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white hover:shadow-sm transition-all duration-200 whitespace-nowrap">
                                                    <span class="material-symbols-outlined"
                                                        style="font-size: 15px;">delete</span>
                                                    Xóa
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-between items-center mt-6">
                        <small class="font-body-md text-body-md text-on-surface-variant">
                            Hiển thị {{ $users->firstItem() }}–{{ $users->lastItem() }} / {{ $users->total() }} người
                            dùng
                        </small>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </main>

    <!-- Custom Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity duration-300"></div>

        <div class="relative bg-surface-container-lowest border border-outline-variant rounded-xl shadow-ambient-lg max-w-md w-full mx-4 p-6 transform scale-95 opacity-0 transition-all duration-300 ease-out"
            id="delete-modal-content">
            <div class="flex flex-col items-center text-center space-y-4">
                <div
                    class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center border border-red-200 text-red-600">
                    <span class="material-symbols-outlined text-4xl">warning</span>
                </div>

                <div>
                    <h3 class="font-headline-sm text-headline-sm text-on-surface font-bold">Xác Nhận Xóa Người Dùng</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-2 leading-relaxed">
                        Bạn có chắc chắn muốn xóa người dùng <strong id="delete-user-name"
                            class="text-red-600 font-semibold"></strong>?
                    </p>
                    <p class="text-xs text-red-500/80 mt-2 italic bg-red-50/50 p-2 rounded border border-red-100">
                        Lưu ý: Tài khoản sẽ được xóa mềm, bạn có thể khôi phục lại ở trang "Đã Xóa".
                    </p>
                </div>

                <div class="flex gap-3 w-full mt-4">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface font-label-md text-label-md rounded-lg hover:bg-surface-container-highest transition-colors">
                        Hủy bỏ
                    </button>
                    <form id="delete-confirm-form" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-600 text-white font-label-md text-label-md rounded-lg hover:bg-red-700 shadow-sm hover:shadow-md transition-all duration-200">
                            Xác nhận xóa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(actionUrl, userName) {
            const modal = document.getElementById('delete-confirm-modal');
            const content = document.getElementById('delete-modal-content');
            const form = document.getElementById('delete-confirm-form');
            const nameSpan = document.getElementById('delete-user-name');

            form.action = actionUrl;
            nameSpan.textContent = `«${userName}»`;

            modal.classList.remove('hidden');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('delete-confirm-modal');
            const content = document.getElementById('delete-modal-content');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    </script>
@endsection
