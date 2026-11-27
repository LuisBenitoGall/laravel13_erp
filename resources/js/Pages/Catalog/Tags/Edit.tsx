import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { TagRow } from '@/types/catalog';

import TagForm from './components/TagForm';

type Props = {
    tag: TagRow;
};

export default function Edit({ tag }: Props) {
    const { i18n } = useTranslation();

    const summaryFields = useMemo(
        () => [
            { labelKey: 'slug', value: tag.slug },
            ...auditRecordSummaryFields({
                created_at: formatDate(tag.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(tag.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [tag.slug, tag.created_at, tag.updated_at, i18n.language],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'etiquetas',
                suboptionKey: 'editar',
                optionHref: '/products/tags',
            }}
            backHref="/products/tags"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_etiqueta"
                    titleParams={{ name: tag.name }}
                    fields={summaryFields}
                />
                <TagForm
                    mode="edit"
                    method="put"
                    submitUrl={`/products/tags/${tag.id}`}
                    initial={tag}
                />
            </div>
        </AppLayout>
    );
}
