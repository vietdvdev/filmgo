<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'email.required'     => 'Email không được để trống.',
            'email.email'        => 'Email không đúng định dạng.',
            'email.unique'       => 'Email này đã được sử dụng.',
            'password.required'  => 'Mật khẩu không được để trống.',
            'password.min'       => 'Mật khẩu phải chứa ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'status'    => 'active',
        ]);

        // Gán vai trò mặc định 'customer'
        $role = Role::where('name', 'customer')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Auth::login($user);

        return redirect()->route('home')->with('success', 'Đăng ký tài khoản thành công!');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email không được để trống.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user) {
            if ($user->status !== 'active') {
                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $loggedInUser = Auth::user();

                // Chặn admin/manager/staff đăng nhập vào portal Khách hàng
                $hasManagementRole = $loggedInUser->roles()->whereIn('name', ['admin', 'manager', 'staff'])->exists();
                if ($hasManagementRole) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    if ($loggedInUser->roles()->where('name', 'admin')->exists()) {
                        throw ValidationException::withMessages([
                            'email' => 'Tài khoản Quản trị viên không thể đăng nhập tại đây. Vui lòng truy cập: /admin/login',
                        ]);
                    }

                    if ($loggedInUser->roles()->where('name', 'manager')->exists()) {
                        throw ValidationException::withMessages([
                            'email' => 'Tài khoản Quản lý Rạp không thể đăng nhập tại đây. Vui lòng truy cập: /manager/login',
                        ]);
                    }

                    throw ValidationException::withMessages([
                        'email' => 'Tài khoản của bạn không có quyền truy cập khu vực Khách hàng.',
                    ]);
                }

                $request->session()->regenerate();
                return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công!');
            }
        }

        throw ValidationException::withMessages([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
    }
}
