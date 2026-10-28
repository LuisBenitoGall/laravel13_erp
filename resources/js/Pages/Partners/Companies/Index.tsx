import { Plus } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import type { ColumnDefinition } from 'tabulator-tables';

import ServerTable from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

export default function Index({ operator = false }: { operator?: boolean }) {
    const { t } = useTranslation();
    const columns = useMemo<ColumnDefinition[]>(() => {
        const base: ColumnDefinition[] = [
            { title: t('slug'), field: 'slug', width: 140 },
            { title: t('nombre'), field: 'name', widthGrow: 1 },
            { title: t('nombre_comercial'), field: 'tradename', widthGrow: 1 },
            { title: t('nif'), field: 'nif', width: 120 },
            { title: t('estado'), field: 'status', width: 120 },
        ];

        if (operator) {
            base.push({
                title: t('autorizaciones'),
                field: 'id',
                width: 140,
                headerSort: false,
                formatter: (cell) => {
                    const id = cell.getValue();
                    return `<a class="text-teal-700 underline" href="/admin/company-module-authorizations?company_id=${id}">${t('autorizaciones')}</a>`;
                },
            });
        }

        return base;
    }, [t, operator]);

    return (
        <AppLayout
            breadcrumb={{
                optionKey: operator ? 'tenants' : 'empresas',
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
            <ServerTable endpoint="/partners/companies/table" columns={columns} />
        </AppLayout>
    );
}
