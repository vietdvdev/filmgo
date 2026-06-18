@extends('layouts.admin')

@section('title', 'Thêm Người Dùng Mới - FilmGo')

@section('content')
    <main class="flex-1 overflow-y-auto pt-16 bg-background">
        <div class="p-margin-page max-w-container-max mx-auto space-y-stack-lg">
            <!-- Page Header -->
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm text-on-surface-variant">
                    <a href="{{ route('admin.users.index') }}" class="hover:underline flex items-center gap-1">
                        <span class="material-symbols-outlined" style="font-size: 16px;">group</span> Người Dùng
                    </a>
                    <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                    <span class="text-outline">Thêm Mới</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Thêm Người Dùng Mới</h2>
            </div>

            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg w-full space-y-6">
                <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <!-- Họ và Tên -->
                    <div class="space-y-2">
                        <label for="full_name" class="block font-label-md text-label-md text-on-surface">
                            Họ và Tên <span class="text-error">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('full_name') border-error @enderror"
                            placeholder="Nguyễn Văn A..." maxlength="255" oninput="updateCharCount(this, 'nameCount', 255)">
                        <div class="flex justify-end text-xs text-on-surface-variant">
                            <div><span id="nameCount">{{ strlen(old('full_name', '')) }}</span>/255</div>
                        </div>
                        @error('full_name')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email & Số Điện Thoại -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="email" class="block font-label-md text-label-md text-on-surface">
                                Email <span class="text-error">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('email') border-error @enderror"
                                placeholder="ten@email.com">
                            @error('email')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="block font-label-md text-label-md text-on-surface">Số Điện
                                Thoại</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('phone') border-error @enderror"
                                placeholder="09xxxxxxxx">
                            @error('phone')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Mật Khẩu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="password" class="block font-label-md text-label-md text-on-surface">
                                Mật Khẩu <span class="text-error">*</span>
                            </label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('password') border-error @enderror"
                                placeholder="Tối thiểu 8 ký tự">
                            @error('password')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block font-label-md text-label-md text-on-surface">
                                Xác Nhận Mật Khẩu <span class="text-error">*</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                                placeholder="Nhập lại mật khẩu">
                        </div>
                    </div>

                    <!-- Ảnh Đại Diện -->
                    <div class="space-y-2">
                        <label for="avatar" class="block font-label-md text-label-md text-on-surface">Ảnh Đại Diện</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*"
                            class="w-full text-sm text-on-surface-variant file:mr-4 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-surface-container-high file:text-on-surface file:font-medium file:cursor-pointer hover:file:bg-surface-container-highest transition-colors">
                        @error('avatar')
                            <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Trạng Thái & Vai Trò -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="status" class="block font-label-md text-label-md text-on-surface">
                                Trạng Thái <span class="text-error">*</span>
                            </label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Hoạt động
                                </option>
                                <option value="locked" {{ old('status') === 'locked' ? 'selected' : '' }}>Đã khóa</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Vai Trò</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach ($roles as $role)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="roles[]" value="{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                            class="peer sr-only">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-on-surface-variant bg-surface-container border border-outline-variant/30 peer-checked:bg-primary peer-checked:text-on-primary peer-checked:border-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2 transition-colors duration-150">
                                            {{ $role->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-outline-variant/30">
                        <button type="submit"
                            class="bg-primary text-on-primary font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-primary-container transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">check</span> Lưu Người Dùng
                        </button>
                        <a href="{{ route('admin.users.index') }}"
                            class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function updateCharCount(input, countId, max) {
            document.getElementById(countId).textContent = input.value.length;
        }
    </script>
@endsection
