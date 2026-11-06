/**
 * Campos metadatos de rol (name, description, universal opcional).
 * Company Create: `showUniversal={false}` (sin mención de universal).
 */
import { Field, Input, Switch, Textarea } from '@/Components/Form';
import { useTranslation } from 'react-i18next';

export type RoleMetadataValues = {
    name: string;
    description: string;
    universal?: boolean;
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
    /** Plataforma: true (default). Tenant Create: false — oculta el bloque entero. */
    showUniversal?: boolean;
    onNameChange: (value: string) => void;
    onDescriptionChange: (value: string) => void;
    onUniversalChange?: (value: boolean) => void;
};

export default function RoleMetadataFields({
    values,
    errors,
    nameReadonly = false,
    universalReadonly = false,
    showUniversal = true,
    onNameChange,
    onDescriptionChange,
    onUniversalChange,
}: Props) {
    const { t } = useTranslation();

    return (
        <div className="space-y-4">
            <div
                className={
                    showUniversal
                        ? 'grid items-start gap-4 sm:grid-cols-2'
                        : 'grid items-start gap-4'
                }
            >
                <Field label={t('nombre')} htmlFor="role-name" error={errors.name} required>
                    <Input
                        id="role-name"
                        value={values.name}
                        disabled={nameReadonly}
                        onChange={(e) => onNameChange(e.target.value)}
                    />
                </Field>

                {showUniversal ? (
                    <Field label={t('rol_universal')} htmlFor="role-universal" error={errors.universal}>
                        <div className="flex min-h-9 items-center gap-3">
                            <Switch
                                id="role-universal"
                                checked={Boolean(values.universal)}
                                disabled={universalReadonly}
                                onCheckedChange={(checked) => onUniversalChange?.(checked)}
                            />
                            <span className="text-sm leading-5 text-slate-500">
                                {t('rol_universal_ayuda')}
                            </span>
                        </div>
                    </Field>
                ) : null}
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
