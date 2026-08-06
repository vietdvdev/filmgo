<?php

namespace App\Services;

use App\Models\User;
use App\Models\Reward;
use App\Models\UserReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class RewardService
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Xử lý đổi quà
     *
     * @param User $user
     * @param Reward $reward
     * @return UserReward
     * @throws Exception
     */
    public function redeemReward(User $user, Reward $reward): UserReward
    {
        // 1. Kiểm tra số lượng quà
        if ($reward->quantity <= 0) {
            throw new Exception('Quà tặng này đã hết số lượng.');
        }

        // 2. Kiểm tra điểm của user
        if ($user->points < $reward->points_required) {
            throw new Exception('Bạn không đủ điểm để đổi quà tặng này.');
        }

        DB::beginTransaction();
        try {
            // 3. Trừ điểm người dùng thông qua LoyaltyService
            $this->loyaltyService->deductPoints($user, $reward->points_required, "Đổi quà tặng: {$reward->name}");

            // 4. Giảm số lượng quà
            $reward->quantity -= 1;
            $reward->save();

            // 5. Tạo mã voucher (UserReward)
            $code = $this->generateUniqueCode();
            
            $userReward = UserReward::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'code' => $code,
                'is_used' => false,
            ]);

            DB::commit();

            return $userReward;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Tạo mã voucher ngẫu nhiên và duy nhất
     *
     * @return string
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (UserReward::where('code', $code)->exists());

        return $code;
    }
}
