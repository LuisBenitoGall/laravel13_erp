import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

type PlatformRoleRow = {
    id: number;
    name: string;
    description: string | null;
    universal: boolean;
    can_delete: boolean;
};

export default function Index() {
    const { t } = useTranslation();

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('descripcion'),
                field: 'description',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('rol_universal'),
                field: 'universal',
                width: 140,
                headerFilterKind: 'select',
                headerFilterValues: {
                    true: t('si'),
                    false: t('no'),
                },
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
                vertAlign: 'middle',
            },
        ],
        [t],
    );

    const rowActions = (row: PlatformRoleRow): Array<RowAction<PlatformRoleRow>> => {
        const actions: Array<RowAction<PlatformRoleRow>> = [
            {
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: () => router.visit(`/admin/roles/${row.id}/edit`),
            },
        ];

        if (row.can_delete) {
            actions.push({
                id: 'delete',
                labelKey: 'eliminar',
                variant: 'danger',
                confirm: true,
                onClick: (current) => {
                    router.delete(`/admin/roles/${current.id}`, { preserveScroll: true });
                },
            });
        }

        return actions;
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles',
                suboptionKey: 'dashboard',
                optionHref: '/admin/roles',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'rol_crear',
                    icon: Plus,
                    disabled: true,
                    onClick: () => undefined,
                },
            ]}
        >
            <ServerTable<PlatformRoleRow>
                endpoint="/admin/roles/table"
                columns={columns}
                persistKey="admin.roles"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
