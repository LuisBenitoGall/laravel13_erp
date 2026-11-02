import { useForm, usePage } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { ColorPicker, Field, IconPicker, Input, LangTabs, Select, Switch, Textarea } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import AppLayout from '@/Layouts/AppLayout';
import type { I18nSharedProps } from '@/types/i18n';

type LocaleMap = Record<string, string>;

type ModuleCreateForm = {
    name: string;
    label: LocaleMap;
    explanation: LocaleMap;
    color: string;
    icon: string;
    level: string;
    status: string;
};

function emptyLocaleMap(locales: string[]): LocaleMap {
    return Object.fromEntries(locales.map((locale) => [locale, '']));
}

export default function Create() {
    const { t } = useTranslation();
    const { supportedLocales, defaultLocale } = usePage<I18nSharedProps>().props;
    const locales = useMemo(() => supportedLocales, [supportedLocales]);
    const { data, setData, post, processing, errors } = useForm<ModuleCreateForm>({
        name: '',
        label: emptyLocaleMap(locales),
        explanation: emptyLocaleMap(locales),
        color: '#0f766e',
        icon: 'settings',
        level: 'basic',
        status: 'active',
    });

    const invalidLocales = locales.filter(
        (locale) => errors[`label.${locale}`] || errors[`explanation.${locale}`],
    );

    function submit(event: FormEvent) {
        event.preventDefault();
        post('/admin/modules');
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'modulos',
                suboptionKey: 'modulo_crear',
                optionHref: '/admin/modules',
            }}
            actions={[]}
        >
            <form
                onSubmit={submit}
                className="w-full space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            >
                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <Field label={t('nombre')} htmlFor="module-name" error={errors.name} required>
                        <Input
                            id="module-name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                        />
                    </Field>
                    <Field label={t('nivel')} htmlFor="module-level" error={errors.level} required>
                        <Select
                            id="module-level"
                            value={data.level}
                            options={[
                                { value: 'admin_only', label: t('nivel_admin_only') },
                                { value: 'basic', label: t('nivel_basic') },
                                { value: 'optional', label: t('nivel_optional') },
                            ]}
                            onChange={(value) => setData('level', value)}
                        />
                    </Field>
                    <IconPicker
                        id="module-icon"
                        label={t('icono')}
                        value={data.icon}
                        error={errors.icon}
                        required
                        onChange={(value) => setData('icon', value)}
                    />
                    <ColorPicker
                        id="module-color"
                        label={t('color')}
                        value={data.color}
                        error={errors.color}
                        required
                        onChange={(value) => setData('color', value)}
                    />
                    <div className="flex flex-col gap-1">
                        <div className="flex items-center gap-3">
                            <Label htmlFor="module-status">{t('estado')}</Label>
                            <Switch
                                id="module-status"
                                checked={data.status === 'active'}
                                onCheckedChange={(checked) => setData('status', checked ? 'active' : 'inactive')}
                            />
                        </div>
                        {errors.status ? <p className="text-sm text-red-600">{errors.status}</p> : null}
                    </div>
                </div>
                <LangTabs locales={locales} invalidLocales={invalidLocales}>
                    {(locale) => (
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <Field
                                label={t('etiqueta')}
                                htmlFor={`module-label-${locale}`}
                                error={errors[`label.${locale}`]}
                                required={locale === defaultLocale}
                            >
                                <Input
                                    id={`module-label-${locale}`}
                                    value={data.label[locale] ?? ''}
                                    onChange={(event) =>
                                        setData('label', { ...data.label, [locale]: event.target.value })
                                    }
                                />
                            </Field>
                            <Field
                                label={t('explicacion')}
                                htmlFor={`module-explanation-${locale}`}
                                error={errors[`explanation.${locale}`]}
                                required={locale === defaultLocale}
                            >
                                <Textarea
                                    id={`module-explanation-${locale}`}
                                    value={data.explanation[locale] ?? ''}
                                    onChange={(event) =>
                                        setData('explanation', {
                                            ...data.explanation,
                                            [locale]: event.target.value,
                                        })
                                    }
                                />
                            </Field>
                        </div>
                    )}
                </LangTabs>
                <Button type="submit" disabled={processing}>
                    {t('guardar')}
                </Button>
            </form>
        </AppLayout>
    );
}
