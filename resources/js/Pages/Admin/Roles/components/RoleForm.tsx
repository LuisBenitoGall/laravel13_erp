import { FormEvent, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Check, Ban, ChevronDown, ChevronRight, ChevronsDownUp, ChevronsUpDown, Copy } from 'lucide-react';
import { toast } from 'sonner';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Field, Input, Textarea } from '@/Components/Form';
import ScrollToTop from '@/Components/Navigation/ScrollToTop';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';

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
    color: string;
    icon: string;
    functionalities: FunctionalityBlock[];
};

type RolePayload = {
    id: number;
    name: string;
    description: string | null;
    universal: boolean;
    protected_template: boolean;
    manager_template: boolean;
    permissions_readonly: boolean;
    permission_names: string[];
};

type CopyCompany = { id: number; name: string };

type Props = {
    role: RolePayload;
    matrix: ModuleBlock[];
    can_copy: boolean;
    copy_companies: CopyCompany[];
};

type ToggleResponse = { permission_names: string[] };

export default function RoleForm({ role, matrix, can_copy, copy_companies }: Props) {
    const { t } = useTranslation();
    const permissionsReadonly = Boolean(role.permissions_readonly);

    const { data, setData, processing, errors, delete: destroy } = useForm({
        description: role.description ?? '',
    });

    const [selected, setSelected] = useState<Set<string>>(
        () => new Set(role.permission_names),
    );
    const [pending, setPending] = useState<Set<string>>(() => new Set());
    const [expanded, setExpanded] = useState<Set<string>>(() => new Set());
    const [copyOpen, setCopyOpen] = useState(false);
    const [copyIds, setCopyIds] = useState<number[]>([]);
    const [copying, setCopying] = useState(false);

    const allFunctionalitySlugs = useMemo(
        () => matrix.flatMap((mod) => mod.functionalities.map((fn) => fn.slug)),
        [matrix],
    );

    const allExpanded =
        allFunctionalitySlugs.length > 0 &&
        allFunctionalitySlugs.every((slug) => expanded.has(slug));

    const moduleHasAccess = useMemo(() => {
        const map = new Map<string, boolean>();
        for (const mod of matrix) {
            map.set(mod.slug, selected.has(`${mod.slug}.access`));
        }
        return map;
    }, [matrix, selected]);

    function toggleExpandAll() {
        if (allExpanded) {
            setExpanded(new Set());
            return;
        }

        setExpanded(new Set(allFunctionalitySlugs));
    }

    function markPending(keys: string[], on: boolean) {
        setPending((prev) => {
            const next = new Set(prev);
            for (const key of keys) {
                if (on) {
                    next.add(key);
                } else {
                    next.delete(key);
                }
            }
            return next;
        });
    }

    async function togglePermission(name: string, enabled: boolean) {
        if (permissionsReadonly || pending.has(name)) {
            return;
        }

        const previous = new Set(selected);
        setSelected((prev) => {
            const next = new Set(prev);
            if (enabled) {
                next.add(name);
            } else {
                next.delete(name);
            }
            return next;
        });
        markPending([name], true);

        try {
            const result = await patchJson<ToggleResponse>(`/admin/roles/${role.id}/permissions`, {
                permission: name,
                enabled,
            });
            setSelected(new Set(result.permission_names));
        } catch {
            setSelected(previous);
            toast.error(t('rol_toggle_error'));
        } finally {
            markPending([name], false);
        }
    }

    async function toggleFunctionality(fn: FunctionalityBlock, enabled: boolean) {
        if (permissionsReadonly) {
            return;
        }

        const names = fn.permissions.map((p) => p.name);
        if (names.some((n) => pending.has(n))) {
            return;
        }

        const previous = new Set(selected);
        setSelected((prev) => {
            const next = new Set(prev);
            for (const name of names) {
                if (enabled) {
                    next.add(name);
                } else {
                    next.delete(name);
                }
            }
            return next;
        });
        markPending(names, true);

        try {
            const result = await patchJson<ToggleResponse>(
                `/admin/roles/${role.id}/functionalities`,
                {
                    functionality: fn.slug,
                    enabled,
                },
            );
            setSelected(new Set(result.permission_names));
        } catch {
            setSelected(previous);
            toast.error(t('rol_toggle_error'));
        } finally {
            markPending(names, false);
        }
    }

    function functionalityState(fn: FunctionalityBlock): boolean | 'indeterminate' {
        const names = fn.permissions.map((p) => p.name);
        const count = names.filter((n) => selected.has(n)).length;
        if (count === 0) {
            return false;
        }
        if (count === names.length) {
            return true;
        }
        return 'indeterminate';
    }

    function submitDescription(e: FormEvent) {
        e.preventDefault();
        router.put(
            `/admin/roles/${role.id}`,
            { description: data.description },
            { preserveScroll: true },
        );
    }

    function onDelete() {
        if (role.protected_template) {
            return;
        }
        destroy(`/admin/roles/${role.id}`);
    }

    function submitCopy(e: FormEvent) {
        e.preventDefault();
        if (copyIds.length === 0) {
            toast.error(t('rol_copy_companies_requeridas'));
            return;
        }
        setCopying(true);
        router.post(
            `/admin/roles/${role.id}/copy`,
            { company_ids: copyIds },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setCopyOpen(false);
                    setCopyIds([]);
                },
                onFinish: () => setCopying(false),
            },
        );
    }

    // CTA Copiar a la derecha de Volver (ViewActionBar): can_copy = !universal (D5).
    const viewActions = [
        ...(can_copy
            ? [
                  {
                      type: 'button' as const,
                      labelKey: 'rol_copiar',
                      icon: Copy,
                      onClick: () => setCopyOpen(true),
                  },
              ]
            : []),
        ...(!role.protected_template
            ? [
                  {
                      type: 'delete' as const,
                      onConfirm: onDelete,
                      confirmKey: 'rol_confirmar_eliminar',
                  },
              ]
            : []),
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles',
                suboptionKey: 'rol_editar',
                optionHref: '/admin/roles',
            }}
            actions={viewActions}
            backHref="/admin/roles"
        >
            <div className="space-y-6">
                <form
                    onSubmit={submitDescription}
                    className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                >
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label={t('nombre')} htmlFor="role-name-ro">
                            <Input
                                id="role-name-ro"
                                value={role.name}
                                readOnly
                                disabled
                            />
                        </Field>
                        <Field label={t('rol_universal')} htmlFor="role-universal-ro">
                            <p id="role-universal-ro" className="flex min-h-9 items-center text-sm text-slate-700">
                                {role.universal ? t('si') : t('no')}
                            </p>
                        </Field>
                    </div>

                    <Field label={t('descripcion')} htmlFor="role-description" error={errors.description}>
                        <Textarea
                            id="role-description"
                            rows={3}
                            maxLength={2000}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                    </Field>

                    {role.universal ? (
                        <p className="text-sm text-slate-500">{t('rol_universal_sin_admin_only')}</p>
                    ) : null}

                    <div className="flex justify-end gap-2">
                        <Button type="button" variant="secondary" onClick={() => router.visit('/admin/roles')}>
                            {t('cancelar')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {t('guardar')}
                        </Button>
                    </div>
                </form>

                {permissionsReadonly ? (
                    <p className="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        {t('rol_manager_matriz_solo_lectura')}
                    </p>
                ) : null}

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <nav className="flex flex-wrap gap-2" aria-label={t('matriz_permisos')}>
                        {matrix.map((mod) => (
                            <a
                                key={mod.slug}
                                href={`#module-${mod.slug}`}
                                className="inline-flex items-center rounded-md px-3.5 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-sm transition-opacity hover:opacity-90"
                                style={{ backgroundColor: mod.color || '#0f766e' }}
                            >
                                {mod.label}
                            </a>
                        ))}
                    </nav>

                    {allFunctionalitySlugs.length > 0 ? (
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            onClick={toggleExpandAll}
                            aria-pressed={allExpanded}
                        >
                            {allExpanded ? (
                                <ChevronsDownUp className="size-4" aria-hidden />
                            ) : (
                                <ChevronsUpDown className="size-4" aria-hidden />
                            )}
                            {allExpanded
                                ? t('rol_permisos_plegar_todos')
                                : t('rol_permisos_desplegar_todos')}
                        </Button>
                    ) : null}
                </div>

                <div className="space-y-8">
                    {matrix.map((mod) => {
                        const active = moduleHasAccess.get(mod.slug) ?? false;

                        return (
                            <section key={mod.slug} id={`module-${mod.slug}`} className="scroll-mt-24">
                                <div
                                    className="mb-3 rounded-lg px-4 py-3 text-white shadow-sm"
                                    style={{ backgroundColor: mod.color || '#0f766e' }}
                                >
                                    <h2 className="flex items-center gap-2 text-base font-semibold tracking-wide uppercase">
                                        {active ? (
                                            <Check className="size-4 shrink-0" aria-hidden />
                                        ) : (
                                            <Ban className="size-4 shrink-0 opacity-80" aria-hidden />
                                        )}
                                        {mod.label}
                                    </h2>
                                </div>

                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    {mod.functionalities.map((fn) => {
                                        const open = expanded.has(fn.slug);
                                        const master = functionalityState(fn);
                                        const masterChecked = master === true;
                                        const masterIndeterminate = master === 'indeterminate';

                                        return (
                                            <div
                                                key={fn.slug}
                                                className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                                            >
                                                <button
                                                    type="button"
                                                    className={`flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm font-medium ${
                                                        open ? 'bg-amber-50 text-amber-950' : 'bg-slate-50 text-slate-800'
                                                    }`}
                                                    onClick={() =>
                                                        setExpanded((prev) => {
                                                            const next = new Set(prev);
                                                            if (next.has(fn.slug)) {
                                                                next.delete(fn.slug);
                                                            } else {
                                                                next.add(fn.slug);
                                                            }
                                                            return next;
                                                        })
                                                    }
                                                >
                                                    <span className="truncate">{fn.label}</span>
                                                    {open ? (
                                                        <ChevronDown className="size-4 shrink-0" />
                                                    ) : (
                                                        <ChevronRight className="size-4 shrink-0" />
                                                    )}
                                                </button>

                                                {open ? (
                                                    <div className="space-y-3 border-t border-slate-100 px-3 py-3">
                                                        <label className="flex items-center gap-2 text-sm font-medium text-slate-700">
                                                            <Checkbox
                                                                checked={
                                                                    masterIndeterminate
                                                                        ? 'indeterminate'
                                                                        : masterChecked
                                                                }
                                                                disabled={permissionsReadonly}
                                                                onCheckedChange={(state) => {
                                                                    void toggleFunctionality(
                                                                        fn,
                                                                        state === true,
                                                                    );
                                                                }}
                                                            />
                                                            {t('rol_funcionalidad_completa')}
                                                        </label>
                                                        <div className="flex flex-wrap gap-x-4 gap-y-2">
                                                            {fn.permissions.map((perm) => (
                                                                <label
                                                                    key={perm.name}
                                                                    className="flex items-center gap-2 text-xs text-slate-600"
                                                                >
                                                                    <Checkbox
                                                                        checked={selected.has(perm.name)}
                                                                        disabled={
                                                                            permissionsReadonly ||
                                                                            pending.has(perm.name)
                                                                        }
                                                                        onCheckedChange={(state) => {
                                                                            void togglePermission(
                                                                                perm.name,
                                                                                state === true,
                                                                            );
                                                                        }}
                                                                    />
                                                                    {perm.action}
                                                                </label>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ) : null}
                                            </div>
                                        );
                                    })}
                                </div>
                            </section>
                        );
                    })}
                </div>
            </div>

            <ScrollToTop />

            <Dialog open={copyOpen} onOpenChange={setCopyOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('rol_copiar')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCopy} className="space-y-4">
                        <p className="text-sm text-slate-600">{t('rol_copiar_ayuda')}</p>
                        {copy_companies.length === 0 ? (
                            <p className="text-sm text-amber-800">{t('rol_copy_sin_empresas')}</p>
                        ) : (
                            <div className="max-h-64 space-y-2 overflow-y-auto rounded-md border border-slate-200 p-3">
                                {copy_companies.map((company) => {
                                    const checked = copyIds.includes(company.id);
                                    return (
                                        <label
                                            key={company.id}
                                            className="flex items-center gap-2 text-sm text-slate-700"
                                        >
                                            <Checkbox
                                                checked={checked}
                                                onCheckedChange={(state) => {
                                                    setCopyIds((prev) => {
                                                        if (state === true) {
                                                            return [...prev, company.id];
                                                        }
                                                        return prev.filter((id) => id !== company.id);
                                                    });
                                                }}
                                            />
                                            {company.name}
                                        </label>
                                    );
                                })}
                            </div>
                        )}
                        <DialogFooter>
                            <Button type="button" variant="secondary" onClick={() => setCopyOpen(false)}>
                                {t('cancelar')}
                            </Button>
                            <Button
                                type="submit"
                                disabled={copying || copy_companies.length === 0 || copyIds.length === 0}
                            >
                                {t('rol_copiar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
