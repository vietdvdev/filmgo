<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    protected PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Hiển thị danh sách mã khuyến mãi.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $promotions = $this->promotionService->getPromotionsPaginated($request->query('search'));

        return view('admin.promotions.index', compact('promotions'));
    }

    /**
     * Hiển thị form tạo mới mã khuyến mãi.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.promotions.create');
    }

    /**
     * Lưu trữ mã khuyến mãi mới vào database.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'code'                => 'required|string|max:50|unique:promotions,code',
            'apply_to'            => 'required|in:all,ticket_only,combo_only',
            'discount_type'       => 'required|in:percent,fixed',
            'discount_value'      => 'required|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:0',
            'min_order_amount'    => 'required|integer|min:0',
            'max_uses_per_user'   => 'required|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'usage_limit'         => 'nullable|integer|min:1',
            'status'              => 'required|in:active,inactive',
        ];

        if ($request->input('discount_type') === 'percent') {
            $rules['discount_value'] .= '|max:100';
        }

        $validated = $request->validate($rules, $this->validationMessages());

        $this->promotionService->createPromotion($validated);

        return redirect()->route('admin.promotions.index')->with('success', 'Thêm mới mã khuyến mãi thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa mã khuyến mãi.
     *
     * @param Promotion $promotion
     * @return View
     */
    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', compact('promotion'));
    }

    /**
     * Cập nhật thông tin mã khuyến mãi trong database.
     *
     * @param Request $request
     * @param Promotion $promotion
     * @return RedirectResponse
     */
    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $rules = [
            'code'                => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'apply_to'            => 'required|in:all,ticket_only,combo_only',
            'discount_type'       => 'required|in:percent,fixed',
            'discount_value'      => 'required|integer|min:1',
            'max_discount_amount' => 'nullable|integer|min:0',
            'min_order_amount'    => 'required|integer|min:0',
            'max_uses_per_user'   => 'required|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'usage_limit'         => 'nullable|integer|min:1',
            'status'              => 'required|in:active,inactive',
        ];

        if ($request->input('discount_type') === 'percent') {
            $rules['discount_value'] .= '|max:100';
        }

        $validated = $request->validate($rules, $this->validationMessages());

        $this->promotionService->updatePromotion($promotion, $validated);

        return redirect()->route('admin.promotions.index')->with('success', 'Cập nhật mã khuyến mãi thành công!');
    }

    /**
     * Xóa mã khuyến mãi (xóa mềm).
     *
     * @param Promotion $promotion
     * @return RedirectResponse
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        $this->promotionService->deletePromotion($promotion);

        return redirect()->route('admin.promotions.index')->with('success', 'Xóa mã khuyến mãi thành công!');
    }

    /**
     * Thông báo lỗi tiếng Việt cho form validation.
     *
     * @return array
     */
    protected function validationMessages(): array
    {
        return [
            'code.required'              => 'Mã khuyến mãi không được để trống.',
            'code.unique'                => 'Mã khuyến mãi này đã tồn tại.',
            'code.max'                   => 'Mã khuyến mãi không được vượt quá 50 ký tự.',
            'apply_to.required'          => 'Phạm vi áp dụng không được để trống.',
            'apply_to.in'                => 'Phạm vi áp dụng không hợp lệ.',
            'discount_type.required'     => 'Loại giảm giá không được để trống.',
            'discount_type.in'           => 'Loại giảm giá không hợp lệ.',
            'discount_value.required'    => 'Giá trị giảm không được để trống.',
            'discount_value.integer'     => 'Giá trị giảm phải là số nguyên.',
            'discount_value.min'         => 'Giá trị giảm phải lớn hơn hoặc bằng 1.',
            'discount_value.max'         => 'Giá trị phần trăm giảm không được vượt quá 100%.',
            'max_discount_amount.integer'=> 'Số tiền giảm tối đa phải là số nguyên.',
            'max_discount_amount.min'    => 'Số tiền giảm tối đa phải lớn hơn hoặc bằng 0.',
            'min_order_amount.required'  => 'Đơn hàng tối thiểu không được để trống.',
            'min_order_amount.integer'   => 'Đơn hàng tối thiểu phải là số nguyên.',
            'min_order_amount.min'       => 'Đơn hàng tối thiểu phải từ 0 ₫.',
            'max_uses_per_user.required' => 'Số lần sử dụng tối đa của mỗi user không được để trống.',
            'max_uses_per_user.integer'  => 'Số lần sử dụng tối đa phải là số nguyên.',
            'max_uses_per_user.min'      => 'Số lần sử dụng tối đa phải lớn hơn hoặc bằng 1.',
            'start_date.required'        => 'Ngày bắt đầu áp dụng không được để trống.',
            'start_date.date'            => 'Ngày bắt đầu áp dụng không đúng định dạng.',
            'end_date.required'          => 'Ngày kết thúc áp dụng không được để trống.',
            'end_date.date'              => 'Ngày kết thúc áp dụng không đúng định dạng.',
            'end_date.after_or_equal'    => 'Ngày kết thúc áp dụng phải lớn hơn hoặc bằng ngày bắt đầu.',
            'usage_limit.integer'        => 'Tổng số lượng phát hành phải là số nguyên.',
            'usage_limit.min'            => 'Tổng số lượng phát hành phải lớn hơn hoặc bằng 1.',
            'status.required'            => 'Trạng thái không được để trống.',
            'status.in'                  => 'Trạng thái hoạt động không hợp lệ.',
        ];
    }
}
