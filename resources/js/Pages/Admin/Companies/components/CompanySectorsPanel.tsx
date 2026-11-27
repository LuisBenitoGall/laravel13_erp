import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Field, FormActions } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

export type SectorOption = {
    id: number;
    name: string;
};

type Props = {
    companyId: number;
    sectors: SectorOption[];
    sectorOptions: SectorOption[];
    canUpdate?: boolean;
};

export default function CompanySectorsPanel({
    companyId,
    sectors,
    sectorOptions,
    canUpdate = true,
}: Props) {
    const { t } = useTranslation();
    const form = useForm<{ sector_ids: number[] }>({
        sector_ids: sectors.map((sector) => sector.id),
    });

    function toggle(id: number, checked: boolean) {
        if (!canUpdate) {
            return;
        }

        const next = checked
            ? [...new Set([...form.data.sector_ids, id])]
            : form.data.sector_ids.filter((value) => value !== id);
        form.setData('sector_ids', next);
    }

    function onSubmit(event: FormEvent) {
        event.preventDefault();
        if (!canUpdate) {
            return;
        }

        form.put(`/companies/${companyId}/sectors`, {
            preserveScroll: true,
        });
    }

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
        >
            <div>
                <h2 className="text-sm font-semibold text-slate-900">{t('sectores_empresa')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('sectores_empresa_ayuda')}</p>
            </div>

            <Field label={t('sectores')} error={form.errors.sector_ids}>
                {sectorOptions.length === 0 ? (
                    <p className="text-sm text-slate-500">{t('sectores_sin_opciones')}</p>
                ) : (
                    <ul className="grid gap-2 sm:grid-cols-2">
                        {sectorOptions.map((option) => {
                            const checked = form.data.sector_ids.includes(option.id);

                            return (
                                <li key={option.id}>
                                    <Checkbox
                                        id={`company-sector-${option.id}`}
                                        label={option.name}
                                        checked={checked}
                                        disabled={!canUpdate}
                                        onCheckedChange={(value) => toggle(option.id, value)}
                                    />
                                </li>
                            );
                        })}
                    </ul>
                )}
            </Field>

            {canUpdate ? (
                <FormActions>
                    <Button type="submit" disabled={form.processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            ) : null}
        </form>
    );
}
