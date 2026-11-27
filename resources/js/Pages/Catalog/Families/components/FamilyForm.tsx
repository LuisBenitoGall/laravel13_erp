import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, FormHelpScope, Input, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { FamilyRow, FamilyStatus } from '@/types/catalog';

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<FamilyRow>;
};

export default function FamilyForm({ mode, submitUrl, method, initial }: Props) {
    const { t } = useTranslation();
    const resolvedMethod = method ?? (mode === 'edit' ? 'put' : 'post');

    const form = useForm({
        name: initial?.name ?? '',
        status: (initial?.status ?? 'inactive') as FamilyStatus,
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
        if (resolvedMethod === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    return (
        <FormHelpScope value="families">
            <form
                onSubmit={onSubmit}
                className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                data-mode={mode}
            >
                <Field
                    label={t('nombre')}
                    htmlFor="family-name"
                    fieldKey="name"
                    error={form.errors.name}
                    required
                >
                    <Input
                        id="family-name"
                        value={form.data.name}
                        maxLength={255}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                </Field>

                {mode === 'edit' && initial?.slug ? (
                    <Field label={t('slug')} htmlFor="family-slug" fieldKey="slug">
                        <Input id="family-slug" value={initial.slug} readOnly disabled />
                    </Field>
                ) : null}

                <Field
                    label={t('estado')}
                    htmlFor="family-status"
                    fieldKey="status"
                    error={form.errors.status}
                    className="w-full md:w-[30%]"
                >
                    <Select
                        id="family-status"
                        options={statusOptions}
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value as FamilyStatus)}
                        placeholder={t('elegir')}
                    />
                </Field>

                <FormActions>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => router.visit('/products/families')}
                    >
                        {t('cancelar')}
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            </form>
        </FormHelpScope>
    );
}
