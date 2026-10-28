import { StickyNote } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

export default function NotesStub() {
    const { t } = useTranslation();

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="relative"
                    aria-label={t('notas')}
                >
                    <StickyNote aria-hidden />
                    <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-slate-200 px-1 text-[10px] font-medium text-slate-700">
                        0
                    </span>
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-80 p-0">
                <div className="border-b border-slate-200 px-3 py-2">
                    <p className="text-sm font-semibold text-slate-900">{t('notas')}</p>
                </div>
                <div className="px-3 py-8 text-center text-sm text-slate-500">
                    {t('notas_vacias')}
                </div>
            </PopoverContent>
        </Popover>
    );
}
