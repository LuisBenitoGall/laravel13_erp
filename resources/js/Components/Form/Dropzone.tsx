import { Upload } from 'lucide-react';
import { useCallback } from 'react';
import { useDropzone, type Accept, type FileRejection } from 'react-dropzone';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { cn } from '@/lib/utils';

export type DropzoneProps = {
    accept?: Accept;
    maxSize?: number;
    maxFiles?: number;
    multiple?: boolean;
    disabled?: boolean;
    onFiles: (files: File[]) => void;
    className?: string;
    label?: string;
    help?: string;
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

export function Dropzone({
    accept,
    maxSize,
    maxFiles,
    multiple = true,
    disabled = false,
    onFiles,
    className,
    label,
    help,
}: DropzoneProps) {
    const { t } = useTranslation();

    const onDrop = useCallback(
        (accepted: File[], rejected: FileRejection[]) => {
            for (const rejection of rejected) {
                const codes = rejection.errors.map((e) => e.code);
                if (codes.includes('file-too-large')) {
                    toast.error(
                        t('dropzone_error_tamano', {
                            name: rejection.file.name,
                            max: maxSize ? formatBytes(maxSize) : '',
                        }),
                    );
                } else if (codes.includes('file-invalid-type')) {
                    toast.error(t('dropzone_error_tipo', { name: rejection.file.name }));
                } else if (codes.includes('too-many-files')) {
                    toast.error(t('dropzone_error_cantidad', { max: maxFiles ?? 0 }));
                } else {
                    toast.error(t('dropzone_error_generico', { name: rejection.file.name }));
                }
            }

            if (accepted.length > 0) {
                onFiles(accepted);
            }
        },
        [maxFiles, maxSize, onFiles, t],
    );

    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        accept,
        maxSize,
        maxFiles,
        multiple,
        disabled,
        onDrop,
    });

    return (
        <div
            {...getRootProps()}
            className={cn(
                'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center transition-colors',
                isDragActive && 'border-teal-600 bg-teal-50',
                disabled && 'cursor-not-allowed opacity-60',
                className,
            )}
            data-testid="form-dropzone"
        >
            <input {...getInputProps()} />
            <Upload className="mb-2 h-8 w-8 text-slate-400" aria-hidden />
            <p className="text-sm font-medium text-slate-800">
                {label ?? t('dropzone_arrastrar')}
            </p>
            {help ? <p className="mt-1 text-xs text-slate-500">{help}</p> : null}
        </div>
    );
}
