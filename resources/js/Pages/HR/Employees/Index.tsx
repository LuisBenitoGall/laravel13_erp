import { router } from '@inertiajs/react';
import { Pencil, Plus, RefreshCw, UserX } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { fmt } from '@/Components/Table/formatters';
import AppLayout from '@/Layouts/AppLayout';
import type { EmployeeRow } from '@/types/h_r';

type Props = {
    contract_type_filter: Record<string, string>;
};

export default function Index({ contract_type_filter }: Props) {
    const { t, i18n } = useTranslation();
    const [tableKey, setTableKey] = useState(0);

    const statusFilterValues = useMemo(
        () => ({
            scheduled: t('programado'),
            active: t('activo'),
            ended: t('finalizado'),
        }),
        [t],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('usuario'),
                field: 'user_name',
                widthGrow: 2,
                headerSort: false,
                headerFilterKind: 'string',
            },
            {
                title: t('tipo_de_contrato'),
                field: 'contract_type_id',
                widthGrow: 2,
                headerSort: false,
                headerFilterKind: 'select',
                headerFilterValues: contract_type_filter,
                formatter: (cell) => {
                    const row = cell.getData() as EmployeeRow;

                    return row.contract_type_name ?? '';
                },
            },
            {
                title: t('puesto'),
                field: 'job_title',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('fecha_inicio'),
                field: 'start_date',
                width: 150,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('fecha_fin'),
                field: 'end_date',
                width: 150,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('estado'),
                field: 'status',
                width: 130,
                headerFilterKind: 'select',
                headerFilterValues: statusFilterValues,
                formatter: (cell) => {
                    const value = cell.getValue();

                    if (value === 'active') {
                        return t('activo');
                    }

                    return value === 'scheduled' ? t('programado') : t('finalizado');
                },
            },
        ],
        [contract_type_filter, i18n.language, statusFilterValues, t],
    );

    const reload = () => setTableKey((key) => key + 1);

    const rowActions = (row: EmployeeRow): Array<RowAction<EmployeeRow> | false> => [
        row.can?.update !== false && {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/hr/employees/${current.id}/edit`),
        },
        row.can?.renew === true && {
            id: 'renew',
            icon: RefreshCw,
            labelKey: 'renovar',
            onClick: (current) => router.visit(`/hr/employees/${current.id}/renew`),
        },
        row.can?.end === true && {
            id: 'end',
            icon: UserX,
            labelKey: 'finalizar',
            confirm: true,
            onClick: (current) => {
                router.post(
                    `/hr/employees/${current.id}/end`,
                    {},
                    {
                        preserveScroll: true,
                        onSuccess: reload,
                        onError: (errors) => {
                            const message = Object.values(errors)[0];
                            if (message) {
                                toast.error(String(message));
                            }
                        },
                    },
                );
            },
        },
        row.can?.delete !== false && {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/hr/employees/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: reload,
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
                optionKey: 'empleados',
                suboptionKey: 'dashboard',
                optionHref: '/hr/employees',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'empleo_nuevo',
                    icon: Plus,
                    onClick: () => router.visit('/hr/employees/create'),
                },
            ]}
        >
            <ServerTable<EmployeeRow>
                key={tableKey}
                endpoint="/hr/employees/table"
                columns={columns}
                persistKey="hr.employees"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
