<?php

namespace App\Http\Resources\LuckyWheel;

use App\Models\LuckyWheelWonGift;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LuckyWheelWonGift
 */
class LuckyWheelWonGiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->getLocalizedName(),
            'image_full_url' => $this->image_full_url,
            'awarded_at' => $this->awarded_at?->toIso8601String(),
            'status' => $this->order_id ? 'attached_to_order' : 'pending',
            'order_id' => $this->order_id,
            'order_status' => $this->whenLoaded('order', fn () => $this->order?->order_status),
        ];
    }
}
