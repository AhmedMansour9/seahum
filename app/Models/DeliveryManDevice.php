<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One logged-in delivery app install: its own session token and push token.
 *
 * @property int $id
 * @property int $delivery_man_id
 * @property string $auth_token
 * @property string|null $fcm_token
 */
class DeliveryManDevice extends Model
{
    protected $hidden = ['auth_token'];

    protected $fillable = [
        'delivery_man_id',
        'auth_token',
        'fcm_token',
    ];

    protected $casts = [
        'id' => 'integer',
        'delivery_man_id' => 'integer',
        'auth_token' => 'string',
        'fcm_token' => 'string',
    ];

    public function deliveryMan(): BelongsTo
    {
        return $this->belongsTo(DeliveryMan::class);
    }
}
