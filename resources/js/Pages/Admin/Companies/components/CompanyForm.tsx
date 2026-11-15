import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, LangTabs, Select, Textarea, Wysiwyg } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type LocaleMap = Record<string, string>;

type CompanyFormData = {
    name: string;
    tradename: string;
    nif: string;
    status: string;
    excerpt: LocaleMap;
    description: LocaleMap;
};

type Props = {
    mode?: 'create' | 'edit';
    initial?: Partial<Omit<CompanyFormData, 'excerpt' | 'description'>> & {
        excerpt?: Record<string, string | null | undefined>;
        description?: Record<string, string | null | undefined>;
    };
    submitUrl: string;
    method?: 'post' | 'put';
};

function emptyLocaleMap(locales: string[]): LocaleMap {
    return Object.fromEntries(locales.map((locale) => [locale, '']));
}

function toLocaleMap(
    locales: string[],
    source?: Record<string, string | null | undefined>,
): LocaleMap {
    const map = emptyLocaleMap(locales);
    if (!source) {
        return map;
    }

    for (const locale of locales) {
        map[locale] = source[locale] ?? '';
    }

    return map;
}

export default function CompanyForm({
    mode = 'edit',
    initial,
    submitUrl,
    method = 'post',
}: Props) {
    const { t } = useTranslation();
    const page = usePage<{ supportedLocales?: string[] }>();
    const locales = page.props.supportedLocales ?? ['es'];

    const form = useForm<CompanyFormData>({
        name: initial?.name ?? '',
        tradename: initial?.tradename ?? '',
        nif: initial?.nif ?? '',
        status: initial?.status ?? 'active',
        excerpt: toLocaleMap(locales, initial?.excerpt),
        description: toLocaleMap(locales, initial?.description),
    });

    const invalidLocales = locales.filter(
        (locale) =>
            Boolean(form.errors[`excerpt.${locale}`]) ||
            Boolean(form.errors[`description.${locale}`]),
    );

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        const payload =
            mode === 'edit'
                ? {
                      name: form.data.name,
                      tradename: form.data.tradename || null,
                      nif: form.data.nif,
                      status: form.data.status,
                      excerpt: form.data.excerpt,
                      description: form.data.description,
                  }
                : {
                      name: form.data.name,
                      tradename: form.data.tradename || null,
                      nif: form.data.nif,
                      status: form.data.status,
                  };
        form.transform(() => payload);
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    const statusOptions = [
        { value: 'draft', label: t('estado_borrador') },
        { value: 'active', label: t('estado_activa') },
        { value: 'closed', label: t('estado_cerrada') },
    ];

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-mode={mode}
        >
            <Field label={t('razon_social')} htmlFor="company-name" error={form.errors.name}>
                <Input
                    id="company-name"
                    value={form.data.name}
                    maxLength={255}
                    onChange={(e) => form.setData('name', e.target.value)}
                />
            </Field>

            <Field
                label={t('nombre_comercial')}
                htmlFor="company-tradename"
                error={form.errors.tradename}
            >
                <Input
                    id="company-tradename"
                    value={form.data.tradename}
                    maxLength={255}
                    onChange={(e) => form.setData('tradename', e.target.value)}
                />
            </Field>

            <Field label={t('nif')} htmlFor="company-nif" error={form.errors.nif}>
                <Input
                    id="company-nif"
                    value={form.data.nif}
                    maxLength={12}
                    onChange={(e) => form.setData('nif', e.target.value)}
                />
            </Field>

            <Field label={t('estado')} htmlFor="company-status" error={form.errors.status}>
                <Select
                    id="company-status"
                    options={statusOptions}
                    value={form.data.status}
                    onChange={(value) => form.setData('status', value)}
                    placeholder={t('elegir')}
                />
            </Field>

            {mode === 'edit' ? (
                <LangTabs locales={locales} invalidLocales={invalidLocales}>
                    {(locale) => (
                        <div className="space-y-4">
                            <Field
                                label={t('extracto')}
                                htmlFor={`company-excerpt-${locale}`}
                                error={form.errors[`excerpt.${locale}`]}
                            >
                                <Textarea
                                    id={`company-excerpt-${locale}`}
                                    value={form.data.excerpt[locale] ?? ''}
                                    maxLength={1000}
                                    rows={3}
                                    onChange={(e) =>
                                        form.setData('excerpt', {
                                            ...form.data.excerpt,
                                            [locale]: e.target.value,
                                        })
                                    }
                                />
                            </Field>
                            <Field
                                label={t('descripcion')}
                                htmlFor={`company-description-${locale}`}
                                error={form.errors[`description.${locale}`]}
                            >
                                <Wysiwyg
                                    id={`company-description-${locale}`}
                                    value={form.data.description[locale] ?? ''}
                                    onChange={(html) =>
                                        form.setData('description', {
                                            ...form.data.description,
                                            [locale]: html,
                                        })
                                    }
                                />
                            </Field>
                        </div>
                    )}
                </LangTabs>
            ) : null}

            <FormActions>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
