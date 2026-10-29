/**
 * Formatos de fecha por locale de sesión (R-FE-04).
 * Ampliar este mapa al añadir idiomas; fallback = es.
 */
export const DATE_FORMATS: Record<string, string> = {
    es: 'dd/mm/yyyy',
    ca: 'dd/mm/yyyy',
    en: 'yyyy/mm/dd',
};

export function dateFormatForLocale(locale: string | undefined | null): string {
    const key = (locale ?? 'es').split('-')[0]?.toLowerCase() ?? 'es';

    return DATE_FORMATS[key] ?? DATE_FORMATS.es;
}

/** Formatea una fecha ISO/Date según el patrón del locale. */
export function formatDate(
    value: string | Date | null | undefined,
    locale: string | undefined | null = 'es',
): string {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const date = value instanceof Date ? value : new Date(String(value));

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    const pattern = dateFormatForLocale(locale);
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const yyyy = String(date.getFullYear());

    return pattern.replace('dd', dd).replace('mm', mm).replace('yyyy', yyyy);
}
