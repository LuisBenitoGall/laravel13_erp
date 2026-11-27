import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { FamilyRow } from '@/types/catalog';

import FamilyForm from './components/FamilyForm';

type Props = {
    family: FamilyRow;
};

export default function Edit({ family }: Props) {
    const { i18n } = useTranslation();

    const summaryFields = useMemo(
        () => [
            { labelKey: 'slug', value: family.slug },
            ...auditRecordSummaryFields({
                created_at: formatDate(family.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(family.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [family.slug, family.created_at, family.updated_at, i18n.language],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'familias',
                suboptionKey: 'editar',
                optionHref: '/products/families',
            }}
            backHref="/products/families"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_familia"
                    titleParams={{ name: family.name }}
                    fields={summaryFields}
                />
                <FamilyForm
                    mode="edit"
                    method="put"
                    submitUrl={`/products/families/${family.id}`}
                    initial={family}
                />
            </div>
        </AppLayout>
    );
}
