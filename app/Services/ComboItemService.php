<?php

namespace App\Services;

use App\Models\ComboItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ComboItemService
{
    /**
     * Lấy danh sách thành phần bắp nước có phân trang và tìm kiếm.
     */
    public function getPaginatedItems(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = ComboItem::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Lấy tất cả thành phần đang hoạt động.
     */
    public function getActiveItems(): Collection
    {
        return ComboItem::where('status', 'active')->orderBy('name')->get();
    }

    /**
     * Tạo thành phần bắp nước mới.
     */
    public function createItem(array $data): ComboItem
    {
        return ComboItem::create($data);
    }

    /**
     * Cập nhật thông tin thành phần bắp nước.
     */
    public function updateItem(ComboItem $item, array $data): bool
    {
        return $item->update($data);
    }

    /**
     * Đổi trạng thái kinh doanh thành phần bắp nước.
     */
    public function toggleStatus(ComboItem $item): bool
    {
        $newStatus = $item->status === 'active' ? 'inactive' : 'active';
        return $item->update(['status' => $newStatus]);
    }

    /**
     * Xóa thành phần bắp nước.
     */
    public function deleteItem(ComboItem $item): ?bool
    {
        return $item->delete();
    }
}
