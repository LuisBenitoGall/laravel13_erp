import { useForm, usePage } from '@inertiajs/react';
import { MoreVertical } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, Input } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';

export default function FavoritesPopover() {
    const { t } = useTranslation();
    const page = usePage();
    const [open, setOpen] = useState(false);

    const form = useForm({
        name: '',
        url: page.url.startsWith('/') ? page.url : `/${page.url}`,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        const url = page.url.startsWith('/') ? page.url : `/${page.url}`;
        form.transform(() => ({
            name: form.data.name,
            url,
        }));
        form.post('/admin/preferences', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('name');
                setOpen(false);
            },
        });
    }

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    aria-label={t('favoritos')}
                >
                    <MoreVertical aria-hidden />
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" className="w-80 p-0">
                <div className="border-b border-slate-200 px-3 py-2">
                    <p className="text-sm font-semibold text-slate-900">
                        {t('mis_opciones_preferidas')}
                    </p>
                </div>
                <form onSubmit={submit} className="space-y-3 px-3 py-3">
                    <Field
                        label={t('nombre')}
                        htmlFor="favorite-name"
                        error={form.errors.name}
                        required
                    >
                        <Input
                            id="favorite-name"
                            value={form.data.name}
                            maxLength={255}
                            autoComplete="off"
                            onChange={(e) => form.setData('name', e.target.value)}
                        />
                    </Field>
                    <p className="text-xs text-slate-500">{t('favorito_vista_actual_ayuda')}</p>
                    {form.errors.url ? (
                        <p className="text-xs text-red-600">{form.errors.url}</p>
                    ) : null}
                    <div className="flex justify-end">
                        <Button type="submit" size="sm" disabled={form.processing}>
                            {t('guardar')}
                        </Button>
                    </div>
                </form>
            </PopoverContent>
        </Popover>
    );
}
