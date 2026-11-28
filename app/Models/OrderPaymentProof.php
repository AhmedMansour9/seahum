<?php

namespace App\Models;

use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A screenshot of the customer's transfer, uploaded when an order is marked paid online.
 *
 * @property int $id
 * @property int $order_id
 * @property string $image
 * @property string $storage
 * @property string $uploaded_by_type
 * @property int|null $uploaded_by_id
 * @property string|null $uploaded_by_name
 */
class OrderPaymentProof extends Model
{
    use StorageTrait;

    public const IMAGE_DIRECTORY = 'order/payment-proof';

    protected $fillable = [
        'order_id',
        'image',
        'storage',
        'uploaded_by_type',
        'uploaded_by_id',
        'uploaded_by_name',
    ];

    protected $appends = ['image_full_url'];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'uploaded_by_id' => 'integer',
        ];
    }

    /**
     * @return array<string, mixed>|string|null
     */
    public function getImageFullUrlAttribute(): array|string|null
    {
        return $this->storageLink(self::IMAGE_DIRECTORY, $this->image, $this->storage ?: 'public');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
