import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

type CompanyRoleRow = {
    id: number;
    name: string;
    description: string | null;
    protected_local: boolean;
    can_edit: boolean;
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
        ],
        [t],
    );

    const rowActions = (row: CompanyRoleRow): Array<RowAction<CompanyRoleRow>> => {
        const actions: Array<RowAction<CompanyRoleRow>> = [];

        if (row.can_edit) {
            actions.push({
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: () => router.visit(`/company/roles/${row.id}/edit`),
            });
        }

        if (row.can_delete) {
            actions.push({
                id: 'delete',
                labelKey: 'eliminar',
                variant: 'danger',
                confirm: true,
                onClick: (current) => {
                    router.delete(`/company/roles/${current.id}`, {
                        preserveScroll: true,
                        onError: (errors) => {
                            const message = Object.values(errors)[0];
                            if (message) {
                                toast.error(String(message));
                            }
                        },
                    });
                },
            });
        }

        return actions;
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles_de_empresa',
                suboptionKey: 'dashboard',
                optionHref: '/company/roles',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/company/roles/create',
                    icon: Plus,
                },
            ]}
        >
            <ServerTable<CompanyRoleRow>
                endpoint="/company/roles/table"
                columns={columns}
                persistKey="company.roles"
                exportable={false}
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
