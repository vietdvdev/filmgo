<?php

namespace App\Services;

use App\Models\Combo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ComboService
{
    /**
     * Lấy danh sách các Combo có phân trang và tìm kiếm.
     *
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedCombos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Combo::query();

        if (!empty($search)) {
            $query->where('combo_name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Tạo một Combo mới và xử lý upload hình ảnh.
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Combo
     */
    public function createCombo(array $data, ?UploadedFile $image = null): Combo
    {
        if ($image) {
            $path = $image->store('combos', 'public');
            $data['image'] = 'storage/' . $path;
        }

        return Combo::create($data);
    }

    /**
     * Cập nhật thông tin Combo và xử lý cập nhật hình ảnh.
     *
     * @param Combo $combo
     * @param array $data
     * @param UploadedFile|null $image
     * @param bool $removeImage
     * @return bool
     */
    public function updateCombo(Combo $combo, array $data, ?UploadedFile $image = null, bool $removeImage = false): bool
    {
        if ($image) {
            // Xóa ảnh cũ nếu có
            $this->deleteImageFile($combo->image);

            $path = $image->store('combos', 'public');
            $data['image'] = 'storage/' . $path;
        } elseif ($removeImage) {
            // Xóa ảnh cũ nếu người dùng yêu cầu xóa ảnh
            $this->deleteImageFile($combo->image);
            $data['image'] = null;
        }

        return $combo->update($data);
    }

    /**
     * Xóa mềm Combo.
     *
     * @param Combo $combo
     * @return bool|null
     */
    public function deleteCombo(Combo $combo): ?bool
    {
        // Vì sử dụng SoftDeletes, không xóa file ảnh trên disk ngay lập tức
        // phòng trường hợp khôi phục (restore) lại combo.
        return $combo->delete();
    }

    /**
     * Xóa tệp tin hình ảnh khỏi disk.
     *
     * @param string|null $imagePath
     * @return void
     */
    private function deleteImageFile(?string $imagePath): void
    {
        if ($imagePath && str_starts_with($imagePath, 'storage/')) {
            $relativePath = str_replace('storage/', '', $imagePath);
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }
    }
}
