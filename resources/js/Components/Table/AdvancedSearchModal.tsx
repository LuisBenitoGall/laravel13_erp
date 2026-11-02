import { useState, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

import type { TableFilter } from '@/Components/Table/tableFilters';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

export type { TableFilter };

export type AdvancedSearchApi = {
    filters: TableFilter[];
    setFilters: (filters: TableFilter[]) => void;
};

type AdvancedSearchSlot = ReactNode | ((api: AdvancedSearchApi) => ReactNode);

/**
 * Modal genérico de búsqueda avanzada (opt-in desde ActionBar).
 * `onApply` entrega `[{ field, type, value }]` al mismo canal `filter` que la cabecera.
 * `fields` o `children` pueden ser un render prop (`{ filters, setFilters }`).
 * Si la página pasa `filters`, esa lista es la que se aplica (controlada).
 */
export default function AdvancedSearchModal({
    open,
    onOpenChange,
    titleKey = 'busqueda_avanzada',
    fields,
    children,
    filters,
    onApply,
    onClear,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    titleKey?: string;
    fields?: AdvancedSearchSlot;
    children?: AdvancedSearchSlot;
    filters?: TableFilter[];
    onApply: (filters: TableFilter[]) => void;
    onClear?: () => void;
}) {
    const { t } = useTranslation();
    const [draft, setDraft] = useState<TableFilter[]>([]);
    const current = filters ?? draft;
    const slot = fields !== undefined ? fields : children;
    const api: AdvancedSearchApi = {
        filters: current,
        setFilters: (next) => {
            if (filters === undefined) {
                setDraft(next);
            }
        },
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>{t(titleKey)}</DialogTitle>
                </DialogHeader>
                <div className="grid gap-3 py-2">
                    {typeof slot === 'function' ? slot(api) : slot}
                </div>
                <DialogFooter className="gap-2 sm:justify-end">
                    {onClear ? (
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => {
                                if (filters === undefined) {
                                    setDraft([]);
                                }

                                onClear();
                            }}
                        >
                            {t('limpiar')}
                        </Button>
                    ) : null}
                    <Button type="button" variant="secondary" onClick={() => onOpenChange(false)}>
                        {t('cancelar')}
                    </Button>
                    <Button
                        type="button"
                        onClick={() => {
                            onApply(current);
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
