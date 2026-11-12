import { router } from '@inertiajs/react';
import { Pencil, Plus, Shield } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

export type CompanyRow = {
    id: number;
    name: string;
    tradename: string | null;
    nif: string | null;
    status: 'draft' | 'active' | 'closed';
    created_at: string | null;
};

export default function Index({ operator = false }: { operator?: boolean }) {
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

    const rowActions = (row: CompanyRow): Array<RowAction<CompanyRow>> => {
        const actions: Array<RowAction<CompanyRow>> = [
            {
                id: 'toggle-status',
                kind: 'toggle',
                labelKey: row.status === 'active' ? 'desactivar' : 'activar',
                checked: row.status === 'active',
                onToggle: async (_current, checked) => {
                    const status = checked ? 'active' : 'closed';
                    await patchJson<{ status: string }>(`/partners/companies/${row.id}/status`, {
                        status,
                    });

                    return { status: status as CompanyRow['status'] };
                },
            },
            {
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: (current) => router.visit(`/partners/companies/${current.id}/edit`),
            },
        ];

        if (operator) {
            actions.push({
                id: 'authorizations',
                icon: Shield,
                labelKey: 'autorizaciones',
                onClick: (current) =>
                    router.visit(`/admin/company-module-authorizations?company_id=${current.id}`),
            });
        }

        actions.push({
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/partners/companies/${current.id}`, { preserveScroll: true });
            },
        });

        return actions;
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mis_empresas',
                suboptionKey: 'dashboard',
                optionHref: '/partners/companies',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/partners/companies/create',
                    icon: Plus,
                },
            ]}
        >
            <ServerTable<CompanyRow>
                endpoint="/partners/companies/table"
                columns={columns}
                persistKey="partners.companies"
                exportable
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
