@extends('layouts.manager')

@section('title', 'Quản Lý Nhân Sự Rạp - FilmGo')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 uppercase">Nhân Sự Chi Nhánh</h2>
            <p class="text-sm text-slate-500 mt-1">Danh sách nhân viên bán vé và vận hành tại rạp.</p>
        </div>
        <button onclick="openCreateModal()" class="bg-blue-600 text-white font-semibold text-sm px-4 py-2.5 hover:bg-blue-700 transition-colors flex items-center gap-1.5 rounded-none">
            <span class="material-symbols-outlined text-sm">add</span> Thêm Nhân Viên Mới
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-semibold rounded-none">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-white border border-slate-200 shadow-sm p-4 rounded-none">
        <form method="GET" action="{{ route('manager.staff.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc email..." 
                   class="w-64 px-3 py-2 bg-slate-50 border border-slate-300 text-sm text-slate-900 rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-4 py-2 rounded-none transition-colors">
                Tìm kiếm
            </button>
            @if(request('search'))
                <a href="{{ route('manager.staff.index') }}" class="bg-slate-200 text-slate-700 hover:bg-slate-300 text-sm font-semibold px-4 py-2 rounded-none transition-colors flex items-center justify-center">
                    Xóa lọc
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white border border-slate-200 shadow-sm rounded-none overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 font-semibold text-xs text-slate-500 uppercase border-b border-slate-200">
                    <th class="py-3 px-6" style="width: 60px;">#</th>
                    <th class="py-3 px-6">Họ và Tên</th>
                    <th class="py-3 px-6">Email</th>
                    <th class="py-3 px-6">Số điện thoại</th>
                    <th class="py-3 px-6" style="width: 130px;">Trạng Thái</th>
                    <th class="py-3 px-6 text-right" style="width: 250px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
                @forelse($staffs as $staff)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-4 px-6 text-slate-500 font-medium">{{ $loop->iteration + ($staffs->currentPage() - 1) * $staffs->perPage() }}</td>
                        <td class="py-4 px-6 font-bold text-slate-900">{{ $staff->full_name }}</td>
                        <td class="py-4 px-6 text-slate-600">{{ $staff->email }}</td>
                        <td class="py-4 px-6 text-slate-600">{{ $staff->phone ?: '—' }}</td>
                        <td class="py-4 px-6">
                            @if($staff->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-emerald-100 text-emerald-800">Hoạt động</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-bold bg-red-100 text-red-800">Bị khóa</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <div class="flex gap-2 justify-end items-center">
                                <!-- Reset Password Button -->
                                <button onclick="openResetPasswordModal('{{ $staff->id }}', '{{ $staff->full_name }}')" 
                                        class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition-all rounded-none">
                                    <span class="material-symbols-outlined text-sm">lock_reset</span> Reset MK
                                </button>
                                
                                <!-- Toggle Status Form -->
                                <form action="{{ route('manager.staff.toggle', $staff->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($staff->status === 'active')
                                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition-all rounded-none">
                                            <span class="material-symbols-outlined text-sm">block</span> Khóa
                                        </button>
                                    @else
                                        <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 bg-emerald-50 text-emerald-600 border border-emerald-200 hover:bg-emerald-600 hover:text-white transition-all rounded-none">
                                            <span class="material-symbols-outlined text-sm">lock_open</span> Mở khóa
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-400 italic">Không tìm thấy nhân sự nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($staffs->hasPages())
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $staffs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Thêm Nhân Viên Mới -->
<div id="create-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <!-- Modal Content -->
    <div class="relative bg-white border border-slate-200 shadow-xl max-w-md w-full mx-4 p-6 rounded-none">
        <h3 class="text-lg font-bold text-slate-900 uppercase border-b border-slate-200 pb-3">Thêm Nhân Viên Mới</h3>
        
        <form action="{{ route('manager.staff.store') }}" method="POST" class="space-y-4 mt-4">
            @csrf
            
            <div>
                <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Họ và Tên</label>
                <input id="full_name" name="full_name" type="text" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Địa chỉ Email</label>
                <input id="email" name="email" type="email" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Số điện thoại</label>
                <input id="phone" name="phone" type="text"
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mật khẩu ban đầu</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Tối thiểu 8 ký tự">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Xác nhận mật khẩu</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                    Thêm nhân viên
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Đặt Lại Mật Khẩu -->
<div id="reset-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    <!-- Modal Content -->
    <div class="relative bg-white border border-slate-200 shadow-xl max-w-md w-full mx-4 p-6 rounded-none">
        <h3 class="text-lg font-bold text-slate-900 uppercase border-b border-slate-200 pb-3">Đặt Lại Mật Khẩu</h3>
        <p class="text-xs text-slate-500 mt-2">Đang thiết lập lại mật khẩu cho: <strong id="reset-staff-name" class="text-slate-800"></strong></p>
        
        <form id="reset-password-form" method="POST" class="space-y-4 mt-4">
            @csrf
            @method('PUT')
            
            <div>
                <label for="new_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Mật khẩu mới</label>
                <input id="new_password" name="password" type="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600" placeholder="Tối thiểu 8 ký tự">
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Xác nhận mật khẩu</label>
                <input id="new_password_confirmation" name="password_confirmation" type="password" required
                       class="mt-1 block w-full px-3 py-2 border border-slate-300 text-sm rounded-none focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 border border-slate-300 text-slate-700 text-sm font-semibold rounded-none hover:bg-slate-50 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-none hover:bg-blue-700 transition-colors">
                    Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }
    
    function openResetPasswordModal(staffId, staffName) {
        document.getElementById('reset-staff-name').textContent = staffName;
        document.getElementById('reset-password-form').action = `/manager/staff/${staffId}/reset-password`;
        document.getElementById('reset-modal').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        document.getElementById('reset-modal').classList.add('hidden');
    }
</script>
@endsection
