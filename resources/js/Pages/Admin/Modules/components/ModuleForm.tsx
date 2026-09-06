import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

type Props = {
    module?: {
        id: number;
        name: string;
        slug: string;
        label: Record<string, string>;
        color: string;
        icon: string;
        level: string;
        status: string;
        explanation?: Record<string, string>;
    };
};

export default function ModuleFormPage({ module }: Props) {
    const editing = Boolean(module);
    const { data, setData, post, put, processing, errors } = useForm({
        name: module?.name ?? '',
        slug: module?.slug ?? '',
        label: { es: module?.label?.es ?? '', en: module?.label?.en ?? '' },
        color: module?.color ?? '#0f766e',
        icon: module?.icon ?? 'settings',
        level: module?.level ?? 'basic',
        status: module?.status ?? 'active',
        explanation: { es: module?.explanation?.es ?? '', en: module?.explanation?.en ?? '' },
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        if (editing && module) {
            put(`/admin/modules/${module.id}`);
        } else {
            post('/admin/modules');
        }
    }

    return (
        <AppLayout title={editing ? 'Editar módulo' : 'Crear módulo'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-4 rounded-xl border bg-white p-6">
                <Field label="Nombre" error={errors.name}>
                    <input
                        className="w-full rounded-md border px-3 py-2"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                </Field>
                {!editing && (
                    <Field label="Slug" error={errors.slug}>
                        <input
                            className="w-full rounded-md border px-3 py-2"
                            value={data.slug}
                            onChange={(e) => setData('slug', e.target.value)}
                        />
                    </Field>
                )}
                <Field label="Etiqueta (ES)" error={errors['label.es']}>
                    <input
                        className="w-full rounded-md border px-3 py-2"
                        value={data.label.es}
                        onChange={(e) => setData('label', { ...data.label, es: e.target.value })}
                    />
                </Field>
                <Field label="Etiqueta (EN)">
                    <input
                        className="w-full rounded-md border px-3 py-2"
                        value={data.label.en}
                        onChange={(e) => setData('label', { ...data.label, en: e.target.value })}
                    />
                </Field>
                <div className="grid grid-cols-2 gap-4">
                    <Field label="Color">
                        <input
                            className="w-full rounded-md border px-3 py-2"
                            value={data.color}
                            onChange={(e) => setData('color', e.target.value)}
                        />
                    </Field>
                    <Field label="Icono">
                        <input
                            className="w-full rounded-md border px-3 py-2"
                            value={data.icon}
                            onChange={(e) => setData('icon', e.target.value)}
                        />
                    </Field>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <Field label="Nivel">
                        <select
                            className="w-full rounded-md border px-3 py-2"
                            value={data.level}
                            onChange={(e) => setData('level', e.target.value)}
                        >
                            <option value="admin_only">Admin only</option>
                            <option value="basic">Basic</option>
                            <option value="optional">Optional</option>
                        </select>
                    </Field>
                    <Field label="Estado">
                        <select
                            className="w-full rounded-md border px-3 py-2"
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </Field>
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

function Field({
    label,
    error,
    children,
}: {
    label: string;
    error?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-1">
            <label className="block text-sm font-medium">{label}</label>
            {children}
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
