/** CSRF token from Laravel XSRF-TOKEN cookie (for same-origin JSON fetches). */
export function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match?.[1] ? decodeURIComponent(match[1]) : '';
}

/**
 * PATCH JSON same-origin (tabla Acciones async; excepción R-FE-03 junto al ajax Tabulator).
 */
export async function patchJson<TResponse = unknown>(
    url: string,
    body: Record<string, unknown>,
): Promise<TResponse> {
    const response = await fetch(url, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        body: JSON.stringify(body),
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return (await response.json()) as TResponse;
}
