import * as React from 'react';

import { Checkbox as UiCheckbox } from '@/Components/ui/checkbox';
import { cn } from '@/lib/utils';

export type FormCheckboxProps = {
    id?: string;
    checked?: boolean;
    onCheckedChange?: (checked: boolean) => void;
    disabled?: boolean;
    className?: string;
    label?: string;
};

export function Checkbox({
    id,
    checked = false,
    onCheckedChange,
    disabled,
    className,
    label,
}: FormCheckboxProps) {
    return (
        <label
            htmlFor={id}
            className={cn('inline-flex items-center gap-2 text-sm text-slate-700', className)}
        >
            <UiCheckbox
                id={id}
                checked={checked}
                disabled={disabled}
                onCheckedChange={(state) => onCheckedChange?.(state === true)}
            />
            {label ? <span>{label}</span> : null}
        </label>
    );
}

/** Alias for RHF Controller consumers that need a ref-friendly API. */
export const CheckboxControl = React.forwardRef<
    React.ElementRef<typeof UiCheckbox>,
    React.ComponentPropsWithoutRef<typeof UiCheckbox>
>((props, ref) => <UiCheckbox ref={ref} {...props} />);
CheckboxControl.displayName = 'CheckboxControl';
