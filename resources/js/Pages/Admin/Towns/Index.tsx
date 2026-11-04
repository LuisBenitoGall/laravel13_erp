import { router, useForm } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

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
import type { CountryRow, ProvinceRow, TownRow } from '@/types/admin';

type Props = {
    province: ProvinceRow;
    country?: CountryRow | null;
};

type FormState = {
    name: string;
    status: string;
};

const emptyForm = (): FormState => ({
    name: '',
    status: 'active',
});

export default function Index({ province, country }: Props) {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<TownRow | null>(null);

    const createForm = useForm(emptyForm());
    const editForm = useForm(emptyForm());

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
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
        ],
        [t],
    );

    function openCreate() {
        createForm.setData(emptyForm());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: TownRow) {
        editForm.setData({
            name: row.name,
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post(`/admin/provinces/${province.id}/towns`, {
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

        editForm.put(`/admin/towns/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: TownRow): Array<RowAction<TownRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/towns/${row.id}/status`, {
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
                router.delete(`/admin/towns/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => setTableKey((key) => key + 1),
                });
            },
        },
    ];

    function renderFields(form: typeof createForm, prefix: string) {
        return (
            <>
                <Field label={t('nombre')} htmlFor={`${prefix}-name`} error={form.errors.name} required>
                    <Input
                        id={`${prefix}-name`}
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                </Field>
                <Field label={t('estado')} htmlFor={`${prefix}-status`} error={form.errors.status} required>
                    <Select
                        id={`${prefix}-status`}
                        value={form.data.status}
                        options={statusOptions}
                        onChange={(value) => form.setData('status', value)}
                    />
                </Field>
            </>
        );
    }

    const provincesHref = country
        ? `/admin/countries/${country.id}/provinces`
        : `/admin/countries/${province.country_id}/provinces`;

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'paises',
                suboptionKey: 'poblaciones',
                optionHref: '/admin/countries',
            }}
            backHref={provincesHref}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nueva_poblacion',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <p className="mb-3 text-sm text-slate-600">
                {country ? (
                    <>
                        {t('pais')}: <span className="font-medium text-slate-900">{country.name}</span>
                        {' · '}
                    </>
                ) : null}
                {t('provincia')}: <span className="font-medium text-slate-900">{province.name}</span>
            </p>

            <ServerTable<TownRow>
                key={tableKey}
                endpoint={`/admin/provinces/${province.id}/towns/table`}
                columns={columns}
                persistKey={`admin.provinces.${province.id}.towns`}
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nueva_poblacion')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        {renderFields(createForm, 'town-create')}
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
                        <DialogTitle>{t('editar_poblacion')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        {renderFields(editForm, 'town-edit')}
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
