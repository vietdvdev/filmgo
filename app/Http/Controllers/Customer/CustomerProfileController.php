<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('customer.profile.edit', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'avatar'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'full_name.string'   => 'Họ và tên phải là chuỗi ký tự.',
            'full_name.max'      => 'Họ và tên không được vượt quá 255 ký tự.',
            'phone.regex'        => 'Số điện thoại không đúng định dạng.',
            'phone.min'          => 'Số điện thoại phải có ít nhất 10 số.',
            'phone.max'          => 'Số điện thoại không được dài quá 15 số.',
            'avatar.image'       => 'File ảnh đại diện phải là hình ảnh.',
            'avatar.mimes'       => 'Ảnh đại diện chỉ chấp nhận định dạng jpeg, png, jpg, gif, svg, webp.',
            'avatar.max'         => 'Ảnh đại diện không được vượt quá 2MB.',
        ]);

        $avatarPath = $user->avatar;
        if ($request->hasFile('avatar')) {
            if ($user->avatar && str_starts_with($user->avatar, 'storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(
                    str_replace('storage/', '', $user->avatar)
                );
            }
            $avatarPath = 'storage/' . $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'full_name' => $request->full_name,
            'phone'     => $request->phone,
            'avatar'    => $avatarPath,
        ]);

        return back()->with('success_profile', 'Cập nhật thông tin hồ sơ thành công!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Mật khẩu hiện tại không được để trống.',
            'new_password.required'     => 'Mật khẩu mới không được để trống.',
            'new_password.min'          => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed'    => 'Xác nhận mật khẩu mới không khớp.',
            'new_password.different'    => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success_password', 'Đổi mật khẩu thành công!');
    }
}
