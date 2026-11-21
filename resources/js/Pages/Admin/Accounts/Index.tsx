import { router, useForm } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Select, Switch, Textarea } from '@/Components/Form';
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
import type { AccountRow } from '@/types/admin';

type AccountFormState = {
    name: string;
    description: string;
    rate: string;
    duration: string;
    status: string;
    is_default: boolean;
};

const emptyForm = (): AccountFormState => ({
    name: '',
    description: '',
    rate: '0',
    duration: '',
    status: 'active',
    is_default: false,
});

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<AccountRow | null>(null);

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
                title: t('tarifa'),
                field: 'rate',
                width: 120,
                valueKind: 'number',
                headerFilterKind: 'number',
            },
            {
                title: t('duracion_dias'),
                field: 'duration',
                width: 120,
                valueKind: 'number',
                headerFilterKind: 'number',
            },
            {
                title: t('por_defecto'),
                field: 'is_default',
                width: 120,
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
            },
        ],
        [t],
    );

    function openCreate() {
        createForm.setData(emptyForm());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: AccountRow) {
        editForm.setData({
            name: row.name,
            description: row.description ?? '',
            rate: String(row.rate ?? '0'),
            duration: row.duration !== null && row.duration !== undefined ? String(row.duration) : '',
            status: row.status,
            is_default: row.is_default,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/accounts', {
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

        editForm.put(`/admin/accounts/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: AccountRow): Array<RowAction<AccountRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/accounts/${row.id}/status`, {
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
                router.delete(`/admin/accounts/${current.id}`, {
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
                optionKey: 'cuentas',
                suboptionKey: 'dashboard',
                optionHref: '/admin/accounts',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nueva_cuenta_saas',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<AccountRow>
                key={tableKey}
                endpoint="/admin/accounts/table"
                columns={columns}
                persistKey="admin.accounts"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nueva_cuenta_saas')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="account-create-name"
                            error={createForm.errors.name}
                            required
                        >
                            <Input
                                id="account-create-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('descripcion')}
                            htmlFor="account-create-description"
                            error={createForm.errors.description}
                        >
                            <Textarea
                                id="account-create-description"
                                value={createForm.data.description}
                                onChange={(event) =>
                                    createForm.setData('description', event.target.value)
                                }
                            />
                        </Field>
                        <Field
                            label={t('tarifa')}
                            htmlFor="account-create-rate"
                            error={createForm.errors.rate}
                            required
                        >
                            <Input
                                id="account-create-rate"
                                type="number"
                                step="0.01"
                                min="0"
                                value={createForm.data.rate}
                                onChange={(event) => createForm.setData('rate', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('duracion_dias')}
                            htmlFor="account-create-duration"
                            error={createForm.errors.duration}
                        >
                            <Input
                                id="account-create-duration"
                                type="number"
                                min="1"
                                value={createForm.data.duration}
                                onChange={(event) =>
                                    createForm.setData('duration', event.target.value)
                                }
                            />
                        </Field>
                        <Field
                            label={t('estado')}
                            htmlFor="account-create-status"
                            error={createForm.errors.status}
                            required
                        >
                            <Select
                                id="account-create-status"
                                value={createForm.data.status}
                                options={statusOptions}
                                onChange={(value) => createForm.setData('status', value)}
                            />
                        </Field>
                        <Switch
                            id="account-create-default"
                            label={t('por_defecto')}
                            checked={createForm.data.is_default}
                            onCheckedChange={(checked) => createForm.setData('is_default', checked)}
                        />
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
                        <DialogTitle>{t('editar_cuenta_saas')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <Field
                            label={t('nombre')}
                            htmlFor="account-edit-name"
                            error={editForm.errors.name}
                            required
                        >
                            <Input
                                id="account-edit-name"
                                value={editForm.data.name}
                                onChange={(event) => editForm.setData('name', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('descripcion')}
                            htmlFor="account-edit-description"
                            error={editForm.errors.description}
                        >
                            <Textarea
                                id="account-edit-description"
                                value={editForm.data.description}
                                onChange={(event) =>
                                    editForm.setData('description', event.target.value)
                                }
                            />
                        </Field>
                        <Field
                            label={t('tarifa')}
                            htmlFor="account-edit-rate"
                            error={editForm.errors.rate}
                            required
                        >
                            <Input
                                id="account-edit-rate"
                                type="number"
                                step="0.01"
                                min="0"
                                value={editForm.data.rate}
                                onChange={(event) => editForm.setData('rate', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('duracion_dias')}
                            htmlFor="account-edit-duration"
                            error={editForm.errors.duration}
                        >
                            <Input
                                id="account-edit-duration"
                                type="number"
                                min="1"
                                value={editForm.data.duration}
                                onChange={(event) => editForm.setData('duration', event.target.value)}
                            />
                        </Field>
                        <Switch
                            id="account-edit-default"
                            label={t('por_defecto')}
                            checked={editForm.data.is_default}
                            disabled={editing?.is_default === true}
                            onCheckedChange={(checked) => {
                                if (editing?.is_default === true && !checked) {
                                    return;
                                }
                                editForm.setData('is_default', checked);
                            }}
                        />
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
