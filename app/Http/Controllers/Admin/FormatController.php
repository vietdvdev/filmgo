<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Format;
use Illuminate\Http\Request;

class FormatController extends Controller
{
    public function index(Request $request)
    {
        $query = Format::withCount('showtimes');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $formats = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.formats.index', compact('formats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:formats,name',
            'surcharge_price' => 'required|integer|min:0',
        ], [
            'name.required'            => 'Tên định dạng không được để trống.',
            'name.unique'              => 'Tên định dạng này đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
        ]);

        Format::create($request->only('name', 'surcharge_price'));

        return redirect()->route('admin.formats.index')->with('success', 'Thêm định dạng thành công!');
    }

    public function update(Request $request, Format $format)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:formats,name,' . $format->id,
            'surcharge_price' => 'required|integer|min:0',
        ], [
            'name.required'            => 'Tên định dạng không được để trống.',
            'name.unique'              => 'Tên định dạng này đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
        ]);

        $format->update($request->only('name', 'surcharge_price'));

        return redirect()->route('admin.formats.index')->with('success', 'Cập nhật định dạng thành công!');
    }

    public function destroy(Format $format)
    {
        if ($format->showtimes()->count() > 0) {
            return redirect()->route('admin.formats.index')
                ->with('error', 'Không thể xóa định dạng đang được sử dụng bởi suất chiếu!');
        }

        $format->delete();

        return redirect()->route('admin.formats.index')->with('success', 'Xóa định dạng thành công!');
    }
}
