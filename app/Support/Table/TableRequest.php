<?php

namespace App\Support\Table;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base abstracta del contrato de tablas (frontend.md §3.1).
 * Whitelists de sort/filter en subclases.
 *
 * Export: `?export=xlsx|csv|pdf` sobre el mismo endpoint; sin paginación UI.
 * Cap de filas: {@see self::EXPORT_MAX_ROWS} (fail 422 si se supera).
 */
abstract class TableRequest extends FormRequest
{
    /** Máximo de filas en export sync (xlsx/csv/pdf). */
    public const EXPORT_MAX_ROWS = 5000;

    /** Operadores de filter[] admitidos (whitelist). */
    public const ALLOWED_FILTER_TYPES = [
        '=',
        'eq',
        '!=',
        'neq',
        'like',
        'in',
        'date_between',
    ];

    /** @var list<string> */
    protected array $sortable = [];

    /** @var list<string> */
    protected array $filterable = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['filter', 'sort'] as $key) {
            $value = $this->input($key);

            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);

                if (is_array($decoded)) {
                    $this->merge([$key => $decoded]);
                }
            }
        }
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
            'export' => ['sometimes', 'string', 'in:xlsx,csv,pdf'],
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

    public function exportFormat(): ?string
    {
        $format = $this->input('export');

        return is_string($format) && $format !== '' ? $format : null;
    }

    public function wantsExport(): bool
    {
        return $this->exportFormat() !== null;
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
                && in_array($item['field'], $this->filterable, true)
                && in_array($item['type'], self::ALLOWED_FILTER_TYPES, true)
                && $this->isValidFilterValue($item),
        ));
    }

    /**
     * @param  array{field?: string, type?: string, value?: mixed}  $item
     */
    private function isValidFilterValue(array $item): bool
    {
        if (($item['type'] ?? null) !== 'date_between') {
            return true;
        }

        $value = $item['value'] ?? null;

        if (! is_array($value)) {
            return false;
        }

        $from = $value['from'] ?? null;
        $to = $value['to'] ?? null;

        $fromOk = $from === null || $from === '' || $this->isIsoDay($from);
        $toOk = $to === null || $to === '' || $this->isIsoDay($to);

        if (! $fromOk || ! $toOk) {
            return false;
        }

        return ($from !== null && $from !== '') || ($to !== null && $to !== '');
    }

    private function isIsoDay(mixed $value): bool
    {
        return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
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
