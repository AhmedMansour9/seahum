<?php

namespace App\Http\Resources\LuckyWheel;

use App\Models\LuckyWheelReward;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LuckyWheelReward
 */
class LuckyWheelRewardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getLocalizedName(),
            'type' => $this->type->value,
            'image_full_url' => $this->image_full_url,
            'display_order' => $this->display_order,
        ];
    }
}
