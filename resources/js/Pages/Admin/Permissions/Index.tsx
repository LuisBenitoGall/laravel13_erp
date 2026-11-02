import { router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
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

type FunctionalityOption = {
    id: number;
    slug: string;
    label: string;
};

type PermissionRow = {
    id: number;
    name: string;
    functionality_slug: string | null;
    functionality_label: string;
    can_delete: boolean;
};

type Props = {
    functionalities: FunctionalityOption[];
};

export default function Index({ functionalities }: Props) {
    const { t } = useTranslation();
    const [createOpen, setCreateOpen] = useState(false);
    const [tableKey, setTableKey] = useState(0);

    const createForm = useForm({
        functionality_id: functionalities[0]?.id ?? 0,
        suffix: '',
    });

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('funcionalidad'),
                field: 'functionality_label',
                widthGrow: 1,
                headerSort: false,
            },
        ],
        [t],
    );

    const functionalityOptions = useMemo(
        () =>
            functionalities.map((item) => ({
                value: String(item.id),
                label: `${item.label} (${item.slug})`,
            })),
        [functionalities],
    );

    function openCreateModal() {
        createForm.setData({
            functionality_id: functionalities[0]?.id ?? 0,
            suffix: '',
        });
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/permissions', {
            preserveScroll: true,
            onSuccess: () => {
                setCreateOpen(false);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: PermissionRow): Array<RowAction<PermissionRow>> => {
        if (!row.can_delete) {
            return [];
        }

        return [
            {
                id: 'delete',
                labelKey: 'eliminar',
                variant: 'danger',
                confirm: true,
                onClick: (current) => {
                    router.delete(`/admin/permissions/${current.id}`, {
                        preserveScroll: true,
                        onSuccess: () => setTableKey((key) => key + 1),
                    });
                },
            },
        ];
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'permisos',
                suboptionKey: 'dashboard',
                optionHref: '/admin/permissions',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nuevo_permiso',
                    icon: Plus,
                    onClick: openCreateModal,
                },
            ]}
        >
            <ServerTable<PermissionRow>
                key={tableKey}
                endpoint="/admin/permissions/table"
                columns={columns}
                persistKey="admin.permissions"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nuevo_permiso')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <Field
                            label={t('funcionalidad')}
                            htmlFor="permission-functionality"
                            error={createForm.errors.functionality_id}
                            required
                        >
                            <Select
                                id="permission-functionality"
                                value={String(createForm.data.functionality_id)}
                                options={functionalityOptions}
                                onChange={(value) =>
                                    createForm.setData('functionality_id', Number(value))
                                }
                            />
                        </Field>
                        <Field
                            label={t('sufijo')}
                            htmlFor="permission-suffix"
                            error={createForm.errors.suffix}
                            required
                        >
                            <Input
                                id="permission-suffix"
                                value={createForm.data.suffix}
                                onChange={(event) => createForm.setData('suffix', event.target.value)}
                            />
                        </Field>
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
        </AppLayout>
    );
}
