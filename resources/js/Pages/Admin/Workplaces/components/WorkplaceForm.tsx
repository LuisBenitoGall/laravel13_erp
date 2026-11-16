import { router, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, Select, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type GeoOption = { id: number; name: string };

type WorkplaceFormData = {
    name: string;
    address: string;
    cp: string;
    town_id: string;
    featured: boolean;
    status: string;
};

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    countries: GeoOption[];
    /** Create: true si no hay centros Active; false si ya hay ≥1 Active. */
    defaultFeatured?: boolean;
    /** Tras crear, redirect al hub (p. ej. `/admin/company-settings`). */
    returnTo?: string | null;
    initial?: Partial<{
        name: string;
        address: string | null;
        cp: string | null;
        town_id: number | null;
        country_id: number | null;
        province_id: number | null;
        featured: boolean;
        status: string;
    }>;
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

export default function WorkplaceForm({
    mode,
    submitUrl,
    method = 'post',
    countries,
    defaultFeatured = true,
    returnTo = null,
    initial,
}: Props) {
    const { t } = useTranslation();
    const [countryId, setCountryId] = useState(initial?.country_id ? String(initial.country_id) : '');
    const [provinceId, setProvinceId] = useState(
        initial?.province_id ? String(initial.province_id) : '',
    );
    const [provinces, setProvinces] = useState<GeoOption[]>([]);
    const [towns, setTowns] = useState<GeoOption[]>([]);

    const form = useForm<WorkplaceFormData>({
        name: initial?.name ?? '',
        address: initial?.address ?? '',
        cp: initial?.cp ?? '',
        town_id: initial?.town_id ? String(initial.town_id) : '',
        featured: initial?.featured ?? (mode === 'create' ? defaultFeatured : false),
        status: initial?.status ?? 'active',
    });

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
    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    useEffect(() => {
        if (!countryId) {
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
    }, [countryId]);

    useEffect(() => {
        if (!provinceId) {
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
    }, [provinceId]);

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        const payload = {
            name: form.data.name,
            address: form.data.address || null,
            cp: form.data.cp || null,
            town_id: form.data.town_id ? Number(form.data.town_id) : null,
            featured: form.data.featured,
            status: form.data.status,
            ...(mode === 'create' && returnTo ? { return_to: returnTo } : {}),
        };
        form.transform(() => payload);
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
        >
            <Field label={t('nombre')} htmlFor="workplace-name" error={form.errors.name} required>
                <Input
                    id="workplace-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <div className="grid gap-4 md:grid-cols-4">
                <Field
                    className="md:col-span-3"
                    label={t('direccion')}
                    htmlFor="workplace-address"
                    error={form.errors.address}
                >
                    <Input
                        id="workplace-address"
                        value={form.data.address}
                        maxLength={255}
                        onChange={(e) => form.setData('address', e.target.value)}
                    />
                </Field>
                <Field label={t('codigo_postal')} htmlFor="workplace-cp" error={form.errors.cp}>
                    <Input
                        id="workplace-cp"
                        value={form.data.cp}
                        maxLength={16}
                        onChange={(e) => form.setData('cp', e.target.value)}
                    />
                </Field>
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <Field label={t('pais')} htmlFor="workplace-country">
                    <Select
                        id="workplace-country"
                        options={countryOptions}
                        value={countryId}
                        onChange={(value) => {
                            setCountryId(value);
                            setProvinceId('');
                            form.setData('town_id', '');
                        }}
                        placeholder={t('elegir')}
                    />
                </Field>
                <Field label={t('provincia')} htmlFor="workplace-province">
                    <Select
                        id="workplace-province"
                        options={provinceOptions}
                        value={provinceId}
                        onChange={(value) => {
                            setProvinceId(value);
                            form.setData('town_id', '');
                        }}
                        placeholder={t('elegir')}
                        disabled={!countryId}
                    />
                </Field>
                <Field
                    label={t('poblacion')}
                    htmlFor="workplace-town"
                    error={form.errors.town_id}
                >
                    <Select
                        id="workplace-town"
                        options={townOptions}
                        value={form.data.town_id}
                        onChange={(value) => form.setData('town_id', value)}
                        placeholder={t('elegir')}
                        disabled={!provinceId}
                    />
                </Field>
            </div>

            <div className="grid gap-4 md:grid-cols-4 md:items-end">
                <Field label={t('estado')} htmlFor="workplace-status" error={form.errors.status}>
                    <Select
                        id="workplace-status"
                        options={statusOptions}
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
                <div className="flex min-h-10 items-center md:col-span-3">
                    <Switch
                        id="workplace-featured"
                        label={t('principal')}
                        checked={form.data.featured}
                        onCheckedChange={(checked) => form.setData('featured', checked)}
                    />
                </div>
            </div>

            <FormActions>
                <Button
                    type="button"
                    variant="secondary"
                    onClick={() => router.visit('/admin/workplaces')}
                >
                    {t('cancelar')}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
