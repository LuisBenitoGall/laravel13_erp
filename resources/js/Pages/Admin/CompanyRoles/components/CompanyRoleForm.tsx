import { FormEvent, useState, type ReactNode } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AppLayout from '@/Layouts/AppLayout';

type PermissionCell = { name: string; action: string };
type FunctionalityBlock = {
    slug: string;
    label: string;
    permissions: PermissionCell[];
};
type ModuleBlock = {
    slug: string;
    label: string;
    level: string;
    functionalities: FunctionalityBlock[];
};

type RolePayload = {
    id: number;
    name: string;
    description: string | null;
    protected_local: boolean;
    local_manager: boolean;
    local_guest: boolean;
    permissions_readonly: boolean;
    name_readonly: boolean;
    permission_names: string[];
};

type Props = {
    role?: RolePayload;
    matrix: ModuleBlock[];
};

export default function CompanyRoleForm({ role, matrix }: Props) {
    const { t } = useTranslation();
    const editing = Boolean(role);
    const { data, setData, processing, errors, delete: destroy } = useForm({
        name: role?.name ?? '',
        description: role?.description ?? '',
        permissions: role?.permission_names ?? ([] as string[]),
    });

    const [selected, setSelected] = useState<Set<string>>(
        () => new Set(role?.permission_names ?? []),
    );

    const permissionsReadonly = Boolean(role?.permissions_readonly);
    const nameReadonly = Boolean(role?.name_readonly);

    function togglePermission(name: string) {
        if (permissionsReadonly) {
            return;
        }
        setSelected((prev) => {
            const next = new Set(prev);
            if (next.has(name)) {
                next.delete(name);
            } else {
                next.add(name);
            }
            setData('permissions', Array.from(next));
            return next;
        });
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        const payload = {
            name: data.name,
            description: data.description,
            permissions: Array.from(selected),
        };
        if (editing && role) {
            router.put(`/company/roles/${role.id}`, payload, { preserveScroll: true });
        } else {
            router.post('/company/roles', payload);
        }
    }

    function onDelete() {
        if (!role || role.protected_local) {
            return;
        }
        if (!window.confirm(t('rol_empresa_confirmar_eliminar'))) {
            return;
        }
        destroy(`/company/roles/${role.id}`);
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles_de_empresa',
                suboptionKey: editing ? 'rol_empresa_editar' : 'rol_empresa_crear',
                optionHref: '/company/roles',
            }}
        >
            <form onSubmit={submit} className="mx-auto max-w-4xl space-y-6 rounded-xl border bg-white p-6">
                <Field label={t('nombre')} error={errors.name}>
                    <Input
                        value={data.name}
                        disabled={nameReadonly}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                </Field>

                <Field label={t('descripcion')} error={errors.description}>
                    <textarea
                        className="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
                        rows={3}
                        maxLength={2000}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </Field>

                <div className="space-y-3">
                    <h2 className="text-lg font-medium text-slate-900">{t('matriz_permisos')}</h2>
                    {permissionsReadonly && (
                        <p className="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            {t('rol_empresa_manager_matriz_solo_lectura')}
                        </p>
                    )}
                    {matrix.map((mod) => (
                        <section key={mod.slug} className="rounded-lg border border-slate-200 p-4">
                            <h3 className="mb-3 font-medium text-slate-800">
                                {mod.label}{' '}
                                <span className="text-xs font-normal text-slate-500">({mod.level})</span>
                            </h3>
                            <div className="space-y-3">
                                {mod.functionalities.map((fn) => (
                                    <div key={fn.slug}>
                                        <p className="mb-1 text-sm font-medium text-slate-700">{fn.label}</p>
                                        <div className="flex flex-wrap gap-3">
                                            {fn.permissions.map((perm) => (
                                                <label
                                                    key={perm.name}
                                                    className="flex items-center gap-1.5 text-xs text-slate-600"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        disabled={permissionsReadonly}
                                                        checked={selected.has(perm.name)}
                                                        onChange={() => togglePermission(perm.name)}
                                                    />
                                                    {perm.action}
                                                </label>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>

                <div className="flex items-center justify-between gap-3">
                    {editing && role && !role.protected_local ? (
                        <button
                            type="button"
                            onClick={onDelete}
                            className="rounded-md border border-red-300 px-3 py-2 text-sm text-red-700 hover:bg-red-50"
                        >
                            {t('eliminar')}
                        </button>
                    ) : (
                        <span />
                    )}
                    <div className="flex gap-2">
                        <button
                            type="button"
                            className="rounded-md border px-3 py-2 text-sm"
                            onClick={() => router.visit('/company/roles')}
                        >
                            {t('cancelar')}
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                        >
                            {t('guardar')}
                        </button>
                    </div>
                </div>
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
    children: ReactNode;
}) {
    return (
        <div className="space-y-1">
            <label className="block text-sm font-medium text-slate-700">{label}</label>
            {children}
            {error && <p className="text-sm text-red-600">{error}</p>}
        </div>
    );
}
