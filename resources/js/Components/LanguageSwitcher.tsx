import { router, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

type SharedProps = {
    locale: string;
    supportedLocales: string[];
};

const localeLabels: Record<string, string> = {
    es: 'Castellano',
    en: 'English',
    ca: 'Català',
};

export default function LanguageSwitcher({ className = '' }: { className?: string }) {
    const { t } = useTranslation();
    const { locale, supportedLocales } = usePage<SharedProps>().props;

    return (
        <label className={`inline-flex items-center gap-2 text-sm ${className}`}>
            <span className="sr-only">{t('idioma')}</span>
            <select
                aria-label={t('idioma')}
                className="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm"
                value={locale}
                onChange={(e) => {
                    router.post(
                        '/locale',
                        { locale: e.target.value },
                        { preserveScroll: true },
                    );
                }}
            >
                {supportedLocales.map((code) => (
                    <option key={code} value={code}>
                        {localeLabels[code] ?? code}
                    </option>
                ))}
            </select>
        </label>
    );
}
