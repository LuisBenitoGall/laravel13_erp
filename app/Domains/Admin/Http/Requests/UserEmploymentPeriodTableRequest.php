<?php

namespace App\Domains\Admin\Http\Requests;

use App\Support\Table\TableRequest;

class UserEmploymentPeriodTableRequest extends TableRequest
{
    protected array $sortable = [
        'start_date',
        'end_date',
        'weekly_hours',
        'year_salary',
        'job_title',
        'id',
    ];

    protected array $filterable = [
        'company_name',
        'workplace_name',
        'contract_type_name',
        'job_title',
        'start_date',
        'end_date',
        'search',
    ];

    /**
     * @return list<array{field: string, type: string, value: mixed}>
     */
    public function filters(): array
    {
        $map = [
            'company_name' => 'companies.name',
            'workplace_name' => 'workplaces.name',
            'contract_type_name' => 'contract_types.name',
            'job_title' => 'employees.job_title',
            'start_date' => 'employees.start_date',
            'end_date' => 'employees.end_date',
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
