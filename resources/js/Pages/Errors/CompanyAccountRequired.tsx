import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type Props = {
    messageKey: string;
    renewUrl: string;
};

export default function CompanyAccountRequired({ messageKey, renewUrl }: Props) {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mi_cuenta',
                suboptionKey: 'dashboard',
                optionHref: renewUrl,
            }}
            showBack={false}
        >
            <div className="rounded-xl border border-dashed border-amber-200 bg-amber-50 p-8">
                <h1 className="text-lg font-semibold text-slate-900">{t('acceso_bloqueado')}</h1>
                <p className="mt-3 text-sm text-slate-700">{t(messageKey)}</p>
                <div className="mt-6">
                    <Button asChild>
                        <Link href={renewUrl}>{t('ir_a_renovar_cuenta')}</Link>
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
