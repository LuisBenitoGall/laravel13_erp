import { Link, useForm } from '@inertiajs/react';
import { ListChecks } from 'lucide-react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import CompanyCurrenciesPanel, {
    type CompanyCurrencyOption,
} from '@/Pages/Admin/Companies/components/CompanyCurrenciesPanel';
import CompanyLogoPanel from '@/Pages/Admin/Companies/components/CompanyLogoPanel';

type LocaleMap = Record<string, string>;

type CompanyPayload = {
    id: number;
    name: string;
    tradename: string | null;
    nif: string | null;
    status: string;
    logo_url?: string | null;
    excerpt?: LocaleMap;
    description?: LocaleMap;
};

type WorkplaceRow = {
    id: number;
    name: string;
    status: string;
    featured: boolean;
};

type SetupState = {
    complete: boolean;
    completed_steps: number;
    total_steps: number;
    steps: {
        identity: { done: boolean; nif: boolean; logo: boolean };
        workplaces: { done: boolean };
        currencies: { done: boolean };
    };
};

type Props = {
    company: CompanyPayload;
    workplaces: WorkplaceRow[];
    currencies: CompanyCurrencyOption[];
    currency_options: CompanyCurrencyOption[];
    default_currency_id: number | null;
    setup: SetupState;
    wizard: boolean;
    can: {
        update_company: boolean;
        create_workplace: boolean;
        update_company_currencies: boolean;
    };
};

type StepId = 'identity' | 'workplaces' | 'currencies';

function firstIncompleteStep(setup: SetupState): StepId {
    if (!setup.steps.identity.done) {
        return 'identity';
    }
    if (!setup.steps.workplaces.done) {
        return 'workplaces';
    }
    return 'currencies';
}

export default function Index({
    company,
    workplaces,
    currencies,
    currency_options,
    default_currency_id,
    setup,
    wizard,
    can,
}: Props) {
    const { t } = useTranslation();

    const [tab, setTab] = useState<StepId>(wizard ? firstIncompleteStep(setup) : 'identity');

    const identityForm = useForm({
        name: company.name,
        tradename: company.tradename ?? '',
        nif: company.nif ?? '',
        status: company.status,
        excerpt: company.excerpt ?? {},
        description: company.description ?? {},
    });

    const progressLabel = useMemo(
        () =>
            t('progreso_configuracion_empresa', {
                done: setup.completed_steps,
                total: setup.total_steps,
            }),
        [setup.completed_steps, setup.total_steps, t],
    );

    const createWorkplaceHref = `/companies/workplaces/create?return_to=${encodeURIComponent(
        wizard ? '/companies/company-settings?wizard=1' : '/companies/company-settings',
    )}`;

    function submitIdentity(event: FormEvent) {
        event.preventDefault();
        if (!can.update_company) {
            return;
        }

        identityForm.transform(() => ({
            name: identityForm.data.name,
            tradename: identityForm.data.tradename || null,
            nif: identityForm.data.nif,
            status: identityForm.data.status,
            excerpt: identityForm.data.excerpt,
            description: identityForm.data.description,
        }));

        identityForm.put(`/companies/${company.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                const nifOk = identityForm.data.nif.trim() !== '';
                const logoOk = Boolean(company.logo_url) || setup.steps.identity.logo;
                if (wizard && nifOk && logoOk && !setup.steps.workplaces.done) {
                    setTab('workplaces');
                } else if (
                    wizard &&
                    nifOk &&
                    logoOk &&
                    setup.steps.workplaces.done &&
                    !setup.steps.currencies.done
                ) {
                    setTab('currencies');
                }
            },
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'configuracion_de_la_empresa',
                suboptionKey: 'dashboard',
                optionHref: '/companies/company-settings',
            }}
            showBack={false}
            actions={
                wizard
                    ? []
                    : [
                          {
                              type: 'link',
                              labelKey: 'continuar_asistente',
                              href: '/companies/company-settings?wizard=1',
                              icon: ListChecks,
                          },
                      ]
            }
        >
            <div className="space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6">
                    <h1 className="text-lg font-semibold text-slate-900">
                        {t('configuracion_de_la_empresa')}
                    </h1>
                    <p className="mt-1 text-sm text-slate-600">{progressLabel}</p>
                    <div className="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div
                            className="h-full rounded-full bg-brand transition-all"
                            style={{
                                width: `${(setup.completed_steps / Math.max(setup.total_steps, 1)) * 100}%`,
                            }}
                        />
                    </div>
                    {wizard ? (
                        <ol className="mt-4 flex flex-wrap gap-3 text-sm">
                            <li
                                className={
                                    setup.steps.identity.done
                                        ? 'font-medium text-brand-ink'
                                        : 'text-slate-700'
                                }
                            >
                                1. {t('paso_identidad')}
                                {setup.steps.identity.done ? ` · ${t('completado')}` : ''}
                            </li>
                            <li
                                className={
                                    setup.steps.workplaces.done
                                        ? 'font-medium text-brand-ink'
                                        : 'text-slate-700'
                                }
                            >
                                2. {t('paso_sedes')}
                                {setup.steps.workplaces.done ? ` · ${t('completado')}` : ''}
                            </li>
                            <li
                                className={
                                    setup.steps.currencies.done
                                        ? 'font-medium text-brand-ink'
                                        : 'text-slate-700'
                                }
                            >
                                3. {t('paso_monedas')}
                                {setup.steps.currencies.done ? ` · ${t('completado')}` : ''}
                            </li>
                        </ol>
                    ) : null}
                </section>

                <div className="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
                    {(
                        [
                            ['identity', 'paso_identidad', setup.steps.identity.done],
                            ['workplaces', 'paso_sedes', setup.steps.workplaces.done],
                            ['currencies', 'paso_monedas', setup.steps.currencies.done],
                        ] as const
                    ).map(([id, labelKey, done]) => (
                        <button
                            key={id}
                            type="button"
                            onClick={() => setTab(id)}
                            className={`rounded-md px-3 py-1.5 text-sm ${
                                tab === id
                                    ? 'bg-brand text-white'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                            }`}
                        >
                            {t(labelKey)}
                            {done ? ' ✓' : ''}
                        </button>
                    ))}
                </div>

                {tab === 'identity' ? (
                    <section className="space-y-6 rounded-xl border border-slate-200 bg-white p-6">
                        <form onSubmit={submitIdentity} className="space-y-4">
                            <Field
                                label={t('nombre')}
                                htmlFor="settings-company-name"
                                error={identityForm.errors.name}
                                required
                            >
                                <Input
                                    id="settings-company-name"
                                    value={identityForm.data.name}
                                    disabled={!can.update_company}
                                    onChange={(e) => identityForm.setData('name', e.target.value)}
                                />
                            </Field>
                            <Field
                                label={t('nombre_comercial')}
                                htmlFor="settings-company-tradename"
                                error={identityForm.errors.tradename}
                            >
                                <Input
                                    id="settings-company-tradename"
                                    value={identityForm.data.tradename}
                                    disabled={!can.update_company}
                                    onChange={(e) =>
                                        identityForm.setData('tradename', e.target.value)
                                    }
                                />
                            </Field>
                            <Field
                                label={t('nif')}
                                htmlFor="settings-company-nif"
                                error={identityForm.errors.nif}
                                required
                            >
                                <Input
                                    id="settings-company-nif"
                                    value={identityForm.data.nif}
                                    disabled={!can.update_company}
                                    onChange={(e) => identityForm.setData('nif', e.target.value)}
                                />
                            </Field>
                            {!setup.steps.identity.nif ? (
                                <p className="text-sm text-amber-700">{t('nif_pendiente')}</p>
                            ) : null}
                            {can.update_company ? (
                                <FormActions>
                                    <Button type="submit" disabled={identityForm.processing}>
                                        {t('guardar')}
                                    </Button>
                                </FormActions>
                            ) : null}
                        </form>

                        <div className="border-t border-slate-100 pt-6">
                            {!setup.steps.identity.logo ? (
                                <p className="mb-3 text-sm text-amber-700">{t('logo_pendiente')}</p>
                            ) : null}
                            {can.update_company ? (
                                <CompanyLogoPanel
                                    companyId={company.id}
                                    logoUrl={company.logo_url ?? null}
                                />
                            ) : null}
                        </div>
                    </section>
                ) : null}

                {tab === 'workplaces' ? (
                    <section className="space-y-4 rounded-xl border border-slate-200 bg-white p-6">
                        {setup.steps.workplaces.done ? (
                            <p className="text-sm text-brand-ink">{t('sede_completa')}</p>
                        ) : (
                            <p className="text-sm text-amber-700">{t('sede_pendiente')}</p>
                        )}

                        {workplaces.length === 0 ? (
                            <p className="text-sm text-slate-600">{t('sin_centros_de_trabajo')}</p>
                        ) : (
                            <ul className="divide-y divide-slate-100 rounded-lg border border-slate-200">
                                {workplaces.map((workplace) => (
                                    <li
                                        key={workplace.id}
                                        className="flex items-center justify-between gap-3 px-4 py-3 text-sm"
                                    >
                                        <span className="font-medium text-slate-900">
                                            {workplace.name}
                                            {workplace.featured ? (
                                                <span className="ml-2 text-xs text-slate-500">
                                                    ({t('principal')})
                                                </span>
                                            ) : null}
                                        </span>
                                        <span className="text-slate-600">
                                            {workplace.status === 'active'
                                                ? t('activo')
                                                : t('inactivo')}
                                        </span>
                                    </li>
                                ))}
                            </ul>
                        )}

                        {can.create_workplace ? (
                            <Button asChild>
                                <Link href={createWorkplaceHref}>{t('anadir_centro_trabajo')}</Link>
                            </Button>
                        ) : null}

                        {wizard && setup.steps.workplaces.done && !setup.steps.currencies.done ? (
                            <Button type="button" variant="outline" onClick={() => setTab('currencies')}>
                                {t('paso_monedas')}
                            </Button>
                        ) : null}
                    </section>
                ) : null}

                {tab === 'currencies' ? (
                    <section className="space-y-4">
                        {setup.steps.currencies.done ? (
                            <p className="text-sm text-brand-ink">{t('monedas_completas')}</p>
                        ) : (
                            <p className="text-sm text-amber-700">{t('moneda_pendiente')}</p>
                        )}
                        <CompanyCurrenciesPanel
                            submitUrl="/companies/company-settings/currencies"
                            currencies={currencies}
                            currencyOptions={currency_options}
                            defaultCurrencyId={default_currency_id}
                            canUpdate={can.update_company_currencies}
                        />
                    </section>
                ) : null}
            </div>
        </AppLayout>
    );
}
