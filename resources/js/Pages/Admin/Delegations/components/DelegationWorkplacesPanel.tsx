import { router, useForm } from '@inertiajs/react';
import { Pencil, Trash2 } from 'lucide-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import ConfirmDialog from '@/Components/Feedback/ConfirmDialog';
import { Field, FormActions, Input, Select, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type GeoOption = { id: number; name: string };

export type DelegationWorkplace = {
    id: number;
    name: string;
    address: string | null;
    cp: string | null;
    town_id: number | null;
    town_name?: string | null;
    country_id?: number | null;
    country_name?: string | null;
    province_id?: number | null;
    province_name?: string | null;
    featured: boolean;
    status: string;
};

function workplaceLocationLine(workplace: DelegationWorkplace): string {
    return [
        workplace.address,
        workplace.cp,
        workplace.town_name,
        workplace.province_name,
        workplace.country_name,
    ]
        .filter((part): part is string => typeof part === 'string' && part.trim() !== '')
        .join(' · ');
}

type Props = {
    delegationId: number;
    workplaces: DelegationWorkplace[];
    countries: GeoOption[];
};

type WorkplaceFormData = {
    name: string;
    address: string;
    cp: string;
    town_id: string;
    featured: boolean;
    status: string;
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

function WorkplaceEditor({
    delegationId,
    workplace,
    countries,
    onDone,
}: {
    delegationId: number;
    workplace: DelegationWorkplace | null;
    countries: GeoOption[];
    onDone: () => void;
}) {
    const { t } = useTranslation();
    const [countryId, setCountryId] = useState(
        workplace?.country_id ? String(workplace.country_id) : '',
    );
    const [provinceId, setProvinceId] = useState(
        workplace?.province_id ? String(workplace.province_id) : '',
    );
    const [provinces, setProvinces] = useState<GeoOption[]>([]);
    const [towns, setTowns] = useState<GeoOption[]>([]);

    const form = useForm<WorkplaceFormData>({
        name: workplace?.name ?? '',
        address: workplace?.address ?? '',
        cp: workplace?.cp ?? '',
        town_id: workplace?.town_id ? String(workplace.town_id) : '',
        featured: workplace?.featured ?? false,
        status: workplace?.status ?? 'active',
    });
    const baseUrl = `/admin/delegations/${delegationId}/workplaces`;
    const statusOptions = [
        { value: 'active', label: t('activo') },
        { value: 'inactive', label: t('inactivo') },
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
        form.transform((data) => ({
            ...data,
            address: data.address || null,
            cp: data.cp || null,
            town_id: data.town_id ? Number(data.town_id) : null,
        }));
        const options = { preserveScroll: true, onSuccess: () => onDone() };
        if (workplace) {
            form.put(`${baseUrl}/${workplace.id}`, options);
        } else {
            form.post(baseUrl, options);
        }
    };

    const idPrefix = `delegation-wp-${workplace?.id ?? 'new'}`;

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4"
            data-testid="delegation-workplace-form"
        >
            <Field label={t('nombre')} htmlFor={`${idPrefix}-name`} error={form.errors.name} required>
                <Input
                    id={`${idPrefix}-name`}
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>
            <div className="grid gap-4 md:grid-cols-4">
                <Field
                    className="md:col-span-3"
                    label={t('direccion')}
                    htmlFor={`${idPrefix}-address`}
                    error={form.errors.address}
                >
                    <Input
                        id={`${idPrefix}-address`}
                        value={form.data.address}
                        maxLength={255}
                        onChange={(e) => form.setData('address', e.target.value)}
                    />
                </Field>
                <Field label={t('codigo_postal')} htmlFor={`${idPrefix}-cp`} error={form.errors.cp}>
                    <Input
                        id={`${idPrefix}-cp`}
                        value={form.data.cp}
                        maxLength={16}
                        onChange={(e) => form.setData('cp', e.target.value)}
                    />
                </Field>
            </div>
            <div className="grid gap-4 md:grid-cols-3">
                <Field label={t('pais')} htmlFor={`${idPrefix}-country`}>
                    <Select
                        id={`${idPrefix}-country`}
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
                <Field label={t('provincia')} htmlFor={`${idPrefix}-province`}>
                    <Select
                        id={`${idPrefix}-province`}
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
                    htmlFor={`${idPrefix}-town`}
                    error={form.errors.town_id}
                >
                    <Select
                        id={`${idPrefix}-town`}
                        options={townOptions}
                        value={form.data.town_id}
                        onChange={(value) => form.setData('town_id', value)}
                        placeholder={t('elegir')}
                        disabled={!provinceId}
                    />
                </Field>
            </div>
            <div className="grid gap-4 md:grid-cols-4 md:items-end">
                <Field label={t('estado')} htmlFor={`${idPrefix}-status`} error={form.errors.status}>
                    <Select
                        id={`${idPrefix}-status`}
                        options={statusOptions}
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value)}
                        placeholder={t('elegir')}
                    />
                </Field>
                <div className="flex min-h-10 items-center md:col-span-3">
                    <Switch
                        id={`${idPrefix}-featured`}
                        label={t('principal')}
                        checked={form.data.featured}
                        onCheckedChange={(checked) => form.setData('featured', checked)}
                    />
                </div>
            </div>
            <FormActions>
                <Button type="button" variant="secondary" onClick={onDone}>
                    {t('cancelar')}
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}

/** Sedes de la Delegación: company_id explícito de la hija; la sesión sigue en la matriz. */
export default function DelegationWorkplacesPanel({
    delegationId,
    workplaces,
    countries,
}: Props) {
    const { t } = useTranslation();
    const [editing, setEditing] = useState<number | 'new' | null>(null);
    const [pendingDelete, setPendingDelete] = useState<DelegationWorkplace | null>(null);

    function confirmDelete() {
        if (pendingDelete === null) {
            return;
        }

        router.delete(`/admin/delegations/${delegationId}/workplaces/${pendingDelete.id}`, {
            preserveScroll: true,
            onFinish: () => setPendingDelete(null),
        });
    }

    return (
        <div className="space-y-4" data-testid="delegation-workplaces">
            {workplaces.length === 0 ? (
                <p className="text-sm text-slate-600">{t('sin_centros_de_trabajo')}</p>
            ) : (
                <ul className="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
                    {workplaces.map((workplace) => (
                        <li key={workplace.id} className="p-4">
                            {editing === workplace.id ? (
                                <WorkplaceEditor
                                    delegationId={delegationId}
                                    workplace={workplace}
                                    countries={countries}
                                    onDone={() => setEditing(null)}
                                />
                            ) : (
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p className="text-sm font-medium text-slate-900">
                                            {workplace.name}
                                            {workplace.featured ? (
                                                <span className="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800">
                                                    {t('principal')}
                                                </span>
                                            ) : null}
                                        </p>
                                        {workplaceLocationLine(workplace) ? (
                                            <p className="text-xs text-slate-500">
                                                {workplaceLocationLine(workplace)}
                                            </p>
                                        ) : null}
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            title={t('editar')}
                                            aria-label={t('editar')}
                                            onClick={() => setEditing(workplace.id)}
                                        >
                                            <Pencil className="h-4 w-4" />
                                            <span className="sr-only">{t('editar')}</span>
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon-sm"
                                            className="text-red-700 hover:text-red-800"
                                            title={t('eliminar')}
                                            aria-label={t('eliminar')}
                                            onClick={() => setPendingDelete(workplace)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                            <span className="sr-only">{t('eliminar')}</span>
                                        </Button>
                                    </div>
                                </div>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            {editing === 'new' ? (
                <WorkplaceEditor
                    delegationId={delegationId}
                    workplace={null}
                    countries={countries}
                    onDone={() => setEditing(null)}
                />
            ) : (
                <Button type="button" onClick={() => setEditing('new')}>
                    {t('anadir_centro_trabajo')}
                </Button>
            )}

            <ConfirmDialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) {
                        setPendingDelete(null);
                    }
                }}
                title={t('confirmar_eliminar')}
                description={pendingDelete?.name}
                confirmLabel={t('eliminar')}
                onConfirm={confirmDelete}
            />
        </div>
    );
}
