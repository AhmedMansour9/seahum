<?php

namespace App\Models;

use App\Enums\LuckyWheel\LuckyWheelRewardType;
use App\Traits\StorageTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property LuckyWheelRewardType $type
 * @property string|null $image
 * @property string|null $image_storage_type
 * @property bool $is_active
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class LuckyWheelReward extends Model
{
    use SoftDeletes, StorageTrait;

    public const IMAGE_DIRECTORY = 'lucky-wheel';

    protected $fillable = [
        'name',
        'type',
        'image',
        'image_storage_type',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'type' => LuckyWheelRewardType::class,
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @return array<string, string> Locale code => reward name, the base name stored under "en".
     */
    public function getNameTranslations(): array
    {
        $nameTranslations = ['en' => $this->name];
        foreach ($this->translations->where('key', 'name') as $translation) {
            $nameTranslations[$translation->locale] = $translation->value;
        }
        return $nameTranslations;
    }

    public function getLocalizedName(): string
    {
        return $this->getNameTranslations()[getDefaultLanguage()] ?? $this->name;
    }

    public function getImageFullUrlAttribute(): array
    {
        return $this->storageLink(self::IMAGE_DIRECTORY, $this->image, $this->image_storage_type ?? 'public');
    }
}
