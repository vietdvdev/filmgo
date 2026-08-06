<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\PointsTransaction;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    protected RewardService $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * Xem điểm, hạng thành viên và danh sách phần thưởng
     */
    public function index()
    {
        $user = Auth::user();
        $rewards = Reward::where('quantity', '>', 0)->get();
        $transactions = $user->pointsTransactions()->latest()->take(10)->get();
        $userRewards = $user->userRewards()->with('reward')->latest()->take(10)->get();

        // Trả về view hoặc JSON (vì không có view cụ thể, ở đây giả định trả về JSON/API để front-end xử lý hoặc trả về mảng dữ liệu cho view nếu có)
        return response()->json([
            'user' => [
                'points' => $user->points,
                'membership_tier' => $user->membership_tier,
            ],
            'rewards' => $rewards,
            'recent_transactions' => $transactions,
            'my_rewards' => $userRewards,
        ]);
    }

    /**
     * Lịch sử giao dịch điểm (phân trang)
     */
    public function history()
    {
        $user = Auth::user();
        $transactions = $user->pointsTransactions()->latest()->paginate(15);
        
        return response()->json($transactions);
    }

    /**
     * Đổi quà tặng
     */
    public function redeem(Request $request, $rewardId)
    {
        $user = Auth::user();
        $reward = Reward::findOrFail($rewardId);

        try {
            $userReward = $this->rewardService->redeemReward($user, $reward);
            return response()->json([
                'success' => true,
                'message' => 'Đổi quà thành công!',
                'data' => $userReward
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
