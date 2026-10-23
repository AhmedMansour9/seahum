<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\LuckyWheelSpinException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LuckyWheel\LuckyWheelRewardResource;
use App\Http\Resources\LuckyWheel\LuckyWheelWonGiftResource;
use App\Models\LuckyWheelWonGift;
use App\Services\LuckyWheelService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class LuckyWheelController extends Controller
{
    public function __construct(
        private readonly LuckyWheelService $luckyWheelService,
    )
    {
    }

    public function index(): View
    {
        abort_unless(isset(VIEW_FILE_NAMES['lucky_wheel']), 404);

        $customerId = auth('customer')->id();
        $wonGifts = LuckyWheelWonGift::with('order:id,order_status')
            ->where('customer_id', $customerId)
            ->latest('id')
            ->paginate(10);

        return view(VIEW_FILE_NAMES['lucky_wheel'], [
            'wheelStatus' => $this->luckyWheelService->getCustomerStatus(customerId: $customerId),
            'wonGifts' => $wonGifts,
        ]);
    }

    public function spin(): JsonResponse
    {
        $customerId = auth('customer')->id();
        try {
            $spin = $this->luckyWheelService->spin(customerId: $customerId);
        } catch (LuckyWheelSpinException $exception) {
            return response()->json(['reason' => $exception->reason, 'message' => translate($exception->reason)], 403);
        }

        $eligibleRewards = $this->luckyWheelService->getCustomerStatus(customerId: $customerId)['rewards'];

        return response()->json([
            'outcome_type' => $spin->outcome_type->value,
            'reward' => LuckyWheelRewardResource::make($spin->reward)->resolve(),
            'reward_index' => $eligibleRewards->search(fn ($reward) => $reward->id === $spin->reward_id),
            'won_gift' => $spin->wonGift ? LuckyWheelWonGiftResource::make($spin->wonGift)->resolve() : null,
        ]);
    }
}
