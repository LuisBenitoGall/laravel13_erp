import { Link, usePage } from '@inertiajs/react';
import { ChevronLeft, Trash2, type LucideIcon } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Button } from '@/Components/ui/button';
import { resolveBackHref } from '@/lib/navOrigin';

export type ViewAction =
    | { type: 'link'; labelKey: string; href: string; icon: LucideIcon }
    | { type: 'button'; labelKey: string; icon: LucideIcon; onClick: () => void }
    | {
          type: 'delete';
          onConfirm: () => void;
          labelKey?: string;
          confirmKey?: string;
      }
    | {
          /** Tipado para ampliación futura; sin UI en este change. */
          type: 'dropdown';
          labelKey: string;
          icon: LucideIcon;
          items: ReadonlyArray<{ labelKey: string; href?: string; onClick?: () => void }>;
      };

function isDelete(action: ViewAction): action is Extract<ViewAction, { type: 'delete' }> {
    return action.type === 'delete';
}

export default function ViewActionBar({
    actions = [],
    /** Index / raíces: false. Create/Edit: true (default) si hay origen. */
    showBack = true,
}: {
    actions?: ViewAction[];
    showBack?: boolean;
}) {
    const { t } = useTranslation();
    const { url } = usePage();
    const backHref = useMemo(
        () => (showBack ? resolveBackHref(url) : null),
        [showBack, url],
    );
    const [pendingDelete, setPendingDelete] = useState<Extract<
        ViewAction,
        { type: 'delete' }
    > | null>(null);

    const regular = actions.filter((action) => !isDelete(action) && action.type !== 'dropdown');
    const deletes = actions.filter(isDelete);

    if (!backHref && regular.length === 0 && deletes.length === 0) {
        return null;
    }

    return (
        <>
            <div className="mb-4 flex flex-wrap items-center justify-start gap-2">
                {backHref ? (
                    <Button variant="secondary" size="sm" asChild>
                        <Link href={backHref}>
                            <ChevronLeft aria-hidden />
                            {t('volver')}
                        </Link>
                    </Button>
                ) : null}

                {regular.map((action, index) => {
                    if (action.type === 'link') {
                        const Icon = action.icon;

                        return (
                            <Button key={`link-${action.href}-${index}`} size="sm" asChild>
                                <Link href={action.href}>
                                    <Icon aria-hidden />
                                    {t(action.labelKey)}
                                </Link>
                            </Button>
                        );
                    }

                    if (action.type === 'button') {
                        const Icon = action.icon;

                        return (
                            <Button
                                key={`button-${action.labelKey}-${index}`}
                                type="button"
                                size="sm"
                                onClick={action.onClick}
                            >
                                <Icon aria-hidden />
                                {t(action.labelKey)}
                            </Button>
                        );
                    }

                    return null;
                })}

                {deletes.map((action, index) => (
                    <Button
                        key={`delete-${index}`}
                        type="button"
                        size="sm"
                        variant="outline"
                        className="ml-1 border-red-300 text-red-700 hover:bg-red-50 hover:text-red-800"
                        onClick={() => setPendingDelete(action)}
                    >
                        <Trash2 aria-hidden />
                        {t(action.labelKey ?? 'eliminar')}
                    </Button>
                ))}
            </div>

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t(pendingDelete?.confirmKey ?? 'confirmar_eliminar')}
                confirmLabel={t('eliminar')}
                onConfirm={() => {
                    pendingDelete?.onConfirm();
                    setPendingDelete(null);
                }}
            />
        </>
    );
}
