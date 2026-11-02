import { createElement, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field } from '@/Components/Form/Field';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { ERP_ICON_NAMES, resolveMenuIcon } from '@/lib/menuIcons';
import { cn } from '@/lib/utils';

export type IconPickerProps = {
    id?: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
};

export function IconPicker({ id, label, value, onChange, error, required }: IconPickerProps) {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);

    return (
        <Field label={label} htmlFor={id} error={error} required={required}>
            <button
                id={id}
                type="button"
                className="flex h-10 w-full items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800"
                onClick={() => setOpen(true)}
            >
                {createElement(resolveMenuIcon(value), {
                    size: 16,
                    strokeWidth: 2,
                    'aria-hidden': true,
                })}
                <span>{value || t('seleccionar_icono')}</span>
            </button>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('seleccionar_icono')}</DialogTitle>
                    </DialogHeader>
                    <div className="grid max-h-80 grid-cols-6 gap-2 overflow-y-auto">
                        {ERP_ICON_NAMES.map((name) => {
                            const selected = name === value;

                            return (
                                <button
                                    key={name}
                                    type="button"
                                    title={name}
                                    aria-label={name}
                                    aria-pressed={selected}
                                    className={cn(
                                        'flex h-10 items-center justify-center rounded-md border text-slate-700',
                                        selected
                                            ? 'border-teal-700 bg-teal-50 text-teal-800'
                                            : 'border-slate-200 hover:bg-slate-50',
                                    )}
                                    onClick={() => {
                                        onChange(name);
                                        setOpen(false);
                                    }}
                                >
                                    {createElement(resolveMenuIcon(name), {
                                        size: 18,
                                        strokeWidth: 2,
                                        'aria-hidden': true,
                                    })}
                                </button>
                            );
                        })}
                    </div>
                </DialogContent>
            </Dialog>
        </Field>
    );
}
