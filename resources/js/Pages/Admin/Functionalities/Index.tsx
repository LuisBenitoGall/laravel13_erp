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
            { title: t('etiqueta'), field: 'label_text', widthGrow: 1 },
            { title: t('modulo'), field: 'module_name', width: 160 },
            { title: t('estado'), field: 'status', width: 120 },
        ],
        [t],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'funcionalidades',
                suboptionKey: 'dashboard',
                optionHref: '/admin/functionalities',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'crear',
                    href: '/admin/functionalities/create',
                    icon: Plus,
                },
            ]}
        >
            <ServerTable endpoint="/admin/functionalities/table" columns={columns} />
        </AppLayout>
    );
}
