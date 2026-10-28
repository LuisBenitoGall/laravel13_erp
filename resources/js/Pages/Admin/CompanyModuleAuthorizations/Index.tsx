import { Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

type CompanyOption = { id: number; name: string; slug: string };
type ModuleRow = {
    id: number;
    slug: string;
    label: string;
    level: string;
    color: string;
    icon: string;
    authorized: boolean;
};

export default function Index({
    companies,
    selected_company_id,
    modules,
    tenants_url,
}: {
    companies: CompanyOption[];
    selected_company_id: number | null;
    modules: ModuleRow[];
    tenants_url?: string;
}) {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'autorizacion_de_modulos',
                suboptionKey: 'dashboard',
                optionHref: '/admin/company-module-authorizations',
            }}
        >
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div className="flex items-center gap-3">
                    <label className="text-sm font-medium text-slate-700" htmlFor="company">
                        {t('empresa')}
                    </label>
                    <select
                        id="company"
                        className="rounded-md border border-slate-300 px-3 py-2 text-sm"
                        value={selected_company_id ?? ''}
                        onChange={(e) =>
                            router.get('/admin/company-module-authorizations', {
                                company_id: e.target.value,
                            })
                        }
                    >
                        {companies.map((company) => (
                            <option key={company.id} value={company.id}>
                                {company.name}
                            </option>
                        ))}
                    </select>
                </div>
                {tenants_url ? (
                    <Link href={tenants_url} className="text-sm text-teal-700 hover:underline">
                        {t('volver_a_tenants')}
                    </Link>
                ) : null}
            </div>

            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {modules.map((module) => (
                    <div key={module.id} className="rounded-lg border border-slate-200 bg-white p-4">
                        <div className="mb-2 font-medium text-slate-900">{module.label}</div>
                        <div className="mb-3 text-xs text-slate-500">
                            {module.slug} · {module.level}
                        </div>
                        <button
                            type="button"
                            className={`rounded-md px-3 py-1.5 text-sm font-medium text-white ${
                                module.authorized
                                    ? 'bg-slate-700 hover:bg-slate-800'
                                    : 'bg-teal-700 hover:bg-teal-800'
                            }`}
                            onClick={() => {
                                if (!selected_company_id) {
                                    return;
                                }
                                router.post(
                                    module.authorized
                                        ? `/admin/company-module-authorizations/${selected_company_id}/${module.id}/revoke`
                                        : `/admin/company-module-authorizations/${selected_company_id}/${module.id}/authorize`,
                                );
                            }}
                        >
                            {module.authorized
                                ? t('revocar')
                                : t('autorizar')}
                        </button>
                    </div>
                ))}
            </div>
        </AppLayout>
    );
}
