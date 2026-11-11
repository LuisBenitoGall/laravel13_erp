import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

export default function ComingSoon() {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mi_cuenta',
                suboptionKey: 'dashboard',
                optionHref: '/account',
            }}
            showBack={false}
        >
            <div className="rounded-xl border border-dashed border-slate-200 bg-white p-8">
                <h1 className="text-lg font-semibold text-slate-900">{t('mi_cuenta')}</h1>
                <p className="mt-3 text-sm text-slate-600">{t('mi_cuenta_proximamente')}</p>
            </div>
        </AppLayout>
    );
}
