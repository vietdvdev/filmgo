<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Format;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormatController extends Controller
{
    public function index(Request $request)
    {
        $query = Format::withCount(['showtimes', 'rooms']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortable = ['name', 'surcharge_price', 'showtimes_count', 'rooms_count', 'created_at'];
        $sort     = in_array($request->sort, $sortable) ? $request->sort : 'created_at';
        $dir      = $request->dir === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, ['showtimes_count', 'rooms_count'])) {
            $query->orderBy($sort, $dir);
        } else {
            $query->orderBy($sort, $dir);
        }

        $formats = $query->paginate(10)->withQueryString();
        $editId  = session('edit_id');

        return view('admin.formats.index', compact('formats', 'editId', 'sort', 'dir'));
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
            'name.unique'              => 'Tên định dạng "' . $request->name . '" đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'  => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
        ]);

        Format::create($request->only('name', 'description', 'surcharge_price', 'status'));

        return redirect()->route('admin.formats.index')
            ->with('success', 'Thêm định dạng "' . $request->name . '" thành công!');
    }

    public function update(Request $request, Format $format)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name'            => ['required', 'string', 'max:100', Rule::unique('formats', 'name')->ignore($format->id)],
            'description'     => 'nullable|string|max:255',
            'surcharge_price' => 'required|integer|min:0',
            'status'          => 'required|in:active,inactive',
        ], [
            'name.required'            => 'Tên định dạng không được để trống.',
            'name.max'                 => 'Tên định dạng không được vượt quá 100 ký tự.',
            'name.unique'              => 'Tên định dạng "' . $request->name . '" đã tồn tại.',
            'surcharge_price.required' => 'Giá phụ thu không được để trống.',
            'surcharge_price.integer'  => 'Giá phụ thu phải là số nguyên.',
            'surcharge_price.min'      => 'Giá phụ thu không được âm.',
            'status.required'          => 'Vui lòng chọn trạng thái.',
            'status.in'                => 'Trạng thái không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.formats.index')
                ->withErrors($validator)
                ->withInput()
                ->with('edit_id', $format->id);
        }

        $format->update($request->only('name', 'description', 'surcharge_price', 'status'));

        return redirect()->route('admin.formats.index')
            ->with('success', 'Cập nhật định dạng "' . $request->name . '" thành công!');
    }

    public function destroy(Format $format)
    {
        $showtimeCount = $format->showtimes()->count();
        $roomCount     = $format->rooms()->count();

        if ($showtimeCount > 0 || $roomCount > 0) {
            $parts = [];
            if ($showtimeCount > 0) $parts[] = "{$showtimeCount} suất chiếu";
            if ($roomCount > 0)     $parts[] = "{$roomCount} phòng chiếu";

            return redirect()->route('admin.formats.index')
                ->with('error', 'Không thể xóa định dạng "' . $format->name . '" vì đang được sử dụng bởi ' . implode(' và ', $parts) . '!');
        }

        $name = $format->name;
        $format->delete();

        return redirect()->route('admin.formats.index')
            ->with('success', 'Đã xóa định dạng "' . $name . '" thành công!');
    }
}
