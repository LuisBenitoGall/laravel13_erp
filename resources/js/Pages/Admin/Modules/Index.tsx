import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { Plus } from 'lucide-react';
import type { ColumnDefinition } from 'tabulator-tables';

import ServerTable from '@/Components/Table/ServerTable';
import AppLayout from '@/Layouts/AppLayout';

export default function Index() {
    const { t } = useTranslation();
    const columns = useMemo<ColumnDefinition[]>(
        () => [
            { title: t('slug'), field: 'slug', width: 140 },
            { title: t('nombre'), field: 'name', widthGrow: 1 },
            { title: t('etiqueta'), field: 'label_text', widthGrow: 1 },
            { title: t('nivel'), field: 'level', width: 120 },
            { title: t('estado'), field: 'status', width: 120 },
        ],
        [t],
    );

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
            <ServerTable endpoint="/admin/modules/table" columns={columns} />
        </AppLayout>
    );
}
