<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComboItem;
use App\Services\ComboItemService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComboItemController extends Controller
{
    protected ComboItemService $comboItemService;

    public function __construct(ComboItemService $comboItemService)
    {
        $this->comboItemService = $comboItemService;
    }

    /**
     * Danh sách món thành phần bắp nước.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $items = $this->comboItemService->getPaginatedItems($search, 15);

        return view('admin.combo_items.index', compact('items'));
    }

    /**
     * Thêm mới thành phần bắp nước.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255|unique:combo_items,name',
            'type'   => 'required|in:popcorn,drink,snack,other',
            'unit'   => 'required|string|max:50',
            'price'  => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required'  => 'Tên thành phần không được để trống.',
            'name.unique'    => 'Tên thành phần này đã tồn tại.',
            'type.required'  => 'Loại thành phần không được để trống.',
            'unit.required font'  => 'Đơn vị tính không được để trống.',
            'price.required' => 'Đơn giá không được để trống.',
            'price.integer'  => 'Đơn giá phải là số nguyên.',
            'price.min'      => 'Đơn giá không được nhỏ hơn 0 ₫.',
        ]);

        $this->comboItemService->createItem($validated);

        return redirect()
            ->route('admin.combo-items.index')
            ->with('success', 'Thêm món thành phần thành công!');
    }

    /**
     * Cập nhật món thành phần.
     */
    public function update(Request $request, ComboItem $comboItem): RedirectResponse
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255|unique:combo_items,name,' . $comboItem->id,
            'type'   => 'required|in:popcorn,drink,snack,other',
            'unit'   => 'required|string|max:50',
            'price'  => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ], [
            'name.required'  => 'Tên thành phần không được để trống.',
            'name.unique'    => 'Tên thành phần này đã tồn tại.',
            'type.required'  => 'Loại thành phần không được để trống.',
            'unit.required'  => 'Đơn vị tính không được để trống.',
            'price.required' => 'Đơn giá không được để trống.',
            'price.integer'  => 'Đơn giá phải là số nguyên.',
            'price.min'      => 'Đơn giá không được nhỏ hơn 0 ₫.',
        ]);

        $this->comboItemService->updateItem($comboItem, $validated);

        return redirect()
            ->route('admin.combo-items.index')
            ->with('success', 'Cập nhật món thành phần thành công!');
    }

    /**
     * Bật/tắt trạng thái kinh doanh.
     */
    public function toggleStatus(ComboItem $comboItem): RedirectResponse
    {
        $this->comboItemService->toggleStatus($comboItem);

        return redirect()
            ->route('admin.combo-items.index')
            ->with('success', 'Đã thay đổi trạng thái hoạt động!');
    }

    /**
     * Xóa thành phần.
     */
    public function destroy(ComboItem $comboItem): RedirectResponse
    {
        $this->comboItemService->deleteItem($comboItem);

        return redirect()
            ->route('admin.combo-items.index')
            ->with('success', 'Xóa món thành phần thành công!');
    }
}
