<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    public function index(Request $request)
    {
        $query = Cinema::withCount('rooms');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $cinemas = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.cinemas.index', compact('cinemas'));
    }

    public function create()
    {
        return view('admin.cinemas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:cinemas,name',
            'address' => 'required|string|max:500',
            'phone'   => 'required|string|max:20',
            'city'    => 'required|string|max:100',
            'status'  => 'required|in:active,inactive',
        ], [
            'name.required'    => 'Tên rạp không được để trống.',
            'name.unique'      => 'Tên rạp này đã tồn tại.',
            'address.required' => 'Địa chỉ không được để trống.',
            'phone.required'   => 'Số điện thoại không được để trống.',
            'city.required'    => 'Thành phố không được để trống.',
        ]);

        Cinema::create($request->only('name', 'address', 'phone', 'city', 'status'));

        return redirect()->route('admin.cinemas.index')->with('success', 'Thêm rạp chiếu phim thành công!');
    }

    public function edit(Cinema $cinema)
    {
        $cinema->loadCount('rooms');
        return view('admin.cinemas.edit', compact('cinema'));
    }

    public function update(Request $request, Cinema $cinema)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:cinemas,name,' . $cinema->id,
            'address' => 'required|string|max:500',
            'phone'   => 'required|string|max:20',
            'city'    => 'required|string|max:100',
            'status'  => 'required|in:active,inactive',
        ], [
            'name.required'    => 'Tên rạp không được để trống.',
            'name.unique'      => 'Tên rạp này đã tồn tại.',
            'address.required' => 'Địa chỉ không được để trống.',
            'phone.required'   => 'Số điện thoại không được để trống.',
            'city.required'    => 'Thành phố không được để trống.',
        ]);

        $cinema->update($request->only('name', 'address', 'phone', 'city', 'status'));

        return redirect()->route('admin.cinemas.index')->with('success', 'Cập nhật rạp chiếu phim thành công!');
    }

    public function destroy(Cinema $cinema)
    {
        if ($cinema->rooms()->count() > 0) {
            return redirect()->route('admin.cinemas.index')
                ->with('error', 'Không thể xóa rạp đang có phòng chiếu liên kết!');
        }

        $cinema->delete();

        return redirect()->route('admin.cinemas.index')->with('success', 'Xóa rạp chiếu phim thành công!');
    }
}
