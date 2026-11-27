/** CSRF token from Laravel XSRF-TOKEN cookie (for same-origin JSON fetches). */
export function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match?.[1] ? decodeURIComponent(match[1]) : '';
}

/**
 * GET JSON same-origin (p. ej. vista rápida de listados).
 */
export async function getJson<TResponse = unknown>(
    url: string,
    init?: { signal?: AbortSignal },
): Promise<TResponse> {
    const response = await fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        signal: init?.signal,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return (await response.json()) as TResponse;
}

async function writeJson<TResponse = unknown>(
    method: 'PATCH' | 'PUT',
    url: string,
    body: Record<string, unknown>,
): Promise<TResponse> {
    const response = await fetch(url, {
        method,
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
        let message = `HTTP ${response.status}`;
        try {
            const payload = (await response.json()) as {
                message?: string;
                errors?: Record<string, string[]>;
            };
            const firstError = payload.errors
                ? Object.values(payload.errors).flat()[0]
                : undefined;
            message = firstError ?? payload.message ?? message;
        } catch {
            // keep status message
        }

        throw new Error(message);
    }

    return (await response.json()) as TResponse;
}

/**
 * PATCH JSON same-origin (tabla Acciones async; excepción R-FE-03 junto al ajax Tabulator).
 */
export async function patchJson<TResponse = unknown>(
    url: string,
    body: Record<string, unknown>,
): Promise<TResponse> {
    return writeJson<TResponse>('PATCH', url, body);
}

/** PUT JSON same-origin (acciones de tabla / sync parcial). */
export async function putJson<TResponse = unknown>(
    url: string,
    body: Record<string, unknown>,
): Promise<TResponse> {
    return writeJson<TResponse>('PUT', url, body);
}
