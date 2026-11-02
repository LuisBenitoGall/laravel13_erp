import { Field } from '@/Components/Form/Field';
import { Input } from '@/Components/Form/Input';

export type ColorPickerProps = {
    id?: string;
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    required?: boolean;
};

function toColorInput(value: string): string {
    return /^#[0-9a-fA-F]{6}$/.test(value) ? value : '#000000';
}

export function ColorPicker({ id, label, value, onChange, error, required }: ColorPickerProps) {
    return (
        <Field label={label} htmlFor={id} error={error} required={required}>
            <div className="flex items-center gap-2">
                <input
                    id={id}
                    type="color"
                    value={toColorInput(value)}
                    onChange={(event) => onChange(event.target.value)}
                    className="h-10 w-12 cursor-pointer rounded-md border border-slate-300 bg-white p-1"
                    aria-label={label}
                />
                <Input
                    value={value}
                    maxLength={7}
                    onChange={(event) => onChange(event.target.value)}
                    className="font-mono"
                    aria-label={label}
                />
            </div>
        </Field>
    );
}
