import type { Area } from 'react-easy-crop';

function loadImage(src: string): Promise<HTMLImageElement> {
    return new Promise((resolve, reject) => {
        const image = new Image();
        image.addEventListener('load', () => resolve(image));
        image.addEventListener('error', (error) => reject(error));
        image.crossOrigin = 'anonymous';
        image.src = src;
    });
}

export type CropOutputMime = 'image/jpeg' | 'image/png' | 'image/webp';

export function cropOutputMime(sourceMime: string | null): CropOutputMime {
    if (sourceMime === 'image/png') {
        return 'image/png';
    }
    if (sourceMime === 'image/webp') {
        return 'image/webp';
    }

    return 'image/jpeg';
}

export function cropFileExtension(mime: CropOutputMime): string {
    if (mime === 'image/png') {
        return 'png';
    }
    if (mime === 'image/webp') {
        return 'webp';
    }

    return 'jpg';
}

export async function getCroppedImageBlob(
    imageSrc: string,
    pixelCrop: Area,
    mime: CropOutputMime = 'image/jpeg',
    quality = 0.92,
): Promise<Blob> {
    const image = await loadImage(imageSrc);
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    if (ctx === null) {
        throw new Error('Canvas 2D context unavailable');
    }

    canvas.width = pixelCrop.width;
    canvas.height = pixelCrop.height;

    ctx.drawImage(
        image,
        pixelCrop.x,
        pixelCrop.y,
        pixelCrop.width,
        pixelCrop.height,
        0,
        0,
        pixelCrop.width,
        pixelCrop.height,
    );

    return new Promise((resolve, reject) => {
        canvas.toBlob(
            (blob) => {
                if (blob === null) {
                    reject(new Error('Canvas toBlob failed'));
                    return;
                }
                resolve(blob);
            },
            mime,
            quality,
        );
    });
}
