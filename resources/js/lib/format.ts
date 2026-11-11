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

/**
 * Máscara visual de teléfono: dígitos en bloques de 3 desde la derecha.
 * Conserva el `+` internacional si venía en el valor. No altera el valor persistido.
 * Ej.: `600578603` → `600 578 603`; `+34600578603` → `+34 600 578 603`.
 */
export function formatPhoneDisplay(value: string | null | undefined): string {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    const trimmed = value.trim();
    const hasPlus = trimmed.startsWith('+');
    const digits = trimmed.replace(/\D/g, '');

    if (digits.length === 0) {
        return trimmed;
    }

    const groups: string[] = [];
    let rest = digits;

    while (rest.length > 3) {
        groups.unshift(rest.slice(-3));
        rest = rest.slice(0, -3);
    }

    if (rest.length > 0) {
        groups.unshift(rest);
    }

    const spaced = groups.join(' ');

    return hasPlus ? `+${spaced}` : spaced;
}
