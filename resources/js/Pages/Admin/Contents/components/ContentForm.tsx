import { useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, LangTabs, Switch, Textarea, Wysiwyg } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import type { I18nSharedProps } from '@/types/i18n';

import StringListField from './StringListField';

type LocaleMap = Record<string, string>;

export type ContentFormData = {
    name: string;
    status: string;
    title: LocaleMap;
    excerpt: LocaleMap;
    content: LocaleMap;
    referrer: string[];
    video: string[];
};

type Props = {
    contentId: number;
    kind: string;
    initial: {
        name: string;
        status: string;
        title?: Record<string, string | null | undefined>;
        excerpt?: Record<string, string | null | undefined>;
        content?: Record<string, string | null | undefined>;
        referrer?: string[] | null;
        video?: string[] | null;
    };
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

function toStringList(source?: string[] | null): string[] {
    if (!Array.isArray(source) || source.length === 0) {
        return [''];
    }

    return source.map((item) => item ?? '');
}

function compactList(values: string[]): string[] {
    return values.map((value) => value.trim()).filter((value) => value !== '');
}

export default function ContentForm({ contentId, kind, initial }: Props) {
    const { t } = useTranslation();
    const page = usePage<I18nSharedProps>();
    const locales = page.props.supportedLocales ?? ['es'];
    const defaultLocale = page.props.defaultLocale ?? locales[0] ?? 'es';
    const isArticle = kind === 'article';

    const form = useForm<ContentFormData>({
        name: initial.name,
        status: initial.status,
        title: toLocaleMap(locales, initial.title),
        excerpt: toLocaleMap(locales, initial.excerpt),
        content: toLocaleMap(locales, initial.content),
        referrer: toStringList(initial.referrer),
        video: toStringList(initial.video),
    });

    const invalidLocales = locales.filter(
        (locale) =>
            Boolean(form.errors[`title.${locale}`]) ||
            Boolean(form.errors[`excerpt.${locale}`]) ||
            Boolean(form.errors[`content.${locale}`]),
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();

        const payload: Record<string, unknown> = {
            name: form.data.name,
            status: form.data.status,
            title: form.data.title,
            excerpt: form.data.excerpt,
            referrer: compactList(form.data.referrer),
            video: compactList(form.data.video),
        };

        if (isArticle) {
            payload.content = form.data.content;
        }

        form.transform(() => payload);
        form.put(`/settings/contents/${contentId}`, { preserveScroll: true });
    };

    return (
        <form
            onSubmit={onSubmit}
            className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
            data-testid="content-edit-form"
        >
            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <Field label={t('nombre')} htmlFor="content-name" error={form.errors.name} required>
                    <Input
                        id="content-name"
                        value={form.data.name}
                        maxLength={150}
                        onChange={(event) => form.setData('name', event.target.value)}
                    />
                </Field>

                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-3">
                        <Label htmlFor="content-status">{t('estado')}</Label>
                        <Switch
                            id="content-status"
                            checked={form.data.status === 'active'}
                            onCheckedChange={(checked) =>
                                form.setData('status', checked ? 'active' : 'inactive')
                            }
                        />
                    </div>
                    {form.errors.status ? (
                        <p className="text-sm text-red-600">{form.errors.status}</p>
                    ) : null}
                </div>
            </div>

            <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <StringListField
                    id="content-referrer"
                    label={t('referrer')}
                    values={form.data.referrer}
                    addLabelKey="anadir_enlace"
                    error={form.errors.referrer ?? form.errors['referrer.0']}
                    onChange={(values) => form.setData('referrer', values)}
                />
                <StringListField
                    id="content-video"
                    label={t('video_url')}
                    values={form.data.video}
                    addLabelKey="anadir_video"
                    inputType="url"
                    error={form.errors.video ?? form.errors['video.0']}
                    onChange={(values) => form.setData('video', values)}
                />
            </div>

            <LangTabs locales={locales} invalidLocales={invalidLocales}>
                {(locale) => (
                    <div className="space-y-4">
                        <Field
                            label={t('titulo')}
                            htmlFor={`content-title-${locale}`}
                            error={form.errors[`title.${locale}`]}
                            required={locale === defaultLocale}
                        >
                            <Input
                                id={`content-title-${locale}`}
                                value={form.data.title[locale] ?? ''}
                                maxLength={255}
                                onChange={(event) =>
                                    form.setData('title', {
                                        ...form.data.title,
                                        [locale]: event.target.value,
                                    })
                                }
                            />
                        </Field>
                        <Field
                            label={t('extracto')}
                            htmlFor={`content-excerpt-${locale}`}
                            error={form.errors[`excerpt.${locale}`]}
                        >
                            <Textarea
                                id={`content-excerpt-${locale}`}
                                value={form.data.excerpt[locale] ?? ''}
                                maxLength={2000}
                                rows={3}
                                onChange={(event) =>
                                    form.setData('excerpt', {
                                        ...form.data.excerpt,
                                        [locale]: event.target.value,
                                    })
                                }
                            />
                        </Field>
                        {isArticle ? (
                            <Field
                                label={t('contenido')}
                                htmlFor={`content-body-${locale}`}
                                error={form.errors[`content.${locale}`]}
                                required={locale === defaultLocale}
                            >
                                <Wysiwyg
                                    id={`content-body-${locale}`}
                                    value={form.data.content[locale] ?? ''}
                                    enableGalleryInsert
                                    onChange={(html) =>
                                        form.setData('content', {
                                            ...form.data.content,
                                            [locale]: html,
                                        })
                                    }
                                />
                            </Field>
                        ) : null}
                    </div>
                )}
            </LangTabs>

            <FormActions>
                <Button type="submit" disabled={form.processing}>
                    {t('guardar')}
                </Button>
            </FormActions>
        </form>
    );
}
