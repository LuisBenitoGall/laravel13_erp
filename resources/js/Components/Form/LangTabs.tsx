import { usePage } from '@inertiajs/react';
import { useState, type ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

type PageProps = {
    supportedLocales?: string[];
};

export type LangTabsProps = {
    locales?: string[];
    children: (locale: string) => ReactNode;
    /** Locales con error de validación (espejo Inertia o RHF). */
    invalidLocales?: string[];
};

export function LangTabs({ locales, children, invalidLocales = [] }: LangTabsProps) {
    const { t } = useTranslation();
    const pageLocales = usePage<PageProps>().props.supportedLocales;
    const tabs = locales ?? pageLocales ?? ['es'];
    const [active, setActive] = useState(tabs[0] ?? 'es');
    const current = tabs.includes(active) ? active : (tabs[0] ?? 'es');

    return (
        <div>
            <div role="tablist" className="flex gap-1 border-b border-slate-200">
                {tabs.map((locale) => {
                    const selected = locale === current;
                    const invalid = invalidLocales.includes(locale);

                    return (
                        <button
                            key={locale}
                            type="button"
                            role="tab"
                            aria-selected={selected}
                            className={cn(
                                'border-b-2 px-3 py-2 text-sm',
                                selected
                                    ? 'border-teal-700 font-medium text-teal-800'
                                    : 'border-transparent text-slate-600',
                                invalid && 'text-red-600',
                            )}
                            onClick={() => setActive(locale)}
                        >
                            {t(`idioma_${locale}`)}
                        </button>
                    );
                })}
            </div>
            <div role="tabpanel" className="space-y-4 pt-4">
                {children(current)}
            </div>
        </div>
    );
}
