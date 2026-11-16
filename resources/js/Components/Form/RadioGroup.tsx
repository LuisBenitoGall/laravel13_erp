import { RadioGroup as UiRadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

export type RadioGroupOption = {
    value: string;
    label: string;
    id?: string;
};

export type RadioGroupProps = {
    id?: string;
    name?: string;
    value?: string;
    onChange: (value: string) => void;
    options: RadioGroupOption[];
    disabled?: boolean;
    className?: string;
    'aria-invalid'?: boolean;
};

export function RadioGroup({
    id,
    name,
    value,
    onChange,
    options,
    disabled = false,
    className,
    'aria-invalid': ariaInvalid,
}: RadioGroupProps) {
    return (
        <UiRadioGroup
            id={id}
            name={name}
            value={value || undefined}
            onValueChange={onChange}
            disabled={disabled}
            aria-invalid={ariaInvalid || undefined}
            className={cn(
                'flex flex-row flex-wrap items-center gap-x-8 gap-y-3',
                className,
            )}
        >
            {options.map((option) => {
                const optionId = option.id ?? (id ? `${id}-${option.value}` : `radio-${option.value}`);

                return (
                    <div key={option.value} className="flex items-center gap-2">
                        <RadioGroupItem value={option.value} id={optionId} disabled={disabled} />
                        <Label htmlFor={optionId} className="cursor-pointer font-normal text-slate-700">
                            {option.label}
                        </Label>
                    </div>
                );
            })}
        </UiRadioGroup>
    );
}
