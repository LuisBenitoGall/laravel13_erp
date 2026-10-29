import type { CellComponent } from 'tabulator-tables';

import { formatDate } from '@/lib/format';

/**
 * Formatters tipados del catálogo — ampliar según entidades.
 * Fechas/importes: declarar `headerFilterKind: 'date'|'number'` o
 * `valueKind: 'date'|'number'` en la ColumnDef para alineación a la derecha.
 */
export const fmt = {
    money:
        (decimals = 2) =>
        (cell: CellComponent): string => {
            const value = cell.getValue();
            const num = typeof value === 'number' ? value : Number(value);

            if (Number.isNaN(num)) {
                return '';
            }

            return new Intl.NumberFormat(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(num);
        },
    /** Fecha según `DATE_FORMATS` / locale de sesión (es|ca → dd/mm/yyyy; en → yyyy/mm/dd). */
    date:
        (locale?: string) =>
        (cell: CellComponent): string =>
            formatDate(cell.getValue() as string | Date | null | undefined, locale),
    number:
        (decimals = 0) =>
        (cell: CellComponent): string => {
            const value = cell.getValue();
            const num = typeof value === 'number' ? value : Number(value);

            if (Number.isNaN(num)) {
                return '';
            }

            return new Intl.NumberFormat(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(num);
        },
    boolean: (cell: CellComponent): string => (cell.getValue() ? '✓' : ''),
};
