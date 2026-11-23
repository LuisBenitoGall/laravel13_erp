import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, NumberInput, Select, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { ContractTypeRow } from '@/types/h_r';

type ContractTypeFormData = {
    name: string;
    indefinite: boolean;
    duration: string;
    with_holidays: boolean;
    status: string;
};

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<ContractTypeRow>;
};

export default function ContractTypeForm({ mode, submitUrl, method = 'post', initial }: Props) {
    const { t } = useTranslation();

    const form = useForm<ContractTypeFormData>({
        name: initial?.name ?? '',
        indefinite: initial?.duration == null,
        duration: initial?.duration != null ? String(initial.duration) : '',
        with_holidays: initial?.with_holidays ?? false,
        status: initial?.status ?? 'active',
    });

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        const payload = {
            name: form.data.name,
            indefinite: form.data.indefinite,
            duration: form.data.indefinite
                ? null
                : form.data.duration === ''
                  ? null
                  : Number(form.data.duration),
            with_holidays: form.data.with_holidays,
            status: form.data.status,
        };
        form.transform(() => payload);
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
            <Field
                label={t('nombre')}
                htmlFor="contract-type-name"
                error={form.errors.name}
                required
            >
                <Input
                    id="contract-type-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <div className="flex min-h-10 items-center">
                <Switch
                    id="contract-type-indefinite"
                    label={t('contrato_indefinido')}
                    checked={form.data.indefinite}
                    onCheckedChange={(checked) => {
                        form.setData('indefinite', checked);
                        if (checked) {
                            form.setData('duration', '');
                        }
                    }}
                />
            </div>

            <div className="grid gap-4 md:grid-cols-4 md:items-end">
                <Field
                    label={t('duracion_dias')}
                    htmlFor="contract-type-duration"
                    error={form.errors.duration}
                    required={!form.data.indefinite}
                >
                    <NumberInput
                        id="contract-type-duration"
                        inputMode="numeric"
                        value={form.data.duration}
                        maxLength={5}
                        disabled={form.data.indefinite}
                        onChange={(value) => form.setData('duration', value.replace(/\D/g, ''))}
                    />
                </Field>
                <Field
                    label={t('estado')}
                    htmlFor="contract-type-status"
                    error={form.errors.status}
                >
                    <Select
                        id="contract-type-status"
                        options={statusOptions}
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
                <div className="flex min-h-10 items-center md:col-span-2">
                    <Switch
                        id="contract-type-with-holidays"
                        label={t('con_vacaciones')}
                        checked={form.data.with_holidays}
                        onCheckedChange={(checked) => form.setData('with_holidays', checked)}
                    />
                </div>
            </div>

            <FormActions>
                <Button
                    type="button"
                    variant="secondary"
                    onClick={() => router.visit('/hr/contract-types')}
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
