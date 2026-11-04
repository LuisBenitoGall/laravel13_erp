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
import type { CurrencyRow } from '@/types/admin';

type CreateState = {
    name: string;
    code: string;
    number: string;
    symbol: string;
    status: string;
};

type EditState = {
    name: string;
    symbol: string;
};

const emptyCreate = (): CreateState => ({
    name: '',
    code: '',
    number: '',
    symbol: '',
    status: 'active',
});

const emptyEdit = (): EditState => ({
    name: '',
    symbol: '',
});

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<CurrencyRow | null>(null);

    const createForm = useForm(emptyCreate());
    const editForm = useForm(emptyEdit());

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
                title: t('numero'),
                field: 'number',
                width: 100,
                headerFilterKind: 'string',
            },
            {
                title: t('simbolo'),
                field: 'symbol',
                width: 100,
                headerFilterKind: 'string',
            },
        ],
        [t],
    );

    function openCreate() {
        createForm.setData(emptyCreate());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: CurrencyRow) {
        editForm.setData({
            name: row.name,
            symbol: row.symbol ?? '',
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/currencies', {
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

        editForm.put(`/admin/currencies/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: CurrencyRow): Array<RowAction<CurrencyRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/currencies/${row.id}/status`, {
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
                router.delete(`/admin/currencies/${current.id}`, {
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
                optionKey: 'monedas',
                suboptionKey: 'dashboard',
                optionHref: '/admin/currencies',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_moneda',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<CurrencyRow>
                key={tableKey}
                endpoint="/admin/currencies/table"
                columns={columns}
                persistKey="admin.currencies"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_moneda')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="currency-create-name"
                            error={createForm.errors.name}
                            required
                        >
                            <Input
                                id="currency-create-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label={t('codigo')}
                                htmlFor="currency-create-code"
                                error={createForm.errors.code}
                            >
                                <Input
                                    id="currency-create-code"
                                    value={createForm.data.code}
                                    maxLength={3}
                                    onChange={(event) => createForm.setData('code', event.target.value)}
                                />
                            </Field>
                            <Field
                                label={t('numero')}
                                htmlFor="currency-create-number"
                                error={createForm.errors.number}
                            >
                                <Input
                                    id="currency-create-number"
                                    value={createForm.data.number}
                                    maxLength={4}
                                    onChange={(event) => createForm.setData('number', event.target.value)}
                                />
                            </Field>
                        </div>
                        <Field
                            label={t('simbolo')}
                            htmlFor="currency-create-symbol"
                            error={createForm.errors.symbol}
                        >
                            <Input
                                id="currency-create-symbol"
                                value={createForm.data.symbol}
                                maxLength={8}
                                onChange={(event) => createForm.setData('symbol', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('estado')}
                            htmlFor="currency-create-status"
                            error={createForm.errors.status}
                            required
                        >
                            <Select
                                id="currency-create-status"
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
                        <DialogTitle>{t('editar_moneda')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="currency-edit-name"
                            error={editForm.errors.name}
                            required
                        >
                            <Input
                                id="currency-edit-name"
                                value={editForm.data.name}
                                onChange={(event) => editForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <div className="grid grid-cols-2 gap-3">
                            <Field label={t('codigo')} htmlFor="currency-edit-code">
                                <Input
                                    id="currency-edit-code"
                                    value={editing?.code ?? ''}
                                    maxLength={3}
                                    readOnly
                                    disabled
                                />
                            </Field>
                            <Field label={t('numero')} htmlFor="currency-edit-number">
                                <Input
                                    id="currency-edit-number"
                                    value={editing?.number ?? ''}
                                    maxLength={4}
                                    readOnly
                                    disabled
                                />
                            </Field>
                        </div>
                        <Field
                            label={t('simbolo')}
                            htmlFor="currency-edit-symbol"
                            error={editForm.errors.symbol}
                        >
                            <Input
                                id="currency-edit-symbol"
                                value={editForm.data.symbol}
                                maxLength={8}
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
