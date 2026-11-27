import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, FormHelpScope, Input, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { ProductRow, ProductStatus, ProductType } from '@/types/catalog';

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<ProductRow>;
};

export default function ProductForm({ mode, submitUrl, method, initial }: Props) {
    const { t } = useTranslation();
    const resolvedMethod = method ?? (mode === 'edit' ? 'put' : 'post');

    if (mode === 'create') {
        return (
            <CreateFields
                submitUrl={submitUrl}
                method={resolvedMethod}
                initial={initial}
                t={t}
            />
        );
    }

    return (
        <EditFields submitUrl={submitUrl} method={resolvedMethod} initial={initial} t={t} />
    );
}

type TFunction = ReturnType<typeof useTranslation>['t'];

function CreateFields({
    submitUrl,
    method,
    initial,
    t,
}: {
    submitUrl: string;
    method: 'post' | 'put';
    initial?: Partial<ProductRow>;
    t: TFunction;
}) {
    const form = useForm({
        name: initial?.name ?? '',
        type: (initial?.type ?? '') as ProductType | '',
    });

    const typeOptions = useMemo(
        () => [
            { value: 'physical', label: t('producto_fisico') },
            { value: 'service', label: t('servicio') },
        ],
        [t],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (method === 'post') {
            form.post(submitUrl);
        }
    };

    return (
        <FormHelpScope value="products">
            <form
                onSubmit={onSubmit}
                className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                data-mode="create"
            >
                <Field
                    label={t('nombre')}
                    htmlFor="product-name"
                    fieldKey="name"
                    error={form.errors.name}
                    required
                >
                    <Input
                        id="product-name"
                        value={form.data.name}
                        maxLength={255}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                </Field>

                <Field
                    label={t('tipo')}
                    htmlFor="product-type"
                    fieldKey="type"
                    error={form.errors.type}
                    required
                    className="w-full md:w-[30%]"
                >
                    <Select
                        id="product-type"
                        options={typeOptions}
                        value={form.data.type}
                        onChange={(value) => form.setData('type', value as ProductType)}
                        placeholder={t('elegir')}
                    />
                </Field>

                <FormActions>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => router.visit('/products')}
                    >
                        {t('cancelar')}
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            </form>
        </FormHelpScope>
    );
}

function EditFields({
    submitUrl,
    method,
    initial,
    t,
}: {
    submitUrl: string;
    method: 'post' | 'put';
    initial?: Partial<ProductRow>;
    t: TFunction;
}) {
    const form = useForm({
        name: initial?.name ?? '',
        status: (initial?.status ?? 'inactive') as ProductStatus,
    });

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (method === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        }
    };

    return (
        <FormHelpScope value="products">
            <form
                onSubmit={onSubmit}
                className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                data-mode="edit"
            >
                <Field
                    label={t('nombre')}
                    htmlFor="product-name"
                    fieldKey="name"
                    error={form.errors.name}
                    required
                >
                    <Input
                        id="product-name"
                        value={form.data.name}
                        maxLength={255}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                </Field>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <Field
                        label={t('estado')}
                        htmlFor="product-status"
                        fieldKey="status"
                        error={form.errors.status}
                        required
                    >
                        <Select
                            id="product-status"
                            options={statusOptions}
                            value={form.data.status}
                            onChange={(value) => form.setData('status', value as ProductStatus)}
                            placeholder={t('elegir')}
                        />
                    </Field>
                </div>

                <FormActions>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => router.visit('/products')}
                    >
                        {t('cancelar')}
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            </form>
        </FormHelpScope>
    );
}
