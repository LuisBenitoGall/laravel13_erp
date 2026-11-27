import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

export type DelegationRow = {
    id: number;
    name: string;
    tradename: string | null;
    nif: string | null;
    status: 'draft' | 'active' | 'closed';
    created_at: string | null;
};

export default function Index() {
    const { t, i18n } = useTranslation();

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('razon_social'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('nombre_comercial'),
                field: 'tradename',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('nif'),
                field: 'nif',
                width: 120,
                headerFilterKind: 'string',
            },
            {
                title: t('fecha_alta'),
                field: 'created_at',
                widthGrow: 1,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
        ],
        [i18n.language, t],
    );

    const rowActions = (row: DelegationRow): Array<RowAction<DelegationRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_current, checked) => {
                const status = checked ? 'active' : 'closed';
                await patchJson<{ status: string }>(`/companies/delegations/${row.id}/status`, {
                    status,
                });

                return { status: status as DelegationRow['status'] };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/companies/delegations/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/companies/delegations/${current.id}`, { preserveScroll: true });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'delegaciones',
                suboptionKey: 'dashboard',
                optionHref: '/companies/delegations',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/companies/delegations/create',
                    icon: Plus,
                },
            ]}
        >
            <ServerTable<DelegationRow>
                endpoint="/companies/delegations/table"
                columns={columns}
                persistKey="admin.delegations"
                exportable
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
