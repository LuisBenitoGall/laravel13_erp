<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class UserCompanyTableRequest extends TableRequest
{
    protected array $sortable = [
        'position',
        'department',
        'created_at',
        'id',
    ];

    protected array $filterable = [
        'company_name',
        'position',
        'department',
        'created_at',
        'search',
    ];

    /**
     * Front fields → columnas SQL cualificadas (join con companies).
     *
     * @return list<array{field: string, type: string, value: mixed}>
     */
    public function filters(): array
    {
        $map = [
            'company_name' => 'companies.name',
            'position' => 'user_companies.position',
            'department' => 'user_companies.department',
            'created_at' => 'user_companies.created_at',
        ];

        return array_map(
            static function (array $filter) use ($map): array {
                $field = $filter['field'];
                if (isset($map[$field])) {
                    $filter['field'] = $map[$field];
                }

                return $filter;
            },
            parent::filters(),
        );
    }
}
