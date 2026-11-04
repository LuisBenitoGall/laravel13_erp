import { router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Select } from '@/Components/Form';
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
import type { VatTypeCountryOption, VatTypeRow } from '@/types/admin';

type VatTypeFormState = {
    country_id: string;
    name: string;
    rate: string;
    equivalence_surcharge: string;
    status: string;
};

type PageProps = {
    countries: VatTypeCountryOption[];
};

const emptyForm = (): VatTypeFormState => ({
    country_id: '',
    name: '',
    rate: '',
    equivalence_surcharge: '',
    status: 'active',
});

export default function Index() {
    const { t } = useTranslation();
    const { countries } = usePage<PageProps>().props;
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<VatTypeRow | null>(null);

    const createForm = useForm(emptyForm());
    const editForm = useForm(emptyForm());

    const countryOptions = useMemo(
        () => countries.map((country) => ({ value: String(country.id), label: country.name })),
        [countries],
    );

    const countryFilterValues = useMemo(() => {
        const values: Record<string, string> = {};
        for (const country of countries) {
            values[String(country.id)] = country.name;
        }
        return values;
    }, [countries]);

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('pais'),
                field: 'country_id',
                widthGrow: 1,
                headerFilterKind: 'select',
                headerFilterValues: countryFilterValues,
                formatter: (cell) => {
                    const row = cell.getData() as VatTypeRow;
                    return row.country?.name ?? '';
                },
            },
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('tipo_iva'),
                field: 'rate',
                width: 110,
                headerFilterKind: 'number',
                valueKind: 'number',
            },
            {
                title: t('recargo_equivalencia'),
                field: 'equivalence_surcharge',
                width: 130,
                headerFilterKind: 'number',
                valueKind: 'number',
            },
        ],
        [t, countryFilterValues],
    );

    function openCreate() {
        createForm.setData(emptyForm());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: VatTypeRow) {
        editForm.setData({
            country_id: String(row.country_id),
            name: row.name,
            rate: String(row.rate ?? ''),
            equivalence_surcharge:
                row.equivalence_surcharge === null || row.equivalence_surcharge === undefined
                    ? ''
                    : String(row.equivalence_surcharge),
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/vat-types', {
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

        editForm.put(`/admin/vat-types/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: VatTypeRow): Array<RowAction<VatTypeRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/vat-types/${row.id}/status`, {
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
                router.delete(`/admin/vat-types/${current.id}`, {
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

    function renderFields(form: typeof createForm, prefix: string, includeStatus: boolean) {
        return (
            <>
                <Field
                    label={t('pais')}
                    htmlFor={`${prefix}-country`}
                    error={form.errors.country_id}
                    required
                >
                    <Select
                        id={`${prefix}-country`}
                        value={form.data.country_id}
                        options={countryOptions}
                        onChange={(value) => form.setData('country_id', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
                <Field
                    label={t('nombre')}
                    htmlFor={`${prefix}-name`}
                    error={form.errors.name}
                    required
                >
                    <Input
                        id={`${prefix}-name`}
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                </Field>
                <div className="grid grid-cols-2 gap-3">
                    <Field
                        label={t('tipo_iva')}
                        htmlFor={`${prefix}-rate`}
                        error={form.errors.rate}
                        required
                    >
                        <Input
                            id={`${prefix}-rate`}
                            type="number"
                            step="0.01"
                            min="0"
                            value={form.data.rate}
                            onChange={(event) => form.setData('rate', event.target.value)}
                        />
                    </Field>
                    <Field
                        label={t('recargo_equivalencia')}
                        htmlFor={`${prefix}-re`}
                        error={form.errors.equivalence_surcharge}
                    >
                        <Input
                            id={`${prefix}-re`}
                            type="number"
                            step="0.01"
                            min="0"
                            value={form.data.equivalence_surcharge}
                            onChange={(event) =>
                                form.setData('equivalence_surcharge', event.target.value)
                            }
                        />
                    </Field>
                </div>
                {includeStatus ? (
                    <Field
                        label={t('estado')}
                        htmlFor={`${prefix}-status`}
                        error={form.errors.status}
                        required
                    >
                        <Select
                            id={`${prefix}-status`}
                            value={form.data.status}
                            options={statusOptions}
                            onChange={(value) => form.setData('status', value)}
                        />
                    </Field>
                ) : null}
            </>
        );
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_iva',
                suboptionKey: 'dashboard',
                optionHref: '/admin/vat-types',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_tipo_iva',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<VatTypeRow>
                key={tableKey}
                endpoint="/admin/vat-types/table"
                columns={columns}
                persistKey="admin.vat-types"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_tipo_iva')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        {renderFields(createForm, 'vat-create', true)}
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
                        <DialogTitle>{t('editar_tipo_iva')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        {renderFields(editForm, 'vat-edit', false)}
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
