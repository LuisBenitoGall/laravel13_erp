import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

type ModuleCard = {
    id: number;
    slug: string;
    name: string;
    label: string;
    color: string;
    icon: string;
    level: string;
    authorized: boolean;
    enabled: boolean;
};

export default function Index({
    company,
    modules,
}: {
    company: { id: number; name: string };
    modules: ModuleCard[];
}) {
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'modulos_empresa',
                suboptionKey: 'dashboard',
                optionHref: '/admin/company-modules',
            }}
            showBack={false}
            actions={[]}
        >
            <p className="mb-4 text-sm text-slate-600">
                {t('modulos_empresa_para', { name: company.name })}
            </p>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {modules.map((module) => {
                    const disabled = !module.authorized;

                    return (
                        <div
                            key={module.id}
                            className={`rounded-lg border p-4 ${
                                disabled
                                    ? 'border-slate-200 bg-slate-100 opacity-60'
                                    : 'border-slate-200 bg-white'
                            }`}
                        >
                            <div className="mb-3 flex items-center gap-2">
                                <span
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-md text-xs font-bold text-white"
                                    style={{ backgroundColor: disabled ? '#94a3b8' : module.color }}
                                >
                                    {module.icon.slice(0, 2)}
                                </span>
                                <div>
                                    <div className="font-medium text-slate-900">{module.label}</div>
                                    <div className="text-xs text-slate-500">
                                        {module.level}
                                        {!module.authorized
                                            ? ` · ${t('no_autorizado')}`
                                            : ''}
                                    </div>
                                </div>
                            </div>
                            {module.authorized ? (
                                <button
                                    type="button"
                                    className={`rounded-md px-3 py-1.5 text-sm font-medium text-white ${
                                        module.enabled
                                            ? 'bg-slate-700 hover:bg-slate-800'
                                            : 'bg-teal-700 hover:bg-teal-800'
                                    }`}
                                    onClick={() =>
                                        router.post(
                                            module.enabled
                                                ? `/admin/company-modules/${module.id}/disable`
                                                : `/admin/company-modules/${module.id}/enable`,
                                        )
                                    }
                                >
                                    {module.enabled
                                        ? t('apagar')
                                        : t('encender')}
                                </button>
                            ) : (
                                <span className="text-xs text-slate-500">
                                    {t('requiere_autorizacion')}
                                </span>
                            )}
                        </div>
                    );
                })}
            </div>
        </AppLayout>
    );
}
