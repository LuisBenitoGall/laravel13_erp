import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

export type WorkplaceRow = {
    id: number;
    name: string;
    cp: string | null;
    town_name: string | null;
    featured: boolean;
    status: 'active' | 'inactive';
};

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
                title: t('poblacion'),
                field: 'town_name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('codigo_postal'),
                field: 'cp',
                width: 110,
                headerFilterKind: 'string',
            },
            {
                title: t('principal'),
                field: 'featured',
                width: 110,
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
            },
            {
                title: t('estado'),
                field: 'status',
                width: 120,
                formatter: (cell) =>
                    cell.getValue() === 'active' ? t('activo') : t('inactivo'),
            },
        ],
        [t],
    );

    const rowActions = (row: WorkplaceRow): Array<RowAction<WorkplaceRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_current, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/workplaces/${row.id}/status`, {
                    status,
                });

                return { status: status as WorkplaceRow['status'] };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/admin/workplaces/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/admin/workplaces/${current.id}`, {
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
                optionKey: 'centros_de_trabajo',
                suboptionKey: 'dashboard',
                optionHref: '/admin/workplaces',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_centro_trabajo',
                    icon: Plus,
                    onClick: () => router.visit('/admin/workplaces/create'),
                },
            ]}
        >
            <ServerTable<WorkplaceRow>
                key={tableKey}
                endpoint="/admin/workplaces/table"
                columns={columns}
                persistKey="admin.workplaces"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
