import { router, useForm } from '@inertiajs/react';
import { Map, Pencil, Plus } from 'lucide-react';
import { FormEvent, type ReactNode, useMemo, useState } from 'react';
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
import type { CountryRow } from '@/types/admin';

type FormState = {
    name: string;
    code: string;
    alfa2: string;
    alfa3: string;
    flag: string;
    status: string;
};

const emptyForm = (): FormState => ({
    name: '',
    code: '',
    alfa2: '',
    alfa3: '',
    flag: '',
    status: 'active',
});

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<CountryRow | null>(null);

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
            {
                title: t('codigo'),
                field: 'code',
                width: 100,
                headerFilterKind: 'string',
            },
            {
                title: t('alfa2'),
                field: 'alfa2',
                width: 90,
                headerFilterKind: 'string',
            },
            {
                title: t('alfa3'),
                field: 'alfa3',
                width: 90,
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

    function openEdit(row: CountryRow) {
        editForm.setData({
            name: row.name,
            code: row.code ?? '',
            alfa2: row.alfa2 ?? '',
            alfa3: row.alfa3 ?? '',
            flag: row.flag ?? '',
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/countries', {
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

        editForm.put(`/admin/countries/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: CountryRow): Array<RowAction<CountryRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/countries/${row.id}/status`, {
                    status,
                });

                return { status };
            },
        },
        {
            id: 'provinces',
            icon: Map,
            labelKey: 'provincias',
            onClick: () => router.visit(`/admin/countries/${row.id}/provinces`),
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
                router.delete(`/admin/countries/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => setTableKey((key) => key + 1),
                    onError: (errors) => {
                        const message = errors.country ?? Object.values(errors)[0];
                        if (message) {
                            toast.error(String(message));
                        }
                    },
                });
            },
        },
    ];

    function renderFields(
        form: typeof createForm,
        prefix: string,
    ): ReactNode {
        return (
            <>
                <Field label={t('nombre')} htmlFor={`${prefix}-name`} error={form.errors.name} required>
                    <Input
                        id={`${prefix}-name`}
                        value={form.data.name}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                </Field>
                <Field label={t('codigo')} htmlFor={`${prefix}-code`} error={form.errors.code}>
                    <Input
                        id={`${prefix}-code`}
                        value={form.data.code}
                        onChange={(event) => form.setData('code', event.target.value)}
                    />
                </Field>
                <div className="grid grid-cols-2 gap-3">
                    <Field label={t('alfa2')} htmlFor={`${prefix}-alfa2`} error={form.errors.alfa2}>
                        <Input
                            id={`${prefix}-alfa2`}
                            value={form.data.alfa2}
                            maxLength={2}
                            onChange={(event) => form.setData('alfa2', event.target.value)}
                        />
                    </Field>
                    <Field label={t('alfa3')} htmlFor={`${prefix}-alfa3`} error={form.errors.alfa3}>
                        <Input
                            id={`${prefix}-alfa3`}
                            value={form.data.alfa3}
                            maxLength={3}
                            onChange={(event) => form.setData('alfa3', event.target.value)}
                        />
                    </Field>
                </div>
                <Field label={t('bandera')} htmlFor={`${prefix}-flag`} error={form.errors.flag}>
                    <Input
                        id={`${prefix}-flag`}
                        value={form.data.flag}
                        onChange={(event) => form.setData('flag', event.target.value)}
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

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'paises',
                suboptionKey: 'dashboard',
                optionHref: '/admin/countries',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_pais',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<CountryRow>
                key={tableKey}
                endpoint="/admin/countries/table"
                columns={columns}
                persistKey="admin.countries"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_pais')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        {renderFields(createForm, 'country-create')}
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
                        <DialogTitle>{t('editar_pais')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        {renderFields(editForm, 'country-edit')}
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
