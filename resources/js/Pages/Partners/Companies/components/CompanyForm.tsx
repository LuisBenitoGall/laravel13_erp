import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, Input, Select } from '@/Components/Form';

type CompanyFormData = {
    name: string;
    slug: string;
    tradename: string;
    nif: string;
    status: string;
    logo: string;
};

type Props = {
    initial?: Partial<CompanyFormData>;
    submitUrl: string;
    method?: 'post' | 'put';
};

export default function CompanyForm({ initial, submitUrl, method = 'post' }: Props) {
    const { t } = useTranslation();
    const form = useForm<CompanyFormData>({
        name: initial?.name ?? '',
        slug: initial?.slug ?? '',
        tradename: initial?.tradename ?? '',
        nif: initial?.nif ?? '',
        status: initial?.status ?? 'active',
        logo: initial?.logo ?? '',
    });

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (method === 'put') {
            form.put(submitUrl);
        } else {
            form.post(submitUrl);
        }
    };

    const statusOptions = [
        { value: 'draft', label: t('estado_borrador') },
        { value: 'active', label: t('estado_activa') },
        { value: 'closed', label: t('estado_cerrada') },
    ];

    const fields: Array<{
        field: keyof CompanyFormData;
        label: string;
        maxLength?: number;
    }> = [
        { field: 'name', label: t('nombre') },
        { field: 'slug', label: t('slug'), maxLength: 64 },
        { field: 'tradename', label: t('nombre_comercial') },
        { field: 'nif', label: t('nif'), maxLength: 32 },
        { field: 'logo', label: t('logo_url') },
    ];

    return (
        <form
            onSubmit={onSubmit}
            className="mx-auto max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6"
        >
            {fields.map(({ field, label, maxLength }) => (
                <Field key={field} label={label} htmlFor={field} error={form.errors[field]}>
                    <Input
                        id={field}
                        value={form.data[field]}
                        maxLength={maxLength}
                        onChange={(e) => form.setData(field, e.target.value)}
                    />
                </Field>
            ))}

            <Field label={t('estado')} htmlFor="status" error={form.errors.status}>
                <Select
                    id="status"
                    options={statusOptions}
                    value={form.data.status}
                    onChange={(value) => form.setData('status', value)}
                    placeholder={t('elegir')}
                />
            </Field>

            <button
                type="submit"
                disabled={form.processing}
                className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
            >
                {t('guardar')}
            </button>
        </form>
    );
}
