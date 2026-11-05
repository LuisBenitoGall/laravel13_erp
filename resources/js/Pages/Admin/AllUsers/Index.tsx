import { Bell } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

type DirectoryUserRow = {
    id: number;
    name: string;
    surname: string | null;
    email: string;
    status: boolean;
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
                title: t('apellidos'),
                field: 'surname',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('email'),
                field: 'email',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('estado'),
                field: 'status',
                width: 110,
                formatter: (cell) => (cell.getValue() ? t('activo') : t('inactivo')),
            },
        ],
        [t],
    );

    const rowActions = (): Array<RowAction<DirectoryUserRow>> => [
        {
            id: 'notify',
            icon: Bell,
            labelKey: 'enviar_notificacion',
            onClick: () => {
                toast.info(t('notificacion_mascara'));
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'todos_los_usuarios',
                suboptionKey: 'dashboard',
                optionHref: '/admin/all-users',
            }}
            showBack={false}
        >
            <ServerTable<DirectoryUserRow>
                endpoint="/admin/all-users/table"
                columns={columns}
                persistKey="admin.all-users"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
