<?php

namespace App\Models\Builders;

use App\Services\OrderActivityLogService;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;

/**
 * Every order update in the app goes through here, both Order::where(...)->update([...]) calls
 * and model saves, so changes to tracked fields are logged with who made them. The affected
 * rows are read just before the update to know the old values.
 */
class OrderQueryBuilder extends Builder
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function update(array $values): int
    {
        $activityLog = app(OrderActivityLogService::class);
        $trackedValues = array_filter($values, fn ($value) => ! $value instanceof Expression);
        $trackedFields = $activityLog->trackedFieldsIn($trackedValues);

        if (empty($trackedFields)) {
            return parent::update($values);
        }

        $columns = array_map(fn (string $column) => $this->getModel()->qualifyColumn($column), ['id', ...$trackedFields]);
        $ordersBefore = (clone $this)->toBase()->select($columns)->get();

        $affectedRows = parent::update($values);

        if ($affectedRows > 0) {
            $activityLog->recordChanges(ordersBefore: $ordersBefore, values: $trackedValues);
        }

        return $affectedRows;
    }
}
