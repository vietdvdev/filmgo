@extends('layouts.admin')

@section('title', 'Thêm Phân Công Rạp - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">

            {{-- Header --}}
            <div class="pb-2 border-b border-outline-variant/20">
                <h2 class="font-headline-lg text-headline-lg text-on-surface">
                    Thêm Phân Công Rạp
                </h2>

                <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                    Phân công nhân viên hoặc quản lý vào rạp chiếu phim trong hệ thống.
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

                <form action="{{ route('admin.user-cinemas.store') }}" method="POST" class="p-8 space-y-6">

                    @csrf

                    {{-- User --}}
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
                                @if ($user->roles->contains('name', 'manager') && $user->status === 'active')
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name }} (manager)
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

                    {{-- Cinema --}}
                    <div>
                        <label class="block mb-2 font-medium text-on-surface">
                            Rạp chiếu phim
                        </label>

                        <select id="cinema_id" name="cinema_id"
                            class="w-full px-4 py-2 border border-outline-variant rounded-lg">

                            <option value="">
                                -- Chọn rạp --
                            </option>

                            @foreach ($cinemas as $cinema)
                                <option value="{{ $cinema->id }}"
                                    style="{{ in_array($cinema->id, $managedCinemaIds) ? 'color:#999;' : '' }}"
                                    {{ in_array($cinema->id, $managedCinemaIds) ? 'disabled' : '' }}>
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

                            Lưu phân công

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </main>
@endsection
