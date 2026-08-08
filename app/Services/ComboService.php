<?php

namespace App\Services;

use App\Models\Combo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComboService
{
    /**
     * Lấy danh sách các Combo có phân trang và tìm kiếm (kèm eager loading items).
     */
    public function getPaginatedCombos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Combo::with('items');

        if (!empty($search)) {
            $query->where('combo_name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Kiểm tra tổ hợp thành phần (id + số lượng) có bị trùng với Combo khác không.
     *
     * @param array  $items         dạng [combo_item_id => quantity, ...]
     * @param int|null $ignoreId    Bỏ qua Combo có ID này (dùng khi update)
     * @return bool                 true = trùng lặp
     */
    public function isDuplicateItemStructure(array $items, ?int $ignoreId = null): bool
    {
        // Chuẩn hoá input thành [id => qty] đã lọc qty > 0
        $input = [];
        foreach ($items as $key => $val) {
            if (is_array($val) && isset($val['id']) && isset($val['quantity']) && intval($val['quantity']) > 0) {
                $input[intval($val['id'])] = intval($val['quantity']);
            } elseif (is_numeric($key) && intval($val) > 0) {
                $input[intval($key)] = intval($val);
            }
        }

        if (empty($input)) {
            return false;
        }

        ksort($input);

        // So sánh với tất cả các Combo khác
        $query = Combo::with('items');
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        foreach ($query->get() as $combo) {
            $existing = [];
            foreach ($combo->items as $item) {
                $existing[$item->id] = $item->pivot->quantity;
            }
            ksort($existing);

            if ($input === $existing) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tạo một Combo mới, xử lý upload hình ảnh và liên kết thành phần bắp nước.
     */
    public function createCombo(array $data, ?UploadedFile $image = null, array $items = []): Combo
    {
        if ($image) {
            $path = $image->store('combos', 'public');
            $data['image'] = $path;
        }

        $combo = Combo::create($data);

        $this->syncComboItems($combo, $items);

        return $combo;
    }

    /**
     * Cập nhật thông tin Combo, xử lý cập nhật hình ảnh và thành phần bắp nước.
     */
    public function updateCombo(Combo $combo, array $data, ?UploadedFile $image = null, bool $removeImage = false, array $items = []): bool
    {
        if ($image) {
            $this->deleteImageFile($combo->image);
            $path = $image->store('combos', 'public');
            $data['image'] = $path;
        } elseif ($removeImage) {
            $this->deleteImageFile($combo->image);
            $data['image'] = null;
        }

        $updated = $combo->update($data);

        $this->syncComboItems($combo, $items);

        return $updated;
    }

    /**
     * Đồng bộ danh sách các thành phần vào Combo.
     */
    public function syncComboItems(Combo $combo, array $items): void
    {
        $syncData = [];

        foreach ($items as $key => $val) {
            if (is_array($val) && isset($val['id']) && isset($val['quantity']) && intval($val['quantity']) > 0) {
                $syncData[intval($val['id'])] = ['quantity' => intval($val['quantity'])];
            } elseif (is_numeric($key) && intval($val) > 0) {
                $syncData[intval($key)] = ['quantity' => intval($val)];
            }
        }

        $combo->items()->sync($syncData);
    }

    /**
     * Xóa mềm Combo.
     */
    public function deleteCombo(Combo $combo): ?bool
    {
        return $combo->delete();
    }

    /**
     * Xóa tệp tin hình ảnh khỏi disk.
     */
    private function deleteImageFile(?string $imagePath): void
    {
        if ($imagePath) {
            $relativePath = ltrim(preg_replace('/^storage\//i', '', $imagePath), '/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}
