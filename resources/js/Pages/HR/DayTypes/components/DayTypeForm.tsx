import { router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { DayTypeRow } from '@/types/calendar';

type DayTypeFormData = {
    name: string;
    color: string;
    is_working: boolean;
};

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<DayTypeRow>;
};

export default function DayTypeForm({ mode, submitUrl, method = 'post', initial }: Props) {
    const { t } = useTranslation();

    const form = useForm<DayTypeFormData>({
        name: initial?.name ?? '',
        color: initial?.color ?? '#3b82f6',
        is_working: initial?.is_working ?? true,
    });

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
        >
            <Field label={t('nombre')} htmlFor="day-type-name" error={form.errors.name} required>
                <Input
                    id="day-type-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-end">
                <Field label={t('color')} htmlFor="day-type-color" error={form.errors.color} required>
                    <div className="flex items-center gap-3">
                        <input
                            id="day-type-color"
                            type="color"
                            value={form.data.color}
                            onChange={(e) => form.setData('color', e.target.value)}
                            className="h-10 w-14 cursor-pointer rounded border border-slate-200 bg-white p-1"
                        />
                        <Input
                            value={form.data.color}
                            maxLength={7}
                            onChange={(e) => form.setData('color', e.target.value)}
                            aria-label={t('color')}
                        />
                    </div>
                </Field>
                <div className="flex min-h-10 items-center">
                    <Switch
                        id="day-type-is-working"
                        label={t('es_laborable')}
                        checked={form.data.is_working}
                        onCheckedChange={(checked) => form.setData('is_working', checked)}
                    />
                </div>
            </div>

            <FormActions>
                <Button
                    type="button"
                    variant="secondary"
                    onClick={() => router.visit('/rrhh/day-types')}
                >
                    {t('cancelar')}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
