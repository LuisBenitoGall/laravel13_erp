import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

export default function Dashboard() {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'dashboard',
                suboptionKey: 'dashboard',
                optionHref: '/dashboard',
            }}
            showBack={false}
            actions={[]}
        >
            <p className="text-sm text-slate-600">{t('panel_subtitulo')}</p>
        </AppLayout>
    );
}
