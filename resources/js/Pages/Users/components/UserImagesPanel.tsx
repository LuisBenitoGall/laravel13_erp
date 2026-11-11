import { Eye, EyeOff, Star, Trash2, X } from 'lucide-react';
import { useMemo, useState } from 'react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Dropzone } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/Components/ui/dialog';
import { cn } from '@/lib/utils';

export type UserImageItem = {
    id: number;
    url: string;
    featured: boolean;
    public: boolean;
    publicly_visible: boolean;
};

type Props = {
    images: UserImageItem[];
    storeUrl: string;
    destroyUrl: (id: number) => string;
    markFeaturedUrl: (id: number) => string;
    updatePublicUrl: (id: number) => string;
    canManage: boolean;
    maxFiles?: number;
    maxSizeBytes?: number;
};

const ACCEPT = {
    'image/jpeg': ['.jpg', '.jpeg'],
    'image/png': ['.png'],
    'image/gif': ['.gif'],
    'image/webp': ['.webp'],
};

export default function UserImagesPanel({
    images,
    storeUrl,
    destroyUrl,
    markFeaturedUrl,
    updatePublicUrl,
    canManage,
    maxFiles = 20,
    maxSizeBytes = 1024 * 1024,
}: Props) {
    const { t } = useTranslation();
    const [pendingDelete, setPendingDelete] = useState<UserImageItem | null>(null);
    const [lightbox, setLightbox] = useState<UserImageItem | null>(null);
    const [uploading, setUploading] = useState(false);

    const remaining = useMemo(() => Math.max(0, maxFiles - images.length), [images.length, maxFiles]);

    function uploadFiles(files: File[]) {
        if (!canManage || files.length === 0) {
            return;
        }

        const batch = files.slice(0, remaining);
        if (batch.length === 0) {
            return;
        }

        const data = new FormData();
        batch.forEach((file) => data.append('images[]', file));

        setUploading(true);
        router.post(storeUrl, data, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => setUploading(false),
        });
    }

    function markFeatured(image: UserImageItem) {
        if (image.featured) {
            return;
        }
        router.post(markFeaturedUrl(image.id), {}, { preserveScroll: true });
    }

    function togglePublic(image: UserImageItem) {
        router.patch(
            updatePublicUrl(image.id),
            { public: !image.public },
            { preserveScroll: true },
        );
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
                    maxFiles={Math.min(maxFiles, Math.max(remaining, 1))}
                    multiple
                    disabled={uploading || remaining === 0}
                    onFiles={uploadFiles}
                    label={t('dropzone_arrastrar_imagenes')}
                    help={t('dropzone_ayuda_imagenes', {
                        max: maxFiles,
                        size: '1 MB',
                        remaining,
                    })}
                />
            ) : null}

            {images.length === 0 ? (
                <p className="text-sm text-slate-500">{t('sin_imagenes')}</p>
            ) : (
                <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    {images.map((item) => (
                        <li
                            key={item.id}
                            className={cn(
                                'group relative overflow-hidden rounded-lg border bg-slate-50/60',
                                item.featured ? 'border-teal-600' : 'border-slate-200',
                            )}
                            data-testid={`user-image-card-${item.id}`}
                        >
                            <button
                                type="button"
                                className="block aspect-square w-full overflow-hidden bg-slate-100"
                                onClick={() => setLightbox(item)}
                            >
                                <img
                                    src={item.url}
                                    alt=""
                                    className="h-full w-full object-cover"
                                    loading="lazy"
                                />
                            </button>
                            <div className="flex items-center justify-between gap-1 border-t border-slate-200 p-2">
                                <div className="flex gap-1">
                                    {canManage ? (
                                        <>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                disabled={item.featured}
                                                title={t('marcar_featured')}
                                                onClick={() => markFeatured(item)}
                                            >
                                                <Star
                                                    className={cn(
                                                        'h-4 w-4',
                                                        item.featured && 'fill-amber-400 text-amber-500',
                                                    )}
                                                />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                title={
                                                    item.public
                                                        ? t('imagen_hacer_privada')
                                                        : t('imagen_hacer_publica')
                                                }
                                                onClick={() => togglePublic(item)}
                                            >
                                                {item.public ? (
                                                    <Eye className="h-4 w-4 text-teal-700" />
                                                ) : (
                                                    <EyeOff className="h-4 w-4 text-slate-400" />
                                                )}
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
                                            </Button>
                                        </>
                                    ) : null}
                                </div>
                                {item.featured ? (
                                    <span className="text-[10px] font-semibold uppercase tracking-wide text-teal-700">
                                        {t('badge_featured')}
                                    </span>
                                ) : null}
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <Dialog
                open={lightbox !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setLightbox(null);
                    }
                }}
            >
                <DialogContent className="max-w-3xl border-0 bg-transparent p-0 shadow-none">
                    <DialogTitle className="sr-only">{t('imagenes')}</DialogTitle>
                    {lightbox ? (
                        <div className="relative">
                            <img
                                src={lightbox.url}
                                alt=""
                                className="max-h-[80vh] w-full rounded-lg object-contain"
                            />
                            <Button
                                type="button"
                                variant="secondary"
                                size="icon-sm"
                                className="absolute right-2 top-2"
                                onClick={() => setLightbox(null)}
                            >
                                <X className="h-4 w-4" />
                            </Button>
                        </div>
                    ) : null}
                </DialogContent>
            </Dialog>

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t('eliminar_imagen_confirmacion')}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </div>
    );
}
