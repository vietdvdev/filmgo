@extends('layouts.manager')

@section('title', 'Quản Lý Nhân Sự Rạp')

@section('content')
    <div class="max-w-7xl mx-auto my-6 px-4">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">
                    Quản Lý Nhân Sự
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Danh sách nhân viên thuộc các rạp bạn quản lý.
                </p>
            </div>

            <a href="{{ route('manager.staff.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-blue-600/10 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Thêm nhân viên
            </a>
        </div>

        @if (session('success'))
            <div
                class="flex items-center gap-3 p-4 mb-6 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-6">
            <form method="GET" action="{{ route('manager.staff.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email..."
                        class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                </div>

                <div class="w-full sm:w-56 relative">
                    <select name="cinema_id"
                        class="w-full rounded-lg border border-slate-200 px-3.5 py-2 text-sm text-slate-800 appearance-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        <option value="">Tất cả rạp</option>
                        @foreach ($cinemas as $cinema)
                            <option value="{{ $cinema->id }}" @selected(request('cinema_id') == $cinema->id)>
                                {{ $cinema->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </div>

                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition-colors">
                    Lọc dữ liệu
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse text-left">
                    <thead>
                        <tr
                            class="bg-slate-50/70 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="p-4 w-16 text-center">#</th>
                            <th class="p-4">Họ tên</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Số điện thoại</th>
                            <th class="p-4">Rạp làm việc</th>
                            <th class="p-4 w-32">Trạng thái</th>
                            <th class="p-4 w-44 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($staffs as $staff)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 text-center font-medium text-slate-400">
                                    {{ $staff->id }}
                                </td>
                                <td class="p-4 font-semibold text-slate-900">
                                    {{ $staff->full_name }}
                                </td>
                                <td class="p-4 text-slate-500">
                                    {{ $staff->email }}
                                </td>
                                <td class="p-4 font-medium text-slate-600">
                                    {{ $staff->phone ?? '---' }}
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $staff->cinemas->first()?->name ?? 'Chưa gán rạp' }}
                                </td>
                                <td class="p-4">
                                    @if ($staff->status === 'active')
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-rose-50 text-rose-700 border border-rose-100">
                                            Bị khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-end items-center gap-2">
                                        <a href="{{ route('manager.staff.edit', $staff->id) }}"
                                            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-md border border-amber-200/60 transition-colors">
                                            Sửa
                                        </a>

                                        <form action="{{ route('manager.staff.toggle', $staff->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border transition-colors {{ $staff->status == 'active' ? 'bg-slate-50 text-slate-700 hover:bg-slate-100 border-slate-200' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border-indigo-200/60' }}">
                                                {{ $staff->status == 'active' ? 'Khóa' : 'Mở khóa' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('manager.staff.destroy', $staff->id) }}" method="POST"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-md border border-rose-200/60 transition-all">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-slate-400 font-medium">
                                    <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-4V8m0 0v8m0-8H8">
                                        </path>
                                    </svg>
                                    Không tìm thấy dữ liệu nhân viên thích hợp
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($staffs->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $staffs->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
