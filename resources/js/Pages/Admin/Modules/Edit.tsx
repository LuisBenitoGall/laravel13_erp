import { router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { ColorPicker, Field, IconPicker, Input, LangTabs, Switch, Textarea } from '@/Components/Form';
import RecordSummary from '@/Components/Records/RecordSummary';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import { formatDate } from '@/lib/format';
import { patchJson } from '@/lib/http';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import { cn } from '@/lib/utils';
import AppLayout from '@/Layouts/AppLayout';
import type { FunctionalityRow } from '@/types/admin';
import type { I18nSharedProps } from '@/types/i18n';

type LocaleMap = Record<string, string>;

type ModuleRecord = {
    id: number;
    name: string;
    slug: string;
    label: LocaleMap;
    color: string;
    icon: string;
    level: string;
    status: string;
    explanation?: LocaleMap;
    created_at?: string | null;
    updated_at?: string | null;
    created_by_name?: string | null;
};

type ModuleEditForm = {
    name: string;
    label: LocaleMap;
    explanation: LocaleMap;
    color: string;
    icon: string;
    status: string;
};

type FunctionalityCreateForm = {
    name: string;
    label: LocaleMap;
    status: string;
};

type FunctionalityEditForm = {
    label: LocaleMap;
};

type EditTab = 'module' | 'functionalities';

function localeMap(locales: string[], source?: LocaleMap): LocaleMap {
    return Object.fromEntries(locales.map((locale) => [locale, source?.[locale] ?? '']));
}

function tabFromPageUrl(url: string): EditTab {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';

    return new URLSearchParams(query).get('tab') === 'functionalities' ? 'functionalities' : 'module';
}

export default function Edit({ module }: { module: ModuleRecord }) {
    const { t, i18n } = useTranslation();
    const page = usePage<I18nSharedProps>();
    const { supportedLocales, defaultLocale } = page.props;
    const locales = useMemo(() => supportedLocales, [supportedLocales]);
    const urlTab = tabFromPageUrl(page.url);
    const [tab, setTab] = useState<EditTab>(urlTab);
    const [prevPageUrl, setPrevPageUrl] = useState(page.url);
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editOpen, setEditOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);

    if (page.url !== prevPageUrl) {
        setPrevPageUrl(page.url);
        setTab(urlTab);
    }

    function selectTab(next: EditTab) {
        setTab(next);
        const path = `/admin/modules/${module.id}/edit`;
        const href = next === 'functionalities' ? `${path}?tab=functionalities` : path;
        window.history.replaceState(window.history.state, '', href);
    }

    const { data, setData, put, processing, errors } = useForm<ModuleEditForm>({
        name: module.name,
        label: localeMap(locales, module.label),
        explanation: localeMap(locales, module.explanation),
        color: module.color,
        icon: module.icon,
        status: module.status,
    });

    const createForm = useForm<FunctionalityCreateForm>({
        name: '',
        label: localeMap(locales),
        status: 'active',
    });

    const editForm = useForm<FunctionalityEditForm>({
        label: localeMap(locales),
    });

    const invalidLocales = locales.filter(
        (locale) => errors[`label.${locale}`] || errors[`explanation.${locale}`],
    );

    const createInvalidLocales = locales.filter(
        (locale) => createForm.errors[`label.${locale}`],
    );

    const editInvalidLocales = locales.filter((locale) => editForm.errors[`label.${locale}`]);

    const isShellModule = ['dashboard', 'cuenta', 'usuarios', 'empresas', 'configuracion'].includes(
        module.slug,
    );
    const levelLabelKey = `nivel_${module.level}` as const;

    function refreshFunctionalitiesTable() {
        setTableKey((key) => key + 1);
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        put(`/admin/modules/${module.id}`);
    }

    function openCreateModal() {
        createForm.setData({
            name: '',
            label: localeMap(locales),
            status: 'active',
        });
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post(`/admin/modules/${module.id}/functionalities`, {
            preserveScroll: true,
            onSuccess: () => {
                setCreateOpen(false);
                refreshFunctionalitiesTable();
            },
        });
    }

    function openEditModal(row: FunctionalityRow) {
        setEditingId(row.id);
        editForm.setData({
            label: localeMap(locales, row.label),
        });
        editForm.clearErrors();
        setEditOpen(true);
    }

    function submitEdit(event: FormEvent) {
        event.preventDefault();
        if (editingId === null) {
            return;
        }

        editForm.put(`/admin/functionalities/${editingId}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditOpen(false);
                setEditingId(null);
                refreshFunctionalitiesTable();
            },
        });
    }

    const functionalityColumns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('etiqueta'),
                field: 'label_text',
                widthGrow: 1,
                headerSort: false,
            },
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
        ],
        [t],
    );

    const functionalityRowActions = (row: FunctionalityRow): Array<RowAction<FunctionalityRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/functionalities/${row.id}/status`, {
                    status,
                });

                return { status };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => openEditModal(current),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/admin/functionalities/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => refreshFunctionalitiesTable(),
                });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'modulos',
                suboptionKey: 'modulo_editar',
                optionHref: '/admin/modules',
            }}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nueva_funcionalidad',
                    icon: Plus,
                    onClick: openCreateModal,
                },
                ...(isShellModule
                    ? []
                    : [
                          {
                              type: 'delete' as const,
                              onConfirm: () => {
                                  router.delete(`/admin/modules/${module.id}`);
                              },
                          },
                      ]),
            ]}
            showBack
            backHref="/admin/modules"
        >
            <div className="w-full">
                <RecordSummary
                    titleKey="edicion_modulo"
                    titleParams={{ name: module.name }}
                    fields={auditRecordSummaryFields({
                        created_at: formatDate(module.created_at, i18n.language),
                        created_by: module.created_by_name ?? undefined,
                        updated_at: formatDate(module.updated_at, i18n.language),
                    })}
                />
                <div role="tablist" className="flex gap-1 border-b border-slate-200">
                    <button
                        type="button"
                        role="tab"
                        aria-selected={tab === 'module'}
                        className={cn(
                            'border-b-2 px-3 py-2 text-sm',
                            tab === 'module'
                                ? 'border-teal-700 font-medium text-teal-800'
                                : 'border-transparent text-slate-600',
                        )}
                        onClick={() => selectTab('module')}
                    >
                        {t('modulo')}
                    </button>
                    <button
                        type="button"
                        role="tab"
                        aria-selected={tab === 'functionalities'}
                        className={cn(
                            'border-b-2 px-3 py-2 text-sm',
                            tab === 'functionalities'
                                ? 'border-teal-700 font-medium text-teal-800'
                                : 'border-transparent text-slate-600',
                        )}
                        onClick={() => selectTab('functionalities')}
                    >
                        {t('funcionalidades')}
                    </button>
                </div>

                {tab === 'module' ? (
                    <form
                        onSubmit={submit}
                        className="mt-4 space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                    >
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field label={t('nombre')} htmlFor="module-name" error={errors.name} required>
                                <Input
                                    id="module-name"
                                    value={data.name}
                                    onChange={(event) => setData('name', event.target.value)}
                                />
                            </Field>
                            <div className="flex flex-col gap-1">
                                <Label>{t('nivel')}</Label>
                                <p className="text-sm text-slate-800" data-testid="module-level-readonly">
                                    {t(levelLabelKey)}
                                </p>
                            </div>
                            <div className="flex flex-col gap-1">
                                <div className="flex items-center gap-3">
                                    <Label htmlFor="module-status">{t('estado')}</Label>
                                    <Switch
                                        id="module-status"
                                        checked={data.status === 'active'}
                                        onCheckedChange={(checked) =>
                                            setData('status', checked ? 'active' : 'inactive')
                                        }
                                    />
                                </div>
                                {errors.status ? <p className="text-sm text-red-600">{errors.status}</p> : null}
                            </div>
                            <IconPicker
                                id="module-icon"
                                label={t('icono')}
                                value={data.icon}
                                error={errors.icon}
                                required
                                onChange={(value) => setData('icon', value)}
                            />
                            <ColorPicker
                                id="module-color"
                                label={t('color')}
                                value={data.color}
                                error={errors.color}
                                required
                                onChange={(value) => setData('color', value)}
                            />
                        </div>
                        <LangTabs locales={locales} invalidLocales={invalidLocales}>
                            {(locale) => (
                                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                    <Field
                                        label={t('etiqueta')}
                                        htmlFor={`module-label-${locale}`}
                                        error={errors[`label.${locale}`]}
                                        required={locale === defaultLocale}
                                    >
                                        <Input
                                            id={`module-label-${locale}`}
                                            value={data.label[locale] ?? ''}
                                            onChange={(event) =>
                                                setData('label', {
                                                    ...data.label,
                                                    [locale]: event.target.value,
                                                })
                                            }
                                        />
                                    </Field>
                                    <Field
                                        label={t('explicacion')}
                                        htmlFor={`module-explanation-${locale}`}
                                        error={errors[`explanation.${locale}`]}
                                        required={locale === defaultLocale}
                                    >
                                        <Textarea
                                            id={`module-explanation-${locale}`}
                                            value={data.explanation[locale] ?? ''}
                                            onChange={(event) =>
                                                setData('explanation', {
                                                    ...data.explanation,
                                                    [locale]: event.target.value,
                                                })
                                            }
                                        />
                                    </Field>
                                </div>
                            )}
                        </LangTabs>
                        <Button type="submit" disabled={processing}>
                            {t('guardar')}
                        </Button>
                    </form>
                ) : (
                    <div className="mt-4">
                        <ServerTable<FunctionalityRow>
                            key={tableKey}
                            endpoint={`/admin/modules/${module.id}/functionalities/table`}
                            columns={functionalityColumns}
                            persistKey={`admin.modules.${module.id}.functionalities`}
                            exportable={false}
                            rowActions={functionalityRowActions}
                        />
                    </div>
                )}
            </div>

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nueva_funcionalidad')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="functionality-create-name"
                            error={createForm.errors.name}
                            required
                        >
                            <Input
                                id="functionality-create-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <LangTabs locales={locales} invalidLocales={createInvalidLocales}>
                            {(locale) => (
                                <Field
                                    label={t('etiqueta')}
                                    htmlFor={`functionality-create-label-${locale}`}
                                    error={createForm.errors[`label.${locale}`]}
                                    required={locale === defaultLocale}
                                >
                                    <Input
                                        id={`functionality-create-label-${locale}`}
                                        value={createForm.data.label[locale] ?? ''}
                                        onChange={(event) =>
                                            createForm.setData('label', {
                                                ...createForm.data.label,
                                                [locale]: event.target.value,
                                            })
                                        }
                                    />
                                </Field>
                            )}
                        </LangTabs>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setCreateOpen(false)}
                            >
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={editOpen}
                onOpenChange={(open) => {
                    setEditOpen(open);
                    if (!open) {
                        setEditingId(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('funcionalidad_editar')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <LangTabs locales={locales} invalidLocales={editInvalidLocales}>
                            {(locale) => (
                                <Field
                                    label={t('etiqueta')}
                                    htmlFor={`functionality-edit-label-${locale}`}
                                    error={editForm.errors[`label.${locale}`]}
                                    required={locale === defaultLocale}
                                >
                                    <Input
                                        id={`functionality-edit-label-${locale}`}
                                        value={editForm.data.label[locale] ?? ''}
                                        onChange={(event) =>
                                            editForm.setData('label', {
                                                ...editForm.data.label,
                                                [locale]: event.target.value,
                                            })
                                        }
                                    />
                                </Field>
                            )}
                        </LangTabs>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setEditOpen(false)}
                            >
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
