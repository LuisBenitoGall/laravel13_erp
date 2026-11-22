import { Link } from '@inertiajs/react';
import { X } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import UserProfileReadout, {
    type UserProfileReadoutCompany,
    type UserProfileReadoutContact,
    type UserProfileReadoutUser,
} from '@/Components/Users/UserProfileReadout';
import { Button } from '@/Components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
} from '@/Components/ui/sheet';

export type UserQuickViewPayload = {
    user: UserProfileReadoutUser;
    emails: UserProfileReadoutContact[];
    phones: UserProfileReadoutContact[];
    companies: UserProfileReadoutCompany[];
    avatar_url: string | null;
    can_edit: boolean;
};

export type UserQuickViewPanelProps = {
    open: boolean;
    onClose: () => void;
    loading: boolean;
    payload: UserQuickViewPayload | null;
    /** Si null/undefined, no se muestra Editar. */
    editHref?: string | null;
};

function preventDismiss(event: Event): void {
    event.preventDefault();
}

export default function UserQuickViewPanel({
    open,
    onClose,
    loading,
    payload,
    editHref = null,
}: UserQuickViewPanelProps) {
    const { t } = useTranslation();
    const fullName = payload
        ? [payload.user.name, payload.user.surname].filter(Boolean).join(' ')
        : '';

    return (
        <Sheet open={open} modal={false}>
            <SheetContent
                side="right"
                className="w-full max-w-md overflow-y-auto"
                data-testid="user-quick-view-panel"
                onInteractOutside={preventDismiss}
                onPointerDownOutside={preventDismiss}
                onEscapeKeyDown={preventDismiss}
            >
                <SheetHeader className="flex-row items-start justify-between gap-3 space-y-0 pr-0">
                    <div className="min-w-0 flex-1 space-y-1">
                        <SheetTitle>{t('usuario')}</SheetTitle>
                        {fullName ? (
                            <p className="truncate text-sm font-medium text-slate-700">{fullName}</p>
                        ) : null}
                    </div>
                    <div className="flex shrink-0 items-center gap-1">
                        {editHref ? (
                            <Button asChild size="sm" variant="secondary">
                                <Link href={editHref}>{t('editar')}</Link>
                            </Button>
                        ) : null}
                        <Button
                            type="button"
                            size="icon-sm"
                            variant="ghost"
                            aria-label={t('cerrar')}
                            data-testid="user-quick-view-close"
                            onClick={onClose}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    </div>
                </SheetHeader>

                <div className="mt-2">
                    {loading && !payload ? (
                        <div
                            className="animate-pulse space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-5"
                            data-testid="user-quick-view-loading"
                        >
                            <div className="mx-auto h-20 w-20 rounded-full bg-slate-200" />
                            <div className="mx-auto h-4 w-32 rounded bg-slate-200" />
                            <div className="h-3 w-full rounded bg-slate-200" />
                            <div className="h-3 w-5/6 rounded bg-slate-200" />
                        </div>
                    ) : payload ? (
                        <UserProfileReadout
                            user={payload.user}
                            emails={payload.emails}
                            phones={payload.phones}
                            companies={payload.companies}
                            avatarUrl={payload.avatar_url}
                        />
                    ) : null}
                </div>
            </SheetContent>
        </Sheet>
    );
}
