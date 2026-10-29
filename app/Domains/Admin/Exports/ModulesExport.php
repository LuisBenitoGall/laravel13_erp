<?php

namespace App\Domains\Admin\Exports;

use App\Domains\Admin\Models\Module;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements FromCollection<int, Module>
 * @implements WithMapping<mixed>
 */
class ModulesExport implements FromCollection, WithHeadings, WithMapping
{
    /** @param  list<Module>  $rows */
    public function __construct(
        private readonly array $rows,
    ) {}

    /** @return Enumerable<int, Module> */
    public function collection(): Enumerable
    {
        return collect($this->rows);
    }

    /** @return list<string> */
    public function headings(): array
    {
        return [
            __('nombre'),
            __('color'),
            __('icono'),
            __('nivel'),
            __('creado'),
            __('estado'),
        ];
    }

    public function map(mixed $row): array
    {
        if (! $row instanceof Module) {
            throw new \InvalidArgumentException('ModulesExport expects Module rows.');
        }

        return [
            $row->name,
            $row->color,
            $row->icon,
            $row->level->value,
            $row->created_at?->toDateTimeString(),
            $row->status->value,
        ];
    }
}
