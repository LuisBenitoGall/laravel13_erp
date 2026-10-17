import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import LanguageSwitcher from '@/Components/LanguageSwitcher';

export default function Notice() {
    const { t } = useTranslation();

    return (
        <div className="relative mx-auto max-w-2xl px-4 py-12">
            <div className="absolute right-4 top-4">
                <LanguageSwitcher />
            </div>
            <h1 className="text-2xl font-semibold text-slate-900">{t('aviso_legal')}</h1>
            <p className="mt-4 text-sm text-slate-600">{t('aviso_legal_cuerpo')}</p>
            <Link href="/register" className="mt-6 inline-block text-sm text-teal-700 underline">
                {t('volver_al_registro')}
            </Link>
        </div>
    );
}
