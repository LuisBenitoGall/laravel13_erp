import { FormEvent, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import type { CellComponent } from 'tabulator-tables';

import { Field, Input } from '@/Components/Form';
import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

export type UserCompanyRow = {
    id: number;
    company_id: number;
    company_name: string;
    position: string | null;
    department: string | null;
    created_at: string | null;
};

type Props = {
    tableEndpoint: string;
    updateUrl: (id: number) => string;
    destroyUrl: (id: number) => string;
    canManage: boolean;
};

function emptyDash(cell: CellComponent): string {
    const value = cell.getValue();

    return value === null || value === undefined || value === '' ? '—' : String(value);
}

export default function UserCompaniesPanel({
    tableEndpoint,
    updateUrl,
    destroyUrl,
    canManage,
}: Props) {
    const { t, i18n } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [editOpen, setEditOpen] = useState(false);
    const [editing, setEditing] = useState<UserCompanyRow | null>(null);

    const editForm = useForm({
        position: '',
        department: '',
    });

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('empresa'),
                field: 'company_name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('posicion'),
                field: 'position',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: emptyDash,
            },
            {
                title: t('departamento'),
                field: 'department',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: emptyDash,
            },
            {
                title: t('fecha_alta'),
                field: 'created_at',
                widthGrow: 1,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
        ],
        [i18n.language, t],
    );

    function openEditModal(row: UserCompanyRow) {
        setEditing(row);
        editForm.setData({
            position: row.position ?? '',
            department: row.department ?? '',
        });
        editForm.clearErrors();
        setEditOpen(true);
    }

    function submitEdit(e: FormEvent) {
        e.preventDefault();
        if (editing === null) {
            return;
        }

        editForm.put(updateUrl(editing.id), {
            preserveScroll: true,
            onSuccess: () => {
                setEditOpen(false);
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: UserCompanyRow): Array<RowAction<UserCompanyRow>> => {
        if (!canManage) {
            return [];
        }

        return [
            {
                id: 'edit',
                icon: Pencil,
                labelKey: 'editar',
                onClick: (current) => openEditModal(current),
            },
            {
                id: 'delete',
                labelKey: 'eliminar_vinculo',
                variant: 'danger',
                confirm: true,
                onClick: (current) => {
                    router.delete(destroyUrl(current.id), {
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
    };

    return (
        <>
            <ServerTable<UserCompanyRow>
                key={tableKey}
                endpoint={tableEndpoint}
                columns={columns}
                persistKey={`user.companies.${tableEndpoint}`}
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog
                open={editOpen}
                onOpenChange={(open) => {
                    setEditOpen(open);
                    if (!open) {
                        setEditing(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('editar_vinculo_empresa')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <p className="text-sm text-slate-600">{editing?.company_name}</p>
                        <Field
                            label={t('posicion')}
                            htmlFor="user-company-position"
                            error={editForm.errors.position}
                        >
                            <Input
                                id="user-company-position"
                                value={editForm.data.position}
                                onChange={(event) =>
                                    editForm.setData('position', event.target.value)
                                }
                            />
                        </Field>
                        <Field
                            label={t('departamento')}
                            htmlFor="user-company-department"
                            error={editForm.errors.department}
                        >
                            <Input
                                id="user-company-department"
                                value={editForm.data.department}
                                onChange={(event) =>
                                    editForm.setData('department', event.target.value)
                                }
                            />
                        </Field>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setEditOpen(false)}
                            >
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={editForm.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
