import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { ProductDocItem, ProductImageItem, ProductRow } from '@/types/catalog';

import ProductDocsPanel from './components/ProductDocsPanel';
import ProductForm from './components/ProductForm';
import ProductImagesPanel from './components/ProductImagesPanel';

type Props = {
    product: ProductRow;
    images: ProductImageItem[];
    docs: ProductDocItem[];
    can: {
        manage_images: boolean;
        manage_docs: boolean;
    };
    image_limits: {
        max_files: number;
        max_kilobytes: number;
    };
    doc_limits: {
        max_files: number;
        max_kilobytes: number;
    };
};

export default function Edit({ product, images, docs, can, image_limits, doc_limits }: Props) {
    const { t, i18n } = useTranslation();

    const typeLabel = product.type === 'service' ? t('servicio') : t('producto_fisico');

    const summaryFields = useMemo(
        () => [
            { labelKey: 'tipo', value: typeLabel },
            ...auditRecordSummaryFields({
                created_at: formatDate(product.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(product.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [product.created_at, product.updated_at, i18n.language, typeLabel],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'catalogo',
                suboptionKey: 'editar',
                optionHref: '/products',
            }}
            backHref="/products"
            actions={[]}
        >
            <RecordEditLayout
                summary={
                    <RecordSummary
                        titleKey="edicion_producto"
                        titleParams={{ name: product.name }}
                        fields={summaryFields}
                    />
                }
                tabs={[
                    {
                        id: 'general',
                        labelKey: 'general',
                        panel: (
                            <ProductForm
                                mode="edit"
                                method="put"
                                submitUrl={`/products/${product.id}`}
                                initial={product}
                            />
                        ),
                    },
                    {
                        id: 'images',
                        labelKey: 'imagenes',
                        panel: (
                            <ProductImagesPanel
                                productId={product.id}
                                images={images}
                                canManage={can.manage_images}
                                maxFiles={image_limits.max_files}
                                maxSizeBytes={image_limits.max_kilobytes * 1024}
                            />
                        ),
                    },
                    {
                        id: 'docs',
                        labelKey: 'documentos',
                        panel: (
                            <ProductDocsPanel
                                productId={product.id}
                                docs={docs}
                                canManage={can.manage_docs}
                                maxFiles={doc_limits.max_files}
                                maxSizeBytes={doc_limits.max_kilobytes * 1024}
                            />
                        ),
                    },
                ]}
            />
        </AppLayout>
    );
}
