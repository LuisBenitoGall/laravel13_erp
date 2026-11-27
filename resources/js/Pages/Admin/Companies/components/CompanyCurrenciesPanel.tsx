import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Field, FormActions, FormHelpScope, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

export type CompanyCurrencyOption = {
    id: number;
    code: string | null;
    symbol: string | null;
    name: string;
    status: string;
    is_default?: boolean;
};

type Props = {
    submitUrl: string;
    currencies: CompanyCurrencyOption[];
    currencyOptions: CompanyCurrencyOption[];
    defaultCurrencyId: number | null;
    canUpdate?: boolean;
};

function labelFor(currency: CompanyCurrencyOption): string {
    const code = currency.code ?? '?';
    const symbol = currency.symbol ? ` ${currency.symbol}` : '';
    return `${code}${symbol} — ${currency.name}`;
}

export default function CompanyCurrenciesPanel({
    submitUrl,
    currencies,
    currencyOptions,
    defaultCurrencyId,
    canUpdate = true,
}: Props) {
    const { t } = useTranslation();

    const form = useForm<{
        currency_ids: number[];
        default_currency_id: string;
    }>({
        currency_ids: currencies.map((currency) => currency.id),
        default_currency_id: defaultCurrencyId ? String(defaultCurrencyId) : '',
    });

    const selectable = useMemo(() => {
        const byId = new Map<number, CompanyCurrencyOption>();
        for (const option of currencyOptions) {
            byId.set(option.id, option);
        }
        for (const linked of currencies) {
            byId.set(linked.id, linked);
        }
        return Array.from(byId.values()).sort((a, b) =>
            (a.code ?? a.name).localeCompare(b.code ?? b.name),
        );
    }, [currencies, currencyOptions]);

    const inactiveSelected = useMemo(
        () =>
            selectable.filter(
                (currency) =>
                    form.data.currency_ids.includes(currency.id) && currency.status !== 'active',
            ),
        [form.data.currency_ids, selectable],
    );

    const defaultOptions = useMemo(
        () =>
            selectable
                .filter((currency) => form.data.currency_ids.includes(currency.id))
                .map((currency) => ({
                    value: String(currency.id),
                    label: labelFor(currency),
                })),
        [form.data.currency_ids, selectable],
    );

    function toggle(id: number, checked: boolean) {
        if (!canUpdate) {
            return;
        }

        const next = checked
            ? [...new Set([...form.data.currency_ids, id])]
            : form.data.currency_ids.filter((value) => value !== id);

        form.setData((data) => ({
            ...data,
            currency_ids: next,
            default_currency_id:
                data.default_currency_id && next.includes(Number(data.default_currency_id))
                    ? data.default_currency_id
                    : next.length === 1
                      ? String(next[0])
                      : '',
        }));
    }

    function onSubmit(event: FormEvent) {
        event.preventDefault();
        if (!canUpdate) {
            return;
        }

        form.transform((data) => ({
            currency_ids: data.currency_ids,
            default_currency_id: data.default_currency_id
                ? Number(data.default_currency_id)
                : null,
        }));

        form.put(submitUrl, {
            preserveScroll: true,
        });
    }

    return (
        <FormHelpScope value="companies">
            <form
                onSubmit={onSubmit}
                className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            >
                <div>
                    <h2 className="text-sm font-semibold text-slate-900">{t('monedas')}</h2>
                    <p className="mt-1 text-sm text-slate-600">{t('monedas_empresa_ayuda')}</p>
                </div>

                <Field
                    label={t('moneda_por_defecto')}
                    htmlFor="company-default-currency"
                    fieldKey="default_currency_id"
                    error={form.errors.default_currency_id}
                    required={form.data.currency_ids.length > 0}
                >
                    <Select
                        id="company-default-currency"
                        options={defaultOptions}
                        value={form.data.default_currency_id || null}
                        disabled={!canUpdate || form.data.currency_ids.length === 0}
                        placeholder={t('elegir')}
                        onChange={(value) => form.setData('default_currency_id', value)}
                    />
                </Field>

                <Field
                    label={t('monedas_permitidas')}
                    error={form.errors.currency_ids}
                    fieldKey="currency_ids"
                >
                    {selectable.length === 0 ? (
                        <p className="text-sm text-slate-500">{t('monedas_sin_opciones')}</p>
                    ) : (
                        <ul className="grid gap-2 sm:grid-cols-2">
                            {selectable.map((option) => {
                                const checked = form.data.currency_ids.includes(option.id);

                                return (
                                    <li key={option.id}>
                                        <Checkbox
                                            id={`company-currency-${option.id}`}
                                            label={
                                                option.status === 'active'
                                                    ? labelFor(option)
                                                    : `${labelFor(option)} (${t('inactivo')})`
                                            }
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

                {inactiveSelected.length > 0 ? (
                    <p className="text-sm text-amber-700">{t('moneda_inactiva_aviso')}</p>
                ) : null}

                {canUpdate ? (
                    <FormActions>
                        <Button type="submit" disabled={form.processing}>
                            {t('guardar')}
                        </Button>
                    </FormActions>
                ) : null}
            </form>
        </FormHelpScope>
    );
}
