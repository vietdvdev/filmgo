<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $roles = Role::orderBy('name')->get();

        $users = User::with('roles')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('roles'), function ($query) use ($request) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->whereIn('roles.id', $request->roles);
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $trashedCount = User::onlyTrashed()->count();

        return view('admin.users.index', compact('users', 'roles', 'trashedCount'));
    }

    public function trashed(Request $request)
    {
        $trashedUsers = User::onlyTrashed()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.trashed', compact('trashedUsers'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'avatar'    => ['nullable', 'image', 'max:2048'],
            'status'    => ['required', 'in:active,locked'],
            'roles'     => ['array'],
            'roles.*'   => ['exists:roles,id'],
        ], $this->validationMessages());

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->roles()->sync($validated['roles'] ?? []);

        // Ngăn tổ hợp role xung đột: customer không được đi kèm admin/manager/staff
        $assignedNames = $user->roles()->pluck('name');
        if ($assignedNames->contains('customer') && $assignedNames->intersect(['admin', 'manager', 'staff'])->isNotEmpty()) {
            $user->roles()->detach();
            return back()->withInput()->withErrors([
                'roles' => 'Không thể gán role customer cùng với admin/manager/staff.',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Thêm người dùng thành công.');
    }

    public function show(User $user)
    {
        return redirect()->route('admin.users.edit', $user);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load('roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar'    => ['nullable', 'image', 'max:2048'],
            'status'    => ['required', 'in:active,locked'],
            'roles'     => ['array'],
            'roles.*'   => ['exists:roles,id'],
        ], $this->validationMessages());

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->roles()->sync($validated['roles'] ?? []);

        // Ngăn tổ hợp role xung đột: customer không được đi kèm admin/manager/staff
        $assignedNames = $user->roles()->pluck('name');
        if ($assignedNames->contains('customer') && $assignedNames->intersect(['admin', 'manager', 'staff'])->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'roles' => 'Không thể gán role customer cùng với admin/manager/staff.',
            ]);
        }

        if ($request->filled('return')) {
            return redirect(urldecode($request->return))
                ->with('success', 'Cập nhật người dùng thành công.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa chính tài khoản đang đăng nhập.');
        }

        if ($user->roles->contains('name', 'admin')) {
            return back()->with('error', 'Không thể xóa tài khoản có vai trò Admin.');
        }

        $hasActiveBookings = $user->bookings()
            ->whereIn('payment_status', ['pending', 'paid'])
            ->whereIn('booking_status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Không thể xóa người dùng đang có đơn đặt vé chưa hoàn tất.');
        }

        if ($user->cinemas()->exists()) {
            return back()->with('error', 'Không thể xóa người dùng đang được phân công quản lý rạp. Hãy hủy phân công trước.');
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa người dùng «' . $user->full_name . '».');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.trashed')
            ->with('success', 'Đã khôi phục người dùng «' . $user->full_name . '».');
    }

    /**
     * Thông báo lỗi validate dùng chung cho store() và update().
     */
    protected function validationMessages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'full_name.string'   => 'Họ tên phải là chuỗi ký tự.',
            'full_name.max'      => 'Họ tên không được vượt quá :max ký tự.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không đúng định dạng.',
            'email.max'      => 'Email không được vượt quá :max ký tự.',
            'email.unique'   => 'Email này đã được sử dụng bởi tài khoản khác.',

            'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
            'phone.max'    => 'Số điện thoại không được vượt quá :max ký tự.',

            'password.required'  => 'Vui lòng nhập mật khẩu.',
            'password.string'    => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min'       => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',

            'avatar.image' => 'Ảnh đại diện phải là một file hình ảnh.',
            'avatar.max'   => 'Dung lượng ảnh đại diện không được vượt quá 2MB.',

            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in'       => 'Trạng thái không hợp lệ.',

            'roles.array'    => 'Dữ liệu vai trò không hợp lệ.',
            'roles.*.exists' => 'Vai trò được chọn không tồn tại trong hệ thống.',
        ];
    }
}
