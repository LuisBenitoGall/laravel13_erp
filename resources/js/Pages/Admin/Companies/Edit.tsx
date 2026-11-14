import { useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { Company } from '@/types/admin';

import CompanyForm from './components/CompanyForm';
import CompanyHierarchyPanel, {
    type HierarchyCompanyOption,
} from './components/CompanyHierarchyPanel';
import CompanyLogoPanel from './components/CompanyLogoPanel';
import CompanyProfileReadout from './components/CompanyProfileReadout';
import CompanySectorsPanel, { type SectorOption } from './components/CompanySectorsPanel';

const BASE_EDIT_TABS = ['info', 'images', 'hierarchy', 'sectors'] as const;
type EditTabId = (typeof BASE_EDIT_TABS)[number];

type CompanyEditCan = {
    view_company_sectors?: boolean;
    update_company_sectors?: boolean;
};

function tabFromPageUrl(url: string, allowed: readonly EditTabId[]): EditTabId {
    const query = url.includes('?') ? url.slice(url.indexOf('?')) : '';
    const value = new URLSearchParams(query).get('tab');

    return allowed.includes(value as EditTabId) ? (value as EditTabId) : 'info';
}

type EditCompany = Company & {
    logo_url?: string | null;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    company: EditCompany;
    parent: HierarchyCompanyOption | null;
    children: HierarchyCompanyOption[];
    hierarchy_options: {
        parents: HierarchyCompanyOption[];
        children: HierarchyCompanyOption[];
    };
    sectors: SectorOption[];
    sector_options: SectorOption[];
    can?: CompanyEditCan;
};

export default function Edit({
    company,
    parent,
    children,
    hierarchy_options,
    sectors,
    sector_options,
    can,
}: Props) {
    const { t, i18n } = useTranslation();
    const page = usePage();
    const canViewSectors = Boolean(can?.view_company_sectors);
    const canUpdateSectors = Boolean(can?.update_company_sectors);

    const allowedTabs = useMemo(
        () =>
            BASE_EDIT_TABS.filter(
                (id) => id !== 'sectors' || canViewSectors,
            ) as EditTabId[],
        [canViewSectors],
    );

    const urlTab = tabFromPageUrl(page.url, allowedTabs);
    const [tab, setTab] = useState<string>(urlTab);
    const [prevPageUrl, setPrevPageUrl] = useState(page.url);

    if (page.url !== prevPageUrl) {
        setPrevPageUrl(page.url);
        setTab(urlTab);
    }

    const activeTab = allowedTabs.includes(tab as EditTabId) ? tab : 'info';
    const basePath = `/admin/companies/${company.id}/edit`;

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
                        submitUrl={`/admin/companies/${company.id}`}
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
        {
            id: 'hierarchy',
            labelKey: 'jerarquia',
            panel: (
                <CompanyHierarchyPanel
                    companyId={company.id}
                    parent={parent}
                    children={children}
                    hierarchyOptions={hierarchy_options}
                />
            ),
        },
        ...(canViewSectors
            ? [
                  {
                      id: 'sectors',
                      labelKey: 'sectores',
                      panel: (
                          <CompanySectorsPanel
                              companyId={company.id}
                              sectors={sectors}
                              sectorOptions={sector_options}
                              canUpdate={canUpdateSectors}
                          />
                      ),
                  },
              ]
            : []),
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mis_empresas',
                suboptionKey: 'empresa_editar',
                optionHref: '/admin/companies',
                suboptionParams: { name: company.name },
            }}
            showBack
            backHref="/admin/companies"
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
                activeTab={activeTab}
                onTabChange={selectTab}
            />
        </AppLayout>
    );
}
