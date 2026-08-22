@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Người Dùng - FilmGo')

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
                    <span class="text-outline">Chỉnh Sửa</span>
                </div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Chỉnh Sửa Người Dùng</h2>
            </div>

            <div
                class="bg-surface-container-lowest rounded-lg border border-outline-variant shadow-ambient-sm p-stack-lg w-full space-y-6">
                <div
                    class="flex items-center gap-3 p-4 bg-primary-fixed text-on-primary-fixed rounded-lg border border-primary-fixed-dim/20">
                    <span class="material-symbols-outlined text-primary"
                        style="font-variation-settings: 'FILL' 1;">info</span>
                    <span class="font-body-md text-body-md">
                        Đang chỉnh sửa: <strong class="text-primary">{{ $user->full_name }}</strong> &nbsp;·&nbsp;
                        <span
                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-secondary-container text-on-secondary-container">
                            <span class="material-symbols-outlined" style="font-size: 14px;">group</span>
                            {{ $user->roles->count() }} vai trò
                        </span>
                    </span>
                </div>

                <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="return" value="{{ request('return') }}">

                    <!-- Ảnh Đại Diện -->
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 rounded-full bg-surface-container-highest overflow-hidden border border-outline-variant shrink-0">
                            @if ($user->avatar)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div
                                    class="w-full h-full flex items-center justify-center text-on-surface-variant font-bold text-lg">
                                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 space-y-2">
                            <label for="avatar" class="block font-label-md text-label-md text-on-surface">Ảnh Đại
                                Diện</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*"
                                class="w-full text-sm text-on-surface-variant file:mr-4 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-surface-container-high file:text-on-surface file:font-medium file:cursor-pointer hover:file:bg-surface-container-highest transition-colors">
                            @error('avatar')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Họ và Tên -->
                    <div class="space-y-2">
                        <label for="full_name" class="block font-label-md text-label-md text-on-surface">
                            Họ và Tên <span class="text-error">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name"
                            value="{{ old('full_name', $user->full_name) }}"
                            class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('full_name') border-error @enderror"
                            maxlength="255" oninput="updateCharCount(this, 'nameCount', 255)">
                        <div class="flex justify-end text-xs text-on-surface-variant">
                            <div><span id="nameCount">{{ strlen(old('full_name', $user->full_name)) }}</span>/255</div>
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
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('email') border-error @enderror">
                            @error('email')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="phone" class="block font-label-md text-label-md text-on-surface">Số Điện
                                Thoại</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('phone') border-error @enderror">
                            @error('phone')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Mật Khẩu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="password" class="block font-label-md text-label-md text-on-surface">Mật Khẩu
                                Mới</label>
                            <input type="password" id="password" name="password"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors @error('password') border-error @enderror"
                                placeholder="Để trống nếu không đổi">
                            @error('password')
                                <p class="text-error font-body-md text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block font-label-md text-label-md text-on-surface">Xác
                                Nhận Mật Khẩu</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                        </div>
                    </div>

                    <!-- Trạng Thái & Vai Trò -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="status" class="block font-label-md text-label-md text-on-surface">
                                Trạng Thái <span class="text-error">*</span>
                            </label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                    Hoạt động</option>
                                <option value="locked" {{ old('status', $user->status) === 'locked' ? 'selected' : '' }}>
                                    Đã khóa</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block font-label-md text-label-md text-on-surface">Vai Trò</label>
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach ($roles as $role)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="roles[]" value="{{ $role->id }}"
                                            {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
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
                            <span class="material-symbols-outlined" style="font-size: 18px;">save</span> Cập Nhật
                        </button>
                        <a href="{{ route('admin.users.index') }}"
                            class="bg-surface-container-high text-on-surface font-label-md text-label-md px-5 py-2.5 rounded-lg hover:bg-surface-container-highest transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px;">close</span> Hủy
                        </a>
                    </div>
                </form>

                <!-- Xóa nhanh nếu không phải tài khoản đang đăng nhập -->
                <div class="pt-6 border-t border-outline-variant/30">
                    @if (auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('Xóa người dùng «{{ $user->full_name }}»? Tài khoản sẽ được xóa mềm và có thể khôi phục lại sau.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors font-label-md text-label-md px-4 py-2.5 rounded-lg flex items-center gap-2">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span> Xóa Người
                                Dùng Này
                            </button>
                        </form>
                    @else
                        <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-amber-500" style="font-size: 16px;">lock</span>
                            <span>Không thể xóa tài khoản đang đăng nhập.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateCharCount(input, countId, max) {
            document.getElementById(countId).textContent = input.value.length;
        }
    </script>
@endsection
