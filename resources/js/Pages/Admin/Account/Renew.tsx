import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import {
    AccountCatalogGrid,
    type AccountCatalogOption,
} from '@/Components/Account/AccountCatalogGrid';
import { FormActions } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type Props = {
    catalogAccounts: AccountCatalogOption[];
    defaultAccountId: number | null;
    canRenew: boolean;
    historyUrl: string;
};

export default function Renew({ catalogAccounts, defaultAccountId, canRenew, historyUrl }: Props) {
    const { t } = useTranslation();

    const initialId = useMemo(() => {
        if (defaultAccountId !== null) {
            return String(defaultAccountId);
        }

        return catalogAccounts[0] ? String(catalogAccounts[0].id) : '';
    }, [catalogAccounts, defaultAccountId]);

    const form = useForm({
        account_id: initialId,
    });

    function submit(event: FormEvent) {
        event.preventDefault();

        if (!canRenew) {
            return;
        }

        form.post('/account-renew', {
            preserveScroll: true,
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'renovar_cuenta',
                suboptionKey: 'mi_cuenta',
                optionHref: '/account-renew',
            }}
            showBack
        >
            <div className="rounded-xl border border-slate-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">{t('renovar_mejorar_cuenta')}</h1>
                <p className="mt-2 text-sm text-slate-600">{t('renovar_cuenta_ayuda')}</p>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <AccountCatalogGrid
                        accounts={catalogAccounts}
                        value={form.data.account_id}
                        onChange={(accountId) => form.setData('account_id', String(accountId))}
                        disabled={!canRenew || form.processing}
                    />
                    {form.errors.account_id ? (
                        <p className="text-sm text-red-600">{form.errors.account_id}</p>
                    ) : null}

                    <FormActions>
                        <Button type="button" variant="secondary" onClick={() => router.visit(historyUrl)}>
                            {t('cancelar')}
                        </Button>
                        <Button type="submit" disabled={!canRenew || form.processing || !form.data.account_id}>
                            {t('guardar')}
                        </Button>
                    </FormActions>
                </form>
            </div>
        </AppLayout>
    );
}
