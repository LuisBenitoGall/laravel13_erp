import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import type { DayTypeRow } from '@/types/calendar';

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('color'),
                field: 'color',
                width: 140,
                formatter: (cell) => {
                    const value = String(cell.getValue() ?? '');
                    const wrap = document.createElement('div');
                    wrap.style.display = 'inline-flex';
                    wrap.style.alignItems = 'center';
                    wrap.style.gap = '8px';
                    const swatch = document.createElement('span');
                    swatch.style.width = '14px';
                    swatch.style.height = '14px';
                    swatch.style.borderRadius = '9999px';
                    swatch.style.background = value;
                    swatch.style.border = '1px solid #cbd5e1';
                    const label = document.createElement('span');
                    label.textContent = value;
                    wrap.append(swatch, label);

                    return wrap;
                },
            },
            {
                title: t('es_laborable'),
                field: 'is_working',
                width: 140,
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
            },
        ],
        [t],
    );

    const rowActions = (): Array<RowAction<DayTypeRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/rrhh/day-types/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/rrhh/day-types/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => setTableKey((key) => key + 1),
                    onError: (errors) => {
                        const message = Object.values(errors)[0];
                        if (message) {
                            toast.error(String(message));
                        }
                    },
                });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_jornada',
                suboptionKey: 'dashboard',
                optionHref: '/rrhh/day-types',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'tipo_jornada_nuevo',
                    icon: Plus,
                    onClick: () => router.visit('/rrhh/day-types/create'),
                },
            ]}
        >
            <ServerTable<DayTypeRow>
                key={tableKey}
                endpoint="/rrhh/day-types/table"
                columns={columns}
                persistKey="hr.day-types"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
