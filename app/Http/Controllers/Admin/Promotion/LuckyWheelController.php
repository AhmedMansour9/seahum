<?php

namespace App\Http\Controllers\Admin\Promotion;

use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use App\Contracts\Repositories\LuckyWheelRewardRepositoryInterface;
use App\Contracts\Repositories\TranslationRepositoryInterface;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\LuckyWheelRewardRequest;
use App\Http\Requests\Admin\LuckyWheelSettingsRequest;
use App\Models\LuckyWheelReward;
use App\Services\LuckyWheelRewardService;
use App\Services\LuckyWheelService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LuckyWheelController extends BaseController
{
    public function __construct(
        private readonly LuckyWheelRewardRepositoryInterface $luckyWheelRewardRepo,
        private readonly BusinessSettingRepositoryInterface  $businessSettingRepo,
        private readonly TranslationRepositoryInterface      $translationRepo,
        private readonly LuckyWheelService                   $luckyWheelService,
        private readonly LuckyWheelRewardService             $luckyWheelRewardService,
    )
    {
    }

    public function index(?Request $request, ?string $type = null): View
    {
        $rewards = $this->luckyWheelRewardRepo->getListWhere(
            orderBy: ['display_order' => 'asc', 'id' => 'asc'],
            searchValue: $request->get('searchValue'),
            relations: ['translations'],
            dataLimit: getWebConfig(name: 'pagination_limit'),
        );
        return view('admin-views.lucky-wheel.index', [
            'rewards' => $rewards,
            'isEnabled' => $this->luckyWheelService->isEnabled(),
            'currentSpinFrequency' => $this->luckyWheelService->getSpinFrequency(),
            'minimumOrderAmount' => usdToDefaultCurrency(amount: (float)$this->luckyWheelService->getMinimumOrderAmount()),
        ]);
    }

    public function updateSettings(LuckyWheelSettingsRequest $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: LuckyWheelService::STATUS_SETTING, value: $request->boolean('is_enabled') ? 1 : 0);
        $this->businessSettingRepo->updateOrInsert(type: LuckyWheelService::SPIN_FREQUENCY_SETTING, value: $request['spin_frequency']);
        $this->businessSettingRepo->updateOrInsert(
            type: LuckyWheelService::MINIMUM_ORDER_AMOUNT_SETTING,
            value: number_format(currencyConverter(amount: (float)$request['minimum_order_amount']), 2, '.', ''),
        );
        ToastMagic::success(translate('lucky_wheel_settings_updated_successfully'));
        return redirect()->route('admin.lucky-wheel.index');
    }

    public function getAddView(): View
    {
        return view('admin-views.lucky-wheel.reward-form', [
            'reward' => null,
            'language' => getWebConfig(name: 'pnc_language') ?? ['en'],
        ]);
    }

    public function add(LuckyWheelRewardRequest $request): RedirectResponse
    {
        $reward = $this->luckyWheelRewardRepo->add(data: $this->luckyWheelRewardService->getAddData(request: $request));
        $this->translationRepo->add(request: $request, model: LuckyWheelReward::class, id: $reward->id);
        ToastMagic::success(translate('lucky_wheel_reward_added_successfully'));
        return redirect()->route('admin.lucky-wheel.index');
    }

    public function getUpdateView(string|int $id): View|RedirectResponse
    {
        $reward = $this->luckyWheelRewardRepo->getFirstWhere(params: ['id' => $id], relations: ['translations']);
        if (!$reward) {
            ToastMagic::error(translate('lucky_wheel_reward_not_found'));
            return redirect()->route('admin.lucky-wheel.index');
        }

        return view('admin-views.lucky-wheel.reward-form', [
            'reward' => $reward,
            'language' => getWebConfig(name: 'pnc_language') ?? ['en'],
        ]);
    }

    public function update(LuckyWheelRewardRequest $request, string|int $id): RedirectResponse
    {
        $reward = $this->luckyWheelRewardRepo->getFirstWhere(params: ['id' => $id]);
        if (!$reward) {
            ToastMagic::error(translate('lucky_wheel_reward_not_found'));
            return redirect()->route('admin.lucky-wheel.index');
        }

        $this->luckyWheelRewardRepo->update(id: $reward->id, data: $this->luckyWheelRewardService->getUpdateData(request: $request, reward: $reward));
        $this->translationRepo->update(request: $request, model: LuckyWheelReward::class, id: $reward->id);
        ToastMagic::success(translate('lucky_wheel_reward_updated_successfully'));
        return redirect()->route('admin.lucky-wheel.index');
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $this->luckyWheelRewardRepo->update(id: (string)$request['id'], data: ['is_active' => $request->boolean('status')]);
        return response()->json(['success' => 1, 'message' => translate('status_updated_successfully')], 200);
    }

    public function delete(Request $request): RedirectResponse
    {
        $this->luckyWheelRewardRepo->delete(params: ['id' => $request['id']]);
        ToastMagic::success(translate('lucky_wheel_reward_deleted_successfully'));
        return redirect()->route('admin.lucky-wheel.index');
    }
}
