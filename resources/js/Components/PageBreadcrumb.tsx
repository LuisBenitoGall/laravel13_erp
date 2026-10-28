import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

export type PageBreadcrumbData = {
    optionKey: string;
    suboptionKey: string;
    optionHref: string;
    /** Params opcionales para interpolación i18n del suboption (p. ej. nombre). */
    suboptionParams?: Record<string, string | number>;
};

export default function PageBreadcrumb({
    optionKey,
    suboptionKey,
    optionHref,
    suboptionParams,
}: PageBreadcrumbData) {
    const { t } = useTranslation();

    return (
        <div className="mb-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
            <Link
                href={optionHref}
                className="text-xl font-semibold tracking-tight text-slate-900 hover:text-teal-800"
            >
                {t(optionKey)}
            </Link>
            <span className="text-sm font-normal text-slate-500">
                {t(suboptionKey, suboptionParams)}
            </span>
        </div>
    );
}
