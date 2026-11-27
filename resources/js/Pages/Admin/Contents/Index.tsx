import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import type { CellComponent } from 'tabulator-tables';

import HelpTip from '@/Components/HelpTip';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

export type ContentRow = {
    id: number;
    name: string;
    code: string;
    kind: string;
    status: string;
};

export default function Index() {
    const { t } = useTranslation();

    const kindValues: Record<string, string> = useMemo(
        () => ({
            help: t('contenido_tipo_informacion'),
            article: t('contenido_tipo_contenido'),
        }),
        [t],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('codigo'),
                field: 'code',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('tipo'),
                field: 'kind',
                width: 160,
                headerFilterKind: 'select',
                headerFilterValues: kindValues,
                formatter: (cell: CellComponent) => {
                    const value = String(cell.getValue() ?? '');

                    return kindValues[value] ?? value;
                },
            },
        ],
        [t, kindValues],
    );

    const rowActions = (row: ContentRow): Array<RowAction<ContentRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/settings/contents/${row.id}/status`, {
                    status,
                });

                return { status };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: () => router.visit(`/settings/contents/${row.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/settings/contents/${current.id}`, { preserveScroll: true });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'contenidos',
                suboptionKey: 'dashboard',
                optionHref: '/settings/contents',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/settings/contents/create',
                    icon: Plus,
                },
            ]}
        >
            <div className="mb-3 flex items-center gap-1">
                <HelpTip code="contents-admin" />
            </div>
            <ServerTable<ContentRow>
                endpoint="/settings/contents/table"
                columns={columns}
                persistKey="admin.contents"
                exportable
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
