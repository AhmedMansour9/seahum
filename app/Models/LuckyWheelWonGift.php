<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A gift won on the lucky wheel. Name and image are snapshots taken at award time,
 * so later reward edits or deletion never change what the customer won.
 *
 * @property int $id
 * @property int $customer_id
 * @property int $spin_id
 * @property int|null $reward_id
 * @property string $name
 * @property array|null $name_translations
 * @property string|null $image
 * @property string|null $image_storage_type
 * @property int|null $order_id
 * @property Carbon $awarded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LuckyWheelWonGift extends Model
{
    use StorageTrait;

    protected $fillable = [
        'customer_id',
        'spin_id',
        'reward_id',
        'name',
        'name_translations',
        'image',
        'image_storage_type',
        'order_id',
        'awarded_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'spin_id' => 'integer',
        'reward_id' => 'integer',
        'name_translations' => 'array',
        'order_id' => 'integer',
        'awarded_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function spin(): BelongsTo
    {
        return $this->belongsTo(LuckyWheelSpin::class, 'spin_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('order_id');
    }

    public function getLocalizedName(): string
    {
        return $this->name_translations[getDefaultLanguage()] ?? $this->name;
    }

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink(LuckyWheelReward::IMAGE_DIRECTORY, $this->image, $this->image_storage_type ?? 'public');
    }
}
