<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeatType;
use Illuminate\Http\Request;

class SeatTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = SeatType::withCount('seats');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $seatTypes = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.seat-types.index', compact('seatTypes'));
    }

    public function create()
    {
        return view('admin.seat-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:50|unique:seat_types,name',
            'surcharge_price'   => 'required|integer|min:0',
        ], [
            'name.required'             => 'Tên loại ghế không được để trống.',
            'name.unique'               => 'Tên loại ghế này đã tồn tại.',
            'name.max'                  => 'Tên loại ghế không được vượt quá 50 ký tự.',
            'surcharge_price.required'  => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'   => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'       => 'Giá phụ thu không được âm.',
        ]);

        SeatType::create($request->only('name', 'surcharge_price'));

        return redirect()->route('admin.seat-types.index')->with('success', 'Thêm loại ghế thành công!');
    }

    public function edit(SeatType $seatType)
    {
        return view('admin.seat-types.edit', compact('seatType'));
    }

    public function update(Request $request, SeatType $seatType)
    {
        $request->validate([
            'name'              => 'required|string|max:50|unique:seat_types,name,' . $seatType->id,
            'surcharge_price'   => 'required|integer|min:0',
        ], [
            'name.required'             => 'Tên loại ghế không được để trống.',
            'name.unique'               => 'Tên loại ghế này đã tồn tại.',
            'name.max'                  => 'Tên loại ghế không được vượt quá 50 ký tự.',
            'surcharge_price.required'  => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'   => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'       => 'Giá phụ thu không được âm.',
        ]);

        $seatType->update($request->only('name', 'surcharge_price'));

        return redirect()->route('admin.seat-types.index')->with('success', 'Cập nhật loại ghế thành công!');
    }

    public function destroy(SeatType $seatType)
    {
        // Kiểm tra nếu loại ghế đang được sử dụng
        if ($seatType->seats()->exists()) {
            return redirect()->route('admin.seat-types.index')->with('error', 'Không thể xóa loại ghế vì đã có ghế sử dụng loại này!');
        }

        $seatType->delete();

        return redirect()->route('admin.seat-types.index')->with('success', 'Xóa loại ghế thành công!');
    }
}
