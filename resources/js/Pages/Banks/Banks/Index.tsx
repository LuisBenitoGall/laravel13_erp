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
import type { BankRow } from '@/types/banks';

type BankFormState = {
    name: string;
    tradename: string;
    lei: string;
    eu_code: string;
    supervisor_code: string;
    swift: string;
    status: string;
};

const emptyForm = (): BankFormState => ({
    name: '',
    tradename: '',
    lei: '',
    eu_code: '',
    supervisor_code: '',
    swift: '',
    status: 'active',
});

export default function Index() {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<BankRow | null>(null);

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
                title: t('nombre_comercial'),
                field: 'tradename',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('codigo_eu'),
                field: 'eu_code',
                width: 120,
                headerFilterKind: 'string',
            },
            {
                title: t('swift'),
                field: 'swift',
                width: 140,
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

    function openEdit(row: BankRow) {
        editForm.setData({
            name: row.name,
            tradename: row.tradename ?? '',
            lei: row.lei ?? '',
            eu_code: row.eu_code ?? '',
            supervisor_code: row.supervisor_code ?? '',
            swift: row.swift ?? '',
            status: row.status,
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/banks', {
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

        editForm.put(`/admin/banks/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: BankRow): Array<RowAction<BankRow>> => [
        {
            id: 'toggle-status',
            kind: 'toggle',
            labelKey: row.status === 'active' ? 'desactivar' : 'activar',
            checked: row.status === 'active',
            onToggle: async (_row, checked) => {
                const status = checked ? 'active' : 'inactive';
                await patchJson<{ status: string }>(`/admin/banks/${row.id}/status`, {
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
                router.delete(`/admin/banks/${current.id}`, {
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

    function renderBankFields(
        form: typeof createForm,
        prefix: string,
        includeStatus: boolean,
    ) {
        return (
            <>
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
                <Field
                    label={t('nombre_comercial')}
                    htmlFor={`${prefix}-tradename`}
                    error={form.errors.tradename}
                >
                    <Input
                        id={`${prefix}-tradename`}
                        value={form.data.tradename}
                        onChange={(event) => form.setData('tradename', event.target.value)}
                    />
                </Field>
                <div className="grid grid-cols-2 gap-3">
                    <Field
                        label={t('codigo_eu')}
                        htmlFor={`${prefix}-eu-code`}
                        error={form.errors.eu_code}
                        required
                    >
                        <Input
                            id={`${prefix}-eu-code`}
                            value={form.data.eu_code}
                            onChange={(event) => form.setData('eu_code', event.target.value)}
                        />
                    </Field>
                    <Field
                        label={t('codigo_supervisor')}
                        htmlFor={`${prefix}-supervisor`}
                        error={form.errors.supervisor_code}
                    >
                        <Input
                            id={`${prefix}-supervisor`}
                            value={form.data.supervisor_code}
                            onChange={(event) => form.setData('supervisor_code', event.target.value)}
                        />
                    </Field>
                </div>
                <div className="grid grid-cols-2 gap-3">
                    <Field label={t('lei')} htmlFor={`${prefix}-lei`} error={form.errors.lei}>
                        <Input
                            id={`${prefix}-lei`}
                            value={form.data.lei}
                            onChange={(event) => form.setData('lei', event.target.value)}
                        />
                    </Field>
                    <Field label={t('swift')} htmlFor={`${prefix}-swift`} error={form.errors.swift}>
                        <Input
                            id={`${prefix}-swift`}
                            value={form.data.swift}
                            maxLength={11}
                            onChange={(event) => form.setData('swift', event.target.value)}
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
                optionKey: 'bancos',
                suboptionKey: 'dashboard',
                optionHref: '/admin/banks',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_banco',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<BankRow>
                key={tableKey}
                endpoint="/admin/banks/table"
                columns={columns}
                persistKey="admin.banks"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_banco')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        {renderBankFields(createForm, 'bank-create', true)}
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
                        <DialogTitle>{t('editar_banco')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        {renderBankFields(editForm, 'bank-edit', false)}
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
