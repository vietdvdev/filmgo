<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LuckyWheelPrize;
use App\Services\LuckyWheelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LuckyWheelController extends Controller
{
    protected LuckyWheelService $luckyWheelService;

    public function __construct(LuckyWheelService $luckyWheelService)
    {
        $this->luckyWheelService = $luckyWheelService;
    }

    /**
     * Lấy danh sách các ô thưởng trên vòng quay
     */
    public function index()
    {
        $prizes = LuckyWheelPrize::all();
        return response()->json([
            'success' => true,
            'data' => $prizes
        ]);
    }

    /**
     * Thực hiện quay số
     */
    public function spin(Request $request)
    {
        $user = Auth::user();

        try {
            $result = $this->luckyWheelService->spin($user);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
