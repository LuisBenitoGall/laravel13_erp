import { router, useForm } from '@inertiajs/react';
import { Copy, Crop, Pencil, Trash2 } from 'lucide-react';
import { FormEvent, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Dropzone, Field, FormActions, Input } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import AppLayout from '@/Layouts/AppLayout';
import { cn } from '@/lib/utils';

import CropDialog from './CropDialog';
import type { GalleryAsset } from './types';

export type { GalleryAsset };

type Props = {
    assets: GalleryAsset[];
    maxKilobytes: number;
    maxPerUpload: number;
    can: { create: boolean; update: boolean; delete: boolean };
};

const ACCEPT = {
    'image/jpeg': ['.jpg', '.jpeg'],
    'image/png': ['.png'],
    'image/gif': ['.gif'],
    'image/webp': ['.webp'],
};

export default function Index({ assets, maxKilobytes, maxPerUpload, can }: Props) {
    const { t } = useTranslation();
    const [uploading, setUploading] = useState(false);
    const [pendingDelete, setPendingDelete] = useState<GalleryAsset | null>(null);
    const [editing, setEditing] = useState<GalleryAsset | null>(null);
    const [cropping, setCropping] = useState<GalleryAsset | null>(null);

    const editForm = useForm({
        name: '',
        alt: '',
    });

    function onFiles(files: File[]) {
        if (!can.create || files.length === 0) {
            return;
        }

        const batch = files.slice(0, maxPerUpload);
        if (batch.length === 0) {
            return;
        }

        setUploading(true);
        const data = new FormData();
        batch.forEach((file) => data.append('images[]', file));

        router.post('/admin/gallery', data, {
            forceFormData: true,
            preserveScroll: true,
            onFinish: () => setUploading(false),
        });
    }

    function openEdit(asset: GalleryAsset) {
        setEditing(asset);
        editForm.setData({ name: asset.name, alt: asset.alt });
        editForm.clearErrors();
    }

    function submitEdit(event: FormEvent) {
        event.preventDefault();
        if (editing === null) {
            return;
        }

        editForm.put(`/admin/gallery/${editing.id}`, {
            preserveScroll: true,
            onSuccess: () => setEditing(null),
        });
    }

    async function copyUrl(url: string) {
        try {
            await navigator.clipboard.writeText(url);
            toast.success(t('galeria_url_copiada'));
        } catch {
            toast.error(t('galeria_url_copiar_error'));
        }
    }

    function confirmDelete() {
        if (pendingDelete === null) {
            return;
        }
        router.delete(`/admin/gallery/${pendingDelete.id}`, { preserveScroll: true });
        setPendingDelete(null);
    }

    const maxSizeLabel =
        maxKilobytes >= 1024
            ? `${(maxKilobytes / 1024).toFixed(maxKilobytes % 1024 === 0 ? 0 : 1)} MB`
            : `${maxKilobytes} KB`;

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'galeria',
                optionHref: '/admin/gallery',
            }}
            showBack={false}
            actions={[]}
        >
            <div className="space-y-6">
                {can.create ? (
                    <Dropzone
                        accept={ACCEPT}
                        maxSize={maxKilobytes * 1024}
                        maxFiles={maxPerUpload}
                        multiple
                        disabled={uploading}
                        onFiles={onFiles}
                        label={t('galeria_dropzone_label')}
                        help={t('galeria_dropzone_ayuda', {
                            size: maxSizeLabel,
                            max: maxPerUpload,
                        })}
                    />
                ) : null}

                {assets.length === 0 ? (
                    <p className="text-sm text-slate-500">{t('galeria_vacia')}</p>
                ) : (
                    <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                        {assets.map((asset) => (
                            <li
                                key={asset.id}
                                className={cn(
                                    'group overflow-hidden rounded-lg border border-slate-200 bg-slate-50/60',
                                )}
                                data-testid={`gallery-card-${asset.id}`}
                            >
                                <div className="aspect-square overflow-hidden bg-slate-100">
                                    <img
                                        src={asset.url}
                                        alt={asset.alt || asset.name}
                                        className="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <div className="space-y-2 border-t border-slate-200 p-2">
                                    <p className="truncate text-xs font-medium text-slate-800" title={asset.name}>
                                        {asset.name}
                                    </p>
                                    <div className="flex flex-wrap gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title={t('galeria_copiar_url')}
                                            onClick={() => void copyUrl(asset.url)}
                                        >
                                            <Copy className="h-4 w-4" />
                                        </Button>
                                        {can.create ? (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                title={t('galeria_recortar')}
                                                data-testid={`gallery-crop-${asset.id}`}
                                                onClick={() => setCropping(asset)}
                                            >
                                                <Crop className="h-4 w-4" />
                                            </Button>
                                        ) : null}
                                        {can.update ? (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                title={t('editar')}
                                                onClick={() => openEdit(asset)}
                                            >
                                                <Pencil className="h-4 w-4" />
                                            </Button>
                                        ) : null}
                                        {can.delete ? (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon-sm"
                                                className="text-red-700 hover:text-red-800"
                                                title={t('eliminar')}
                                                onClick={() => setPendingDelete(asset)}
                                            >
                                                <Trash2 className="h-4 w-4" />
                                            </Button>
                                        ) : null}
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>

            <Dialog
                open={editing !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setEditing(null);
                    }
                }}
            >
                <DialogContent className="sm:max-w-md">
                    <DialogHeader>
                        <DialogTitle>{t('galeria_editar')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-4">
                        <Field label={t('nombre')} htmlFor="gallery-name" error={editForm.errors.name}>
                            <Input
                                id="gallery-name"
                                value={editForm.data.name}
                                onChange={(e) => editForm.setData('name', e.target.value)}
                                maxLength={150}
                            />
                        </Field>
                        <Field label={t('galeria_alt')} htmlFor="gallery-alt" error={editForm.errors.alt}>
                            <Input
                                id="gallery-alt"
                                value={editForm.data.alt}
                                onChange={(e) => editForm.setData('alt', e.target.value)}
                                maxLength={255}
                            />
                        </Field>
                        <DialogFooter>
                            <FormActions>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setEditing(null)}
                                >
                                    {t('cancelar')}
                                </Button>
                                <Button type="submit" disabled={editForm.processing}>
                                    {t('guardar')}
                                </Button>
                            </FormActions>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <CropDialog asset={cropping} onClose={() => setCropping(null)} />

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t('eliminar')}
                description={t('eliminar_imagen_confirmacion')}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </AppLayout>
    );
}
