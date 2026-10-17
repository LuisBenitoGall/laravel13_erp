import { Link, router, usePage } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { type ReactNode, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

import LanguageSwitcher from '@/Components/LanguageSwitcher';

type MenuItem = {
    slug: string;
    label: string;
    color: string;
    icon: string;
    functionalities: Array<{ slug: string; label: string; href: string }>;
};

type SharedProps = {
    auth: { user: { id: number; name: string; email: string } | null };
    menu: MenuItem[];
    flash: { success?: string | null; error?: string | null };
    tenant: { id: number | null; name: string | null; logo_url: string | null };
    available_companies: Array<{ id: number; name: string }>;
};

export default function AppLayout({
    title,
    children,
}: {
    title?: string;
    children: ReactNode;
}) {
    const { t } = useTranslation();
    const { auth, menu, flash, tenant, available_companies } = usePage<SharedProps>().props;

    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash.success, flash.error]);

    return (
        <div className="flex min-h-screen bg-slate-100 text-slate-900">
            <aside className="flex w-64 flex-col border-r border-slate-200 bg-white">
                <div className="flex h-14 items-center gap-2 border-b border-slate-200 px-4">
                    {tenant.logo_url ? (
                        <img src={tenant.logo_url} alt="" className="h-8 w-auto" />
                    ) : (
                        <span className="text-sm font-semibold tracking-tight">
                            {tenant.name ?? t('bienvenida_titulo')}
                        </span>
                    )}
                </div>
                <nav className="flex-1 space-y-4 overflow-y-auto p-3">
                    {menu.map((module) => (
                        <div key={module.slug}>
                            <div
                                className="mb-1 flex items-center gap-2 px-2 text-xs font-semibold uppercase tracking-wide"
                                style={{ color: module.color }}
                            >
                                <span>{module.icon}</span>
                                <span>{module.label}</span>
                            </div>
                            <ul className="space-y-1">
                                {module.functionalities.map((item) => (
                                    <li key={item.slug}>
                                        <Link
                                            href={item.href}
                                            className="block rounded-md px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-100"
                                        >
                                            {item.label}
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </nav>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex h-14 items-center justify-between border-b border-slate-200 bg-white px-6">
                    <h1 className="text-base font-semibold">{title ?? ''}</h1>
                    <div className="flex items-center gap-3 text-sm">
                        <LanguageSwitcher />
                        {available_companies.length > 1 && (
                            <select
                                className="rounded-md border border-slate-300 px-2 py-1"
                                value={tenant.id ?? ''}
                                onChange={(e) =>
                                    router.post('/companies/switch', {
                                        company_id: Number(e.target.value),
                                    })
                                }
                            >
                                {available_companies.map((company) => (
                                    <option key={company.id} value={company.id}>
                                        {company.name}
                                    </option>
                                ))}
                            </select>
                        )}
                        <span className="text-slate-600">{auth.user?.name}</span>
                        <button
                            type="button"
                            className="rounded-md border border-slate-300 px-2 py-1 hover:bg-slate-50"
                            onClick={() => router.post('/logout')}
                        >
                            {t('salir')}
                        </button>
                    </div>
                </header>
                <main className="flex-1 p-6">{children}</main>
            </div>
            <Toaster richColors position="top-right" />
        </div>
    );
}
