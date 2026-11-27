import { router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { patchJson } from '@/lib/http';
import { auditRecordSummaryFields } from '@/lib/recordSummary';

import CompanyCurrenciesPanel, {
    type CompanyCurrencyOption,
} from '@/Pages/Admin/Companies/components/CompanyCurrenciesPanel';

import DelegationForm from './components/DelegationForm';
import DelegationLinkedWorkplacesTable from './components/DelegationLinkedWorkplacesTable';

const EDIT_TABS = ['info', 'workplaces'] as const;
type EditTabId = (typeof EDIT_TABS)[number];

function tabFromPageUrl(url: string): EditTabId {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';
    const value = new URLSearchParams(query).get('tab');

    return EDIT_TABS.includes(value as EditTabId) ? (value as EditTabId) : 'info';
}

type Props = {
    delegation: {
        id: number;
        name: string;
        tradename: string | null;
        nif: string | null;
        status: string;
        created_at?: string | null;
        updated_at?: string | null;
    };
    matrix: { id: number; name: string };
    currencies: CompanyCurrencyOption[];
    currency_options: CompanyCurrencyOption[];
    default_currency_id: number | null;
    can?: {
        update_company_currencies?: boolean;
        update_preferred_workplace?: boolean;
    };
};

export default function Edit({
    delegation,
    matrix,
    currencies,
    currency_options,
    default_currency_id,
    can,
}: Props) {
    const { t, i18n } = useTranslation();
    const page = usePage();
    const urlTab = tabFromPageUrl(page.url);
    const [tab, setTab] = useState<string>(urlTab);
    const [prevPageUrl, setPrevPageUrl] = useState(page.url);
    const [status, setStatus] = useState(delegation.status);
    const canUpdateCurrencies = Boolean(can?.update_company_currencies);
    const canUpdatePreferred = Boolean(can?.update_preferred_workplace);

    if (page.url !== prevPageUrl) {
        setPrevPageUrl(page.url);
        setTab(urlTab);
    }

    const activeTab = EDIT_TABS.includes(tab as EditTabId) ? tab : 'info';
    const basePath = `/companies/delegations/${delegation.id}/edit`;

    function selectTab(next: string) {
        setTab(next);
        const href = next === 'info' ? basePath : `${basePath}?tab=${next}`;
        window.history.replaceState(window.history.state, '', href);
    }

    const defaultCurrency = useMemo(
        () => currencies.find((currency) => currency.id === default_currency_id) ?? null,
        [currencies, default_currency_id],
    );

    const summaryFields = useMemo(() => {
        const fields = [
            { labelKey: 'delegacion_matriz', value: matrix.name },
            ...auditRecordSummaryFields({
                created_at: formatDate(delegation.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(delegation.updated_at ?? null, i18n.language) || undefined,
            }),
        ];

        if (defaultCurrency) {
            fields.splice(1, 0, {
                labelKey: 'moneda_por_defecto',
                value: defaultCurrency.code ?? defaultCurrency.name,
            });
        }

        return fields;
    }, [
        defaultCurrency,
        delegation.created_at,
        delegation.updated_at,
        i18n.language,
        matrix.name,
    ]);

    async function toggleStatus(checked: boolean) {
        const next = checked ? 'active' : 'closed';
        const response = await patchJson<{ status: string }>(
            `/companies/delegations/${delegation.id}/status`,
            { status: next },
        );
        setStatus(response.status);
    }

    const tabs = [
        {
            id: 'info',
            labelKey: 'informacion',
            panel: (
                <div className="space-y-6">
                    <DelegationForm
                        mode="edit"
                        method="put"
                        submitUrl={`/companies/delegations/${delegation.id}`}
                        statusChecked={status === 'active'}
                        onStatusToggle={async (checked) => {
                            try {
                                await toggleStatus(checked);
                            } catch (error) {
                                toast.error(
                                    error instanceof Error ? error.message : t('error_carga'),
                                );
                                throw error;
                            }
                        }}
                        initial={{
                            name: delegation.name,
                            tradename: delegation.tradename ?? '',
                            nif: delegation.nif ?? '',
                            status,
                        }}
                    />
                    <CompanyCurrenciesPanel
                        submitUrl={`/companies/delegations/${delegation.id}/currencies`}
                        currencies={currencies}
                        currencyOptions={currency_options}
                        defaultCurrencyId={default_currency_id}
                        canUpdate={canUpdateCurrencies}
                    />
                </div>
            ),
        },
        {
            id: 'workplaces',
            labelKey: 'centros_de_trabajo',
            panel: (
                <DelegationLinkedWorkplacesTable
                    delegationId={delegation.id}
                    canUpdatePreferred={canUpdatePreferred}
                />
            ),
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'delegaciones',
                suboptionKey: 'delegacion_editar',
                optionHref: '/companies/delegations',
                suboptionParams: { name: delegation.name },
            }}
            showBack
            backHref="/companies/delegations"
            actions={[
                {
                    type: 'delete',
                    onConfirm: () => {
                        router.delete(`/companies/delegations/${delegation.id}`);
                    },
                },
            ]}
        >
            <RecordEditLayout
                summary={
                    <RecordSummary
                        titleKey="delegacion_editar"
                        titleParams={{ name: delegation.name }}
                        fields={summaryFields}
                    />
                }
                tabs={tabs}
                activeTab={activeTab}
                onTabChange={selectTab}
            />
        </AppLayout>
    );
}
