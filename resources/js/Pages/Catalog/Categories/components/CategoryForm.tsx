import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, FormHelpScope, Input, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import type { CategoryParentOption, CategoryRow, CategoryStatus } from '@/types/catalog';

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<CategoryRow>;
    parentOptions: CategoryParentOption[];
};

export default function CategoryForm({
    mode,
    submitUrl,
    method,
    initial,
    parentOptions,
}: Props) {
    const { t } = useTranslation();
    const resolvedMethod = method ?? (mode === 'edit' ? 'put' : 'post');

    const form = useForm({
        name: initial?.name ?? '',
        status: (initial?.status ?? 'inactive') as CategoryStatus,
        parent_id: initial?.parent_id != null ? String(initial.parent_id) : '',
    });

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const parentSelectOptions = useMemo(
        () => [{ value: '', label: t('categoria_raiz') }, ...parentOptions],
        [parentOptions, t],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        form.transform((data) => ({
            ...data,
            parent_id: data.parent_id === '' ? null : data.parent_id,
        }));
        if (resolvedMethod === 'put') {
            form.put(submitUrl, { preserveScroll: true });
        } else {
            form.post(submitUrl);
        }
    };

    return (
        <FormHelpScope value="categories">
            <form
                onSubmit={onSubmit}
                className="space-y-4 rounded-xl border border-slate-200 bg-white p-6"
                data-mode={mode}
            >
                <Field
                    label={t('nombre')}
                    htmlFor="category-name"
                    fieldKey="name"
                    error={form.errors.name}
                    required
                >
                    <Input
                        id="category-name"
                        value={form.data.name}
                        maxLength={255}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                </Field>

                {mode === 'edit' && initial?.slug ? (
                    <Field label={t('slug')} htmlFor="category-slug" fieldKey="slug">
                        <Input id="category-slug" value={initial.slug} readOnly disabled />
                    </Field>
                ) : null}

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <Field
                        label={t('categoria_padre')}
                        htmlFor="category-parent"
                        fieldKey="parent_id"
                        error={form.errors.parent_id}
                    >
                        <Select
                            id="category-parent"
                            options={parentSelectOptions}
                            value={form.data.parent_id}
                            onChange={(value) => form.setData('parent_id', value)}
                            placeholder={t('elegir')}
                        />
                    </Field>

                    <Field
                        label={t('estado')}
                        htmlFor="category-status"
                        fieldKey="status"
                        error={form.errors.status}
                    >
                        <Select
                            id="category-status"
                            options={statusOptions}
                            value={form.data.status}
                            onChange={(value) => form.setData('status', value as CategoryStatus)}
                            placeholder={t('elegir')}
                        />
                    </Field>
                </div>

                <FormActions>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => router.visit('/products/categories')}
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
