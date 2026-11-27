import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Input, RadioGroup } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type ContentCreateForm = {
    name: string;
    kind: string;
    code: string;
};

export default function Create() {
    const { t } = useTranslation();
    const form = useForm<ContentCreateForm>({
        name: '',
        kind: '',
        code: '',
    });
    const { data, setData, post, processing, errors, setError, clearErrors, transform } = form;

    const isHelp = data.kind === 'help';

    function submit(event: FormEvent) {
        event.preventDefault();
        clearErrors();

        const nextErrors: Partial<Record<keyof ContentCreateForm, string>> = {};
        if (!data.name.trim()) {
            nextErrors.name = t('validacion_nombre_obligatorio');
        }
        if (!data.kind) {
            nextErrors.kind = t('validacion_tipo_obligatorio');
        }
        if (isHelp && !data.code.trim()) {
            nextErrors.code = t('validacion_codigo_obligatorio');
        }

        if (nextErrors.name || nextErrors.kind || nextErrors.code) {
            if (nextErrors.name) {
                setError('name', nextErrors.name);
            }
            if (nextErrors.kind) {
                setError('kind', nextErrors.kind);
            }
            if (nextErrors.code) {
                setError('code', nextErrors.code);
            }

            return;
        }

        transform((current) => ({
            name: current.name,
            kind: current.kind,
            ...(current.kind === 'help' ? { code: current.code.trim().toLowerCase() } : {}),
        }));
        post('/settings/contents');
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'contenidos',
                suboptionKey: 'contenido_crear',
                optionHref: '/settings/contents',
            }}
            backHref="/settings/contents"
            actions={[]}
        >
            <form
                onSubmit={submit}
                className="w-full max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                noValidate
            >
                <Field label={t('nombre')} htmlFor="content-name" error={errors.name} required>
                    <Input
                        id="content-name"
                        value={data.name}
                        maxLength={150}
                        autoComplete="off"
                        aria-invalid={Boolean(errors.name)}
                        onChange={(event) => {
                            setData('name', event.target.value);
                            if (errors.name) {
                                clearErrors('name');
                            }
                        }}
                    />
                </Field>
                <Field label={t('tipo')} htmlFor="content-kind" error={errors.kind} required>
                    <RadioGroup
                        id="content-kind"
                        name="kind"
                        value={data.kind}
                        aria-invalid={Boolean(errors.kind)}
                        options={[
                            { value: 'help', label: t('contenido_tipo_informacion') },
                            { value: 'article', label: t('contenido_tipo_contenido') },
                        ]}
                        onChange={(value) => {
                            setData({
                                ...data,
                                kind: value,
                                code: value === 'help' ? data.code : '',
                            });
                            if (errors.kind) {
                                clearErrors('kind');
                            }
                            if (value !== 'help' && errors.code) {
                                clearErrors('code');
                            }
                        }}
                    />
                </Field>
                {isHelp ? (
                    <Field
                        label={t('codigo')}
                        htmlFor="content-code"
                        error={errors.code}
                        help={t('contenido_code_hint')}
                        required
                    >
                        <Input
                            id="content-code"
                            value={data.code}
                            maxLength={64}
                            autoComplete="off"
                            aria-invalid={Boolean(errors.code)}
                            placeholder="companies.nif"
                            onChange={(event) => {
                                setData('code', event.target.value);
                                if (errors.code) {
                                    clearErrors('code');
                                }
                            }}
                        />
                    </Field>
                ) : null}
                <FormActions>
                    <Button type="submit" disabled={processing}>
                        {t('continuar')}
                    </Button>
                </FormActions>
            </form>
        </AppLayout>
    );
}
