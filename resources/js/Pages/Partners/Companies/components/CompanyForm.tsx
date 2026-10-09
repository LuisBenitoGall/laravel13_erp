import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

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

    return (
        <form onSubmit={onSubmit} className="mx-auto max-w-xl space-y-4 rounded-lg border border-slate-200 bg-white p-6">
            {(
                [
                    ['name', 'Nombre'],
                    ['slug', 'Slug'],
                    ['tradename', 'Nombre comercial'],
                    ['nif', 'NIF'],
                    ['logo', 'Logo (URL)'],
                ] as const
            ).map(([field, label]) => (
                <div key={field}>
                    <label className="mb-1 block text-sm font-medium" htmlFor={field}>
                        {label}
                    </label>
                    <input
                        id={field}
                        className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
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
                    Estado
                </label>
                <select
                    id="status"
                    className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                    value={form.data.status}
                    onChange={(e) => form.setData('status', e.target.value)}
                >
                    <option value="draft">Borrador</option>
                    <option value="active">Activa</option>
                    <option value="closed">Cerrada</option>
                </select>
            </div>
            <button
                type="submit"
                disabled={form.processing}
                className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800"
            >
                Guardar
            </button>
        </form>
    );
}
