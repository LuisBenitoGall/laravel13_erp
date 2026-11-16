import { Info } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

type HelpTipPayload = {
    title?: string;
    excerpt?: string;
    content?: string;
    referrer?: string | null;
    video?: string | null;
};

type SharedHelpProps = {
    help_tips?: Record<string, HelpTipPayload>;
};

export default function HelpTip({ code }: { code: string }) {
    const { t } = useTranslation();
    const { help_tips = {} } = usePage<SharedHelpProps>().props;
    const tip = help_tips[code];

    if (!tip || (!tip.title && !tip.excerpt)) {
        return null;
    }

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="text-slate-500 hover:text-slate-800"
                    aria-label={t('ayuda')}
                >
                    <Info aria-hidden className="h-4 w-4" />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="start" className="w-80 space-y-2 p-3">
                {tip.title ? (
                    <p className="text-sm font-semibold text-slate-900">{tip.title}</p>
                ) : null}
                {tip.excerpt ? (
                    <p className="text-sm whitespace-pre-wrap text-slate-700">{tip.excerpt}</p>
                ) : null}
                {tip.referrer ? (
                    <a
                        href={tip.referrer}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-block text-sm text-sky-700 underline-offset-2 hover:underline"
                    >
                        {t('leer_mas')}
                    </a>
                ) : null}
            </PopoverContent>
        </Popover>
    );
}
