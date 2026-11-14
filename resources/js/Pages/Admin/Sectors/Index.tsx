import { router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, LangTabs, Select } from '@/Components/Form';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import AppLayout from '@/Layouts/AppLayout';
import { patchJson } from '@/lib/http';
import type { SectorRow } from '@/types/admin';
import type { I18nSharedProps } from '@/types/i18n';

type LocaleMap = Record<string, string>;

type SectorFormState = {
    name: LocaleMap;
    status: string;
};

function emptyLocaleMap(locales: string[]): LocaleMap {
    return Object.fromEntries(locales.map((locale) => [locale, '']));
}

function localeMap(locales: string[], source?: LocaleMap): LocaleMap {
    return Object.fromEntries(locales.map((locale) => [locale, source?.[locale] ?? '']));
}

const emptyForm = (locales: string[]): SectorFormState => ({
    name: emptyLocaleMap(locales),
    status: 'active',
});

export default function Index() {
    const { t } = useTranslation();
    const { supportedLocales, defaultLocale } = usePage<I18nSharedProps>().props;
    const locales = useMemo(() => supportedLocales, [supportedLocales]);

    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<SectorRow | null>(null);

    const createForm = useForm(emptyForm(locales));
    const editForm = useForm(emptyForm(locales));

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const createInvalidLocales = locales.filter((locale) => createForm.errors[`name.${locale}`]);
    const editInvalidLocales = locales.filter((locale) => editForm.errors[`name.${locale}`]);

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
        ],
        [t],
    );

    function openCreate() {
        createForm.setData(emptyForm(locales));
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: SectorRow) {
        editForm.setData({
            name: localeMap(locales, row.names),
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/sectors', {
            preserveScroll: true,
            onSuccess: () => {
                setCreateOpen(false);
                setTableKey((key) => key + 1);
            },
        });
    }

    function submitEdit(event: FormEvent) {
        event.preventDefault();
        if (!editing) {
            return;
        }

        editForm.put(`/admin/sectors/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: SectorRow): Array<RowAction<SectorRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/sectors/${row.id}/status`, {
                    status,
                });

                return { status };
            },
        },
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: () => openEdit(row),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/admin/sectors/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => setTableKey((key) => key + 1),
                    onError: (errors) => {
                        const message = Object.values(errors)[0];
                        if (message) {
                            toast.error(String(message));
                        }
                    },
                });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'sectores',
                suboptionKey: 'dashboard',
                optionHref: '/admin/sectors',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_sector',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<SectorRow>
                key={tableKey}
                endpoint="/admin/sectors/table"
                columns={columns}
                persistKey="admin.sectors"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_sector')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <LangTabs locales={locales} invalidLocales={createInvalidLocales}>
                            {(locale) => (
                                <Field
                                    label={t('nombre')}
                                    htmlFor={`sector-create-name-${locale}`}
                                    error={createForm.errors[`name.${locale}`]}
                                    required={locale === defaultLocale}
                                >
                                    <Input
                                        id={`sector-create-name-${locale}`}
                                        value={createForm.data.name[locale] ?? ''}
                                        onChange={(event) =>
                                            createForm.setData('name', {
                                                ...createForm.data.name,
                                                [locale]: event.target.value,
                                            })
                                        }
                                    />
                                </Field>
                            )}
                        </LangTabs>
                        <Field
                            label={t('estado')}
                            htmlFor="sector-create-status"
                            error={createForm.errors.status}
                            required
                        >
                            <Select
                                id="sector-create-status"
                                value={createForm.data.status}
                                options={statusOptions}
                                onChange={(value) => createForm.setData('status', value)}
                            />
                        </Field>
                        <DialogFooter>
                            <Button type="button" variant="secondary" onClick={() => setCreateOpen(false)}>
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={createForm.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={editing !== null} onOpenChange={(open) => !open && setEditing(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('editar_sector')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <LangTabs locales={locales} invalidLocales={editInvalidLocales}>
                            {(locale) => (
                                <Field
                                    label={t('nombre')}
                                    htmlFor={`sector-edit-name-${locale}`}
                                    error={editForm.errors[`name.${locale}`]}
                                    required={locale === defaultLocale}
                                >
                                    <Input
                                        id={`sector-edit-name-${locale}`}
                                        value={editForm.data.name[locale] ?? ''}
                                        onChange={(event) =>
                                            editForm.setData('name', {
                                                ...editForm.data.name,
                                                [locale]: event.target.value,
                                            })
                                        }
                                    />
                                </Field>
                            )}
                        </LangTabs>
                        <DialogFooter>
                            <Button type="button" variant="secondary" onClick={() => setEditing(null)}>
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
