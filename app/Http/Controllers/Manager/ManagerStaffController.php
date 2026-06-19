<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ManagerStaffController extends Controller
{
    private function getCinemaId()
    {
        return Auth::user()->cinemas()->first()->id;
    }

    public function index(Request $request)
    {
        // Mock dữ liệu giả lập cho danh sách nhân viên
        $mockData = collect([
            (object)[
                'id' => 1,
                'full_name' => 'Nguyễn Văn Hải',
                'email' => 'hai.nv@filmgo.vn',
                'phone' => '0987654321',
                'status' => 'active',
                'created_at' => now()
            ],
            (object)[
                'id' => 2,
                'full_name' => 'Trần Thị Thu Trang',
                'email' => 'trang.ttt@filmgo.vn',
                'phone' => '0912345678',
                'status' => 'active',
                'created_at' => now()
            ],
            (object)[
                'id' => 3,
                'full_name' => 'Lê Hoàng Nam',
                'email' => 'nam.lh@filmgo.vn',
                'phone' => '0909090909',
                'status' => 'locked',
                'created_at' => now()
            ]
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $mockData = $mockData->filter(fn($item) => stripos($item->full_name, $search) !== false || stripos($item->email, $search) !== false);
        }

        $currentPage = 1;
        $perPage = 10;
        $staffs = new \Illuminate\Pagination\LengthAwarePaginator(
            $mockData->forPage($currentPage, $perPage),
            $mockData->count(),
            $perPage,
            $currentPage,
            ['path' => route('manager.staff.index')]
        );

        return view('manager.staff.index', compact('staffs'));
    }

    public function create()
    {
        return view('manager.staff.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email'     => 'required|email|max:255',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
            'email.required'     => 'Email không được để trống.',
            'password.required'  => 'Mật khẩu không được để trống.',
            'password.min'       => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        // Mock hành động thêm mới thành công
        return redirect()->route('manager.staff.index')->with('success', 'Thêm nhân viên mới thành công (dữ liệu giả lập)!');
    }

    public function edit($id)
    {
        $staff = (object)[
            'id' => $id,
            'full_name' => 'Nguyễn Văn Hải',
            'email' => 'hai.nv@filmgo.vn',
            'phone' => '0987654321',
            'status' => 'active'
        ];

        return view('manager.staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
        ], [
            'full_name.required' => 'Họ và tên không được để trống.',
        ]);

        // Mock hành động cập nhật thành công
        return redirect()->route('manager.staff.index')->with('success', 'Cập nhật thông tin nhân viên thành công (dữ liệu giả lập)!');
    }

    public function toggleStatus($id)
    {
        // Mock hành động khóa/mở khóa thành công
        return redirect()->route('manager.staff.index')->with('success', 'Thay đổi trạng thái tài khoản nhân viên thành công (dữ liệu giả lập)!');
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'Mật khẩu mới không được để trống.',
            'password.min'      => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
        ]);

        // Mock hành động reset mật khẩu thành công
        return redirect()->route('manager.staff.index')->with('success', 'Đã đặt lại mật khẩu nhân viên thành công (dữ liệu giả lập)!');
    }
}
