<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PriceRule;
use Illuminate\Http\Request;

class PriceRuleController extends Controller
{
    public function index(Request $request)
    {
        $query = PriceRule::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('day_of_week') && $request->day_of_week !== '') {
            $query->where('day_of_week', $request->day_of_week);
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        $priceRules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(15)->withQueryString();

        return view('admin.price-rules.index', compact('priceRules'));
    }

    public function create()
    {
        return view('admin.price-rules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'day_of_week'          => 'required|integer|between:0,6',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'adjustment_amount'    => 'required|integer',
            'is_active'            => 'required|boolean',
        ], [
            'name.required'            => 'Tên quy tắc giá không được để trống.',
            'name.max'                 => 'Tên quy tắc giá không được vượt quá 100 ký tự.',
            'day_of_week.required'     => 'Vui lòng chọn ngày trong tuần.',
            'day_of_week.between'      => 'Ngày trong tuần phải từ 0 (Chủ Nhật) đến 6 (Thứ 7).',
            'start_time.required'      => 'Thời gian bắt đầu không được để trống.',
            'start_time.date_format'   => 'Thời gian bắt đầu phải có định dạng HH:MM.',
            'end_time.required'        => 'Thời gian kết thúc không được để trống.',
            'end_time.date_format'     => 'Thời gian kết thúc phải có định dạng HH:MM.',
            'end_time.after'           => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'adjustment_amount.required' => 'Mức điều chỉnh giá không được để trống.',
            'adjustment_amount.integer' => 'Mức điều chỉnh giá phải là số nguyên.',
            'is_active.required'       => 'Trạng thái hoạt động không được để trống.',
        ]);

        PriceRule::create([
            'name'              => $request->name,
            'day_of_week'       => $request->day_of_week,
            'start_time'        => $request->start_time . ':00',
            'end_time'          => $request->end_time . ':00',
            'adjustment_amount' => $request->adjustment_amount,
            'is_active'         => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.price-rules.index')->with('success', 'Thêm quy tắc giá thành công!');
    }

    public function edit(PriceRule $priceRule)
    {
        return view('admin.price-rules.edit', compact('priceRule'));
    }

    public function update(Request $request, PriceRule $priceRule)
    {
        $request->validate([
            'name'                 => 'required|string|max:100',
            'day_of_week'          => 'required|integer|between:0,6',
            'start_time'           => 'required|date_format:H:i',
            'end_time'             => 'required|date_format:H:i|after:start_time',
            'adjustment_amount'    => 'required|integer',
            'is_active'            => 'required|boolean',
        ], [
            'name.required'            => 'Tên quy tắc giá không được để trống.',
            'name.max'                 => 'Tên quy tắc giá không được vượt quá 100 ký tự.',
            'day_of_week.required'     => 'Vui lòng chọn ngày trong tuần.',
            'day_of_week.between'      => 'Ngày trong tuần phải từ 0 (Chủ Nhật) đến 6 (Thứ 7).',
            'start_time.required'      => 'Thời gian bắt đầu không được để trống.',
            'start_time.date_format'   => 'Thời gian bắt đầu phải có định dạng HH:MM.',
            'end_time.required'        => 'Thời gian kết thúc không được để trống.',
            'end_time.date_format'     => 'Thời gian kết thúc phải có định dạng HH:MM.',
            'end_time.after'           => 'Thời gian kết thúc phải sau thời gian bắt đầu.',
            'adjustment_amount.required' => 'Mức điều chỉnh giá không được để trống.',
            'adjustment_amount.integer' => 'Mức điều chỉnh giá phải là số nguyên.',
            'is_active.required'       => 'Trạng thái hoạt động không được để trống.',
        ]);

        $priceRule->update([
            'name'              => $request->name,
            'day_of_week'       => $request->day_of_week,
            'start_time'        => $request->start_time . ':00',
            'end_time'          => $request->end_time . ':00',
            'adjustment_amount' => $request->adjustment_amount,
            'is_active'         => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.price-rules.index')->with('success', 'Cập nhật quy tắc giá thành công!');
    }

    public function destroy(PriceRule $priceRule)
    {
        $priceRule->delete();

        return redirect()->route('admin.price-rules.index')->with('success', 'Xóa quy tắc giá thành công!');
    }

    public function toggleStatus(Request $request, PriceRule $priceRule)
    {
        $priceRule->update(['is_active' => !$priceRule->is_active]);

        return back()->with('success', 'Cập nhật trạng thái thành công!');
    }
}
