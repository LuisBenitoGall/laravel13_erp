import { useForm } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, Select } from '@/Components/Form';
import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef } from '@/Components/Table/ServerTable';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';

type CatalogAccount = {
    id: number;
    name: string;
    rate: string | number;
    duration: number | null;
    is_default: boolean;
};

type CurrentAccount = {
    id: number;
    account_name?: string | null;
    start_date: string | null;
    end_date: string | null;
    price: string | number;
    status: string;
};

type CompanyAccountRow = {
    id: number;
    account_name: string | null;
    start_date: string | null;
    end_date: string | null;
    price: string | number;
    payment_date: string | null;
    status: string;
};

type Props = {
    current: CurrentAccount | null;
    catalogAccounts: CatalogAccount[];
    canRenew: boolean;
};

const companyAccountStatusKeys: Record<string, string> = {
    pending: 'estado_pendiente',
    active: 'estado_activa',
    expired: 'estado_caducada',
    cancelled: 'estado_cancelada',
};

function companyAccountStatusLabel(
    status: string | null | undefined,
    translate: (key: string) => string,
): string {
    if (!status) {
        return translate('sin_datos');
    }

    const key = companyAccountStatusKeys[status];

    return key ? translate(key) : status;
}

export default function Show({ current, catalogAccounts, canRenew }: Props) {
    const { t, i18n } = useTranslation();
    const [renewOpen, setRenewOpen] = useState(false);
    const [tableKey, setTableKey] = useState(0);

    const defaultAccountId = useMemo(() => {
        const preferred = catalogAccounts.find((row) => row.is_default) ?? catalogAccounts[0];

        return preferred ? String(preferred.id) : '';
    }, [catalogAccounts]);

    const renewForm = useForm({
        account_id: defaultAccountId,
    });

    const accountOptions = useMemo(
        () =>
            catalogAccounts.map((row) => ({
                value: String(row.id),
                label: row.name,
            })),
        [catalogAccounts],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('cuenta'),
                field: 'account_name',
                widthGrow: 2,
            },
            {
                title: t('fecha_inicio'),
                field: 'start_date',
                width: 140,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('fecha_fin'),
                field: 'end_date',
                width: 140,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('precio'),
                field: 'price',
                width: 120,
                valueKind: 'number',
                headerFilterKind: 'number',
                formatter: fmt.money(2),
            },
            {
                title: t('estado'),
                field: 'status',
                width: 120,
                headerFilterKind: 'select',
                headerFilterValues: {
                    pending: t('estado_pendiente'),
                    active: t('estado_activa'),
                    expired: t('estado_caducada'),
                    cancelled: t('estado_cancelada'),
                },
                formatter: (cell) => companyAccountStatusLabel(String(cell.getValue() ?? ''), t),
            },
        ],
        [i18n.language, t],
    );

    function openRenew() {
        renewForm.setData('account_id', defaultAccountId);
        renewForm.clearErrors();
        setRenewOpen(true);
    }

    function submitRenew(event: FormEvent) {
        event.preventDefault();
        renewForm.post('/account/renew', {
            preserveScroll: true,
            onSuccess: () => {
                setRenewOpen(false);
                setTableKey((key) => key + 1);
            },
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mi_cuenta',
                suboptionKey: 'dashboard',
                optionHref: '/account',
            }}
            showBack={false}
            actions={
                canRenew
                    ? [
                          {
                              type: 'button',
                              labelKey: 'renovar_cuenta',
                              onClick: openRenew,
                          },
                      ]
                    : []
            }
        >
            <div className="mb-6 rounded-xl border border-slate-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">{t('mi_cuenta')}</h1>
                {current ? (
                    <dl className="mt-4 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                        <div>
                            <dt className="text-slate-500">{t('cuenta')}</dt>
                            <dd className="font-medium">{current.account_name ?? t('sin_datos')}</dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">{t('estado')}</dt>
                            <dd className="font-medium">
                                {companyAccountStatusLabel(current.status, t)}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">{t('fecha_inicio')}</dt>
                            <dd className="font-medium">
                                {formatDate(current.start_date, i18n.language) || t('sin_datos')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">{t('fecha_fin')}</dt>
                            <dd className="font-medium">
                                {formatDate(current.end_date, i18n.language) || t('sin_datos')}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-slate-500">{t('precio')}</dt>
                            <dd className="font-medium">{current.price}</dd>
                        </div>
                    </dl>
                ) : (
                    <p className="mt-3 text-sm text-slate-600">{t('cuenta_empresa_sin_activa')}</p>
                )}
            </div>

            <ServerTable<CompanyAccountRow>
                key={tableKey}
                endpoint="/account/company-accounts/table"
                columns={columns}
                persistKey="account.company-accounts"
                exportable={false}
            />

            <Dialog open={renewOpen} onOpenChange={setRenewOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('renovar_cuenta')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitRenew} className="space-y-4">
                        <Field
                            label={t('cuenta')}
                            htmlFor="account-renew-catalog"
                            error={renewForm.errors.account_id}
                            required
                        >
                            <Select
                                id="account-renew-catalog"
                                value={renewForm.data.account_id}
                                options={accountOptions}
                                onChange={(value) => renewForm.setData('account_id', value)}
                            />
                        </Field>
                        <DialogFooter>
                            <Button type="button" variant="secondary" onClick={() => setRenewOpen(false)}>
                                {t('cancelar')}
                            </Button>
                            <Button type="submit" disabled={renewForm.processing}>
                                {t('guardar')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
