<?php

namespace App\Support\Table;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator as ConcretePaginator;

/**
 * Aplica sort/filter/paginación whitelistados sobre un query tenant-scoped.
 */
class TableBuilder
{
    /** @var Builder<covariant Model>|null */
    private ?Builder $query = null;

    /** @var list<string> */
    private array $searchable = [];

    private ?TableRequest $request = null;

    /** @var class-string<JsonResource>|null */
    private ?string $resource = null;

    /**
     * @param  Builder<covariant Model>  $query
     */
    public function for(Builder $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param  list<string>  $columns
     */
    public function searchable(array $columns): self
    {
        $this->searchable = $columns;

        return $this;
    }

    public function request(TableRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param  class-string<JsonResource>  $resource
     */
    public function through(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function json(): JsonResponse
    {
        $paginator = $this->paginate();
        $items = $paginator->items();

        if ($this->resource !== null) {
            $data = $this->resource::collection(collect($items))->resolve();
        } else {
            $data = collect($items)
                ->map(static fn (Model $item): array => $item->toArray())
                ->all();
        }

        return response()->json([
            'data' => $data,
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Model>
     */
    public function paginate(): LengthAwarePaginator
    {
        if ($this->request === null || $this->query === null) {
            throw new \RuntimeException('TableBuilder requires for() and request().');
        }

        $this->applyFilters();
        $this->applySorts();

        /** @var ConcretePaginator<int, Model> $paginator */
        $paginator = $this->query->paginate(
            perPage: $this->request->size(),
            page: $this->request->page(),
        );

        return $paginator;
    }

    private function applyFilters(): void
    {
        if ($this->request === null || $this->query === null) {
            return;
        }

        foreach ($this->request->filters() as $filter) {
            $field = $filter['field'];
            $type = $filter['type'];
            $value = $filter['value'] ?? null;

            if ($field === 'search' && $this->searchable !== []) {
                $this->query->where(function (Builder $q) use ($value): void {
                    foreach ($this->searchable as $column) {
                        $q->orWhere($column, 'like', '%'.(string) $value.'%');
                    }
                });

                continue;
            }

            match ($type) {
                '=', 'eq' => $this->query->where($field, '=', $value),
                '!=', 'neq' => $this->query->where($field, '!=', $value),
                'like' => $this->query->where($field, 'like', '%'.(string) $value.'%'),
                'in' => $this->query->whereIn($field, (array) $value),
                default => null,
            };
        }
    }

    private function applySorts(): void
    {
        if ($this->request === null || $this->query === null) {
            return;
        }

        foreach ($this->request->sorts() as $sort) {
            $this->query->orderBy($sort['field'], $sort['dir']);
        }
    }
}
