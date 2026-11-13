import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, Input, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type CompanyFormData = {
    name: string;
    tradename: string;
    nif: string;
    status: string;
};

type Props = {
    mode?: 'create' | 'edit';
    initial?: Partial<CompanyFormData>;
    submitUrl: string;
    method?: 'post' | 'put';
};

export default function CompanyForm({
    mode = 'edit',
    initial,
    submitUrl,
    method = 'post',
}: Props) {
    const { t } = useTranslation();
    const form = useForm<CompanyFormData>({
        name: initial?.name ?? '',
        tradename: initial?.tradename ?? '',
        nif: initial?.nif ?? '',
        status: initial?.status ?? 'active',
    });

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        const payload = {
            name: form.data.name,
            tradename: form.data.tradename || null,
            nif: form.data.nif,
            status: form.data.status,
        };
        form.transform(() => payload);
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    const statusOptions = [
        { value: 'draft', label: t('estado_borrador') },
        { value: 'active', label: t('estado_activa') },
        { value: 'closed', label: t('estado_cerrada') },
    ];

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
        >
            <Field label={t('razon_social')} htmlFor="company-name" error={form.errors.name}>
                <Input
                    id="company-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <Field
                label={t('nombre_comercial')}
                htmlFor="company-tradename"
                error={form.errors.tradename}
            >
                <Input
                    id="company-tradename"
                    value={form.data.tradename}
                    maxLength={255}
                    onChange={(e) => form.setData('tradename', e.target.value)}
                />
            </Field>

            <Field label={t('nif')} htmlFor="company-nif" error={form.errors.nif}>
                <Input
                    id="company-nif"
                    value={form.data.nif}
                    maxLength={12}
                    onChange={(e) => form.setData('nif', e.target.value)}
                />
            </Field>

            <Field label={t('estado')} htmlFor="company-status" error={form.errors.status}>
                <Select
                    id="company-status"
                    options={statusOptions}
                    value={form.data.status}
                    onChange={(value) => form.setData('status', value)}
                    placeholder={t('elegir')}
                />
            </Field>

            <Button type="submit" disabled={form.processing}>
                {t('guardar')}
            </Button>
        </form>
    );
}
