import { useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Field, FormActions, RadioGroup } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

export type DelegationOption = {
    id: number;
    name: string;
};

export type WorkplaceDelegationMap = {
    options: DelegationOption[];
    linked_ids: number[];
    preferred_id: number | null;
};

type Props = {
    workplaceId: number;
    map: WorkplaceDelegationMap;
};

const NO_PREFERRED = 'none';

/** Mapa N:M sede de la matriz ↔ Delegaciones hijas + preferida (≤1). Replace del set al guardar. */
export default function WorkplaceDelegationsPanel({ workplaceId, map }: Props) {
    const { t } = useTranslation();
    const form = useForm<{ delegation_ids: number[]; preferred_delegation_id: number | null }>({
        delegation_ids: map.linked_ids,
        preferred_delegation_id: map.preferred_id,
    });

    const preferredOptions = useMemo(
        () => [
            { value: NO_PREFERRED, label: t('sin_delegacion_preferida') },
            ...map.options
                .filter((option) => form.data.delegation_ids.includes(option.id))
                .map((option) => ({ value: String(option.id), label: option.name })),
        ],
        [map.options, form.data.delegation_ids, t],
    );

    function toggle(id: number, checked: boolean) {
        const next = checked
            ? [...new Set([...form.data.delegation_ids, id])]
            : form.data.delegation_ids.filter((value) => value !== id);
        form.setData({
            delegation_ids: next,
            preferred_delegation_id:
                form.data.preferred_delegation_id !== null &&
                next.includes(form.data.preferred_delegation_id)
                    ? form.data.preferred_delegation_id
                    : null,
        });
    }

    function onSubmit(event: FormEvent) {
        event.preventDefault();
        form.put(`/companies/workplaces/${workplaceId}/delegations`, { preserveScroll: true });
    }

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-testid="workplace-delegations-panel"
        >
            <div>
                <h2 className="text-sm font-semibold text-slate-900">{t('delegaciones_sede')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('delegaciones_sede_ayuda')}</p>
            </div>

            <Field label={t('delegaciones')} error={form.errors.delegation_ids}>
                {map.options.length === 0 ? (
                    <p className="text-sm text-slate-500">{t('delegaciones_sin_opciones')}</p>
                ) : (
                    <ul className="grid gap-2 sm:grid-cols-2">
                        {map.options.map((option) => (
                            <li key={option.id}>
                                <Checkbox
                                    id={`workplace-delegation-${option.id}`}
                                    label={option.name}
                                    checked={form.data.delegation_ids.includes(option.id)}
                                    onCheckedChange={(value) => toggle(option.id, value)}
                                />
                            </li>
                        ))}
                    </ul>
                )}
            </Field>

            {map.options.length > 0 ? (
                <Field
                    label={t('delegacion_preferida')}
                    error={form.errors.preferred_delegation_id}
                >
                    <RadioGroup
                        id="workplace-delegation-preferred"
                        options={preferredOptions}
                        value={
                            form.data.preferred_delegation_id !== null
                                ? String(form.data.preferred_delegation_id)
                                : NO_PREFERRED
                        }
                        onChange={(value) =>
                            form.setData(
                                'preferred_delegation_id',
                                value === NO_PREFERRED ? null : Number(value),
                            )
                        }
                    />
                </Field>
            ) : null}

            {map.options.length > 0 ? (
                <FormActions>
                    <Button type="submit" disabled={form.processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            ) : null}
        </form>
    );
}
