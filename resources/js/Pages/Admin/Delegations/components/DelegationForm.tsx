import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Field, FormActions, Input, Select, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type GeoOption = { id: number; name: string };
type WorkplaceOption = { id: number; name: string };

type DelegationFormData = {
    name: string;
    tradename: string;
    nif: string;
    status: string;
    workplace_ids: number[];
    workplace: {
        name: string;
        address: string;
        cp: string;
        town_id: string;
    };
};

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    countries?: GeoOption[];
    matrixWorkplaces?: WorkplaceOption[];
    initial?: Partial<Pick<DelegationFormData, 'name' | 'tradename' | 'nif' | 'status'>>;
    /** Edit: toggle inmediato active/closed (mismo endpoint que el Index). */
    statusChecked?: boolean;
    onStatusToggle?: (checked: boolean) => Promise<void>;
};

async function fetchJson<T>(url: string): Promise<T> {
    const response = await fetch(url, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return response.json() as Promise<T>;
}

/** Alta/edición de Delegación (Company hija). Alta: centro nuevo y/o sedes existentes de la matriz. */
export default function DelegationForm({
    mode,
    submitUrl,
    method = 'post',
    countries = [],
    matrixWorkplaces = [],
    initial,
    statusChecked = false,
    onStatusToggle,
}: Props) {
    const { t } = useTranslation();
    const [countryId, setCountryId] = useState('');
    const [provinceId, setProvinceId] = useState('');
    const [provinces, setProvinces] = useState<GeoOption[]>([]);
    const [towns, setTowns] = useState<GeoOption[]>([]);
    const [statusBusy, setStatusBusy] = useState(false);

    const form = useForm<DelegationFormData>({
        name: initial?.name ?? '',
        tradename: initial?.tradename ?? '',
        nif: initial?.nif ?? '',
        status: initial?.status ?? 'active',
        workplace_ids: [],
        workplace: { name: '', address: '', cp: '', town_id: '' },
    });

    const statusOptions = [
        { value: 'draft', label: t('estado_borrador') },
        { value: 'active', label: t('estado_activa') },
        { value: 'closed', label: t('estado_cerrada') },
    ];

    const countryOptions = useMemo(
        () => countries.map((c) => ({ value: String(c.id), label: c.name })),
        [countries],
    );
    const provinceOptions = useMemo(
        () => provinces.map((p) => ({ value: String(p.id), label: p.name })),
        [provinces],
    );
    const townOptions = useMemo(
        () => towns.map((town) => ({ value: String(town.id), label: town.name })),
        [towns],
    );

    useEffect(() => {
        if (mode !== 'create' || !countryId) {
            setProvinces([]);
            return;
        }

        let cancelled = false;
        fetchJson<GeoOption[]>(`/admin/workplaces/geo/countries/${countryId}/provinces`)
            .then((rows) => {
                if (!cancelled) {
                    setProvinces(rows);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setProvinces([]);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [countryId, mode]);

    useEffect(() => {
        if (mode !== 'create' || !provinceId) {
            setTowns([]);
            return;
        }

        let cancelled = false;
        fetchJson<GeoOption[]>(`/admin/workplaces/geo/provinces/${provinceId}/towns`)
            .then((rows) => {
                if (!cancelled) {
                    setTowns(rows);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setTowns([]);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [mode, provinceId]);

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        const company = {
            name: form.data.name,
            tradename: form.data.tradename || null,
            nif: form.data.nif,
            status: form.data.status,
        };
        form.transform(() =>
            mode === 'create'
                ? {
                      ...company,
                      workplace_ids: form.data.workplace_ids,
                      workplace: {
                          name: form.data.workplace.name || null,
                          address: form.data.workplace.address || null,
                          cp: form.data.workplace.cp || null,
                          town_id: form.data.workplace.town_id
                              ? Number(form.data.workplace.town_id)
                              : null,
                      },
                  }
                : company,
        );

        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    const setWorkplace = (key: keyof DelegationFormData['workplace'], value: string) =>
        form.setData('workplace', { ...form.data.workplace, [key]: value });

    function toggleWorkplaceId(id: number, checked: boolean) {
        const next = checked
            ? [...new Set([...form.data.workplace_ids, id])]
            : form.data.workplace_ids.filter((value) => value !== id);
        form.setData('workplace_ids', next);
    }

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
            data-testid="delegation-form"
        >
            <Field label={t('razon_social')} htmlFor="delegation-name" error={form.errors.name} required>
                <Input
                    id="delegation-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <Field
                label={t('nombre_comercial')}
                htmlFor="delegation-tradename"
                error={form.errors.tradename}
            >
                <Input
                    id="delegation-tradename"
                    value={form.data.tradename}
                    maxLength={255}
                    onChange={(e) => form.setData('tradename', e.target.value)}
                />
            </Field>

            <Field label={t('nif')} htmlFor="delegation-nif" error={form.errors.nif} required>
                <Input
                    id="delegation-nif"
                    value={form.data.nif}
                    maxLength={12}
                    onChange={(e) => form.setData('nif', e.target.value)}
                />
            </Field>

            {mode === 'create' ? (
                <Field label={t('estado')} htmlFor="delegation-status" error={form.errors.status}>
                    <Select
                        id="delegation-status"
                        options={statusOptions}
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
            ) : (
                <div className="flex min-h-10 items-center">
                    <Switch
                        id="delegation-status-toggle"
                        label={t('activo')}
                        checked={statusChecked}
                        disabled={statusBusy || !onStatusToggle}
                        onCheckedChange={(checked) => {
                            if (!onStatusToggle) {
                                return;
                            }
                            setStatusBusy(true);
                            void onStatusToggle(checked).finally(() => setStatusBusy(false));
                        }}
                    />
                </div>
            )}

            {mode === 'create' ? (
                <>
                    <fieldset className="space-y-4 rounded-lg border border-slate-200 p-4">
                        <legend className="px-2 text-sm font-medium text-slate-700">
                            {t('centro_trabajo_empresa')}
                        </legend>
                        <p className="text-sm text-slate-600">{t('centro_trabajo_empresa_ayuda')}</p>

                        <Field
                            label={t('nombre')}
                            htmlFor="delegation-workplace-name"
                            error={form.errors['workplace.name']}
                        >
                            <Input
                                id="delegation-workplace-name"
                                value={form.data.workplace.name}
                                maxLength={255}
                                onChange={(e) => setWorkplace('name', e.target.value)}
                            />
                        </Field>

                        <div className="grid gap-4 md:grid-cols-4">
                            <Field
                                className="md:col-span-3"
                                label={t('direccion')}
                                htmlFor="delegation-workplace-address"
                                error={form.errors['workplace.address']}
                            >
                                <Input
                                    id="delegation-workplace-address"
                                    value={form.data.workplace.address}
                                    maxLength={255}
                                    onChange={(e) => setWorkplace('address', e.target.value)}
                                />
                            </Field>
                            <Field
                                label={t('codigo_postal')}
                                htmlFor="delegation-workplace-cp"
                                error={form.errors['workplace.cp']}
                            >
                                <Input
                                    id="delegation-workplace-cp"
                                    value={form.data.workplace.cp}
                                    maxLength={16}
                                    onChange={(e) => setWorkplace('cp', e.target.value)}
                                />
                            </Field>
                        </div>

                        <div className="grid gap-4 md:grid-cols-3">
                            <Field label={t('pais')} htmlFor="delegation-workplace-country">
                                <Select
                                    id="delegation-workplace-country"
                                    options={countryOptions}
                                    value={countryId}
                                    onChange={(value) => {
                                        setCountryId(value);
                                        setProvinceId('');
                                        setWorkplace('town_id', '');
                                    }}
                                    placeholder={t('elegir')}
                                />
                            </Field>
                            <Field label={t('provincia')} htmlFor="delegation-workplace-province">
                                <Select
                                    id="delegation-workplace-province"
                                    options={provinceOptions}
                                    value={provinceId}
                                    onChange={(value) => {
                                        setProvinceId(value);
                                        setWorkplace('town_id', '');
                                    }}
                                    placeholder={t('elegir')}
                                    disabled={!countryId}
                                />
                            </Field>
                            <Field
                                label={t('poblacion')}
                                htmlFor="delegation-workplace-town"
                                error={form.errors['workplace.town_id']}
                            >
                                <Select
                                    id="delegation-workplace-town"
                                    options={townOptions}
                                    value={form.data.workplace.town_id}
                                    onChange={(value) => setWorkplace('town_id', value)}
                                    placeholder={t('elegir')}
                                    disabled={!provinceId}
                                />
                            </Field>
                        </div>
                    </fieldset>

                    <fieldset className="space-y-3 rounded-lg border border-slate-200 p-4">
                        <legend className="px-2 text-sm font-medium text-slate-700">
                            {t('usar_centros_existentes')}
                        </legend>
                        <p className="text-sm text-slate-600">{t('usar_centros_existentes_ayuda')}</p>
                        <Field label={t('centros_de_trabajo')} error={form.errors.workplace_ids}>
                            {matrixWorkplaces.length === 0 ? (
                                <p className="text-sm text-slate-500">{t('sin_centros_de_trabajo')}</p>
                            ) : (
                                <ul className="grid gap-2 sm:grid-cols-2">
                                    {matrixWorkplaces.map((option) => (
                                        <li key={option.id}>
                                            <Checkbox
                                                id={`delegation-existing-workplace-${option.id}`}
                                                label={option.name}
                                                checked={form.data.workplace_ids.includes(option.id)}
                                                onCheckedChange={(value) =>
                                                    toggleWorkplaceId(option.id, value)
                                                }
                                            />
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Field>
                    </fieldset>
                </>
            ) : null}

            <FormActions>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
