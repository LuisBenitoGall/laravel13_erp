import { router, usePage } from '@inertiajs/react';
import { Globe } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

type SharedProps = {
    locale: string;
    supportedLocales: string[];
};

export default function LocaleSwitcherCompact() {
    const { t } = useTranslation();
    const { locale, supportedLocales } = usePage<SharedProps>().props;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    className="gap-1.5 px-2"
                    aria-label={t('idioma')}
                >
                    <Globe className="size-4" aria-hidden />
                    <span className="text-xs font-semibold uppercase tracking-wide">
                        {locale}
                    </span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start">
                {supportedLocales.map((code) => (
                    <DropdownMenuItem
                        key={code}
                        onSelect={() => {
                            if (code === locale) {
                                return;
                            }
                            router.post('/locale', { locale: code }, { preserveScroll: true });
                        }}
                    >
                        <span className="uppercase">{code}</span>
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
