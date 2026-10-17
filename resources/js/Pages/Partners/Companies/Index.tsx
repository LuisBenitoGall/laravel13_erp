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
            { title: t('slug'), field: 'slug', width: 140 },
            { title: t('nombre'), field: 'name', widthGrow: 1 },
            { title: t('nombre_comercial'), field: 'tradename', widthGrow: 1 },
            { title: t('nif'), field: 'nif', width: 120 },
            { title: t('estado'), field: 'status', width: 120 },
        ],
        [t],
    );

    return (
        <AppLayout title={t('empresas')}>
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-600">{t('empresas_subtitulo')}</p>
                <Link
                    href="/partners/companies/create"
                    className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
                >
                    {t('crear')}
                </Link>
            </div>
            <ServerTable endpoint="/partners/companies/table" columns={columns} />
        </AppLayout>
    );
}
