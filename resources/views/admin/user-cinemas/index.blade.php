@extends('layouts.admin')

@section('title', 'Phân Công Rạp - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

            {{-- Header --}}
            <div class="flex justify-between items-center pb-2 border-b border-outline-variant/20">
                <div>
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">
                        Phân Công Rạp
                    </h2>

                    <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                        Quản lý nhân viên và quản lý được phân công cho các rạp phim.
                    </p>
                </div>

                <a href="{{ route('admin.user-cinemas.create') }}"
                    class="bg-primary text-on-primary font-label-md px-4 py-2.5 rounded-lg hover:bg-blue-700 hover:shadow-md transition-all duration-200 flex items-center gap-2">

                    <span class="material-symbols-outlined">
                        add
                    </span>

                    Phân Công Mới
                </a>
            </div>

            {{-- Alert --}}
            @if (session('success'))
                <div class="flex items-center gap-3 p-4 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                    <span class="material-symbols-outlined">
                        check_circle
                    </span>

                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg">
                    <span class="material-symbols-outlined">
                        error
                    </span>

                    {{ session('error') }}
                </div>
            @endif

            {{-- Content --}}
            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden p-stack-lg space-y-4">

                {{-- Filter --}}
                <form method="GET" action="{{ route('admin.user-cinemas.index') }}" class="flex flex-wrap gap-2">

                    <div class="relative w-72">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">
                            search
                        </span>

                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên hoặc email..."
                            class="w-full pl-10 pr-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest">
                    </div>

                    <select name="cinema_id"
                        class="px-4 py-2 border border-outline-variant rounded-lg bg-surface-container-lowest">

                        <option value="">
                            Tất cả rạp
                        </option>

                        @foreach ($cinemas as $cinema)
                            <option value="{{ $cinema->id }}" {{ request('cinema_id') == $cinema->id ? 'selected' : '' }}>
                                {{ $cinema->name }}
                            </option>
                        @endforeach

                    </select>

                    <button type="submit" class="bg-primary text-white px-4 py-2 rounded-lg">

                        Tìm kiếm

                    </button>

                    @if (request('search') || request('cinema_id'))
                        <a href="{{ route('admin.user-cinemas.index') }}"
                            class="px-4 py-2 rounded-lg bg-surface-container-high">

                            Xóa lọc

                        </a>
                    @endif

                </form>

                {{-- Table --}}
                @if ($assignments->count() === 0)

                    <div class="text-center py-16 border border-dashed border-outline-variant rounded-lg">

                        <span class="material-symbols-outlined text-5xl">
                            theater_comedy
                        </span>

                        <p class="mt-3">
                            Chưa có dữ liệu phân công rạp
                        </p>

                    </div>
                @else
                    <div class="overflow-x-auto rounded-lg border border-outline-variant/40">

                        <table class="w-full text-left border-collapse">

                            <thead>

                                <tr class="bg-surface-container/60 border-b border-outline-variant/60">

                                    <th class="py-3 px-6 w-16">#</th>

                                    <th class="py-3 px-6">
                                        Nhân viên
                                    </th>

                                    <th class="py-3 px-6">
                                        Email
                                    </th>

                                    <th class="py-3 px-6">
                                        Vai trò
                                    </th>

                                    <th class="py-3 px-6">
                                        Rạp
                                    </th>

                                    <th class="py-3 px-6">
                                        Ngày phân công
                                    </th>

                                    <th class="py-3 px-6 text-right">
                                        Thao tác
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-outline-variant/40">

                                @foreach ($assignments as $assignment)
                                    <tr class="hover:bg-surface-container-low/60 transition-all">

                                        <td class="py-4 px-6">

                                            {{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}

                                        </td>

                                        <td class="py-4 px-6">

                                            {{ $assignment->user->full_name }}

                                        </td>

                                        <td class="py-4 px-6">

                                            {{ $assignment->user->email }}

                                        </td>

                                        <td class="py-4 px-6">

                                            @foreach ($assignment->user->roles as $role)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">

                                                    {{ $role->name }}

                                                </span>
                                            @endforeach

                                        </td>

                                        <td class="py-4 px-6">

                                            {{ $assignment->cinema->name }}

                                        </td>

                                        <td class="py-4 px-6">

                                            {{ $assignment->created_at?->format('d/m/Y H:i') }}

                                        </td>

                                        <td class="py-4 px-6 text-right">

                                            <div class="flex justify-end items-center gap-2">

                                                <a href="{{ route('admin.user-cinemas.edit', $assignment->id) }}"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all">

                                                    <span class="material-symbols-outlined text-[15px]">
                                                        edit
                                                    </span>

                                                    Sửa

                                                </a>

                                                <form action="{{ route('admin.user-cinemas.destroy', $assignment->id) }}"
                                                    method="POST">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" onclick="return confirm('Hủy phân công này?')"
                                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all">

                                                        <span class="material-symbols-outlined text-[15px]">
                                                            delete
                                                        </span>

                                                        Hủy

                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    <div class="flex justify-between items-center mt-6">

                        <small class="text-on-surface-variant">

                            Hiển thị
                            {{ $assignments->firstItem() }}
                            -
                            {{ $assignments->lastItem() }}
                            /
                            {{ $assignments->total() }}

                            phân công

                        </small>

                        {{ $assignments->links() }}

                    </div>

                @endif

            </div>

        </div>
    </main>
@endsection
