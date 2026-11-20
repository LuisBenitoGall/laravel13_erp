import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';
import Cropper, { type Area } from 'react-easy-crop';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { FormActions } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { cn } from '@/lib/utils';

import {
    cropFileExtension,
    cropOutputMime,
    getCroppedImageBlob,
} from './cropImage';
import type { GalleryAsset } from './types';

type CropRatio = '1:1' | '4:3' | '16:9' | 'libre';

type Props = {
    asset: GalleryAsset | null;
    onClose: () => void;
};

const RATIOS: CropRatio[] = ['1:1', '4:3', '16:9', 'libre'];

const RATIO_LABEL_KEYS: Record<CropRatio, string> = {
    '1:1': 'galeria_ratio_1_1',
    '4:3': 'galeria_ratio_4_3',
    '16:9': 'galeria_ratio_16_9',
    libre: 'galeria_ratio_libre',
};

function aspectForRatio(ratio: CropRatio, asset: GalleryAsset): number {
    if (ratio === '1:1') {
        return 1;
    }
    if (ratio === '16:9') {
        return 16 / 9;
    }
    if (ratio === 'libre') {
        if (
            asset.width !== null &&
            asset.height !== null &&
            asset.width > 0 &&
            asset.height > 0
        ) {
            return asset.width / asset.height;
        }

        return 4 / 3;
    }

    return 4 / 3;
}

export default function CropDialog({ asset, onClose }: Props) {
    const { t } = useTranslation();
    const [crop, setCrop] = useState({ x: 0, y: 0 });
    const [zoom, setZoom] = useState(1);
    const [ratio, setRatio] = useState<CropRatio>('4:3');
    const [croppedAreaPixels, setCroppedAreaPixels] = useState<Area | null>(null);
    const [saving, setSaving] = useState(false);

    const onCropComplete = useCallback((_: Area, pixels: Area) => {
        setCroppedAreaPixels(pixels);
    }, []);

    function resetView() {
        setCrop({ x: 0, y: 0 });
        setZoom(1);
        setCroppedAreaPixels(null);
    }

    function selectRatio(next: CropRatio) {
        setRatio(next);
        resetView();
    }

    function handleOpenChange(open: boolean) {
        if (!open) {
            setRatio('4:3');
            resetView();
            setSaving(false);
            onClose();
        }
    }

    async function saveCopy() {
        if (asset === null || croppedAreaPixels === null || saving) {
            return;
        }

        setSaving(true);
        try {
            const mime = cropOutputMime(asset.mime);
            const blob = await getCroppedImageBlob(asset.url, croppedAreaPixels, mime);
            const ext = cropFileExtension(mime);
            const data = new FormData();
            data.append('image', blob, `crop.${ext}`);

            router.post(`/admin/gallery/${asset.id}/copies`, data, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => handleOpenChange(false),
                onFinish: () => setSaving(false),
            });
        } catch {
            toast.error(t('galeria_recortar_error'));
            setSaving(false);
        }
    }

    const aspect = asset !== null ? aspectForRatio(ratio, asset) : 4 / 3;

    return (
        <Dialog open={asset !== null} onOpenChange={handleOpenChange}>
            <DialogContent className="sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle>{t('galeria_recortar_titulo')}</DialogTitle>
                </DialogHeader>

                {asset !== null ? (
                    <div className="space-y-4">
                        <p className="text-sm text-slate-600">{t('galeria_recortar_ayuda')}</p>
                        <div className="relative h-72 w-full overflow-hidden rounded-md bg-slate-900">
                            <Cropper
                                image={asset.url}
                                crop={crop}
                                zoom={zoom}
                                aspect={aspect}
                                onCropChange={setCrop}
                                onZoomChange={setZoom}
                                onCropComplete={onCropComplete}
                            />
                        </div>
                        <div
                            className="flex flex-wrap gap-2"
                            role="group"
                            aria-label={t('galeria_recortar_ratios')}
                        >
                            {RATIOS.map((item) => (
                                <Button
                                    key={item}
                                    type="button"
                                    size="sm"
                                    variant={ratio === item ? 'default' : 'outline'}
                                    aria-pressed={ratio === item}
                                    data-testid={`gallery-crop-ratio-${item}`}
                                    className={cn(ratio === item && 'pointer-events-none')}
                                    onClick={() => selectRatio(item)}
                                >
                                    {t(RATIO_LABEL_KEYS[item])}
                                </Button>
                            ))}
                        </div>
                        <label className="flex items-center gap-3 text-sm text-slate-700">
                            <span className="shrink-0">{t('galeria_recortar_zoom')}</span>
                            <input
                                type="range"
                                min={1}
                                max={3}
                                step={0.05}
                                value={zoom}
                                onChange={(event) => setZoom(Number(event.target.value))}
                                className="w-full accent-slate-800"
                                aria-label={t('galeria_recortar_zoom')}
                            />
                        </label>
                    </div>
                ) : null}

                <DialogFooter>
                    <FormActions>
                        <Button type="button" variant="outline" onClick={() => handleOpenChange(false)}>
                            {t('cancelar')}
                        </Button>
                        <Button
                            type="button"
                            disabled={saving || croppedAreaPixels === null}
                            onClick={() => void saveCopy()}
                            data-testid="gallery-crop-save"
                        >
                            {t('galeria_guardar_copia')}
                        </Button>
                    </FormActions>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
