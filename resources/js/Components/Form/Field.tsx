import type { ReactNode } from 'react';
import { Controller, useFormContext, type Control, type FieldPath, type FieldValues } from 'react-hook-form';

import HelpTip from '@/Components/HelpTip';
import { Label } from '@/Components/ui/label';
import { cn } from '@/lib/utils';

import { useFormHelpScope } from './FormHelpScope';

type FieldProps = {
    label: string;
    htmlFor?: string;
    error?: string;
    help?: string;
    /** Override explícito del code HelpTip. ≠ hint textual `help`. */
    helpCode?: string;
    /**
     * Clave de campo para auto-derive (`{helpScope}.{fieldKey}`).
     * Si falta: `name` → último segmento de `htmlFor` tras `-`.
     */
    fieldKey?: string;
    /** Identidad RHF / nombre lógico (prioridad sobre htmlFor para fieldKey). */
    name?: string;
    /**
     * Alineación vertical control ↔ HelpTip.
     * `center` para Switch/Checkbox (controles bajos); `start` (default) para Input/Textarea.
     */
    tipAlign?: 'start' | 'center';
    required?: boolean;
    className?: string;
    children: ReactNode;
};

function resolveFieldKey(fieldKey?: string, name?: string, htmlFor?: string): string | null {
    if (fieldKey && fieldKey.trim() !== '') {
        return fieldKey.trim();
    }

    if (name && name.trim() !== '') {
        return name.trim();
    }

    if (htmlFor && htmlFor.trim() !== '') {
        const parts = htmlFor.trim().split('-').filter(Boolean);

        return parts.length > 0 ? (parts[parts.length - 1] ?? null) : null;
    }

    return null;
}

function resolveHelpCode(
    helpCode: string | undefined,
    fieldKey: string | undefined,
    name: string | undefined,
    htmlFor: string | undefined,
    helpScope: string | null,
): string | null {
    if (helpCode && helpCode.trim() !== '') {
        return helpCode.trim();
    }

    if (!helpScope || helpScope.trim() === '') {
        return null;
    }

    const key = resolveFieldKey(fieldKey, name, htmlFor);
    if (!key) {
        return null;
    }

    return `${helpScope.trim()}.${key}`;
}

/** Presentational field shell (Inertia useForm or any controlled input). */
export function Field({
    label,
    htmlFor,
    error,
    help,
    helpCode,
    fieldKey,
    name,
    tipAlign = 'start',
    required,
    className,
    children,
}: FieldProps) {
    const helpScope = useFormHelpScope();
    const resolvedCode = resolveHelpCode(helpCode, fieldKey, name, htmlFor, helpScope);

    return (
        <div className={cn('space-y-1', className)}>
            <Label htmlFor={htmlFor} className="block">
                {label}
                {required ? <span className="text-red-600"> *</span> : null}
            </Label>
            <div
                className={cn(
                    'flex gap-2',
                    tipAlign === 'center' ? 'items-center' : 'items-start',
                )}
            >
                <div className={cn('min-w-0', tipAlign === 'center' ? 'w-auto' : 'flex-1')}>
                    {children}
                </div>
                {resolvedCode ? <HelpTip code={resolvedCode} /> : null}
            </div>
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
    helpCode?: string;
    fieldKey?: string;
    tipAlign?: 'start' | 'center';
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
    helpCode,
    fieldKey,
    tipAlign,
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
                    name={name}
                    error={fieldState.error?.message}
                    help={help}
                    helpCode={helpCode}
                    fieldKey={fieldKey}
                    tipAlign={tipAlign}
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
