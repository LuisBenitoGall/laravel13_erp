/**
 * Campos metadatos de rol (name, description, universal).
 * Pensado para reutilizar en roles de empresa (change posterior); la matriz de
 * permisos queda fuera — se atribuye en Edit.
 */
import { Field, Input, Switch, Textarea } from '@/Components/Form';
import { useTranslation } from 'react-i18next';

export type RoleMetadataValues = {
    name: string;
    description: string;
    universal: boolean;
};

export type RoleMetadataErrors = {
    name?: string;
    description?: string;
    universal?: string;
};

type Props = {
    values: RoleMetadataValues;
    errors: RoleMetadataErrors;
    nameReadonly?: boolean;
    universalReadonly?: boolean;
    onNameChange: (value: string) => void;
    onDescriptionChange: (value: string) => void;
    onUniversalChange: (value: boolean) => void;
};

export default function RoleMetadataFields({
    values,
    errors,
    nameReadonly = false,
    universalReadonly = false,
    onNameChange,
    onDescriptionChange,
    onUniversalChange,
}: Props) {
    const { t } = useTranslation();

    return (
        <div className="space-y-4">
            <div className="grid items-start gap-4 sm:grid-cols-2">
                <Field label={t('nombre')} htmlFor="role-name" error={errors.name} required>
                    <Input
                        id="role-name"
                        value={values.name}
                        disabled={nameReadonly}
                        onChange={(e) => onNameChange(e.target.value)}
                    />
                </Field>

                <Field label={t('rol_universal')} htmlFor="role-universal" error={errors.universal}>
                    {/* Bloque: evita que Label (inline) + Switch (inline-flex) queden en una sola línea. */}
                    <div className="flex min-h-9 items-center gap-3">
                        <Switch
                            id="role-universal"
                            checked={values.universal}
                            disabled={universalReadonly}
                            onCheckedChange={onUniversalChange}
                        />
                        <span className="text-sm leading-5 text-slate-500">
                            {t('rol_universal_ayuda')}
                        </span>
                    </div>
                </Field>
            </div>

            <Field label={t('descripcion')} htmlFor="role-description" error={errors.description}>
                <Textarea
                    id="role-description"
                    rows={3}
                    maxLength={2000}
                    value={values.description}
                    onChange={(e) => onDescriptionChange(e.target.value)}
                />
            </Field>
        </div>
    );
}
