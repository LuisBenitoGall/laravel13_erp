import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';

import WorkplaceForm from './components/WorkplaceForm';

type GeoOption = { id: number; name: string };

type Workplace = {
    id: number;
    name: string;
    address: string | null;
    cp: string | null;
    town_id: number | null;
    country_id?: number | null;
    province_id?: number | null;
    featured: boolean;
    status: string;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    workplace: Workplace;
    countries: GeoOption[];
};

export default function Edit({ workplace, countries }: Props) {
    const { i18n } = useTranslation();

    const summaryFields = useMemo(
        () =>
            auditRecordSummaryFields({
                created_at: formatDate(workplace.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(workplace.updated_at ?? null, i18n.language) || undefined,
            }),
        [workplace.created_at, workplace.updated_at, i18n.language],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'centros_de_trabajo',
                suboptionKey: 'editar',
                optionHref: '/admin/workplaces',
            }}
            backHref="/admin/workplaces"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_centro_trabajo"
                    titleParams={{ name: workplace.name }}
                    fields={summaryFields}
                />
                <WorkplaceForm
                    mode="edit"
                    method="put"
                    submitUrl={`/admin/workplaces/${workplace.id}`}
                    countries={countries}
                    initial={{
                        name: workplace.name,
                        address: workplace.address,
                        cp: workplace.cp,
                        town_id: workplace.town_id,
                        country_id: workplace.country_id ?? null,
                        province_id: workplace.province_id ?? null,
                        featured: workplace.featured,
                        status: workplace.status,
                    }}
                />
            </div>
        </AppLayout>
    );
}
