<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Services\ComboService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComboController extends Controller
{
    protected ComboService $comboService;

    /**
     * ComboController constructor.
     *
     * @param ComboService $comboService
     */
    public function __construct(ComboService $comboService)
    {
        $this->comboService = $comboService;
    }

    /**
     * Hiển thị danh sách combo.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $combos = $this->comboService->getPaginatedCombos($search, 10);

        return view('admin.combos.index', compact('combos'));
    }

    /**
     * Hiển thị form tạo mới combo.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.combos.create');
    }

    /**
     * Lưu trữ combo mới vào database.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'combo_name'    => 'required|string|max:255',
            'price'         => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status'        => 'required|in:active,inactive',
        ], [
            'combo_name.required' => 'Tên combo không được để trống.',
            'combo_name.max'      => 'Tên combo không được vượt quá 255 ký tự.',
            'price.required'      => 'Giá bán không được để trống.',
            'price.integer'       => 'Giá bán phải là số nguyên.',
            'price.min'           => 'Giá bán không được nhỏ hơn 0 ₫.',
            'image.image'         => 'File upload phải là hình ảnh.',
            'image.mimes'         => 'Ảnh chỉ chấp nhận định dạng jpeg, png, jpg, webp.',
            'image.max'           => 'Dung lượng ảnh không được vượt quá 2MB.',
            'status.required'     => 'Trạng thái hoạt động không được để trống.',
            'status.in'           => 'Trạng thái hoạt động không hợp lệ.',
        ]);

        $this->comboService->createCombo(
            $request->only('combo_name', 'price', 'description', 'status'),
            $request->file('image')
        );

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Thêm combo bắp nước thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa combo.
     *
     * @param Combo $combo
     * @return View
     */
    public function edit(Combo $combo): View
    {
        return view('admin.combos.edit', compact('combo'));
    }

    /**
     * Cập nhật thông tin combo trong database.
     *
     * @param Request $request
     * @param Combo $combo
     * @return RedirectResponse
     */
    public function update(Request $request, Combo $combo): RedirectResponse
    {
        $request->validate([
            'combo_name'    => 'required|string|max:255',
            'price'         => 'required|integer|min:0',
            'description'   => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status'        => 'required|in:active,inactive',
        ], [
            'combo_name.required' => 'Tên combo không được để trống.',
            'combo_name.max'      => 'Tên combo không được vượt quá 255 ký tự.',
            'price.required'      => 'Giá bán không được để trống.',
            'price.integer'       => 'Giá bán phải là số nguyên.',
            'price.min'           => 'Giá bán không được nhỏ hơn 0 ₫.',
            'image.image'         => 'File upload phải là hình ảnh.',
            'image.mimes'         => 'Ảnh chỉ chấp nhận định dạng jpeg, png, jpg, webp.',
            'image.max'           => 'Dung lượng ảnh không được vượt quá 2MB.',
            'status.required'     => 'Trạng thái hoạt động không được để trống.',
            'status.in'           => 'Trạng thái hoạt động không hợp lệ.',
        ]);

        $this->comboService->updateCombo(
            $combo,
            $request->only('combo_name', 'price', 'description', 'status'),
            $request->file('image'),
            $request->boolean('remove_image')
        );

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Cập nhật combo bắp nước thành công!');
    }

    /**
     * Xóa combo (xóa mềm).
     *
     * @param Combo $combo
     * @return RedirectResponse
     */
    public function destroy(Combo $combo): RedirectResponse
    {
        $this->comboService->deleteCombo($combo);

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Xóa combo bắp nước thành công!');
    }
}
