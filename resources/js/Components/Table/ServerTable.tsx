import { usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    useCallback,
    useEffect,
    useMemo,
    useRef,
    useState,
    type ReactNode,
} from 'react';
import { useTranslation } from 'react-i18next';
import {
    TabulatorFull as Tabulator,
    type CellComponent,
    type ColumnDefinition,
} from 'tabulator-tables';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import {
    createDateRangeHeaderFilter,
    isActiveDateRangeValue,
} from '@/Components/Table/dateRangeHeaderFilter';
import { Button } from '@/Components/ui/button';
import { readTableState, writeTableState } from '@/lib/tablePersistence';
import { cn } from '@/lib/utils';
import '../../../css/tabulator-erp.css';

export type ColumnDef = ColumnDefinition & {
    field?: string;
    headerFilterKind?: 'string' | 'select' | 'date' | 'number';
    headerFilterValues?: Record<string, string>;
    /**
     * Tipo semántico del valor. Fechas y números se alinean a la derecha
     * (hozAlign) si la columna no declara `hozAlign` explícito.
     * `headerFilterKind: 'date'|'number'` implica el valueKind correspondiente.
     */
    valueKind?: 'string' | 'date' | 'number';
};

export type RowAction<T = Record<string, unknown>> = {
    id: string;
    labelKey: string;
    /** default = botón icono; toggle = switch a la izquierda (activar/desactivar). */
    kind?: 'button' | 'toggle';
    icon?: LucideIcon;
    onClick?: (row: T) => void;
    /**
     * Toggle: estado actual. Tras éxito puede devolver patch de fila
     * (p. ej. `{ status: 'active' }`) para actualizar Tabulator sin recarga.
     */
    checked?: boolean;
    onToggle?: (
        row: T,
        checked: boolean,
    ) => void | Promise<void | Partial<T> | Record<string, unknown>>;
    confirm?: boolean | { titleKey?: string };
    variant?: 'default' | 'danger';
};

export type TableFilter = {
    field: string;
    type: string;
    value: unknown;
};

type AuthPage = {
    auth: { user: { id: number; name: string; email: string } | null };
};

const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

function filterTypeForKind(kind: ColumnDef['headerFilterKind']): string {
    if (kind === 'date') {
        return 'date_between';
    }

    if (kind === 'select' || kind === 'number') {
        return '=';
    }

    return 'like';
}

function resolvedValueKind(col: ColumnDef): ColumnDef['valueKind'] {
    if (col.valueKind) {
        return col.valueKind;
    }

    if (col.headerFilterKind === 'date') {
        return 'date';
    }

    if (col.headerFilterKind === 'number') {
        return 'number';
    }

    return undefined;
}

function isRightAlignedValue(col: ColumnDef): boolean {
    const kind = resolvedValueKind(col);

    return kind === 'date' || kind === 'number';
}

function buildTabulatorLang(t: (key: string) => string): Record<string, unknown> {
    const ui = {
        columns: {
            name: t('nombre'),
        },
        data: {
            loading: t('cargando'),
            error: t('error_carga'),
        },
        pagination: {
            page_size: t('mostrar_registros'),
            page_title: t('mostrar'),
            first: t('primera'),
            first_title: t('primera_pagina'),
            last: t('ultima'),
            last_title: t('ultima_pagina'),
            prev: t('anterior'),
            prev_title: t('pagina_anterior'),
            next: t('siguiente'),
            next_title: t('pagina_siguiente'),
            all: t('todos'),
            counter: {
                showing: t('mostrando'),
                of: t('de'),
                rows: t('filas'),
                pages: t('paginas'),
            },
        },
        headerFilters: {
            default: t('filtrar_columna'),
        },
    };

    return {
        default: ui,
        es: ui,
        en: ui,
        ca: ui,
    };
}

export default function ServerTable<T extends object = Record<string, unknown>>({
    endpoint,
    columns,
    persistKey,
    exportable = false,
    rowActions,
    pageSize: initialPageSize = 25,
    className,
    toolbarExtra,
}: {
    endpoint: string;
    columns: ColumnDef[];
    persistKey?: string;
    exportable?: boolean;
    rowActions?: (row: T) => Array<RowAction<T> | false | null | undefined>;
    pageSize?: number;
    className?: string;
    toolbarExtra?: ReactNode;
}) {
    const { t, i18n } = useTranslation();
    const { auth } = usePage<AuthPage>().props;
    const userId = auth.user?.id ?? null;
    const ref = useRef<HTMLDivElement>(null);
    const tableRef = useRef<Tabulator | null>(null);
    const filtersRef = useRef<TableFilter[]>([]);

    const persisted = useMemo(
        () => (persistKey ? readTableState(persistKey, userId) : null),
        [persistKey, userId],
    );

    const [pageSize, setPageSize] = useState(persisted?.pageSize ?? initialPageSize);
    const [hiddenColumns, setHiddenColumns] = useState<string[]>(persisted?.hiddenColumns ?? []);
    const [columnsOpen, setColumnsOpen] = useState(false);
    const [pendingDelete, setPendingDelete] = useState<{
        titleKey: string;
        onConfirm: () => void;
    } | null>(null);

    const dataColumns = useMemo(
        () => columns.filter((c) => c.field && c.field !== 'acciones'),
        [columns],
    );

    const persist = useCallback(
        (nextHidden: string[], nextSize: number) => {
            if (!persistKey) {
                return;
            }

            writeTableState(persistKey, userId, {
                hiddenColumns: nextHidden,
                pageSize: nextSize,
            });
        },
        [persistKey, userId],
    );

    const buildAjaxUrl = useCallback(
        (params: Record<string, unknown>) => {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('page', String(params.page ?? 1));
            url.searchParams.set('size', String(params.size ?? pageSize));

            if (Array.isArray(params.sort) && params.sort.length > 0) {
                url.searchParams.set('sort', JSON.stringify(params.sort));
            }

            const filters = filtersRef.current.filter(
                (f) => f.value !== '' && f.value !== null && f.value !== undefined,
            );

            if (filters.length > 0) {
                url.searchParams.set('filter', JSON.stringify(filters));
            }

            return `${url.pathname}${url.search}`;
        },
        [endpoint, pageSize],
    );

    const exportDownload = useCallback(
        (format: 'xlsx' | 'pdf') => {
            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('export', format);
            const filters = filtersRef.current.filter(
                (f) => f.value !== '' && f.value !== null && f.value !== undefined,
            );

            if (filters.length > 0) {
                url.searchParams.set('filter', JSON.stringify(filters));
            }

            window.location.assign(`${url.pathname}${url.search}`);
        },
        [endpoint],
    );

    const tabulatorColumns = useMemo((): ColumnDefinition[] => {
        const cols: ColumnDefinition[] = dataColumns.map((col) => {
            const field = col.field as string;
            const kind = col.headerFilterKind;
            const definition: ColumnDefinition = {
                ...col,
                visible: !hiddenColumns.includes(field),
            };

            if (col.hozAlign === undefined && isRightAlignedValue(col)) {
                definition.hozAlign = 'right';
                definition.headerHozAlign = col.headerHozAlign ?? 'right';
            }

            if (kind === 'string') {
                definition.headerFilter = 'input';
                definition.headerFilterPlaceholder = t('filtrar_columna');
            } else if (kind === 'select' && col.headerFilterValues) {
                definition.headerFilter = 'list';
                definition.headerFilterParams = {
                    values: { '': t('todos'), ...col.headerFilterValues },
                    clearable: true,
                };
            } else if (kind === 'date') {
                // Tabulator 6.5: editor nativo `date` = un solo día (y Luxon opcional).
                // Rango desde–hasta → custom headerFilter (dos input[type=date]).
                definition.headerFilter = createDateRangeHeaderFilter({
                    fromLabel: t('desde'),
                    toLabel: t('hasta'),
                    applyLabel: t('aplicar'),
                    clearLabel: t('limpiar'),
                    emptyLabel: t('filtrar_columna'),
                    locale: i18n.language,
                });
                definition.headerFilterLiveFilter = false;
            } else if (kind === 'number') {
                definition.headerFilter = 'input';
                definition.headerFilterPlaceholder = t('filtrar_columna');
            }

            return definition;
        });

        if (rowActions) {
            cols.push({
                title: t('acciones'),
                field: 'acciones',
                headerSort: false,
                hozAlign: 'right',
                headerHozAlign: 'center',
                width: 130,
                cssClass: 'tabulator-actions-col',
                formatter: (cell: CellComponent) => {
                    const rowData = cell.getRow().getData() as T;
                    const actions = (rowActions?.(rowData) ?? []).filter(
                        (a): a is RowAction<T> => Boolean(a),
                    );
                    const toggles = actions.filter((a) => a.kind === 'toggle');
                    const buttons = actions.filter((a) => a.kind !== 'toggle');
                    const ordered = [
                        ...toggles,
                        ...buttons.filter((a) => a.variant !== 'danger'),
                        ...buttons.filter((a) => a.variant === 'danger'),
                    ];

                    const wrap = document.createElement('div');
                    wrap.className = 'tabulator-actions-wrap';

                    for (const action of ordered) {
                        if (action.kind === 'toggle') {
                            const label = document.createElement('label');
                            label.className = 'tabulator-actions-toggle';
                            label.title = t(action.labelKey);
                            const input = document.createElement('input');
                            input.type = 'checkbox';
                            input.checked = Boolean(action.checked);
                            input.setAttribute('aria-label', t(action.labelKey));
                            input.addEventListener('click', (event) => {
                                event.stopPropagation();
                            });
                            input.addEventListener('change', () => {
                                void (async () => {
                                    const next = input.checked;
                                    const previous = !next;
                                    input.disabled = true;

                                    try {
                                        const patch = await action.onToggle?.(rowData, next);
                                        if (patch && typeof patch === 'object') {
                                            await cell.getRow().update(patch);
                                        }
                                    } catch {
                                        input.checked = previous;
                                    } finally {
                                        input.disabled = false;
                                    }
                                })();
                            });
                            label.appendChild(input);
                            wrap.appendChild(label);

                            continue;
                        }

                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.title = t(action.labelKey);
                        btn.setAttribute('aria-label', t(action.labelKey));
                        btn.className = cn(
                            'tabulator-actions-btn',
                            action.variant === 'danger' && 'tabulator-actions-btn-danger',
                        );
                        btn.innerHTML =
                            action.variant === 'danger'
                                ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>'
                                : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>';
                        btn.addEventListener('click', (event) => {
                            event.stopPropagation();

                            if (action.confirm) {
                                const titleKey =
                                    typeof action.confirm === 'object'
                                        ? (action.confirm.titleKey ?? 'confirmar_eliminar')
                                        : 'confirmar_eliminar';
                                setPendingDelete({
                                    titleKey,
                                    onConfirm: () => action.onClick?.(rowData),
                                });

                                return;
                            }

                            action.onClick?.(rowData);
                        });
                        wrap.appendChild(btn);
                    }

                    return wrap;
                },
            });
        }

        return cols;
    }, [dataColumns, hiddenColumns, rowActions, t, i18n.language]);

    useEffect(() => {
        if (!ref.current) {
            return;
        }

        const langs = buildTabulatorLang(t);

        const table = new Tabulator(ref.current, {
            layout: 'fitColumns',
            pagination: true,
            paginationMode: 'remote',
            paginationSize: pageSize,
            filterMode: 'remote',
            sortMode: 'remote',
            ajaxURL: endpoint,
            ajaxConfig: 'GET',
            columns: tabulatorColumns,
            langs,
            locale: true,
            ajaxURLGenerator: (_url, _config, params) => {
                const sort = Array.isArray(params.sort)
                    ? params.sort.map((s: { field: string; dir: string }) => ({
                          field: s.field,
                          dir: s.dir,
                      }))
                    : [];

                const rawFilters = Array.isArray(params.filter) ? params.filter : [];
                filtersRef.current = rawFilters.flatMap(
                    (filter: { field: string; value: unknown }) => {
                        const col = dataColumns.find((c) => c.field === filter.field);
                        const kind = col?.headerFilterKind;

                        if (kind === 'date') {
                            if (!isActiveDateRangeValue(filter.value)) {
                                return [];
                            }

                            return [
                                {
                                    field: filter.field,
                                    type: 'date_between',
                                    value: {
                                        from: filter.value.from || null,
                                        to: filter.value.to || null,
                                    },
                                },
                            ];
                        }

                        if (
                            filter.value === '' ||
                            filter.value === null ||
                            filter.value === undefined
                        ) {
                            return [];
                        }

                        return [
                            {
                                field: filter.field,
                                type: filterTypeForKind(kind),
                                value: filter.value,
                            },
                        ];
                    },
                );

                return buildAjaxUrl({
                    page: params.page,
                    size: params.size,
                    sort,
                });
            },
            ajaxResponse: (_url, _params, response) => ({
                data: response.data,
                last_page: response.last_page,
            }),
        });

        tableRef.current = table;

        return () => {
            table.destroy();
            tableRef.current = null;
        };
        // Recreate on structural changes; pageSize handled via setPageSize below.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [endpoint, tabulatorColumns, buildAjaxUrl, t, i18n.language, dataColumns]);

    useEffect(() => {
        tableRef.current?.setPageSize(pageSize);
    }, [pageSize]);

    useEffect(() => {
        const table = tableRef.current;

        if (!table) {
            return;
        }

        for (const col of dataColumns) {
            const field = col.field as string;

            if (hiddenColumns.includes(field)) {
                table.hideColumn(field);
            } else {
                table.showColumn(field);
            }
        }
    }, [hiddenColumns, dataColumns]);

    const toggleColumn = (field: string, visible: boolean) => {
        const next = visible
            ? hiddenColumns.filter((f) => f !== field)
            : [...hiddenColumns, field];
        setHiddenColumns(next);
        persist(next, pageSize);
    };

    const changePageSize = (size: number) => {
        setPageSize(size);
        persist(hiddenColumns, size);
    };

    return (
        <div className={cn('space-y-3', className)}>
            <div className="flex flex-wrap items-center gap-2">
                <div className="relative">
                    <Button
                        type="button"
                        size="sm"
                        variant="outline"
                        onClick={() => setColumnsOpen((open) => !open)}
                    >
                        {t('mostrar_ocultar_columnas')}
                    </Button>
                    {columnsOpen ? (
                        <div className="absolute z-20 mt-1 min-w-48 rounded-md border border-slate-200 bg-white p-2 shadow-md">
                            {dataColumns.map((col) => {
                                const field = col.field as string;
                                const checked = !hiddenColumns.includes(field);

                                return (
                                    <label
                                        key={field}
                                        className="flex cursor-pointer items-center gap-2 px-1 py-1 text-sm text-slate-700"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={checked}
                                            onChange={(event) =>
                                                toggleColumn(field, event.target.checked)
                                            }
                                        />
                                        {String(col.title ?? field)}
                                    </label>
                                );
                            })}
                        </div>
                    ) : null}
                </div>

                <label className="flex items-center gap-2 text-sm text-slate-600">
                    <span>{t('mostrar_registros')}</span>
                    <select
                        className="h-8 rounded-md border border-slate-200 bg-white px-2 text-sm"
                        value={pageSize}
                        onChange={(event) => changePageSize(Number(event.target.value))}
                    >
                        {PAGE_SIZE_OPTIONS.map((size) => (
                            <option key={size} value={size}>
                                {size}
                            </option>
                        ))}
                    </select>
                </label>

                {exportable ? (
                    <>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            onClick={() => exportDownload('xlsx')}
                        >
                            {t('exportar_excel')}
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            variant="outline"
                            onClick={() => exportDownload('pdf')}
                        >
                            {t('exportar_pdf')}
                        </Button>
                    </>
                ) : null}

                {toolbarExtra}
            </div>

            <div ref={ref} className="tabulator-erp" />

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t(pendingDelete?.titleKey ?? 'confirmar_eliminar')}
                confirmLabel={t('eliminar')}
                onConfirm={() => {
                    pendingDelete?.onConfirm();
                    setPendingDelete(null);
                }}
            />
        </div>
    );
}
