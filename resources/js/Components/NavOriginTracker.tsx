import { router } from '@inertiajs/react';
import { useEffect } from 'react';

import { normalizeAppPath, writeNavOrigin } from '@/lib/navOrigin';

/**
 * Guarda la URL previa en sessionStorage antes de cada visita Inertia
 * (solo si el destino es distinto).
 */
export default function NavOriginTracker() {
    useEffect(() => {
        return router.on('before', (event) => {
            const visit = event.detail.visit;
            const nextRaw =
                typeof visit.url === 'string' ? visit.url : visit.url.toString();
            const next = normalizeAppPath(nextRaw);
            const current = normalizeAppPath(
                `${window.location.pathname}${window.location.search}`,
            );

            if (next !== current) {
                writeNavOrigin(current);
            }
        });
    }, []);

    return null;
}
