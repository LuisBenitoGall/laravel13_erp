import { Pencil, Plus } from 'lucide-react';
import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

type DirectoryUserRow = {
    id: number;
    name: string;
    surname: string | null;
    email: string;
    status: boolean;
};

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);

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
        ],
        [t],
    );

    const rowActions = (row: DirectoryUserRow): Array<RowAction<DirectoryUserRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status ? 'desactivar' : 'activar',
            checked: row.status,
            onToggle: async (_row, checked) => {
                const result = await patchJson<{ status: boolean }>(
                    `/admin/company-users/${row.id}/status`,
                    { status: checked },
                );

                return { status: result.status };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: () => {
                router.visit(`/admin/company-users/${row.id}/edit`);
            },
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/admin/company-users/${current.id}`, {
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
                optionKey: 'usuarios_empresa',
                suboptionKey: 'dashboard',
                optionHref: '/admin/company-users',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'crear_usuario',
                    icon: Plus,
                    onClick: () => {
                        router.visit('/admin/company-users/create');
                    },
                },
            ]}
        >
            <ServerTable<DirectoryUserRow>
                key={tableKey}
                endpoint="/admin/company-users/table"
                columns={columns}
                persistKey="admin.company-users"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
