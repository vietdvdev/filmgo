<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email'    => 'Email không đúng định dạng.',
            'email.exists'   => 'Email này không tồn tại trong hệ thống.',
        ]);

        $token = Str::random(60);
        $email = $request->email;

        // Lưu hoặc cập nhật token trong bảng password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Gửi email notification
        $user = User::where('email', $email)->first();
        $user->notify(new ResetPasswordNotification($token, $email));

        return back()->with('success', 'Chúng tôi đã gửi email chứa liên kết đặt lại mật khẩu của bạn!');
    }
}
