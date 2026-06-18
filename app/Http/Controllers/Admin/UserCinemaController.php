<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\User;
use App\Models\UserCinema;
use Illuminate\Http\Request;

class UserCinemaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $assignments = UserCinema::with([
            'user.roles',
            'cinema'
        ])

            ->when($request->filled('search'), function ($query) use ($request) {

                $query->whereHas('user', function ($q) use ($request) {

                    $q->where('full_name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })

            ->when($request->filled('cinema_id'), function ($query) use ($request) {

                $query->where('cinema_id', $request->cinema_id);
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        $cinemas = Cinema::orderBy('name')->get();

        return view(
            'admin.user-cinemas.index',
            compact(
                'assignments',
                'cinemas'
            )


        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        $cinemas = Cinema::orderBy('name')->get();

        // Lấy các rạp đã có manager
        $managedCinemaIds = UserCinema::whereHas('user.roles', function ($q) {
            $q->where('name', 'manager');
        })
            ->pluck('cinema_id')
            ->toArray();

        return view(
            'admin.user-cinemas.create',
            compact(
                'users',
                'cinemas',
                'managedCinemaIds'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                'exists:users,id'
            ],
            'cinema_id' => [
                'required',
                'exists:cinemas,id'
            ]
        ]);

        $exists = UserCinema::where(
            'user_id',
            $request->user_id
        )
            ->where(
                'cinema_id',
                $request->cinema_id
            )
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Người dùng đã được phân công vào rạp này.'
                );
        }

        UserCinema::create([
            'user_id' => $request->user_id,
            'cinema_id' => $request->cinema_id,
        ]);

        return redirect()
            ->route('admin.user-cinemas.index')
            ->with(
                'success',
                'Phân công rạp thành công.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $assignment = UserCinema::findOrFail($id);

        $users = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        $cinemas = Cinema::orderBy('name')->get();

        $managedCinemaIds = UserCinema::whereHas('user.roles', function ($q) {
            $q->where('name', 'manager');
        })
            ->where('id', '!=', $assignment->id)
            ->pluck('cinema_id')
            ->toArray();

        return view(
            'admin.user-cinemas.edit',
            compact(
                'assignment',
                'users',
                'cinemas',
                'managedCinemaIds'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        UserCinema $userCinema
    ) {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cinema_id' => 'required|exists:cinemas,id',
        ]);

        $exists = UserCinema::where(
            'user_id',
            $request->user_id
        )
            ->where(
                'cinema_id',
                $request->cinema_id
            )
            ->where(
                'id',
                '!=',
                $userCinema->id
            )
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Người dùng đã được phân công vào rạp này.'
                );
        }

        $userCinema->update([
            'user_id' => $request->user_id,
            'cinema_id' => $request->cinema_id,
        ]);

        return redirect()
            ->route('admin.user-cinemas.index')
            ->with(
                'success',
                'Cập nhật phân công thành công.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assignment = UserCinema::findOrFail($id);

        $userName = $assignment->user?->full_name;
        $cinemaName = $assignment->cinema?->name;

        $assignment->delete();

        return redirect()
            ->route('admin.user-cinemas.index')
            ->with(
                'success',
                "Đã hủy phân công {$userName} khỏi rạp {$cinemaName}."
            );
    }
}
