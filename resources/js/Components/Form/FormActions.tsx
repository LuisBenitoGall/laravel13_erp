import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

export type FormActionsProps = {
    children: ReactNode;
    className?: string;
};

/**
 * Fila de acciones de formulario (Guardar / Cancelar / …).
 * Siempre alineada a la derecha (R-FE-09). ≠ ViewActionBar.
 */
export function FormActions({ children, className }: FormActionsProps) {
    return (
        <div className={cn('flex flex-wrap items-center justify-end gap-2', className)}>
            {children}
        </div>
    );
}
