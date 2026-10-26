import type { ReactNode } from 'react';
import { Controller, useFormContext, type Control, type FieldPath, type FieldValues } from 'react-hook-form';

import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

type FieldProps = {
    label: string;
    htmlFor?: string;
    error?: string;
    help?: string;
    required?: boolean;
    className?: string;
    children: ReactNode;
};

/** Presentational field shell (Inertia useForm or any controlled input). */
export function Field({ label, htmlFor, error, help, required, className, children }: FieldProps) {
    return (
        <div className={cn('space-y-1', className)}>
            <Label htmlFor={htmlFor}>
                {label}
                {required ? <span className="text-red-600"> *</span> : null}
            </Label>
            {children}
            {help && !error ? <p className="text-xs text-slate-500">{help}</p> : null}
            {error ? <p className="text-sm text-red-600">{error}</p> : null}
        </div>
    );
}

type FormFieldProps<T extends FieldValues> = {
    name: FieldPath<T>;
    control?: Control<T>;
    label: string;
    help?: string;
    required?: boolean;
    className?: string;
    children: (args: {
        field: {
            name: string;
            value: unknown;
            onChange: (...event: unknown[]) => void;
            onBlur: () => void;
            ref: (instance: HTMLElement | null) => void;
        };
        error?: string;
    }) => ReactNode;
};

/** RHF wrapper: label/error homogéneos vía Field + Controller. */
export function FormField<T extends FieldValues>({
    name,
    control,
    label,
    help,
    required,
    className,
    children,
}: FormFieldProps<T>) {
    const ctx = useFormContext<T>();
    const resolvedControl = control ?? ctx.control;

    return (
        <Controller
            name={name}
            control={resolvedControl}
            render={({ field, fieldState }) => (
                <Field
                    label={label}
                    htmlFor={name}
                    error={fieldState.error?.message}
                    help={help}
                    required={required}
                    className={className}
                >
                    {children({
                        field: {
                            name: field.name,
                            value: field.value,
                            onChange: field.onChange,
                            onBlur: field.onBlur,
                            ref: field.ref as (instance: HTMLElement | null) => void,
                        },
                        error: fieldState.error?.message,
                    })}
                </Field>
            )}
        />
    );
}
