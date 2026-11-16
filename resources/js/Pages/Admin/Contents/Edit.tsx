import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type ContentEditProps = {
    content: {
        id: number;
        name: string;
        code: string;
        kind: string;
        status: string;
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
    const { t } = useTranslation();

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'contenidos',
                suboptionKey: 'contenido_editar',
                optionHref: '/admin/contents',
            }}
            backHref="/admin/contents"
            actions={[]}
        >
            <div className="w-full max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                <h1 className="text-lg font-semibold text-slate-900">{content.name}</h1>
                <dl className="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt className="text-slate-500">{t('codigo')}</dt>
                        <dd className="font-medium text-slate-900">{content.code}</dd>
                    </div>
                    <div>
                        <dt className="text-slate-500">{t('tipo')}</dt>
                        <dd className="font-medium text-slate-900">{kindLabel(content.kind, t)}</dd>
                    </div>
                    <div>
                        <dt className="text-slate-500">{t('estado')}</dt>
                        <dd className="font-medium text-slate-900">
                            {content.status === 'active' ? t('activo') : t('inactivo')}
                        </dd>
                    </div>
                </dl>
                <p className="text-sm text-slate-600">{t('contenido_edicion_proximamente')}</p>
                <Button type="button" variant="outline" asChild>
                    <Link href="/admin/contents">{t('volver')}</Link>
                </Button>
            </div>
        </AppLayout>
    );
}
