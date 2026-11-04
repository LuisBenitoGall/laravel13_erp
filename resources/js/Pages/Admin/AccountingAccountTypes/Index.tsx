import { router, useForm } from '@inertiajs/react';
import { ListTree, Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input } from '@/Components/Form';
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
import type { AccountingAccountTypeRow } from '@/types/admin';

type CreateState = { code: string; name: string };
type EditState = { name: string };

const emptyCreate = (): CreateState => ({ code: '', name: '' });
const emptyEdit = (): EditState => ({ name: '' });

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<AccountingAccountTypeRow | null>(null);

    const createForm = useForm(emptyCreate());
    const editForm = useForm(emptyEdit());

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('codigo'),
                field: 'code',
                width: 120,
                headerFilterKind: 'string',
            },
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('nivel'),
                field: 'level',
                width: 90,
                valueKind: 'number',
            },
        ],
        [t],
    );

    function openCreate() {
        createForm.setData(emptyCreate());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: AccountingAccountTypeRow) {
        editForm.setData({ name: row.name ?? '' });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/accounting-account-types', {
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

        editForm.put(`/admin/accounting-account-types/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: AccountingAccountTypeRow): Array<RowAction<AccountingAccountTypeRow>> => [
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
                router.delete(`/admin/accounting-account-types/${current.id}`, {
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
                optionKey: 'grupos_contables',
                suboptionKey: 'dashboard',
                optionHref: '/admin/accounting-account-types',
            }}
            showBack={false}
            actions={[
                {
                    type: 'link',
                    labelKey: 'ver_plan_pgc',
                    href: '/admin/accounting-account-types/show',
                    icon: ListTree,
                },
                {
                    type: 'button',
                    labelKey: 'nuevo_grupo_contable',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<AccountingAccountTypeRow>
                key={tableKey}
                endpoint="/admin/accounting-account-types/table"
                columns={columns}
                persistKey="admin.accounting-account-types"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_grupo_contable')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('codigo')}
                            htmlFor="aat-create-code"
                            error={createForm.errors.code}
                            required
                        >
                            <Input
                                id="aat-create-code"
                                value={createForm.data.code}
                                onChange={(event) => createForm.setData('code', event.target.value)}
                            />
                        </Field>
                        <Field
                            label={t('nombre')}
                            htmlFor="aat-create-name"
                            error={createForm.errors.name}
                            required
                        >
                            <Input
                                id="aat-create-name"
                                value={createForm.data.name}
                                onChange={(event) => createForm.setData('name', event.target.value)}
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
                        <DialogTitle>{t('editar_grupo_contable')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <Field label={t('codigo')} htmlFor="aat-edit-code">
                            <Input
                                id="aat-edit-code"
                                value={editing?.code ?? ''}
                                readOnly
                                disabled
                            />
                        </Field>
                        <Field
                            label={t('nombre')}
                            htmlFor="aat-edit-name"
                            error={editForm.errors.name}
                            required
                        >
                            <Input
                                id="aat-edit-name"
                                value={editForm.data.name}
                                onChange={(event) => editForm.setData('name', event.target.value)}
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
