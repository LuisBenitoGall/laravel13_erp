import { Bell } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

export default function NotificationsPanel() {
    const { t } = useTranslation();

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    className="relative"
                    aria-label={t('notificaciones')}
                >
                    <Bell aria-hidden />
                    <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-slate-200 px-1 text-[10px] font-medium text-slate-700">
                        0
                    </span>
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-80 p-0">
                <div className="border-b border-slate-200 px-3 py-2">
                    <p className="text-sm font-semibold text-slate-900">{t('notificaciones')}</p>
                </div>
                <div className="px-3 py-8 text-center text-sm text-slate-500">
                    {t('no_hay_notificaciones')}
                </div>
                <div className="flex items-center justify-between gap-2 border-t border-slate-200 px-2 py-2">
                    <Button type="button" variant="ghost" size="sm">
                        {t('actualizar')}
                    </Button>
                    <Button type="button" variant="ghost" size="sm">
                        {t('ver_todo')}
                    </Button>
                    <Button type="button" variant="ghost" size="sm">
                        {t('marcar_leidas')}
                    </Button>
                </div>
            </PopoverContent>
        </Popover>
    );
}
