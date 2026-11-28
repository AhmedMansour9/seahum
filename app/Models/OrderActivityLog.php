<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One changed order field: who changed it, from where, and the value before and after.
 *
 * @property int $id
 * @property int $order_id
 * @property string $field
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string $actor_type admin, vendor, delivery_man, customer, guest or system
 * @property int|null $actor_id
 * @property string|null $actor_name
 * @property string $source admin_panel, vendor_panel, delivery_app, vendor_app, customer_app, website or system
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 */
class OrderActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'field',
        'old_value',
        'new_value',
        'actor_type',
        'actor_id',
        'actor_name',
        'source',
        'ip_address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'actor_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
