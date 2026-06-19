@extends('layouts.admin')

@section('title', 'Sửa Phân Công Rạp - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

            {{-- Header --}}
            <div class="pb-2 border-b border-outline-variant/20">
                <h2 class="font-headline-lg text-headline-lg text-on-surface">
                    Sửa Phân Công Rạp
                </h2>

                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Cập nhật thông tin phân công nhân viên hoặc quản lý cho rạp chiếu phim.
                </p>
            </div>

            {{-- Alert --}}
            @if (session('error'))
                <div class="flex items-center gap-3 p-4 bg-red-50 text-red-700 border border-red-200 rounded-lg shadow-sm">
                    <span class="material-symbols-outlined">
                        error
                    </span>

                    {{ session('error') }}
                </div>
            @endif

            {{-- Form --}}
            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm overflow-hidden">

                <form action="{{ route('admin.user-cinemas.update', $assignment->id) }}" method="POST" class="p-8 space-y-6">

                    @csrf
                    @method('PUT')

                    {{-- Người dùng --}}
<div>
    <label class="block mb-2 font-medium text-on-surface">
        Quản lý (Active)
    </label>

    <select name="user_id"
        class="w-full px-4 py-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">

        <option value="">
            -- Chọn quản lý --
        </option>

        @foreach ($users as $user)
            @php
                // Điều kiện 1: Phải có role 'manager' và trạng thái 'active'
                $isManagerActive = $user->roles->first() && $user->roles->first()->name === 'manager' && $user->status === 'active';
                
                // Điều kiện 2: Hoặc đây chính là người đang được phân công trong bản ghi này (để tránh lỗi hiển thị khi sửa)
                $isCurrentAssigned = old('user_id', $assignment->user_id) == $user->id;
            @endphp

            @if ($isManagerActive || $isCurrentAssigned)
                <option value="{{ $user->id }}"
                    {{ $isCurrentAssigned ? 'selected' : '' }}>

                    {{ $user->full_name }}

                    @if ($user->roles->first())
                        ({{ $user->roles->first()->name }})
                    @endif
                    
                    @if ($user->status !== 'active')
                        - [Tạm khóa]
                    @endif

                </option>
            @endif
        @endforeach

    </select>

    @error('user_id')
        <p class="mt-2 text-sm text-red-600">
            {{ $message }}
        </p>
    @enderror
</div>

                    {{-- Rạp --}}
                    <div>
                        <label class="block mb-2 font-medium text-on-surface">
                            Rạp chiếu phim
                        </label>

                        <select name="cinema_id"
                            class="w-full px-4 py-3 rounded-lg border border-outline-variant bg-surface-container-lowest focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">

                            <option value="">
                                -- Chọn rạp --
                            </option>

                            @foreach ($cinemas as $cinema)
                                @php
                                    $disabled =
                                        in_array($cinema->id, $managedCinemaIds) &&
                                        $cinema->id != $assignment->cinema_id;
                                @endphp

                                <option value="{{ $cinema->id }}"
                                    {{ old('cinema_id', $assignment->cinema_id) == $cinema->id ? 'selected' : '' }}
                                    {{ $disabled ? 'disabled' : '' }}
                                    style="{{ $disabled ? 'color:#999;background:#f5f5f5;' : '' }}">

                                    {{ $cinema->name }}

                                </option>
                            @endforeach

                        </select>

                        @error('cinema_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Action --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant/20">

                        <a href="{{ route('admin.user-cinemas.index') }}"
                            class="px-5 py-2.5 rounded-lg border border-outline-variant bg-surface-container-low text-on-surface hover:bg-surface-container transition-all">

                            Quay lại

                        </a>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-primary text-on-primary hover:bg-blue-700 hover:shadow-md transition-all">

                            Cập nhật phân công

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </main>
@endsection
