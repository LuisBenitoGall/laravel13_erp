import { Plus } from 'lucide-react';
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
            <ServerTable endpoint="/company/roles/table" columns={columns} />
        </AppLayout>
    );
}
