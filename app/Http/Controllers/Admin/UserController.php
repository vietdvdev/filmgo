<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
{
    $roles = Role::orderBy('name')->get();

    $users = User::with('roles')

        // Tìm kiếm
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })

        // Lọc trạng thái
        ->when($request->filled('status'), function ($query) use ($request) {
            $query->where('status', $request->status);
        })

        // Lọc nhiều role
        ->when($request->filled('roles'), function ($query) use ($request) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->whereIn('roles.id', $request->roles);
            });
        })

        ->latest()
        ->paginate(15)
        ->withQueryString();

    $trashedUsers = User::onlyTrashed()
        ->latest()
        ->get();

    return view('admin.users.index', compact(
        'users',
        'roles',
        'trashedUsers'
    ));
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
        ]);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->roles()->sync($validated['roles'] ?? []);

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
        ]);

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

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật người dùng thành công.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa chính tài khoản đang đăng nhập.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa người dùng.');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
            ->with('success', 'Khôi phục người dùng thành công.');
    }
}