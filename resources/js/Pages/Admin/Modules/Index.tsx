import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import type { CellComponent } from 'tabulator-tables';

import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';
import type { ModuleRow } from '@/types/admin';

function colorSwatch(cell: CellComponent): HTMLElement {
    const value = String(cell.getValue() ?? '');
    const wrap = document.createElement('div');
    wrap.className = 'cell-vcenter';
    const swatch = document.createElement('span');
    swatch.className = 'inline-block h-4 w-4 shrink-0 rounded border border-slate-200';
    swatch.style.backgroundColor = value || 'transparent';
    swatch.title = value;
    const label = document.createElement('span');
    label.className = 'text-xs text-slate-600';
    label.textContent = value;
    wrap.append(swatch, label);

    return wrap;
}

export default function Index() {
    const { t, i18n } = useTranslation();

    const columns = useMemo<ColumnDef[]>(() => {
        const levelValues: Record<string, string> = {
            admin_only: t('nivel_admin_only'),
            basic: t('nivel_basic'),
            optional: t('nivel_optional'),
        };
        const statusValues: Record<string, string> = {
            active: t('estado_active'),
            inactive: t('estado_inactive'),
        };

        return [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('color'),
                field: 'color',
                width: 140,
                headerFilterKind: 'string',
                formatter: colorSwatch,
                vertAlign: 'middle',
                headerSort: false,
            },
            {
                title: t('icono'),
                field: 'icon',
                width: 120,
                headerFilterKind: 'string',
                vertAlign: 'middle',
            },
            {
                title: t('nivel'),
                field: 'level',
                width: 140,
                headerFilterKind: 'select',
                headerFilterValues: levelValues,
                vertAlign: 'middle',
            },
            {
                title: t('creado'),
                field: 'created_at',
                width: 200,
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
                vertAlign: 'middle',
            },
            {
                title: t('estado'),
                field: 'status',
                width: 120,
                headerFilterKind: 'select',
                headerFilterValues: statusValues,
                vertAlign: 'middle',
            },
        ];
    }, [t, i18n.language]);

    const rowActions = (row: ModuleRow): Array<RowAction<ModuleRow> | false> => {
        // admin/workspace: Inactive en catálogo ⇒ 403 en middleware module: (shell).
        const shellCritical = row.slug === 'admin' || row.slug === 'workspace';

        return [
            !shellCritical && {
                id: 'toggle-status',
                kind: 'toggle' as const,
                labelKey: row.status === 'active' ? 'desactivar' : 'activar',
                checked: row.status === 'active',
                onToggle: async (_row, checked) => {
                    const status = checked ? 'active' : 'inactive';
                    await patchJson<{ status: string }>(`/admin/modules/${row.id}/status`, {
                        status,
                    });

                    return { status };
                },
            },
            {
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: () => router.visit(`/admin/modules/${row.id}/edit`),
            },
        ];
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'modulos',
                suboptionKey: 'dashboard',
                optionHref: '/admin/modules',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/admin/modules/create',
                    icon: Plus,
                },
            ]}
        >
            <ServerTable<ModuleRow>
                endpoint="/admin/modules/table"
                columns={columns}
                persistKey="admin.modules"
                exportable
                rowActions={rowActions}
            />
        </AppLayout>
    );
}
