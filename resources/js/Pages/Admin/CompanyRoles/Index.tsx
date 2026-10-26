import { Link } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import type { ColumnDefinition } from 'tabulator-tables';

import ServerTable from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

export default function Index() {
    const { t } = useTranslation();
    const columns = useMemo<ColumnDefinition[]>(
        () => [
            { title: t('nombre'), field: 'name', widthGrow: 1 },
            { title: t('descripcion'), field: 'description', widthGrow: 2 },
            {
                title: t('accion'),
                field: 'id',
                width: 100,
                headerSort: false,
                formatter: (cell) => {
                    const id = cell.getValue();
                    return `<a class="text-teal-700 underline" href="/company/roles/${id}/edit">${t('editar')}</a>`;
                },
            },
        ],
        [t],
    );

    return (
        <AppLayout title={t('roles_de_empresa')}>
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-600">{t('roles_de_empresa_subtitulo')}</p>
                <Link
                    href="/company/roles/create"
                    className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
                >
                    {t('crear')}
                </Link>
            </div>
            <ServerTable endpoint="/company/roles/table" columns={columns} />
        </AppLayout>
    );
}
