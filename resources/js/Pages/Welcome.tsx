import { useTranslation } from 'react-i18next';

import LanguageSwitcher from '@/Components/LanguageSwitcher';

export default function Welcome() {
    const { t } = useTranslation();

    return (
        <main className="relative flex min-h-screen items-center justify-center bg-slate-50">
            <div className="absolute right-4 top-4">
                <LanguageSwitcher />
            </div>
            <div className="space-y-2 text-center">
                <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
                    {t('bienvenida_titulo')}
                </h1>
                <p className="text-slate-600">{t('bienvenida_subtitulo')}</p>
            </div>
        </main>
    );
}
