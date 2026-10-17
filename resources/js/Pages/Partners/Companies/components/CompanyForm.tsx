import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';

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

    const fields = [
        ['name', t('nombre')],
        ['slug', t('slug')],
        ['tradename', t('nombre_comercial')],
        ['nif', t('nif')],
        ['logo', t('logo_url')],
    ] as const;

    return (
        <form
            onSubmit={onSubmit}
            className="mx-auto max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6"
        >
            {fields.map(([field, label]) => (
                <div key={field}>
                    <label className="mb-1 block text-sm font-medium" htmlFor={field}>
                        {label}
                    </label>
                    <Input
                        id={field}
                        value={form.data[field]}
                        onChange={(e) => form.setData(field, e.target.value)}
                    />
                    {form.errors[field] && (
                        <p className="mt-1 text-sm text-red-600">{form.errors[field]}</p>
                    )}
                </div>
            ))}
            <div>
                <label className="mb-1 block text-sm font-medium" htmlFor="status">
                    {t('estado')}
                </label>
                <select
                    id="status"
                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    value={form.data.status}
                    onChange={(e) => form.setData('status', e.target.value)}
                >
                    <option value="draft">{t('estado_borrador')}</option>
                    <option value="active">{t('estado_activa')}</option>
                    <option value="closed">{t('estado_cerrada')}</option>
                </select>
            </div>
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
