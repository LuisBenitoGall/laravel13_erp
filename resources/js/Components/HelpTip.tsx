import { Info } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

type HelpTipPayload = {
    title?: string;
    excerpt?: string;
    content?: string;
    referrer?: string[] | string | null;
    video?: string[] | string | null;
};

type SharedHelpProps = {
    help_tips?: Record<string, HelpTipPayload>;
};

function toList(value: string[] | string | null | undefined): string[] {
    if (Array.isArray(value)) {
        return value.filter((item): item is string => typeof item === 'string' && item.trim() !== '');
    }

    if (typeof value === 'string' && value.trim() !== '') {
        return [value.trim()];
    }

    return [];
}

export default function HelpTip({ code }: { code: string }) {
    const { t } = useTranslation();
    const { help_tips = {} } = usePage<SharedHelpProps>().props;
    const tip = help_tips[code];

    // Visible si hay título, extracto o cuerpo (Create nace con title vacío hasta Edit).
    if (!tip || (!tip.title && !tip.excerpt && !tip.content)) {
        return null;
    }

    const referrers = toList(tip.referrer);
    const videos = toList(tip.video);

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="shrink-0 self-center rounded-full bg-amber-100 text-amber-800 hover:bg-amber-200 hover:text-amber-900"
                    aria-label={t('ayuda')}
                    data-testid={`helptip-${code}`}
                >
                    <Info aria-hidden className="h-3.5 w-3.5" />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="start" className="w-80 space-y-2 p-3">
                {tip.title ? (
                    <p className="text-sm font-semibold text-slate-900">{tip.title}</p>
                ) : null}
                {tip.excerpt ? (
                    <p className="text-sm whitespace-pre-wrap text-slate-700">{tip.excerpt}</p>
                ) : null}
                {tip.content ? (
                    <div
                        className="prose prose-sm max-w-none text-slate-700"
                        dangerouslySetInnerHTML={{ __html: tip.content }}
                    />
                ) : null}
                {referrers.length > 0 ? (
                    <div className="flex flex-col gap-1">
                        {referrers.map((href, index) => (
                            <a
                                key={`${href}-${index}`}
                                href={href}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-block text-sm text-sky-700 underline-offset-2 hover:underline"
                            >
                                {index === 0 ? t('leer_mas') : href}
                            </a>
                        ))}
                    </div>
                ) : null}
                {videos.length > 0 ? (
                    <div className="flex flex-col gap-1">
                        {videos.map((href, index) => (
                            <a
                                key={`${href}-${index}`}
                                href={href}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-block text-sm text-sky-700 underline-offset-2 hover:underline"
                            >
                                {index === 0 ? t('video_url') : href}
                            </a>
                        ))}
                    </div>
                ) : null}
            </PopoverContent>
        </Popover>
    );
}
