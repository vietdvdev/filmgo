<?php

namespace App\Services;

use App\Models\User;
use App\Models\PointsTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class LoyaltyService
{
    /**
     * Tính điểm dựa trên số tiền giao dịch
     * Tỷ lệ: 10,000 VND = 1 điểm
     *
     * @param float $amount
     * @return int
     */
    public function calculatePointsFromAmount($amount): int
    {
        return (int) floor($amount / 10000);
    }

    /**
     * Thêm điểm cho người dùng và ghi nhận giao dịch
     *
     * @param User $user
     * @param int $points
     * @param string $description
     * @return void
     * @throws Exception
     */
    public function addPoints(User $user, int $points, string $description = ''): void
    {
        if ($points <= 0) {
            return;
        }

        DB::beginTransaction();
        try {
            // Thêm điểm cho user
            $user->points += $points;
            // Kiểm tra nâng hạng thành viên
            $user->checkAndUpdateTier();
            $user->save();

            // Ghi nhận lịch sử giao dịch
            PointsTransaction::create([
                'user_id' => $user->id,
                'amount' => $points,
                'type' => 'earn',
                'description' => $description,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Trừ điểm của người dùng (khi đổi quà)
     *
     * @param User $user
     * @param int $points
     * @param string $description
     * @return void
     * @throws Exception
     */
    public function deductPoints(User $user, int $points, string $description = ''): void
    {
        if ($points <= 0) {
            return;
        }

        if ($user->points < $points) {
            throw new Exception('Người dùng không đủ điểm để thực hiện giao dịch này.');
        }

        DB::beginTransaction();
        try {
            // Trừ điểm của user
            $user->points -= $points;
            // Cập nhật hạng
            $user->checkAndUpdateTier();
            $user->save();

            // Ghi nhận lịch sử giao dịch
            PointsTransaction::create([
                'user_id' => $user->id,
                'amount' => -$points, // Lưu số âm để biết là trừ điểm
                'type' => 'redeem',
                'description' => $description,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
