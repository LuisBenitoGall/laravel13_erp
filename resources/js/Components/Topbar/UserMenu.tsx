import { Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

function initialsFromName(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0][0] ?? ''}${parts[1][0] ?? ''}`.toUpperCase();
}

export default function UserMenu({
    name,
    email,
    avatarUrl = null,
}: {
    name: string;
    email: string;
    avatarUrl?: string | null;
}) {
    const { t } = useTranslation();
    const initials = initialsFromName(name);

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="overflow-hidden rounded-full bg-teal-700 p-0 text-xs font-semibold text-white hover:bg-teal-800 hover:text-white"
                    aria-label={t('menu_usuario')}
                    title={email}
                >
                    {avatarUrl ? (
                        <img src={avatarUrl} alt="" className="h-full w-full object-cover" />
                    ) : (
                        initials
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem asChild>
                    <Link href="/admin/profile" className="cursor-pointer">
                        {t('perfil')}
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem
                    onSelect={() => {
                        router.post('/logout');
                    }}
                >
                    {t('salir')}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
