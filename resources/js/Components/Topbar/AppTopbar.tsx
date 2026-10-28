import { Menu, PanelLeftClose, PanelLeftOpen } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import ChatStub from '@/Components/Topbar/ChatStub';
import CompanySwitcher from '@/Components/Topbar/CompanySwitcher';
import FavoritesStub from '@/Components/Topbar/FavoritesStub';
import LocaleSwitcherCompact from '@/Components/Topbar/LocaleSwitcherCompact';
import NotesStub from '@/Components/Topbar/NotesStub';
import NotificationsPanel from '@/Components/Topbar/NotificationsPanel';
import UserMenu from '@/Components/Topbar/UserMenu';
import { Button } from '@/Components/ui/button';

type CompanyOption = { id: number; name: string };

export default function AppTopbar({
    asideCollapsed,
    onToggleAside,
    tenantId,
    tenantName,
    companies,
    user,
}: {
    asideCollapsed: boolean;
    onToggleAside: () => void;
    tenantId: number | null;
    tenantName: string | null;
    companies: CompanyOption[];
    user: { name: string; email: string };
}) {
    const { t } = useTranslation();

    return (
        <header className="flex h-14 items-center justify-between gap-3 border-b border-slate-200 bg-white px-3 sm:px-4">
            <div className="flex items-center">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    onClick={onToggleAside}
                    aria-label={asideCollapsed ? t('expandir_menu') : t('comprimir_menu')}
                    aria-expanded={!asideCollapsed}
                >
                    {asideCollapsed ? (
                        <PanelLeftOpen aria-hidden className="hidden sm:block" />
                    ) : (
                        <PanelLeftClose aria-hidden className="hidden sm:block" />
                    )}
                    <Menu aria-hidden className="sm:hidden" />
                </Button>
            </div>
            <div className="flex min-w-0 items-center gap-1 sm:gap-2">
                <LocaleSwitcherCompact />
                <NotificationsPanel />
                <NotesStub />
                <ChatStub />
                <FavoritesStub />
                <CompanySwitcher
                    tenantId={tenantId}
                    tenantName={tenantName}
                    companies={companies}
                />
                <UserMenu name={user.name} email={user.email} />
            </div>
        </header>
    );
}
