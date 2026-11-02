export type TableFilter = {
    field: string;
    type: string;
    value: unknown;
};

/** Cómo combina el modal con los filtros de cabecera. No hay un tercer modo. */
export type AdvancedFilterMode = 'merge' | 'replace';

export type TableQuery = {
    page?: number | string;
    size?: number | string;
    sort?: unknown;
    exportFormat?: 'xlsx' | 'pdf';
};

export function isActiveTableFilter(filter: TableFilter): boolean {
    return filter.value !== '' && filter.value !== null && filter.value !== undefined;
}

/**
 * `advancedFilters === undefined` o sin modo: solo cabecera (el modal no ha escrito).
 * `merge`: cabecera y luego modal, en el mismo array (un field repetido se envía dos veces).
 * `replace`: solo la lista del modal, aunque esté vacía.
 */
export function tableFiltersForRequest(
    headerFilters: TableFilter[],
    advancedFilters: TableFilter[] | undefined,
    mode: AdvancedFilterMode | undefined,
): TableFilter[] {
    const header = headerFilters.filter(isActiveTableFilter);

    if (advancedFilters === undefined || mode === undefined) {
        return header;
    }

    const advanced = advancedFilters.filter(isActiveTableFilter);

    if (mode === 'replace') {
        return advanced;
    }

    return [...header, ...advanced];
}

export function buildTableEndpointUrl(
    endpoint: string,
    query: TableQuery,
    filters: TableFilter[],
    origin: string,
): string {
    const url = new URL(endpoint, origin);

    if (query.exportFormat !== undefined) {
        url.searchParams.set('export', query.exportFormat);
    }

    if (query.page !== undefined) {
        url.searchParams.set('page', String(query.page));
    }

    if (query.size !== undefined) {
        url.searchParams.set('size', String(query.size));
    }

    if (Array.isArray(query.sort) && query.sort.length > 0) {
        url.searchParams.set('sort', JSON.stringify(query.sort));
    }

    const active = filters.filter(isActiveTableFilter);

    if (active.length > 0) {
        url.searchParams.set('filter', JSON.stringify(active));
    }

    return `${url.pathname}${url.search}`;
}
