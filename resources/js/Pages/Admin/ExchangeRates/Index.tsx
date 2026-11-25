import { router, useForm } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Select } from '@/Components/Form';
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
import AppLayout from '@/Layouts/AppLayout';
import type { CurrencyRow, ExchangeRateRow } from '@/types/admin';

type Props = {
    currencies: CurrencyRow[];
};

type CreateState = {
    from_currency_id: string;
    to_currency_id: string;
    rate_date: string;
    rate_ab: string;
    rate_ba: string;
};

type EditState = {
    rate_ab: string;
    rate_ba: string;
};

const emptyCreate = (): CreateState => ({
    from_currency_id: '',
    to_currency_id: '',
    rate_date: '',
    rate_ab: '',
    rate_ba: '',
});

const emptyEdit = (): EditState => ({
    rate_ab: '',
    rate_ba: '',
});

function currencyCode(list: CurrencyRow[], id: string): string {
    return list.find((c) => String(c.id) === id)?.code ?? '?';
}

export default function Index({ currencies }: Props) {
    const { t, i18n } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [createOpen, setCreateOpen] = useState(false);
    const [editing, setEditing] = useState<ExchangeRateRow | null>(null);

    const createForm = useForm(emptyCreate());
    const editForm = useForm(emptyEdit());

    const currencyList = Array.isArray(currencies) ? currencies : [];

    const currencyOptions = useMemo(
        () =>
            currencyList.map((c) => ({
                value: String(c.id),
                label: c.code ? `${c.code} — ${c.name}` : c.name,
            })),
        [currencyList],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('par'),
                field: 'pair_label',
                widthGrow: 1,
                headerFilterKind: 'string',
            },
            {
                title: t('tasa_a_b'),
                field: 'rate_ab',
                width: 140,
                headerFilterKind: 'number',
                valueKind: 'number',
                formatter: fmt.number(6),
            },
            {
                title: t('tasa_b_a'),
                field: 'rate_ba',
                width: 140,
                headerFilterKind: 'number',
                valueKind: 'number',
                formatter: fmt.number(6),
            },
            {
                title: t('fecha'),
                field: 'rate_date',
                width: 140,
                headerFilterKind: 'date',
                valueKind: 'date',
                formatter: fmt.date(i18n.language),
            },
        ],
        [i18n.language, t],
    );

    function openCreate() {
        createForm.setData(emptyCreate());
        createForm.clearErrors();
        setCreateOpen(true);
    }

    function openEdit(row: ExchangeRateRow) {
        editForm.setData({
            rate_ab: row.rate_ab ?? '',
            rate_ba: row.rate_ba ?? '',
        });
        editForm.clearErrors();
        setEditing(row);
    }

    function fillInverseCreate() {
        const ab = parseFloat(createForm.data.rate_ab);
        if (!isNaN(ab) && ab > 0) {
            createForm.setData('rate_ba', (1 / ab).toFixed(6));
        }
    }

    function fillInverseEdit() {
        const ab = parseFloat(editForm.data.rate_ab);
        if (!isNaN(ab) && ab > 0) {
            editForm.setData('rate_ba', (1 / ab).toFixed(6));
        }
    }

    function submitCreate(event: FormEvent) {
        event.preventDefault();
        createForm.post('/admin/exchange-rates', {
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

        editForm.put(`/admin/exchange-rates/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setEditing(null);
                setTableKey((key) => key + 1);
            },
        });
    }

    const rowActions = (row: ExchangeRateRow): Array<RowAction<ExchangeRateRow>> => [
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
                router.delete(`/admin/exchange-rates/${current.id}`, {
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

    const createCodeA = currencyCode(currencyList, createForm.data.from_currency_id);
    const createCodeB = currencyCode(currencyList, createForm.data.to_currency_id);
    const createHasPair =
        createForm.data.from_currency_id !== '' && createForm.data.to_currency_id !== '';

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'conversion_monedas',
                suboptionKey: 'dashboard',
                optionHref: '/admin/exchange-rates',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'nueva_conversion_moneda',
                    icon: Plus,
                    onClick: openCreate,
                },
            ]}
        >
            <ServerTable<ExchangeRateRow>
                key={tableKey}
                endpoint="/admin/exchange-rates/table"
                columns={columns}
                persistKey="admin.exchange-rates"
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('nueva_conversion_moneda')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitCreate} className="space-y-4">
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label={t('moneda_a')}
                                htmlFor="er-create-from"
                                error={createForm.errors.from_currency_id}
                                required
                            >
                                <Select
                                    id="er-create-from"
                                    value={createForm.data.from_currency_id}
                                    options={currencyOptions}
                                    onChange={(value) =>
                                        createForm.setData('from_currency_id', value)
                                    }
                                />
                            </Field>
                            <Field
                                label={t('moneda_b')}
                                htmlFor="er-create-to"
                                error={createForm.errors.to_currency_id}
                                required
                            >
                                <Select
                                    id="er-create-to"
                                    value={createForm.data.to_currency_id}
                                    options={currencyOptions}
                                    onChange={(value) =>
                                        createForm.setData('to_currency_id', value)
                                    }
                                />
                            </Field>
                        </div>
                        <Field
                            label={t('fecha')}
                            htmlFor="er-create-date"
                            error={createForm.errors.rate_date}
                            required
                        >
                            <Input
                                id="er-create-date"
                                type="date"
                                value={createForm.data.rate_date}
                                onChange={(event) =>
                                    createForm.setData('rate_date', event.target.value)
                                }
                            />
                        </Field>
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label={
                                    createHasPair
                                        ? t('tasa_uno_igual', {
                                              from: createCodeA,
                                              to: createCodeB,
                                          })
                                        : t('tasa_a_b')
                                }
                                htmlFor="er-create-rate-ab"
                                error={createForm.errors.rate_ab}
                            >
                                <Input
                                    id="er-create-rate-ab"
                                    type="number"
                                    step="0.000001"
                                    min="0"
                                    value={createForm.data.rate_ab}
                                    onChange={(event) =>
                                        createForm.setData('rate_ab', event.target.value)
                                    }
                                />
                            </Field>
                            <Field
                                label={
                                    createHasPair
                                        ? t('tasa_uno_igual', {
                                              from: createCodeB,
                                              to: createCodeA,
                                          })
                                        : t('tasa_b_a')
                                }
                                htmlFor="er-create-rate-ba"
                                error={createForm.errors.rate_ba}
                            >
                                <Input
                                    id="er-create-rate-ba"
                                    type="number"
                                    step="0.000001"
                                    min="0"
                                    value={createForm.data.rate_ba}
                                    onChange={(event) =>
                                        createForm.setData('rate_ba', event.target.value)
                                    }
                                />
                            </Field>
                        </div>
                        <div className="flex justify-start">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={fillInverseCreate}
                            >
                                {t('rellenar_inverso')}
                            </Button>
                        </div>
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

            <Dialog open={editing !== null} onOpenChange={(open) => !open && setEditing(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('editar_conversion_moneda')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <div className="grid grid-cols-2 gap-3">
                            <Field label={t('par')} htmlFor="er-edit-pair">
                                <Input
                                    id="er-edit-pair"
                                    value={editing?.pair_label ?? ''}
                                    readOnly
                                    disabled
                                />
                            </Field>
                            <Field label={t('fecha')} htmlFor="er-edit-date">
                                <Input
                                    id="er-edit-date"
                                    value={editing?.rate_date ?? ''}
                                    readOnly
                                    disabled
                                />
                            </Field>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <Field
                                label={t('tasa_uno_igual', {
                                    from: editing?.code_a ?? 'A',
                                    to: editing?.code_b ?? 'B',
                                })}
                                htmlFor="er-edit-rate-ab"
                                error={editForm.errors.rate_ab}
                            >
                                <Input
                                    id="er-edit-rate-ab"
                                    type="number"
                                    step="0.000001"
                                    min="0"
                                    value={editForm.data.rate_ab}
                                    onChange={(event) =>
                                        editForm.setData('rate_ab', event.target.value)
                                    }
                                />
                            </Field>
                            <Field
                                label={t('tasa_uno_igual', {
                                    from: editing?.code_b ?? 'B',
                                    to: editing?.code_a ?? 'A',
                                })}
                                htmlFor="er-edit-rate-ba"
                                error={editForm.errors.rate_ba}
                            >
                                <Input
                                    id="er-edit-rate-ba"
                                    type="number"
                                    step="0.000001"
                                    min="0"
                                    value={editForm.data.rate_ba}
                                    onChange={(event) =>
                                        editForm.setData('rate_ba', event.target.value)
                                    }
                                />
                            </Field>
                        </div>
                        <div className="flex justify-start">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={fillInverseEdit}
                            >
                                {t('rellenar_inverso')}
                            </Button>
                        </div>
                        <DialogFooter>
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={() => setEditing(null)}
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
        </AppLayout>
    );
}
