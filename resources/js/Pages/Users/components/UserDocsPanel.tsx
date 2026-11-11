import { Download, FileText, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Dropzone } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

export type UserDocItem = {
    id: number;
    original_name: string;
    mime: string;
    size_bytes: number;
    created_at: string | null;
};

type Props = {
    docs: UserDocItem[];
    storeUrl: string;
    downloadUrl: (id: number) => string;
    destroyUrl: (id: number) => string;
    canManage: boolean;
    maxFiles?: number;
    maxSizeBytes?: number;
};

const ACCEPT = {
    'application/pdf': ['.pdf'],
    'application/msword': ['.doc'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document': ['.docx'],
    'application/vnd.ms-excel': ['.xls'],
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': ['.xlsx'],
};

function formatBytes(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }
    if (bytes < 1024 * 1024) {
        return `${Math.round(bytes / 1024)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export default function UserDocsPanel({
    docs,
    storeUrl,
    downloadUrl,
    destroyUrl,
    canManage,
    maxFiles = 30,
    maxSizeBytes = 20 * 1024 * 1024,
}: Props) {
    const { t, i18n } = useTranslation();
    const [pendingDelete, setPendingDelete] = useState<UserDocItem | null>(null);
    const [uploading, setUploading] = useState(false);

    const remaining = useMemo(() => Math.max(0, maxFiles - docs.length), [docs.length, maxFiles]);

    function uploadFiles(files: File[]) {
        if (!canManage || files.length === 0) {
            return;
        }

        const batch = files.slice(0, remaining);
        if (batch.length === 0) {
            return;
        }

        const data = new FormData();
        batch.forEach((file) => data.append('docs[]', file));

        setUploading(true);
        router.post(storeUrl, data, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => setUploading(false),
        });
    }

    function confirmDelete() {
        if (pendingDelete === null) {
            return;
        }
        router.delete(destroyUrl(pendingDelete.id), { preserveScroll: true });
        setPendingDelete(null);
    }

    return (
        <div className="space-y-5">
            {canManage ? (
                <Dropzone
                    accept={ACCEPT}
                    maxSize={maxSizeBytes}
                    maxFiles={Math.min(10, Math.max(remaining, 1))}
                    multiple
                    disabled={uploading || remaining === 0}
                    onFiles={uploadFiles}
                    label={t('dropzone_arrastrar_docs')}
                    help={t('dropzone_ayuda_docs', {
                        max: maxFiles,
                        size: '20 MB',
                        remaining,
                    })}
                />
            ) : null}

            {docs.length === 0 ? (
                <p className="text-sm text-slate-500">{t('sin_documentos')}</p>
            ) : (
                <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    {docs.map((item) => {
                        const dateLabel = item.created_at
                            ? new Date(item.created_at).toLocaleDateString(i18n.language)
                            : null;

                        return (
                            <li
                                key={item.id}
                                className="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-slate-50/60"
                                data-testid={`user-doc-card-${item.id}`}
                            >
                                <div className="flex flex-1 flex-col gap-2 p-3">
                                    <div className="flex h-16 items-center justify-center rounded-md bg-white">
                                        <FileText className="h-8 w-8 text-teal-700" aria-hidden />
                                    </div>
                                    <div className="min-w-0">
                                        <p
                                            className="truncate text-sm font-medium text-slate-900"
                                            title={item.original_name}
                                        >
                                            {item.original_name}
                                        </p>
                                        <p className="mt-0.5 text-xs text-slate-500">
                                            {formatBytes(item.size_bytes)}
                                            {dateLabel ? ` · ${dateLabel}` : ''}
                                        </p>
                                    </div>
                                </div>
                                {canManage ? (
                                    <div className="flex items-center justify-end gap-1 border-t border-slate-200 p-2">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title={t('descargar')}
                                            asChild
                                        >
                                            <a href={downloadUrl(item.id)}>
                                                <Download className="h-4 w-4" />
                                                <span className="sr-only">{t('descargar')}</span>
                                            </a>
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            className="text-red-700 hover:text-red-800"
                                            title={t('eliminar')}
                                            onClick={() => setPendingDelete(item)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                            <span className="sr-only">{t('eliminar')}</span>
                                        </Button>
                                    </div>
                                ) : null}
                            </li>
                        );
                    })}
                </ul>
            )}

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t('eliminar_documento_confirmacion')}
                description={pendingDelete?.original_name}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </div>
    );
}
