@extends('layouts.manager')

@section('title', 'Thêm Nhân Viên')

@section('content')
    <div class="w-full">

        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    Thêm nhân viên
                </h1>

                <p class="text-slate-500 mt-1">
                    Tạo tài khoản nhân viên mới cho hệ thống rạp phim.
                </p>
            </div>

            <a href="{{ route('manager.staff.index') }}"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                ← Quay lại
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-slate-200">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="font-semibold text-lg text-slate-700">
                    Thông tin nhân viên
                </h2>
            </div>

            <form action="{{ route('manager.staff.store') }}" method="POST">
                @csrf

                <div class="p-6">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- Họ tên --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Họ và tên <span class="text-red-500">*</span>
                            </label>

                            <input type="text" name="full_name" value="{{ old('full_name') }}"
                                placeholder="Nhập họ tên nhân viên"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('full_name')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>

                            <input type="email" name="email" value="{{ old('email') }}" placeholder="example@gmail.com"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('email')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Số điện thoại
                            </label>

                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0987654321"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('phone')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Mật khẩu <span class="text-red-500">*</span>
                            </label>

                            <input type="password" name="password"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                            @error('password')
                                <p class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Confirm --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Xác nhận mật khẩu <span class="text-red-500">*</span>
                            </label>

                            <input type="password" name="password_confirmation"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        {{-- Cinema --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                Rạp làm việc <span class="text-red-500">*</span>
                            </label>

                            <select name="cinema_id"
                                class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">
                                    -- Chọn rạp --
                                </option>

                                @foreach ($cinemas as $cinema)
                                    <option value="{{ $cinema->id }}"
                                        {{ old('cinema_id') == $cinema->id ? 'selected' : '' }}>
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

                {{-- Footer --}}
                <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 rounded-b-2xl">
                    <div class="flex justify-end gap-3">

                        <a href="{{ route('manager.staff.index') }}"
                            class="px-5 py-2.5 rounded-lg border border-slate-300 hover:bg-slate-100 transition">
                            Hủy
                        </a>

                        <button type="submit"
                            class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                            Thêm nhân viên
                        </button>

                    </div>
                </div>

            </form>

        </div>

    </div>
@endsection
