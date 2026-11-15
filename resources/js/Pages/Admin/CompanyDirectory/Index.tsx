import { Link, router } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type SectorOption = {
    id: number;
    name: string;
};

type DirectoryCompany = {
    id: number;
    name: string;
    tradename: string | null;
    nif: string | null;
    logo_url: string | null;
    sectors: SectorOption[];
};

type Props = {
    companies: {
        data: DirectoryCompany[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
        links: {
            prev: string | null;
            next: string | null;
        };
    };
    sectors: SectorOption[];
    filters: {
        sector_ids: number[];
        sort: string;
    };
};

export default function Index({ companies, sectors, filters }: Props) {
    const { t } = useTranslation();

    const sortOptions = useMemo(
        () => [
            { value: 'name_asc', label: t('directorio_orden_nombre_asc') },
            { value: 'name_desc', label: t('directorio_orden_nombre_desc') },
            { value: 'recent', label: t('directorio_orden_recientes') },
        ],
        [t],
    );

    function visit(next: { sector_ids?: number[]; sort?: string; page?: number }) {
        router.get(
            '/admin/company-directory',
            {
                sector_ids: next.sector_ids ?? filters.sector_ids,
                sort: next.sort ?? filters.sort,
                page: next.page ?? 1,
            },
            { preserveState: true, replace: true },
        );
    }

    function toggleSector(id: number, checked: boolean) {
        const selected = new Set(filters.sector_ids);
        if (checked) {
            selected.add(id);
        } else {
            selected.delete(id);
        }
        visit({ sector_ids: [...selected].sort((a, b) => a - b), page: 1 });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'directorio',
                suboptionKey: 'dashboard',
                optionHref: '/admin/company-directory',
            }}
            showBack={false}
        >
            <div className="grid gap-6 lg:grid-cols-[minmax(220px,280px)_1fr]">
                <aside className="space-y-4 rounded-xl border border-slate-200 bg-white p-4">
                    <div>
                        <h2 className="text-sm font-semibold text-slate-900">{t('sectores')}</h2>
                        <p className="mt-1 text-xs text-slate-500">{t('directorio_filtro_sectores_ayuda')}</p>
                    </div>
                    {sectors.length === 0 ? (
                        <p className="text-sm text-slate-500">{t('sectores_sin_opciones')}</p>
                    ) : (
                        <ul className="space-y-2">
                            {sectors.map((sector) => (
                                <li key={sector.id}>
                                    <Checkbox
                                        id={`directory-sector-${sector.id}`}
                                        label={sector.name}
                                        checked={filters.sector_ids.includes(sector.id)}
                                        onCheckedChange={(value) => toggleSector(sector.id, value)}
                                    />
                                </li>
                            ))}
                        </ul>
                    )}
                </aside>

                <section className="space-y-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <p className="text-sm text-slate-600">
                            {t('directorio_resultados', { count: companies.meta.total })}
                        </p>
                        <div className="w-56">
                            <Select
                                id="directory-sort"
                                value={filters.sort}
                                options={sortOptions}
                                onChange={(value) => visit({ sort: value, page: 1 })}
                            />
                        </div>
                    </div>

                    {companies.data.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                            {t('directorio_vacio')}
                        </div>
                    ) : (
                        <ul className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            {companies.data.map((company) => (
                                <li key={company.id}>
                                    <Link
                                        href={`/admin/company-directory/${company.id}`}
                                        className="flex h-full flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300 hover:shadow-sm"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg bg-slate-100">
                                                {company.logo_url ? (
                                                    <img
                                                        src={company.logo_url}
                                                        alt=""
                                                        className="max-h-full max-w-full object-contain"
                                                    />
                                                ) : (
                                                    <span className="text-xs text-slate-400">
                                                        {t('sin_logo')}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="min-w-0">
                                                <h3 className="truncate text-sm font-semibold text-slate-900">
                                                    {company.name}
                                                </h3>
                                                {company.tradename ? (
                                                    <p className="truncate text-xs text-slate-500">
                                                        {company.tradename}
                                                    </p>
                                                ) : null}
                                            </div>
                                        </div>
                                        {company.nif ? (
                                            <p className="text-xs text-slate-600">
                                                {t('nif')}: {company.nif}
                                            </p>
                                        ) : null}
                                        {company.sectors.length > 0 ? (
                                            <p className="line-clamp-2 text-xs text-slate-500">
                                                {company.sectors.map((s) => s.name).join(' · ')}
                                            </p>
                                        ) : null}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}

                    {companies.meta.last_page > 1 ? (
                        <div className="flex items-center justify-between gap-3">
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={!companies.links.prev}
                                onClick={() =>
                                    visit({ page: Math.max(1, companies.meta.current_page - 1) })
                                }
                            >
                                {t('anterior')}
                            </Button>
                            <span className="text-sm text-slate-600">
                                {companies.meta.current_page} / {companies.meta.last_page}
                            </span>
                            <Button
                                type="button"
                                variant="secondary"
                                disabled={!companies.links.next}
                                onClick={() =>
                                    visit({
                                        page: Math.min(
                                            companies.meta.last_page,
                                            companies.meta.current_page + 1,
                                        ),
                                    })
                                }
                            >
                                {t('siguiente')}
                            </Button>
                        </div>
                    ) : null}
                </section>
            </div>
        </AppLayout>
    );
}
