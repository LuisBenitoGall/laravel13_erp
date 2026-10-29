import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

export type TableFilter = {
    field: string;
    type: string;
    value: unknown;
};

/**
 * Modal genérico de búsqueda avanzada (opt-in desde ActionBar).
 * Aplica filtros al mismo canal `filter[]` del protocolo table.
 */
export default function AdvancedSearchModal({
    open,
    onOpenChange,
    titleKey = 'busqueda_avanzada',
    children,
    onApply,
    onClear,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    titleKey?: string;
    children: ReactNode;
    onApply: () => void;
    onClear?: () => void;
}) {
    const { t } = useTranslation();

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t(titleKey)}</DialogTitle>
                </DialogHeader>
                <div className="grid gap-3 py-2">{children}</div>
                <DialogFooter className="gap-2 sm:justify-end">
                    {onClear ? (
                        <Button type="button" variant="outline" onClick={onClear}>
                            {t('limpiar')}
                        </Button>
                    ) : null}
                    <Button type="button" variant="secondary" onClick={() => onOpenChange(false)}>
                        {t('cancelar')}
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            onApply();
                            onOpenChange(false);
                        }}
                    >
                        {t('aplicar')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
