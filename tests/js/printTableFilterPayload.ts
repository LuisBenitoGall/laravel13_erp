import {
    buildTableEndpointUrl,
    tableFiltersForRequest,
    type TableFilter,
} from '../../resources/js/Components/Table/tableFilters.ts';

const origin = 'http://localhost';
const endpoint = '/admin/modules/table';

const header: TableFilter[] = [
    { field: 'status', type: '=', value: 'active' },
    { field: 'name', type: 'like', value: 'viejo' },
];

const advanced: TableFilter[] = [{ field: 'name', type: 'like', value: 'acme' }];

function url(
    filters: TableFilter[],
    query: { page?: number; size?: number; exportFormat?: 'xlsx' | 'pdf' },
): string {
    return buildTableEndpointUrl(endpoint, query, filters, origin);
}

const page = { page: 1, size: 25 };

console.log(
    JSON.stringify({
        merge: url(tableFiltersForRequest(header, advanced, 'merge'), page),
        replace: url(tableFiltersForRequest(header, advanced, 'replace'), page),
        export: url(tableFiltersForRequest(header, advanced, 'merge'), { exportFormat: 'xlsx' }),
        exportSorted: buildTableEndpointUrl(
            endpoint,
            {
                exportFormat: 'xlsx',
                sort: [{ field: 'name', dir: 'desc' }],
            },
            tableFiltersForRequest(header, advanced, 'merge'),
            origin,
        ),
        headerOnly: url(tableFiltersForRequest(header, undefined, 'merge'), page),
        replaceEmpty: url(tableFiltersForRequest(header, [], 'replace'), page),
        droppedEmpty: url(
            tableFiltersForRequest(header, [{ field: 'color', type: 'like', value: '' }], 'merge'),
            page,
        ),
    }),
);
