import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

type SectorOption = {
    id: number;
    name: string;
};

type Props = {
    company: {
        id: number;
        name: string;
        tradename: string | null;
        nif: string | null;
        logo_url: string | null;
        sectors: SectorOption[];
    };
};

export default function Show({ company }: Props) {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'directorio',
                suboptionKey: 'directorio_ficha',
                optionHref: '/companies/company-directory',
                suboptionParams: { name: company.name },
            }}
            showBack
            backHref="/companies/company-directory"
        >
            <article className="mx-auto max-w-3xl space-y-6 rounded-xl border border-slate-200 bg-white p-6">
                <div className="flex flex-wrap items-start gap-4">
                    <div className="flex h-24 w-24 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                        {company.logo_url ? (
                            <img
                                src={company.logo_url}
                                alt=""
                                className="max-h-full max-w-full object-contain"
                            />
                        ) : (
                            <span className="text-sm text-slate-400">{t('sin_logo')}</span>
                        )}
                    </div>
                    <div className="min-w-0 space-y-1">
                        <h1 className="text-xl font-semibold text-slate-900">{company.name}</h1>
                        {company.tradename ? (
                            <p className="text-sm text-slate-600">{company.tradename}</p>
                        ) : null}
                        {company.nif ? (
                            <p className="text-sm text-slate-600">
                                {t('nif')}: {company.nif}
                            </p>
                        ) : null}
                    </div>
                </div>

                <section className="space-y-2">
                    <h2 className="text-sm font-semibold text-slate-900">{t('sectores')}</h2>
                    {company.sectors.length === 0 ? (
                        <p className="text-sm text-slate-500">{t('directorio_sin_sectores')}</p>
                    ) : (
                        <ul className="flex flex-wrap gap-2">
                            {company.sectors.map((sector) => (
                                <li
                                    key={sector.id}
                                    className="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-700"
                                >
                                    {sector.name}
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </article>
        </AppLayout>
    );
}
