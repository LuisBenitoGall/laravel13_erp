import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { CategoryParentOption, CategoryRow } from '@/types/catalog';

import CategoryForm from './components/CategoryForm';

type Props = {
    category: CategoryRow;
    parent_options: CategoryParentOption[];
};

export default function Edit({ category, parent_options }: Props) {
    const { t, i18n } = useTranslation();

    const summaryFields = useMemo(
        () => [
            { labelKey: 'slug', value: category.slug },
            {
                labelKey: 'categoria_padre',
                value: category.parent_name ?? t('categoria_raiz'),
            },
            ...auditRecordSummaryFields({
                created_at: formatDate(category.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(category.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [category.slug, category.parent_name, category.created_at, category.updated_at, i18n.language, t],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'categorias',
                suboptionKey: 'editar',
                optionHref: '/products/categories',
            }}
            backHref="/products/categories"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_categoria"
                    titleParams={{ name: category.name }}
                    fields={summaryFields}
                />
                <CategoryForm
                    mode="edit"
                    method="put"
                    submitUrl={`/products/categories/${category.id}`}
                    initial={category}
                    parentOptions={parent_options}
                />
            </div>
        </AppLayout>
    );
}
