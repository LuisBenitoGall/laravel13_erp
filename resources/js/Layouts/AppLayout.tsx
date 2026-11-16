import { Head, Link, router, usePage } from '@inertiajs/react';
import { Toaster, toast } from 'sonner';
import { type ReactNode, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import AsideNav, { type AsideNavItem } from '@/Components/AsideNav';
import NavOriginTracker from '@/Components/NavOriginTracker';
import PageBreadcrumb, { type PageBreadcrumbData } from '@/Components/PageBreadcrumb';
import AppTopbar from '@/Components/Topbar/AppTopbar';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import ViewActionBar, { type ViewAction } from '@/Components/ViewActionBar';
import { readAsideCollapsed, writeAsideCollapsed } from '@/lib/asideStorage';

type CompanySetupShared = {
    incomplete: boolean;
    show_modal: boolean;
    hub_url: string;
    wizard_url: string;
    completed_steps: number;
    total_steps: number;
} | null;

type SharedProps = {
    auth: { user: { id: number; name: string; email: string; avatar_url?: string | null } | null };
    menu: AsideNavItem[];
    flash: { success?: string | null; error?: string | null };
    branding: { logo_url: string };
    tenant: { id: number | null; name: string | null; logo_url: string | null };
    available_companies: Array<{ id: number; name: string }>;
    company_setup?: CompanySetupShared;
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
    actions,
    /** Index / raíces: `false`. Create/Edit mantienen default `true` (Volver si hay origen). */
    showBack = true,
    /** Destino fijo de Volver. Prioridad sobre el origen de navegación. */
    backHref,
    children,
}: {
    title?: string;
    breadcrumb?: PageBreadcrumbData;
    actions?: ViewAction[];
    showBack?: boolean;
    backHref?: string;
    children: ReactNode;
}) {
    const { t } = useTranslation();
    const { auth, menu, flash, branding, tenant, available_companies, company_setup } =
        usePage<SharedProps>().props;

    const [setupModalOpen, setSetupModalOpen] = useState(
        () => Boolean(company_setup?.show_modal),
    );

    useEffect(() => {
        setSetupModalOpen(Boolean(company_setup?.show_modal));
    }, [company_setup?.show_modal]);

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
            <NavOriginTracker />
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
                            railMode ? 'h-10 px-1' : 'h-14 px-4'
                        }`}
                    >
                        {branding.logo_url ? (
                            <img
                                src={branding.logo_url}
                                alt={t('bienvenida_titulo')}
                                className={`mx-auto w-auto object-contain ${railMode ? 'h-7' : 'h-8 max-w-full'}`}
                            />
                        ) : (
                            <span
                                className={
                                    railMode
                                        ? 'flex h-8 w-8 items-center justify-center rounded bg-teal-700 text-xs font-bold text-white'
                                        : 'text-center text-sm font-semibold tracking-tight'
                                }
                            >
                                {railMode ? erpInitial : t('bienvenida_titulo')}
                            </span>
                        )}
                    </div>
                    <div
                        className={`flex items-center justify-center border-t border-slate-100 ${
                            railMode ? 'h-12 px-1' : 'h-14 px-4'
                        }`}
                    >
                        {tenant.logo_url ? (
                            <img
                                src={tenant.logo_url}
                                alt={tenant.name ?? ''}
                                className={`mx-auto w-auto object-contain ${railMode ? 'h-10' : 'h-11 max-w-full'}`}
                            />
                        ) : (
                            <span
                                className={
                                    railMode
                                        ? 'flex h-7 w-7 items-center justify-center rounded bg-slate-200 text-[10px] font-semibold text-slate-700'
                                        : 'truncate text-center text-xs font-medium text-slate-600'
                                }
                                title={tenant.name ?? undefined}
                            >
                                {railMode ? companyInitial : (tenant.name ?? '')}
                            </span>
                        )}
                    </div>
                </div>
                <AsideNav menu={menu ?? []} rail={railMode} />
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                {user ? (
                    <AppTopbar
                        asideCollapsed={asideCollapsed}
                        onToggleAside={toggleAside}
                        tenantId={tenant.id}
                        tenantName={tenant.name}
                        companies={available_companies}
                        user={{
                            name: user.name,
                            email: user.email,
                            avatar_url: user.avatar_url ?? null,
                        }}
                    />
                ) : null}
                <main className="flex-1 px-6 pb-6 pt-3">
                    {company_setup?.incomplete ? (
                        <div
                            className="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
                            role="status"
                        >
                            <div>
                                <p className="font-medium">{t('configuracion_empresa_incompleta')}</p>
                                <p className="text-amber-900/80">
                                    {t('configuracion_empresa_incompleta_ayuda')}
                                </p>
                            </div>
                            <Button asChild size="sm" variant="outline">
                                <Link href={company_setup.wizard_url}>
                                    {t('ir_a_configuracion_empresa')}
                                </Link>
                            </Button>
                        </div>
                    ) : null}
                    {breadcrumb ? <PageBreadcrumb {...breadcrumb} /> : null}
                    <ViewActionBar actions={actions ?? []} showBack={showBack} backHref={backHref} />
                    {children}
                </main>
            </div>

            <Dialog open={setupModalOpen} onOpenChange={setSetupModalOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('completar_configuracion_empresa')}</DialogTitle>
                        <DialogDescription>
                            {t('configuracion_empresa_incompleta_ayuda')}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="secondary"
                            onClick={() => {
                                router.post(
                                    '/admin/company-settings/dismiss-modal',
                                    {},
                                    {
                                        preserveScroll: true,
                                        onSuccess: () => setSetupModalOpen(false),
                                    },
                                );
                            }}
                        >
                            {t('ahora_no')}
                        </Button>
                        <Button asChild>
                            <Link
                                href={company_setup?.wizard_url ?? '/admin/company-settings?wizard=1'}
                                onClick={() => setSetupModalOpen(false)}
                            >
                                {t('ir_a_configuracion_empresa')}
                            </Link>
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <Toaster richColors position="top-right" />
        </div>
    );
}
