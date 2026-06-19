@extends('layouts.manager')

@section('title', 'Cập Nhật Nhân Viên')

@section('content')

    <div class="w-full">

        ```
        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Cập Nhật Nhân Viên
                </h1>

                <p class="text-slate-500 mt-1">
                    Chỉnh sửa và quản lý thông tin nhân viên trong hệ thống.
                </p>
            </div>

            <a href="{{ route('manager.staff.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                ← Quay lại
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">

            {{-- Header Card --}}
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="font-semibold text-lg text-slate-700">
                    Thông tin nhân viên
                </h2>
            </div>

            {{-- Thông tin nhanh --}}
            <div class="px-6 py-5 bg-slate-50 border-b border-slate-200">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide">
                            Mã nhân viên
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            #{{ $staff->id }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide">
                            Email đăng nhập
                        </p>

                        <p class="font-semibold text-slate-800 mt-1 break-all">
                            {{ $staff->email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wide">
                            Ngày tạo
                        </p>

                        <p class="font-semibold text-slate-800 mt-1">
                            {{ $staff->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Form cập nhật --}}
            <form id="update-form" action="{{ route('manager.staff.update', $staff->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-6">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- Họ tên --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Họ và tên <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="full_name" value="{{ old('full_name', $staff->full_name) }}"
                                placeholder="Nhập họ và tên"
                                class="w-full rounded-lg border px-4 py-3
                        @error('full_name')
                            border-red-500
                        @else
                            border-slate-300
                        @enderror
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('full_name')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Email
                            </label>

                            <input type="email" value="{{ $staff->email }}" readonly
                                class="w-full rounded-lg border border-slate-300 bg-slate-100 px-4 py-3 text-slate-500 cursor-not-allowed">

                            <p class="text-xs text-slate-500 mt-2">
                                Email đăng nhập không thể thay đổi.
                            </p>
                        </div>

                        {{-- Số điện thoại --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Số điện thoại
                            </label>

                            <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}"
                                placeholder="Nhập số điện thoại"
                                class="w-full rounded-lg border px-4 py-3
                        @error('phone')
                            border-red-500
                        @else
                            border-slate-300
                        @enderror
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('phone')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Rạp --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Rạp làm việc <span class="text-red-500">*</span>
                            </label>

                            <select name="cinema_id"
                                class="w-full rounded-lg border px-4 py-3
                        @error('cinema_id')
                            border-red-500
                        @else
                            border-slate-300
                        @enderror
                        focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">
                                    -- Chọn rạp --
                                </option>

                                @foreach ($cinemas as $cinema)
                                    <option value="{{ $cinema->id }}"
                                        {{ old('cinema_id', $staff->cinemas->first()?->id) == $cinema->id ? 'selected' : '' }}>
                                        {{ $cinema->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('cinema_id')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>

                </div>
            </form>

            {{-- Form xóa --}}
            <form id="delete-form" action="{{ route('manager.staff.destroy', $staff->id) }}" method="POST"
                onsubmit="return confirm('Bạn có chắc chắn muốn xóa nhân viên này không?')">
                @csrf
                @method('DELETE')
            </form>

            {{-- Footer --}}
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">

                <div class="flex flex-col md:flex-row justify-between gap-3">

                    <button type="submit" form="delete-form"
                        class="px-5 py-2.5 rounded-lg border border-red-300 text-red-600 hover:bg-red-600 hover:text-white transition">
                        Xóa nhân viên
                    </button>

                    <div class="flex gap-3">

                        <a href="{{ route('manager.staff.index') }}"
                            class="px-5 py-2.5 rounded-lg border border-slate-300 hover:bg-slate-100 transition">
                            Hủy
                        </a>

                        <button type="submit" form="update-form"
                            class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                            Lưu thay đổi
                        </button>

                    </div>

                </div>

            </div>

        </div>
        ```

    </div>
@endsection
