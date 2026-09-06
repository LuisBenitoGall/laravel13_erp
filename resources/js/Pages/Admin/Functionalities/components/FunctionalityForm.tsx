import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

type ModuleOption = { id: number; slug: string; label: string | Record<string, string> };

type Props = {
    modules: ModuleOption[];
    functionality?: {
        id: number;
        module_id: number;
        name: string;
        slug: string;
        label: Record<string, string>;
        status: string;
    };
};

export default function FunctionalityFormPage({ modules, functionality }: Props) {
    const editing = Boolean(functionality);
    const { data, setData, post, put, processing, errors } = useForm({
        module_id: functionality?.module_id ?? modules[0]?.id ?? 0,
        name: functionality?.name ?? '',
        slug: functionality?.slug ?? '',
        label: { es: functionality?.label?.es ?? '', en: functionality?.label?.en ?? '' },
        status: functionality?.status ?? 'active',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (editing && functionality) {
            put(`/admin/functionalities/${functionality.id}`);
        } else {
            post('/admin/functionalities');
        }
    }

    return (
        <AppLayout title={editing ? 'Editar funcionalidad' : 'Crear funcionalidad'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-xl border bg-white p-6">
                <div className="space-y-1">
                    <label className="block text-sm font-medium">Módulo</label>
                    <select
                        className="w-full rounded-md border px-3 py-2"
                        value={data.module_id}
                        onChange={(e) => setData('module_id', Number(e.target.value))}
                    >
                        {modules.map((m) => (
                            <option key={m.id} value={m.id}>
                                {m.slug}
                            </option>
                        ))}
                    </select>
                    {errors.module_id && <p className="text-sm text-red-600">{errors.module_id}</p>}
                </div>
                <div className="space-y-1">
                    <label className="block text-sm font-medium">Nombre</label>
                    <input
                        className="w-full rounded-md border px-3 py-2"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                </div>
                {!editing && (
                    <div className="space-y-1">
                        <label className="block text-sm font-medium">Slug</label>
                        <input
                            className="w-full rounded-md border px-3 py-2"
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                        />
                        {errors.slug && <p className="text-sm text-red-600">{errors.slug}</p>}
                    </div>
                )}
                {editing && (
                    <p className="text-sm text-slate-500">
                        Slug inmutable: <code>{functionality?.slug}</code>
                    </p>
                )}
                <div className="space-y-1">
                    <label className="block text-sm font-medium">Etiqueta (ES)</label>
                    <input
                        className="w-full rounded-md border px-3 py-2"
                        value={data.label.es}
                        onChange={(e) => setData('label', { ...data.label, es: e.target.value })}
                    />
                </div>
                <div className="space-y-1">
                    <label className="block text-sm font-medium">Estado</label>
                    <select
                        className="w-full rounded-md border px-3 py-2"
                        value={data.status}
                        onChange={(e) => setData('status', e.target.value)}
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-md bg-teal-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    Guardar
                </button>
            </form>
        </AppLayout>
    );
}
