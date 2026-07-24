<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserCinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagerStaffController extends Controller
{
    /**
     * Lấy danh sách rạp mà manager đang quản lý
     */
    private function managerCinemaIds()
    {
        return Auth::user()
            ->cinemas()
            ->pluck('cinemas.id')
            ->toArray();
    }

    /**
     * Danh sách nhân viên
     */
    public function index(Request $request)
    {
        $cinemaIds = $this->managerCinemaIds();

        $staffs = User::with(['roles', 'cinemas'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'staff');
            })
            ->whereHas('cinemas', function ($q) use ($cinemaIds) {
                $q->whereIn('cinemas.id', $cinemaIds);
            })

            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('full_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })

            ->when($request->filled('cinema_id'), function ($query) use ($request) {
                $query->whereHas('cinemas', function ($q) use ($request) {
                    $q->where('cinemas.id', $request->cinema_id);
                });
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        $cinemas = Auth::user()->cinemas()->orderBy('name')->get();

        return view(
            'manager.staff.index',
            compact(
                'staffs',
                'cinemas'
            )
        );
    }

    /**
     * Thêm nhân viên
     */

    public function create()
{
    $cinemas = auth()->user()
        ->cinemas()
        ->orderBy('name')
        ->get();

    return view(
        'manager.staff.create',
        compact('cinemas')
    );
}
    public function store(Request $request)
    {
        $request->validate([
    'full_name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'phone' => 'nullable|regex:/^(0)[0-9]{9}$/',
    'password' => 'required|min:6|confirmed',
    'cinema_id' => 'required|exists:cinemas,id',
], [
    'full_name.required' => 'Vui lòng nhập họ và tên.',
    'full_name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

    'email.required' => 'Vui lòng nhập email.',
    'email.email' => 'Email không đúng định dạng.',
    'email.unique' => 'Email đã tồn tại trong hệ thống.',

    'phone.regex' => 'Số điện thoại không hợp lệ.',

    'password.required' => 'Vui lòng nhập mật khẩu.',
    'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
    'password.confirmed' => 'Xác nhận mật khẩu không khớp.',

    'cinema_id.required' => 'Vui lòng chọn rạp làm việc.',
    'cinema_id.exists' => 'Rạp được chọn không tồn tại.',
]);

        $managerCinemaIds = $this->managerCinemaIds();

        if (!in_array($request->cinema_id, $managerCinemaIds)) {
            abort(403);
        }

        $staff = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $staffRole = Role::where('name', 'staff')->first();

        if ($staffRole) {
            $staff->roles()->attach($staffRole->id);
        }

        UserCinema::create([
            'user_id' => $staff->id,
            'cinema_id' => $request->cinema_id,
        ]);

        return redirect()
            ->route('manager.staff.index')
            ->with(
                'success',
                'Thêm nhân viên thành công.'
            );
    }

    /**
     * Tìm nhân viên thuộc rạp của Manager đang đăng nhập.
     * Throw 403 nếu nhân viên không thuộc rạp được phân công.
     */
    private function findStaffInMyCinemas(int $id): User
    {
        $cinemaIds = $this->managerCinemaIds();

        return User::whereHas('roles', fn($q) => $q->where('name', 'staff'))
            ->whereHas('cinemas', fn($q) => $q->whereIn('cinemas.id', $cinemaIds))
            ->findOrFail($id);
    }

    /**
     * Cập nhật nhân viên
     */

    public function edit($id)
{
    $staff = $this->findStaffInMyCinemas((int)$id);
    $staff->load('cinemas');

    $cinemas = auth()->user()
        ->cinemas()
        ->orderBy('name')
        ->get();

    return view(
        'manager.staff.edit',
        compact(
            'staff',
            'cinemas'
        )
    );
}
    public function update(Request $request, $id)
{
    $staff = $this->findStaffInMyCinemas((int)$id);

    $request->validate([
    'full_name' => 'required|string|max:255',
    'phone' => 'nullable|regex:/^(0)[0-9]{9}$/',
    'cinema_id' => 'required|exists:cinemas,id',
], [
    'full_name.required' => 'Vui lòng nhập họ và tên.',
    'full_name.max' => 'Họ tên không được vượt quá 255 ký tự.',

    'phone.regex' => 'Số điện thoại không hợp lệ.',

    'cinema_id.required' => 'Vui lòng chọn rạp làm việc.',
    'cinema_id.exists' => 'Rạp được chọn không tồn tại.',
]);

    $managerCinemaIds = $this->managerCinemaIds();

    if (!in_array($request->cinema_id, $managerCinemaIds)) {
        abort(403);
    }

    $staff->update([
        'full_name' => $request->full_name,
        'phone'     => $request->phone,
    ]);

    // Xóa phân công cũ và tạo mới để tránh trường hợp staff được gán nhiều rạp
    UserCinema::where('user_id', $staff->id)->delete();
    UserCinema::create([
        'user_id'   => $staff->id,
        'cinema_id' => $request->cinema_id,
    ]);

    return redirect()
        ->route('manager.staff.index')
        ->with('success', 'Cập nhật nhân viên thành công.');
}

    /**
     * Xóa nhân viên
     */
    public function destroy($id)
    {
        $staff = $this->findStaffInMyCinemas((int)$id);

        UserCinema::where('user_id', $staff->id)->delete();
        $staff->delete();

        return redirect()
            ->route('manager.staff.index')
            ->with(
                'success',
                'Xóa nhân viên thành công.'
            );
    }

    /**
     * Khóa / Mở khóa
     */
    public function toggleStatus($id)
    {
        $staff = $this->findStaffInMyCinemas((int)$id);

        $staff->update([
            'status' => $staff->status === 'active'
                ? 'locked'
                : 'active'
        ]);

        return redirect()
            ->route('manager.staff.index')
            ->with(
                'success',
                'Cập nhật trạng thái thành công.'
            );
    }

    /**
     * Reset mật khẩu
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $staff = $this->findStaffInMyCinemas((int)$id);

        $staff->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()
            ->route('manager.staff.index')
            ->with(
                'success',
                'Đặt lại mật khẩu thành công.'
            );
    }
}