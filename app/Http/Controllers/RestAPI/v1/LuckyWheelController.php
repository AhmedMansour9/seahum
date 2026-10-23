<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Exceptions\LuckyWheelSpinException;
use App\Http\Controllers\Controller;
use App\Http\Resources\LuckyWheel\LuckyWheelRewardResource;
use App\Http\Resources\LuckyWheel\LuckyWheelWonGiftResource;
use App\Models\LuckyWheelWonGift;
use App\Services\LuckyWheelService;
use App\Utils\CartManager;
use App\Utils\CustomerManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LuckyWheelController extends Controller
{
    public function __construct(
        private readonly LuckyWheelService $luckyWheelService,
    )
    {
    }

    public function getStatus(Request $request): JsonResponse
    {
        return response()->json($this->formatStatus(status: $this->luckyWheelService->getCustomerStatus(customerId: $request->user()->id)), 200);
    }

    public function spin(Request $request): JsonResponse
    {
        try {
            $spin = $this->luckyWheelService->spin(customerId: $request->user()->id);
        } catch (LuckyWheelSpinException $exception) {
            return response()->json([
                'reason' => $exception->reason,
                'message' => translate($exception->reason),
            ], 403);
        }

        $status = $this->luckyWheelService->getCustomerStatus(customerId: $request->user()->id);

        return response()->json([
            'outcome_type' => $spin->outcome_type->value,
            'reward' => LuckyWheelRewardResource::make($spin->reward)->resolve(),
            'reward_index' => $status['rewards']->search(fn ($reward) => $reward->id === $spin->reward_id),
            'won_gift' => $spin->wonGift ? LuckyWheelWonGiftResource::make($spin->wonGift)->resolve() : null,
            'status' => $this->formatStatus(status: $status),
        ], 200);
    }

    public function getWonGifts(Request $request): JsonResponse
    {
        $wonGifts = LuckyWheelWonGift::with('order:id,order_status')
            ->where('customer_id', $request->user()->id)
            ->when($request->get('status') === 'pending', fn ($query) => $query->pending())
            ->latest('id')
            ->paginate(min($request->integer('limit', 10), 50), ['*'], 'page', $request->integer('offset', 1));

        return response()->json([
            'total_size' => $wonGifts->total(),
            'limit' => $wonGifts->perPage(),
            'offset' => $wonGifts->currentPage(),
            'gifts' => LuckyWheelWonGiftResource::collection($wonGifts->items()),
        ], 200);
    }

    /**
     * Resources are resolved before translate() runs, because translate() switches the app locale.
     */
    /**
     * Gift minimum preview for the checked cart. The coupon is applied only when the order is placed,
     * where the same rule is re-evaluated on the stored order amounts.
     */
    public function getCheckoutSummary(Request $request): JsonResponse
    {
        $customer = $request->user();
        if ($this->luckyWheelService->getPendingGifts(customerId: $customer->id)->isEmpty()) {
            return response()->json(['lucky_wheel' => null], 200);
        }

        $checkoutGiftSummary = $this->luckyWheelService->getCheckoutGiftSummary(
            customerId: $customer->id,
            itemSubtotal: CartManager::getCartGrandTotalWithoutShippingCharge(type: 'checked'),
            productDiscount: 0,
            couponDiscount: 0,
            referralDiscount: CustomerManager::getReferralDiscountAmount(user: $customer, couponDiscount: 0),
        );

        if ($checkoutGiftSummary) {
            $checkoutGiftSummary['pending_gifts'] = LuckyWheelWonGiftResource::collection($checkoutGiftSummary['pending_gifts'])->resolve();
        }
        return response()->json(['lucky_wheel' => $checkoutGiftSummary], 200);
    }

    private function formatStatus(array $status): array
    {
        $status['rewards'] = LuckyWheelRewardResource::collection($status['rewards'])->resolve();
        $status['reason_message'] = $status['reason'] ? translate($status['reason']) : null;
        return $status;
    }
}
