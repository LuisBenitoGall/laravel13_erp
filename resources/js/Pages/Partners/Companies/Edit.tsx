import { useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { Company } from '@/types/partners';

import CompanyForm from './components/CompanyForm';
import CompanyLogoPanel from './components/CompanyLogoPanel';
import CompanyProfileReadout from './components/CompanyProfileReadout';

const EDIT_TABS = ['info', 'images'] as const;
type EditTabId = (typeof EDIT_TABS)[number];

function tabFromPageUrl(url: string): EditTabId {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';
    const value = new URLSearchParams(query).get('tab');

    return EDIT_TABS.includes(value as EditTabId) ? (value as EditTabId) : 'info';
}

type EditCompany = Company & {
    logo_url?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
};

export default function Edit({ company }: { company: EditCompany }) {
    const { t, i18n } = useTranslation();
    const page = usePage();
    const urlTab = tabFromPageUrl(page.url);
    const [tab, setTab] = useState<string>(urlTab);
    const [prevPageUrl, setPrevPageUrl] = useState(page.url);

    if (page.url !== prevPageUrl) {
        setPrevPageUrl(page.url);
        setTab(urlTab);
    }

    const basePath = `/partners/companies/${company.id}/edit`;

    function selectTab(next: string) {
        setTab(next);
        const href = next === 'info' ? basePath : `${basePath}?tab=${next}`;
        window.history.replaceState(window.history.state, '', href);
    }

    const summaryFields = useMemo(
        () =>
            auditRecordSummaryFields({
                created_at: formatDate(company.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(company.updated_at ?? null, i18n.language) || undefined,
            }),
        [company.created_at, company.updated_at, i18n.language],
    );

    const tabs = [
        {
            id: 'info',
            labelKey: 'informacion',
            panel: (
                <div className="grid gap-6 lg:grid-cols-[minmax(240px,280px)_1fr]">
                    <CompanyProfileReadout
                        company={{
                            name: company.name,
                            tradename: company.tradename,
                            nif: company.nif,
                            status: company.status,
                            logo_url: company.logo_url ?? null,
                        }}
                    />
                    <CompanyForm
                        mode="edit"
                        method="put"
                        submitUrl={`/partners/companies/${company.id}`}
                        initial={{
                            name: company.name,
                            tradename: company.tradename ?? '',
                            nif: company.nif ?? '',
                            status: company.status,
                        }}
                    />
                </div>
            ),
        },
        {
            id: 'images',
            labelKey: 'imagenes',
            panel: (
                <CompanyLogoPanel
                    companyId={company.id}
                    logoUrl={company.logo_url ?? null}
                />
            ),
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mis_empresas',
                suboptionKey: 'empresa_editar',
                optionHref: '/partners/companies',
                suboptionParams: { name: company.name },
            }}
            showBack
            backHref="/partners/companies"
            actions={[]}
        >
            <RecordEditLayout
                summary={
                    <RecordSummary
                        titleKey="empresa_editar"
                        titleParams={{ name: company.name }}
                        fields={summaryFields}
                    />
                }
                tabs={tabs}
                activeTab={tab}
                onTabChange={selectTab}
            />
        </AppLayout>
    );
}
