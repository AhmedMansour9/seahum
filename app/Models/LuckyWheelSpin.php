<?php

namespace App\Models;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Enums\LuckyWheel\LuckyWheelSpinFrequency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $reward_id
 * @property LuckyWheelRewardType $outcome_type
 * @property string $spin_date Business date (Y-m-d) in the store timezone
 * @property LuckyWheelSpinFrequency $spin_frequency
 * @property array $reward_snapshot
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LuckyWheelSpin extends Model
{
    protected $fillable = [
        'customer_id',
        'reward_id',
        'outcome_type',
        'spin_date',
        'spin_frequency',
        'reward_snapshot',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'reward_id' => 'integer',
        'outcome_type' => LuckyWheelRewardType::class,
        'spin_frequency' => LuckyWheelSpinFrequency::class,
        'reward_snapshot' => 'array',
    ];

    public function reward(): BelongsTo
    {
        return $this->belongsTo(LuckyWheelReward::class, 'reward_id')->withTrashed();
    }

    public function wonGift(): HasOne
    {
        return $this->hasOne(LuckyWheelWonGift::class, 'spin_id');
    }
}
