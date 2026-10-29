export type TablePersistedState = {
    hiddenColumns: string[];
    pageSize: number;
};

export function tableStorageKey(persistKey: string, userId: number | string | null | undefined): string {
    const uid = userId ?? 'anon';

    return `erp.table.${persistKey}.${uid}`;
}

export function readTableState(persistKey: string, userId: number | string | null | undefined): TablePersistedState | null {
    if (typeof window === 'undefined' || !persistKey) {
        return null;
    }

    try {
        const raw = window.localStorage.getItem(tableStorageKey(persistKey, userId));

        if (!raw) {
            return null;
        }

        const parsed = JSON.parse(raw) as Partial<TablePersistedState>;

        return {
            hiddenColumns: Array.isArray(parsed.hiddenColumns)
                ? parsed.hiddenColumns.filter((v): v is string => typeof v === 'string')
                : [],
            pageSize: typeof parsed.pageSize === 'number' && parsed.pageSize > 0 ? parsed.pageSize : 25,
        };
    } catch {
        return null;
    }
}

export function writeTableState(
    persistKey: string,
    userId: number | string | null | undefined,
    state: TablePersistedState,
): void {
    if (typeof window === 'undefined' || !persistKey) {
        return;
    }

    window.localStorage.setItem(tableStorageKey(persistKey, userId), JSON.stringify(state));
}
