import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

export default function Show({
    profile,
}: {
    profile: { name: string; email: string };
}) {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'perfil',
                suboptionKey: 'dashboard',
                optionHref: '/profile',
            }}
            showBack={false}
            actions={[]}
        >
            <dl className="max-w-lg space-y-4 rounded-lg border border-slate-200 bg-white p-6">
                <div>
                    <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">
                        {t('nombre')}
                    </dt>
                    <dd className="mt-1 text-sm text-slate-900">{profile.name}</dd>
                </div>
                <div>
                    <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">
                        {t('email')}
                    </dt>
                    <dd className="mt-1 text-sm text-slate-900">{profile.email}</dd>
                </div>
                <p className="text-sm text-slate-500">{t('perfil_proximamente')}</p>
            </dl>
        </AppLayout>
    );
}
