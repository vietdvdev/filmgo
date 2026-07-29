<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\ComboItem;
use App\Services\ComboService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ComboController extends Controller
{
    protected ComboService $comboService;

    public function __construct(ComboService $comboService)
    {
        $this->comboService = $comboService;
    }

    /**
     * Hiển thị danh sách combo.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $combos = $this->comboService->getPaginatedCombos($search, 10);

        return view('admin.combos.index', compact('combos'));
    }

    /**
     * Hiển thị form tạo mới combo.
     */
    public function create(): View
    {
        $comboItems = ComboItem::where('status', 'active')->orderBy('name')->get();

        return view('admin.combos.create', compact('comboItems'));
    }

    /**
     * Lưu trữ combo mới vào database.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'combo_name'       => ['required', 'string', 'max:255', Rule::unique('combos', 'combo_name')->whereNull('deleted_at')],
            'price'            => 'required|integer|min:0',
            'description'      => 'nullable|string',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status'           => 'required|in:active,inactive',
            'items'            => 'nullable|array',
            'items.*.id'       => 'nullable|exists:combo_items,id',
            'items.*.quantity' => 'nullable|integer|min:1',
        ], [
            'combo_name.required' => 'Tên combo không được để trống.',
            'combo_name.max'      => 'Tên combo không được vượt quá 255 ký tự.',
            'combo_name.unique'   => 'Tên combo này đã tồn tại, vui lòng chọn tên khác.',
            'price.required'      => 'Giá bán thực tế không được để trống.',
            'price.integer'       => 'Giá bán thực tế phải là số nguyên.',
            'price.min'           => 'Giá bán thực tế không được nhỏ hơn 0 ₫.',
            'image.image'         => 'File upload phải là hình ảnh.',
            'image.mimes'         => 'Ảnh chỉ chấp nhận định dạng jpeg, png, jpg, webp.',
            'image.max'           => 'Dung lượng ảnh không được vượt quá 2MB.',
            'status.required'     => 'Trạng thái hoạt động không được để trống.',
            'status.in'           => 'Trạng thái hoạt động không hợp lệ.',
        ]);

        $items = $request->input('items', []);

        // Kiểm tra tổ hợp thành phần trùng lặp với Combo khác
        if ($this->comboService->isDuplicateItemStructure($items)) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Tổ hợp thành phần này đã tồn tại trong một Combo khác. Vui lòng thay đổi thành phần hoặc số lượng.']);
        }

        $this->comboService->createCombo(
            $request->only('combo_name', 'price', 'description', 'status'),
            $request->file('image'),
            $items
        );

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Thêm combo bắp nước thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa combo.
     */
    public function edit(Combo $combo): View
    {
        $combo->load('items');
        $comboItems = ComboItem::where('status', 'active')->orderBy('name')->get();

        return view('admin.combos.edit', compact('combo', 'comboItems'));
    }

    /**
     * Cập nhật thông tin combo trong database.
     */
    public function update(Request $request, Combo $combo): RedirectResponse
    {
        $request->validate([
            'combo_name'       => ['required', 'string', 'max:255', Rule::unique('combos', 'combo_name')->ignore($combo->id)->whereNull('deleted_at')],
            'price'            => 'required|integer|min:0',
            'description'      => 'nullable|string',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status'           => 'required|in:active,inactive',
            'items'            => 'nullable|array',
            'items.*.id'       => 'nullable|exists:combo_items,id',
            'items.*.quantity' => 'nullable|integer|min:1',
        ], [
            'combo_name.required' => 'Tên combo không được để trống.',
            'combo_name.max'      => 'Tên combo không được vượt quá 255 ký tự.',
            'combo_name.unique'   => 'Tên combo này đã tồn tại, vui lòng chọn tên khác.',
            'price.required'      => 'Giá bán thực tế không được để trống.',
            'price.integer'       => 'Giá bán thực tế phải là số nguyên.',
            'price.min'           => 'Giá bán thực tế không được nhỏ hơn 0 ₫.',
            'image.image'         => 'File upload phải là hình ảnh.',
            'image.mimes'         => 'Ảnh chỉ chấp nhận định dạng jpeg, png, jpg, webp.',
            'image.max'           => 'Dung lượng ảnh không được vượt quá 2MB.',
            'status.required'     => 'Trạng thái hoạt động không được để trống.',
            'status.in'           => 'Trạng thái hoạt động không hợp lệ.',
        ]);

        $items = $request->input('items', []);

        // Kiểm tra tổ hợp thành phần trùng lặp với Combo khác (bỏ qua Combo hiện tại)
        if ($this->comboService->isDuplicateItemStructure($items, $combo->id)) {
            return back()
                ->withInput()
                ->withErrors(['items' => 'Tổ hợp thành phần này đã tồn tại trong một Combo khác. Vui lòng thay đổi thành phần hoặc số lượng.']);
        }

        $this->comboService->updateCombo(
            $combo,
            $request->only('combo_name', 'price', 'description', 'status'),
            $request->file('image'),
            $request->boolean('remove_image'),
            $items
        );

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Cập nhật combo bắp nước thành công!');
    }

    /**
     * Xóa combo (xóa mềm).
     */
    public function destroy(Combo $combo): RedirectResponse
    {
        $this->comboService->deleteCombo($combo);

        return redirect()
            ->route('admin.combos.index')
            ->with('success', 'Xóa combo bắp nước thành công!');
    }
}
