import { Head, usePage } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { type ReactNode, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import AsideNav, { type AsideNavItem } from '@/Components/AsideNav';
import PageBreadcrumb, { type PageBreadcrumbData } from '@/Components/PageBreadcrumb';
import AppTopbar from '@/Components/Topbar/AppTopbar';
import { readAsideCollapsed, writeAsideCollapsed } from '@/lib/asideStorage';

type SharedProps = {
    auth: { user: { id: number; name: string; email: string } | null };
    menu: AsideNavItem[];
    flash: { success?: string | null; error?: string | null };
    branding: { logo_url: string };
    tenant: { id: number | null; name: string | null; logo_url: string | null };
    available_companies: Array<{ id: number; name: string }>;
};

function readIsMobile(): boolean {
    if (typeof window === 'undefined') {
        return false;
    }

    return window.matchMedia('(max-width: 767px)').matches;
}

export default function AppLayout({
    title,
    breadcrumb,
    children,
}: {
    title?: string;
    breadcrumb?: PageBreadcrumbData;
    children: ReactNode;
}) {
    const { t } = useTranslation();
    const { auth, menu, flash, branding, tenant, available_companies } =
        usePage<SharedProps>().props;

    const documentTitle =
        title ??
        (breadcrumb
            ? `${t(breadcrumb.optionKey)} · ${t(breadcrumb.suboptionKey, breadcrumb.suboptionParams)}`
            : undefined);

    const [isMobile, setIsMobile] = useState(readIsMobile);
    /** Preferencia md+ (rail vs expandido). */
    const [desktopCollapsed, setDesktopCollapsed] = useState(
        () => readAsideCollapsed() ?? false,
    );
    /** Off-canvas móvil: true = aside visible. */
    const [mobileOpen, setMobileOpen] = useState(false);

    const asideCollapsed = isMobile ? !mobileOpen : desktopCollapsed;
    const railMode = !isMobile && desktopCollapsed;

    useEffect(() => {
        const media = window.matchMedia('(max-width: 767px)');
        const onChange = () => {
            const mobile = media.matches;
            setIsMobile(mobile);
            if (mobile) {
                setMobileOpen(false);
            } else {
                setDesktopCollapsed(readAsideCollapsed() ?? false);
            }
        };
        media.addEventListener('change', onChange);

        return () => media.removeEventListener('change', onChange);
    }, []);

    useEffect(() => {
        if (flash.success) {
            toast.success(flash.success);
        }
        if (flash.error) {
            toast.error(flash.error);
        }
    }, [flash.success, flash.error]);

    const toggleAside = (): void => {
        if (isMobile) {
            setMobileOpen((open) => !open);

            return;
        }

        setDesktopCollapsed((collapsed) => {
            const next = !collapsed;
            writeAsideCollapsed(next);

            return next;
        });
    };

    const user = auth.user;
    const erpInitial = (t('bienvenida_titulo') || 'ERP').slice(0, 1).toUpperCase();
    const companyInitial = (tenant.name ?? '?').slice(0, 1).toUpperCase();

    return (
        <div className="relative flex min-h-screen bg-slate-100 text-slate-900">
            {documentTitle ? <Head title={documentTitle} /> : null}

            {isMobile && mobileOpen ? (
                <button
                    type="button"
                    className="fixed inset-0 z-30 bg-slate-900/40"
                    aria-label={t('comprimir_menu')}
                    onClick={() => setMobileOpen(false)}
                />
            ) : null}

            <aside
                className={`z-40 flex flex-col border-r border-slate-200 bg-white transition-[width,transform] duration-200 ${
                    isMobile
                        ? `fixed inset-y-0 left-0 w-64 ${
                              mobileOpen ? 'translate-x-0' : '-translate-x-full'
                          }`
                        : `static ${railMode ? 'w-14' : 'w-64'}`
                }`}
            >
                <div className={`border-b border-slate-200 ${railMode ? 'py-2' : ''}`}>
                    <div
                        className={`flex items-center justify-center ${
                            railMode ? 'h-10 px-1' : 'h-14 px-4 justify-start'
                        }`}
                    >
                        {branding.logo_url ? (
                            <img
                                src={branding.logo_url}
                                alt={t('bienvenida_titulo')}
                                className={`w-auto object-contain ${railMode ? 'h-7' : 'h-8 max-w-full'}`}
                            />
                        ) : (
                            <span
                                className={
                                    railMode
                                        ? 'flex h-8 w-8 items-center justify-center rounded bg-teal-700 text-xs font-bold text-white'
                                        : 'text-sm font-semibold tracking-tight'
                                }
                            >
                                {railMode ? erpInitial : t('bienvenida_titulo')}
                            </span>
                        )}
                    </div>
                    <div
                        className={`flex items-center border-t border-slate-100 ${
                            railMode ? 'h-10 justify-center px-1' : 'h-12 justify-start px-4'
                        }`}
                    >
                        {tenant.logo_url ? (
                            <img
                                src={tenant.logo_url}
                                alt={tenant.name ?? ''}
                                className={`w-auto object-contain ${railMode ? 'h-6' : 'h-7 max-w-full'}`}
                            />
                        ) : (
                            <span
                                className={
                                    railMode
                                        ? 'flex h-7 w-7 items-center justify-center rounded bg-slate-200 text-[10px] font-semibold text-slate-700'
                                        : 'truncate text-xs font-medium text-slate-600'
                                }
                                title={tenant.name ?? undefined}
                            >
                                {railMode ? companyInitial : (tenant.name ?? '')}
                            </span>
                        )}
                    </div>
                </div>
                <AsideNav menu={menu} rail={railMode} />
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                {user ? (
                    <AppTopbar
                        asideCollapsed={asideCollapsed}
                        onToggleAside={toggleAside}
                        tenantId={tenant.id}
                        tenantName={tenant.name}
                        companies={available_companies}
                        user={{ name: user.name, email: user.email }}
                    />
                ) : null}
                <main className="flex-1 p-6">
                    {breadcrumb ? <PageBreadcrumb {...breadcrumb} /> : null}
                    {children}
                </main>
            </div>
            <Toaster richColors position="top-right" />
        </div>
    );
}
