import { Switch as UiSwitch } from '@/Components/ui/switch';
import { cn } from '@/lib/utils';

export type FormSwitchProps = {
    id?: string;
    checked?: boolean;
    onCheckedChange?: (checked: boolean) => void;
    disabled?: boolean;
    className?: string;
    label?: string;
};

export function Switch({
    id,
    checked = false,
    onCheckedChange,
    disabled,
    className,
    label,
}: FormSwitchProps) {
    return (
        <label
            htmlFor={id}
            className={cn('inline-flex items-center gap-2 text-sm text-slate-700', className)}
        >
            <UiSwitch
                id={id}
                checked={checked}
                disabled={disabled}
                onCheckedChange={(state) => onCheckedChange?.(state)}
            />
            {label ? <span>{label}</span> : null}
        </label>
    );
}
