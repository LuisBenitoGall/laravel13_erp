import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import type { FamilyRow } from '@/types/catalog';

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
                title: t('slug'),
                field: 'slug',
                width: 180,
                headerFilterKind: 'string',
            },
            {
                title: t('estado'),
                field: 'status',
                width: 120,
                formatter: (cell) => (cell.getValue() === 'active' ? t('activo') : t('inactivo')),
            },
        ],
        [t],
    );

    const rowActions = (): Array<RowAction<FamilyRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/products/families/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/products/families/${current.id}`, {
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
                optionKey: 'familias',
                suboptionKey: 'dashboard',
                optionHref: '/products/families',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'familia_nueva',
                    icon: Plus,
                    onClick: () => router.visit('/products/families/create'),
                },
            ]}
        >
            <ServerTable<FamilyRow>
                key={tableKey}
                endpoint="/products/families/table"
                columns={columns}
                persistKey="catalog.families"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
