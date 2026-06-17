<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerResetPasswordController extends Controller
{
    public function showResetForm($token, Request $request)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required'    => 'Token khôi phục không hợp lệ.',
            'email.required'    => 'Email không được để trống.',
            'email.email'       => 'Email không đúng định dạng.',
            'email.exists'      => 'Email không tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu mới không được để trống.',
            'password.min'      => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu mới không khớp.',
        ]);

        // Kiểm tra tính hợp lệ của token trong bảng password_reset_tokens
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'email' => 'Yêu cầu khôi phục mật khẩu không hợp lệ hoặc liên kết đã bị thay đổi.',
            ]);
        }

        // Kiểm tra hết hạn (60 phút)
        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            // Xóa token đã hết hạn
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            
            throw ValidationException::withMessages([
                'email' => 'Liên kết khôi phục mật khẩu đã hết hạn (giới hạn 60 phút). Vui lòng gửi lại yêu cầu.',
            ]);
        }

        // Cập nhật mật khẩu mới cho user
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Xóa token sau khi sử dụng thành công
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Mật khẩu của bạn đã được khôi phục thành công! Hãy đăng nhập bằng mật khẩu mới.');
    }
}
