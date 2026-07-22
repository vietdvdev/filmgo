<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionService
{
    /**
     * Lấy danh sách mã khuyến mãi có phân trang và tìm kiếm theo code.
     *
     * @param string|null $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPromotionsPaginated(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Promotion::query();

        if (!empty($search)) {
            $query->where('code', 'like', '%' . trim($search) . '%');
        }

        return $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Tạo mới mã khuyến mãi.
     *
     * @param array $data
     * @return Promotion
     */
    public function createPromotion(array $data): Promotion
    {
        $formattedData = $this->formatPromotionData($data);
        return Promotion::create($formattedData);
    }

    /**
     * Cập nhật thông tin mã khuyến mãi.
     *
     * @param Promotion $promotion
     * @param array $data
     * @return bool
     */
    public function updatePromotion(Promotion $promotion, array $data): bool
    {
        $formattedData = $this->formatPromotionData($data);
        return $promotion->update($formattedData);
    }

    /**
     * Xóa mã khuyến mãi (xóa mềm).
     *
     * @param Promotion $promotion
     * @return bool|null
     */
    public function deletePromotion(Promotion $promotion): ?bool
    {
        return $promotion->delete();
    }

    /**
     * Chuẩn hóa dữ liệu trước khi lưu/cập nhật.
     *
     * @param array $data
     * @return array
     */
    protected function formatPromotionData(array $data): array
    {
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        // Đảm bảo max_discount_amount = null nếu loại giảm giá là tiền mặt cố định (fixed)
        if (isset($data['discount_type']) && $data['discount_type'] === 'fixed') {
            $data['max_discount_amount'] = null;
        }

        return $data;
    }
}
