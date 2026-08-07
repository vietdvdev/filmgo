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

        // Truyền lại old input + errors khi validation thất bại từ store/update
        $editId = session('edit_id');

        return view('admin.formats.index', compact('formats', 'editId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:100|unique:formats,name',
            'description'     => 'nullable|string|max:255',
            'surcharge_price' => 'required|integer|min:0',
            'status'          => 'required|in:active,inactive',
        ], [
            'name.required'            => 'Tên định dạng không được để trống.',
            'name.max'                 => 'Tên định dạng không được vượt quá 100 ký tự.',
            'name.unique'              => 'Tên định dạng này đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'  => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
        ]);

        Format::create($request->only('name', 'description', 'surcharge_price', 'status'));

        return redirect()->route('admin.formats.index')->with('success', 'Thêm định dạng thành công!');
    }

    public function update(Request $request, Format $format)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:100|unique:formats,name,' . $format->id,
            'description'     => 'nullable|string|max:255',
            'surcharge_price' => 'required|integer|min:0',
            'status'          => 'required|in:active,inactive',
        ], [
            'name.required'            => 'Tên định dạng không được để trống.',
            'name.max'                 => 'Tên định dạng không được vượt quá 100 ký tự.',
            'name.unique'              => 'Tên định dạng này đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'  => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
        ]);

        $format->update($validated);

        return redirect()->route('admin.formats.index')->with('success', 'Cập nhật định dạng thành công!');
    }

    public function destroy(Format $format)
    {
        if ($format->showtimes()->count() > 0) {
            return redirect()->route('admin.formats.index')
                ->with('error', 'Không thể xóa định dạng đang được sử dụng bởi ' . $format->showtimes()->count() . ' suất chiếu!');
        }

        $format->delete();

        return redirect()->route('admin.formats.index')->with('success', 'Xóa định dạng thành công!');
    }
}
