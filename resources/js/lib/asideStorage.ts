export const ASIDE_COLLAPSED_KEY = 'erp.aside.collapsed';

/**
 * Preferencia de colapso del aside en md+ (`'1'` colapsado / rail, `'0'` expandido).
 * `null` si no hay valor o no hay window.
 */
export function readAsideCollapsed(): boolean | null {
    if (typeof window === 'undefined') {
        return null;
    }

    const value = window.localStorage.getItem(ASIDE_COLLAPSED_KEY);

    if (value === '1') {
        return true;
    }

    if (value === '0') {
        return false;
    }

    return null;
}

export function writeAsideCollapsed(collapsed: boolean): void {
    if (typeof window === 'undefined') {
        return;
    }

    window.localStorage.setItem(ASIDE_COLLAPSED_KEY, collapsed ? '1' : '0');
}
