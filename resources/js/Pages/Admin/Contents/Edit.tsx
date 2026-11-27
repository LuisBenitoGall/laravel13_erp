import { router } from '@inertiajs/react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';

import ContentForm from './components/ContentForm';

type ContentEditProps = {
    content: {
        id: number;
        name: string;
        code: string;
        kind: string;
        status: string;
        titles?: Record<string, string | null>;
        excerpts?: Record<string, string | null>;
        contents?: Record<string, string | null>;
        referrer?: string[] | null;
        video?: string[] | null;
        created_at?: string | null;
        updated_at?: string | null;
    };
};

function kindLabel(kind: string, t: (key: string) => string): string {
    if (kind === 'help') {
        return t('contenido_tipo_informacion');
    }
    if (kind === 'article') {
        return t('contenido_tipo_contenido');
    }

    return kind;
}

export default function Edit({ content }: ContentEditProps) {
    const { t, i18n } = useTranslation();

    const summaryFields = useMemo(
        () => [
            {
                labelKey: 'tipo',
                value: kindLabel(content.kind, t),
            },
            ...auditRecordSummaryFields({
                created_at: formatDate(content.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(content.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [content.created_at, content.kind, content.updated_at, i18n.language, t],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'contenidos',
                suboptionKey: 'contenido_editar',
                optionHref: '/settings/contents',
            }}
            backHref="/settings/contents"
            actions={[
                {
                    type: 'delete',
                    confirmKey: 'contenido_confirmar_eliminar',
                    onConfirm: () => {
                        router.delete(`/settings/contents/${content.id}`);
                    },
                },
            ]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_contenido"
                    titleParams={{ name: content.name }}
                    identifier={content.code}
                    fields={summaryFields}
                />
                <ContentForm
                    contentId={content.id}
                    kind={content.kind}
                    initial={{
                        name: content.name,
                        status: content.status,
                        title: content.titles,
                        excerpt: content.excerpts,
                        content: content.contents,
                        referrer: content.referrer,
                        video: content.video,
                    }}
                />
            </div>
        </AppLayout>
    );
}
