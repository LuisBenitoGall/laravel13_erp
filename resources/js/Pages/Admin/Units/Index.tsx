import { router, useForm } from '@inertiajs/react';
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
import type { UnitRow } from '@/types/admin';

type UnitFormState = {
    name: string;
    symbol: string;
    status: string;
};

const emptyForm = (): UnitFormState => ({
    name: '',
    symbol: '',
    status: 'active',
});

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<UnitRow | null>(null);

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
                title: t('simbolo'),
                field: 'symbol',
                width: 120,
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

    function openEdit(row: UnitRow) {
        editForm.setData({
            name: row.name,
            symbol: row.symbol ?? '',
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/units', {
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

        editForm.put(`/admin/units/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: UnitRow): Array<RowAction<UnitRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/units/${row.id}/status`, {
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
                router.delete(`/admin/units/${current.id}`, {
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
                optionKey: 'unidades',
                suboptionKey: 'dashboard',
                optionHref: '/admin/units',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nueva_unidad',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<UnitRow>
                key={tableKey}
                endpoint="/admin/units/table"
                columns={columns}
                persistKey="admin.units"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nueva_unidad')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="unit-create-name"
                            error={createForm.errors.name}
                            required
                        >
                            <Input
                                id="unit-create-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('simbolo')}
                            htmlFor="unit-create-symbol"
                            error={createForm.errors.symbol}
                        >
                            <Input
                                id="unit-create-symbol"
                                value={createForm.data.symbol}
                                maxLength={4}
                                onChange={(event) => createForm.setData('symbol', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('estado')}
                            htmlFor="unit-create-status"
                            error={createForm.errors.status}
                            required
                        >
                            <Select
                                id="unit-create-status"
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
                        <DialogTitle>{t('editar_unidad')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="unit-edit-name"
                            error={editForm.errors.name}
                            required
                        >
                            <Input
                                id="unit-edit-name"
                                value={editForm.data.name}
                                onChange={(event) => editForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('simbolo')}
                            htmlFor="unit-edit-symbol"
                            error={editForm.errors.symbol}
                        >
                            <Input
                                id="unit-edit-symbol"
                                value={editForm.data.symbol}
                                maxLength={4}
                                onChange={(event) => editForm.setData('symbol', event.target.value)}
                            />
                        </Field>
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
