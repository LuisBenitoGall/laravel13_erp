import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import type { ContractTypeRow } from '@/types/h_r';

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
                title: t('duracion_dias'),
                field: 'duration',
                width: 150,
                formatter: (cell) => {
                    const value = cell.getValue();

                    return value === null || value === undefined ? t('indefinido') : String(value);
                },
            },
            {
                title: t('con_vacaciones'),
                field: 'with_holidays',
                width: 150,
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
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

    const rowActions = (): Array<RowAction<ContractTypeRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/rrhh/contract-types/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/rrhh/contract-types/${current.id}`, {
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
                optionKey: 'tipos_de_contrato',
                suboptionKey: 'dashboard',
                optionHref: '/rrhh/contract-types',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'tipo_contrato_nuevo',
                    icon: Plus,
                    onClick: () => router.visit('/rrhh/contract-types/create'),
                },
            ]}
        >
            <ServerTable<ContractTypeRow>
                key={tableKey}
                endpoint="/rrhh/contract-types/table"
                columns={columns}
                persistKey="hr.contract-types"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
