<?php

namespace App\Services;

use App\Models\LuckyWheelReward;
use App\Models\LuckyWheelWonGift;
use App\Traits\FileManagerTrait;

class LuckyWheelRewardService
{
    use FileManagerTrait;

    private const IMAGE_DIRECTORY = LuckyWheelReward::IMAGE_DIRECTORY.'/';

    public function getAddData(object $request): array
    {
        return [
            ...$this->getCommonData(request: $request),
            'image' => $request->file('image') ? $this->upload(self::IMAGE_DIRECTORY, 'webp', $request->file('image')) : null,
            'image_storage_type' => $request->file('image') ? $this->getStorageType() : null,
        ];
    }

    public function getUpdateData(object $request, LuckyWheelReward $reward): array
    {
        if (!$request->file('image')) {
            return $this->getCommonData(request: $request);
        }

        $this->deleteImageIfUnused(reward: $reward);
        return [
            ...$this->getCommonData(request: $request),
            'image' => $this->upload(self::IMAGE_DIRECTORY, 'webp', $request->file('image')),
            'image_storage_type' => $this->getStorageType(),
        ];
    }

    /**
     * Won gifts keep pointing at the image they were awarded with, so it is only removed once nothing else references it.
     */
    public function deleteImageIfUnused(LuckyWheelReward $reward): void
    {
        if (!$reward->image) {
            return;
        }

        $isReferenced = LuckyWheelWonGift::where('image', $reward->image)->exists()
            || LuckyWheelReward::withTrashed()->where('image', $reward->image)->whereKeyNot($reward->id)->exists();
        if (!$isReferenced) {
            $this->delete(filePath: self::IMAGE_DIRECTORY.$reward->image);
        }
    }

    private function getCommonData(object $request): array
    {
        return [
            'name' => $request['name'][(int)array_search('en', $request['lang'])],
            'type' => $request['type'],
            'display_order' => (int)$request['display_order'],
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function getStorageType(): string
    {
        return config('filesystems.disks.default') ?? 'public';
    }
}
