<?php

namespace App\Repositories;

use App\Contracts\Repositories\LuckyWheelRewardRepositoryInterface;
use App\Models\LuckyWheelReward;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class LuckyWheelRewardRepository implements LuckyWheelRewardRepositoryInterface
{
    public function __construct(
        private readonly LuckyWheelReward $luckyWheelReward,
    )
    {
    }

    public function add(array $data): string|object
    {
        return $this->luckyWheelReward->create($data);
    }

    public function getFirstWhere(array $params, array $relations = []): ?Model
    {
        return $this->luckyWheelReward->where($params)->with($relations)->first();
    }

    public function getList(array $orderBy = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->getListWhere(orderBy: $orderBy, relations: $relations, dataLimit: $dataLimit, offset: $offset);
    }

    public function getListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator
    {
        $query = $this->luckyWheelReward->with($relations)
            ->when($searchValue, function ($query) use ($searchValue) {
                return $query->where('name', 'like', "%$searchValue%");
            })
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                return $query->where('is_active', $filters['is_active']);
            })
            ->when(!empty($orderBy), function ($query) use ($orderBy) {
                foreach ($orderBy as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
                return $query;
            });

        $filters += ['searchValue' => $searchValue];
        return $dataLimit == 'all' ? $query->get() : $query->paginate($dataLimit, ['*'], 'page', $offset)->appends($filters);
    }

    public function update(string $id, array $data): bool
    {
        return (bool)$this->luckyWheelReward->where('id', $id)->update($data);
    }

    public function delete(array $params): bool
    {
        $this->luckyWheelReward->where($params)->delete();
        return true;
    }
}
