export const NAV_ORIGIN_KEY = 'erp.nav.origin';

export function normalizeAppPath(url: string): string {
    try {
        const parsed = new URL(url, window.location.origin);

        return `${parsed.pathname}${parsed.search}`;
    } catch {
        return url.split('#')[0] ?? url;
    }
}

export function readNavOrigin(): string | null {
    if (typeof window === 'undefined') {
        return null;
    }

    const value = window.sessionStorage.getItem(NAV_ORIGIN_KEY);

    return value && value.length > 0 ? value : null;
}

export function writeNavOrigin(url: string): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.sessionStorage.setItem(NAV_ORIGIN_KEY, normalizeAppPath(url));
}

export function clearNavOrigin(): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.sessionStorage.removeItem(NAV_ORIGIN_KEY);
}

/** Origen usable para Volver: distinto de la URL actual. */
export function resolveBackHref(currentUrl: string): string | null {
    const origin = readNavOrigin();

    if (!origin) {
        return null;
    }

    if (normalizeAppPath(origin) === normalizeAppPath(currentUrl)) {
        return null;
    }

    return origin;
}
