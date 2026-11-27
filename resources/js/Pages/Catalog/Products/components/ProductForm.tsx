import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

import {
    Field,
    FormActions,
    FormHelpScope,
    Input,
    MultiSelect,
    Select,
    type MultiSelectOption,
    type SelectOption,
} from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import { postJson } from '@/lib/http';
import type {
    ClassificationOption,
    ProductRow,
    ProductStatus,
    ProductType,
} from '@/types/catalog';

type Props = {
    mode: 'create' | 'edit';
    submitUrl: string;
    method?: 'post' | 'put';
    initial?: Partial<ProductRow>;
    familyOptions?: ClassificationOption[];
    categoryOptions?: ClassificationOption[];
    tagOptions?: ClassificationOption[];
    canCreateTags?: boolean;
};

export default function ProductForm({
    mode,
    submitUrl,
    method,
    initial,
    familyOptions = [],
    categoryOptions = [],
    tagOptions = [],
    canCreateTags = false,
}: Props) {
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
        <EditFields
            submitUrl={submitUrl}
            method={resolvedMethod}
            initial={initial}
            familyOptions={familyOptions}
            categoryOptions={categoryOptions}
            tagOptions={tagOptions}
            canCreateTags={canCreateTags}
            t={t}
        />
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
    familyOptions,
    categoryOptions,
    tagOptions: initialTagOptions,
    canCreateTags,
    t,
}: {
    submitUrl: string;
    method: 'post' | 'put';
    initial?: Partial<ProductRow>;
    familyOptions: ClassificationOption[];
    categoryOptions: ClassificationOption[];
    tagOptions: ClassificationOption[];
    canCreateTags: boolean;
    t: TFunction;
}) {
    const [tagOptions, setTagOptions] = useState<MultiSelectOption[]>(initialTagOptions);
    const [creatingTag, setCreatingTag] = useState(false);

    const form = useForm({
        name: initial?.name ?? '',
        status: (initial?.status ?? 'inactive') as ProductStatus,
        family_id: initial?.family_id != null ? String(initial.family_id) : '',
        category_ids: (initial?.category_ids ?? []).map(String),
        tag_ids: (initial?.tag_ids ?? []).map(String),
    });

    const statusOptions = useMemo(
        () => [
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const familySelectOptions = useMemo<SelectOption[]>(
        () => [{ value: '', label: t('sin_familia') }, ...familyOptions],
        [familyOptions, t],
    );

    const onSubmit = (event: FormEvent) => {
        event.preventDefault();
        if (method !== 'put') {
            return;
        }

        // transform no encadena: no retorna el form (Inertia useForm).
        // Arrays vacíos se omiten en FormData → enviar '' para clear explícito.
        form.transform((data) => ({
            ...data,
            family_id: data.family_id === '' ? null : Number(data.family_id),
            category_ids:
                data.category_ids.length > 0 ? data.category_ids.map(Number) : '',
            tag_ids: data.tag_ids.length > 0 ? data.tag_ids.map(Number) : '',
        }));
        form.put(submitUrl, { preserveScroll: true });
    };

    const handleCreateTag = async (name: string): Promise<MultiSelectOption | null> => {
        setCreatingTag(true);
        try {
            const created = await postJson<{
                id: number;
                name: string;
                slug: string;
                status: string;
            }>('/products/tags/quick', { name });

            const option = { value: String(created.id), label: created.name };
            setTagOptions((prev) =>
                prev.some((o) => o.value === option.value) ? prev : [...prev, option],
            );

            return option;
        } catch {
            return null;
        } finally {
            setCreatingTag(false);
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

                    <Field
                        label={t('familia')}
                        htmlFor="product-family"
                        fieldKey="family_id"
                        error={form.errors.family_id}
                    >
                        <Select
                            id="product-family"
                            options={familySelectOptions}
                            value={form.data.family_id}
                            onChange={(value) => form.setData('family_id', value)}
                            placeholder={t('elegir')}
                        />
                    </Field>
                </div>

                <Field
                    label={t('categorias')}
                    htmlFor="product-categories"
                    fieldKey="category_ids"
                    error={form.errors.category_ids}
                >
                    <MultiSelect
                        id="product-categories"
                        options={categoryOptions}
                        value={form.data.category_ids}
                        onChange={(ids) => form.setData('category_ids', ids)}
                        placeholder={t('elegir')}
                        searchPlaceholder={t('buscar')}
                        emptyLabel={t('sin_datos')}
                    />
                </Field>

                <Field
                    label={t('etiquetas')}
                    htmlFor="product-tags"
                    fieldKey="tag_ids"
                    error={form.errors.tag_ids}
                >
                    <MultiSelect
                        id="product-tags"
                        options={tagOptions}
                        value={form.data.tag_ids}
                        onChange={(ids) => form.setData('tag_ids', ids)}
                        placeholder={t('elegir')}
                        searchPlaceholder={t('buscar')}
                        emptyLabel={t('sin_datos')}
                        creatable={canCreateTags}
                        creating={creatingTag}
                        createLabel={(query) => t('crear_etiqueta_nombre', { name: query })}
                        onCreate={canCreateTags ? handleCreateTag : undefined}
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
