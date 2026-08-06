<?php

namespace App\Services;

use App\Models\User;
use App\Models\LuckyWheelPrize;
use App\Models\WheelSpin;
use App\Models\Reward;
use App\Models\UserReward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class LuckyWheelService
{
    protected LoyaltyService $loyaltyService;

    public function __construct(LoyaltyService $loyaltyService)
    {
        $this->loyaltyService = $loyaltyService;
    }

    /**
     * Xử lý quay vòng quay may mắn
     * Thuật toán: Random theo tỷ lệ phần trăm (probability)
     *
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function spin(User $user): array
    {
        // 1. (Tùy chọn) Kiểm tra điều kiện quay: VD trừ điểm để quay (giả sử tốn 50 điểm/lần)
        // Nếu dự án có yêu cầu trừ điểm, un-comment đoạn code dưới:
        // if ($user->points < 50) {
        //     throw new Exception('Bạn không đủ 50 điểm để tham gia vòng quay.');
        // }
        
        DB::beginTransaction();
        try {
            // $this->loyaltyService->deductPoints($user, 50, 'Tham gia vòng quay may mắn');

            // 2. Lấy danh sách giải thưởng
            $prizes = LuckyWheelPrize::where('quantity', '>', 0)
                                    ->orWhere('quantity', 0)->where('type', 'points') // Điểm thường không giới hạn số lượng
                                    ->get();

            if ($prizes->isEmpty()) {
                throw new Exception('Hiện không có giải thưởng nào khả dụng.');
            }

            // 3. Thuật toán chọn giải ngẫu nhiên theo tỷ lệ phần trăm
            $winningPrize = $this->calculateWinningPrize($prizes);

            // 4. Ghi lại lịch sử quay
            $spin = WheelSpin::create([
                'user_id' => $user->id,
                'prize_id' => $winningPrize ? $winningPrize->id : null,
            ]);

            $resultMessage = 'Chúc bạn may mắn lần sau!';
            $rewardCode = null;

            // 5. Trả thưởng nếu trúng
            if ($winningPrize) {
                if ($winningPrize->type === 'points') {
                    $this->loyaltyService->addPoints($user, (int) $winningPrize->value, 'Trúng thưởng vòng quay may mắn');
                    $resultMessage = "Chúc mừng! Bạn đã trúng {$winningPrize->value} điểm.";
                } elseif ($winningPrize->type === 'reward') {
                    $reward = Reward::find($winningPrize->value);
                    if ($reward) {
                        // Tạo UserReward
                        $code = strtoupper(Str::random(10));
                        UserReward::create([
                            'user_id' => $user->id,
                            'reward_id' => $reward->id,
                            'code' => $code,
                            'is_used' => false,
                        ]);
                        $resultMessage = "Chúc mừng! Bạn đã trúng phần quà: {$reward->name}.";
                        $rewardCode = $code;

                        // Giảm số lượng nếu có giới hạn
                        if ($winningPrize->quantity > 0) {
                            $winningPrize->quantity -= 1;
                            $winningPrize->save();
                        }
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'prize' => $winningPrize,
                'message' => $resultMessage,
                'reward_code' => $rewardCode
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Thuật toán tính toán giải thưởng trúng dựa trên probability
     * 
     * @param \Illuminate\Database\Eloquent\Collection $prizes
     * @return LuckyWheelPrize|null
     */
    private function calculateWinningPrize($prizes)
    {
        // Tính tổng tỉ lệ (nếu tổng < 100 thì phần còn lại là trượt)
        $rand = mt_rand(1, 10000) / 100; // Số ngẫu nhiên từ 0.01 đến 100.00
        $currentProb = 0;

        foreach ($prizes as $prize) {
            $currentProb += $prize->probability;
            if ($rand <= $currentProb) {
                return $prize; // Trúng giải này
            }
        }

        return null; // Trượt (nếu tổng probability của tất cả giải thưởng < 100)
    }
}
