import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import type { CategoryRow } from '@/types/catalog';

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
                title: t('categoria_padre'),
                field: 'parent_name',
                width: 180,
                formatter: (cell) => {
                    const value = cell.getValue();

                    return value ? String(value) : '—';
                },
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

    const rowActions = (): Array<RowAction<CategoryRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/products/categories/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/products/categories/${current.id}`, {
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
                optionKey: 'categorias',
                suboptionKey: 'dashboard',
                optionHref: '/products/categories',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'categoria_nueva',
                    icon: Plus,
                    onClick: () => router.visit('/products/categories/create'),
                },
            ]}
        >
            <ServerTable<CategoryRow>
                key={tableKey}
                endpoint="/products/categories/table"
                columns={columns}
                persistKey="catalog.categories"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
