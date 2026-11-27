import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import type { ProductRow } from '@/types/catalog';

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
                title: t('tipo'),
                field: 'type',
                width: 140,
                formatter: (cell) => {
                    const value = cell.getValue();

                    return value === 'service' ? t('servicio') : t('producto_fisico');
                },
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

    const rowActions = (): Array<RowAction<ProductRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/products/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/products/${current.id}`, {
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
                optionKey: 'catalogo',
                suboptionKey: 'dashboard',
                optionHref: '/products',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'producto_nuevo',
                    icon: Plus,
                    onClick: () => router.visit('/products/create'),
                },
            ]}
        >
            <ServerTable<ProductRow>
                key={tableKey}
                endpoint="/products/table"
                columns={columns}
                persistKey="catalog.products"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
