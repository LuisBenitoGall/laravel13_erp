<?php

namespace App\Support\Table;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base abstracta del contrato de tablas (frontend.md §3.1).
 * Whitelists de sort/filter en subclases.
 */
abstract class TableRequest extends FormRequest
{
    /** @var list<string> */
    protected array $sortable = [];

    /** @var list<string> */
    protected array $filterable = [];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'sort' => ['sometimes', 'array'],
            'sort.*.field' => ['required_with:sort', 'string'],
            'sort.*.dir' => ['required_with:sort', 'in:asc,desc'],
            'filter' => ['sometimes', 'array'],
            'filter.*.field' => ['required_with:filter', 'string'],
            'filter.*.type' => ['required_with:filter', 'string'],
            'filter.*.value' => ['nullable'],
            'export' => ['sometimes', 'string', 'in:xlsx,csv'],
        ];
    }

    public function page(): int
    {
        return (int) $this->input('page', 1);
    }

    public function size(): int
    {
        return (int) $this->input('size', 25);
    }

    /**
     * @return list<array{field: string, dir: string}>
     */
    public function sorts(): array
    {
        /** @var list<array{field?: string, dir?: string}> $sort */
        $sort = $this->input('sort', []);

        return array_values(array_filter(
            $sort,
            fn (array $item): bool => isset($item['field'])
                && in_array($item['field'], $this->sortable, true)
                && isset($item['dir'])
                && in_array($item['dir'], ['asc', 'desc'], true),
        ));
    }

    /**
     * @return list<array{field: string, type: string, value: mixed}>
     */
    public function filters(): array
    {
        /** @var list<array{field?: string, type?: string, value?: mixed}> $filter */
        $filter = $this->input('filter', []);

        return array_values(array_filter(
            $filter,
            fn (array $item): bool => isset($item['field'], $item['type'])
                && in_array($item['field'], $this->filterable, true),
        ));
    }

    /** @return list<string> */
    public function sortableFields(): array
    {
        return $this->sortable;
    }

    /** @return list<string> */
    public function filterableFields(): array
    {
        return $this->filterable;
    }
}
