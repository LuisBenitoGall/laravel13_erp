import { router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Field, Input, Select } from '@/Components/Form';
import AdvancedSearchModal from '@/Components/Table/AdvancedSearchModal';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import type { TableFilter } from '@/Components/Table/tableFilters';
import AppLayout from '@/Layouts/AppLayout';
import type { ClassificationOption, ProductRow } from '@/types/catalog';

type Props = {
    family_options: ClassificationOption[];
    category_options: ClassificationOption[];
    tag_options: ClassificationOption[];
};

function upsertFilter(
    filters: TableFilter[],
    field: string,
    type: string,
    value: string,
): TableFilter[] {
    const without = filters.filter((f) => f.field !== field);
    if (value === '') {
        return without;
    }

    return [...without, { field, type, value }];
}

function filterValue(filters: TableFilter[], field: string): string {
    const found = filters.find((f) => f.field === field);

    return found?.value != null ? String(found.value) : '';
}

export default function Index({
    family_options = [],
    category_options = [],
    tag_options = [],
}: Props) {
    const { t } = useTranslation();
    const [tableKey, setTableKey] = useState(0);
    const [advancedOpen, setAdvancedOpen] = useState(false);
    const [advancedFilters, setAdvancedFilters] = useState<TableFilter[] | undefined>(
        undefined,
    );

    const typeOptions = useMemo(
        () => [
            { value: '', label: t('elegir') },
            { value: 'physical', label: t('producto_fisico') },
            { value: 'service', label: t('servicio') },
        ],
        [t],
    );

    const statusOptions = useMemo(
        () => [
            { value: '', label: t('elegir') },
            { value: 'active', label: t('activo') },
            { value: 'inactive', label: t('inactivo') },
        ],
        [t],
    );

    const familySelectOptions = useMemo(
        () => [{ value: '', label: t('elegir') }, ...family_options],
        [family_options, t],
    );

    const categorySelectOptions = useMemo(
        () => [{ value: '', label: t('elegir') }, ...category_options],
        [category_options, t],
    );

    const tagSelectOptions = useMemo(
        () => [{ value: '', label: t('elegir') }, ...tag_options],
        [tag_options, t],
    );

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('tipo'),
                field: 'type',
                width: 140,
                formatter: (cell) => {
                    const value = cell.getValue();

                    return value === 'service' ? t('servicio') : t('producto_fisico');
                },
            },
            {
                title: t('estado'),
                field: 'status',
                width: 120,
                formatter: (cell) => (cell.getValue() === 'active' ? t('activo') : t('inactivo')),
            },
        ],
        [t],
    );

    const rowActions = (): Array<RowAction<ProductRow>> => [
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) => router.visit(`/products/${current.id}/edit`),
        },
        {
            id: 'delete',
            labelKey: 'eliminar',
            variant: 'danger',
            confirm: true,
            onClick: (current) => {
                router.delete(`/products/${current.id}`, {
                    preserveScroll: true,
                    onSuccess: () => setTableKey((key) => key + 1),
                    onError: (errors) => {
                        const message = Object.values(errors)[0];
                        if (message) {
                            toast.error(String(message));
                        }
                    },
                });
            },
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'catalogo',
                suboptionKey: 'dashboard',
                optionHref: '/products',
            }}
            showBack={false}
            actions={[
                {
                    type: 'button',
                    labelKey: 'producto_nuevo',
                    icon: Plus,
                    onClick: () => router.visit('/products/create'),
                },
            ]}
        >
            <ServerTable<ProductRow>
                key={tableKey}
                endpoint="/products/table"
                columns={columns}
                persistKey="catalog.products"
                exportable={false}
                rowActions={rowActions}
                advancedFilterMode="merge"
                advancedFilters={advancedFilters}
                onOpenAdvancedSearch={() => setAdvancedOpen(true)}
            />

            <AdvancedSearchModal
                open={advancedOpen}
                onOpenChange={setAdvancedOpen}
                tableKey="catalog.products"
                seedFilters={advancedFilters}
                onApply={(filters) => setAdvancedFilters(filters)}
                onClear={() => setAdvancedFilters(undefined)}
                fields={({ filters, setFilters }) => (
                    <div className="grid gap-3">
                        <Field label={t('nombre')} htmlFor="adv-product-name" fieldKey="name">
                            <Input
                                id="adv-product-name"
                                value={filterValue(filters, 'name')}
                                maxLength={255}
                                onChange={(e) =>
                                    setFilters(
                                        upsertFilter(filters, 'name', 'like', e.target.value),
                                    )
                                }
                            />
                        </Field>
                        <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <Field label={t('tipo')} htmlFor="adv-product-type" fieldKey="type">
                                <Select
                                    id="adv-product-type"
                                    options={typeOptions}
                                    value={filterValue(filters, 'type')}
                                    onChange={(value) =>
                                        setFilters(upsertFilter(filters, 'type', 'eq', value))
                                    }
                                    placeholder={t('elegir')}
                                />
                            </Field>
                            <Field
                                label={t('estado')}
                                htmlFor="adv-product-status"
                                fieldKey="status"
                            >
                                <Select
                                    id="adv-product-status"
                                    options={statusOptions}
                                    value={filterValue(filters, 'status')}
                                    onChange={(value) =>
                                        setFilters(upsertFilter(filters, 'status', 'eq', value))
                                    }
                                    placeholder={t('elegir')}
                                />
                            </Field>
                        </div>
                        <Field
                            label={t('familia')}
                            htmlFor="adv-product-family"
                            fieldKey="family_id"
                        >
                            <Select
                                id="adv-product-family"
                                options={familySelectOptions}
                                value={filterValue(filters, 'family_id')}
                                onChange={(value) =>
                                    setFilters(upsertFilter(filters, 'family_id', 'eq', value))
                                }
                                placeholder={t('elegir')}
                            />
                        </Field>
                        <Field
                            label={t('categorias')}
                            htmlFor="adv-product-category"
                            fieldKey="category_id"
                        >
                            <Select
                                id="adv-product-category"
                                options={categorySelectOptions}
                                value={filterValue(filters, 'category_id')}
                                onChange={(value) =>
                                    setFilters(upsertFilter(filters, 'category_id', 'eq', value))
                                }
                                placeholder={t('elegir')}
                            />
                        </Field>
                        <Field
                            label={t('etiquetas')}
                            htmlFor="adv-product-tag"
                            fieldKey="tag_id"
                        >
                            <Select
                                id="adv-product-tag"
                                options={tagSelectOptions}
                                value={filterValue(filters, 'tag_id')}
                                onChange={(value) =>
                                    setFilters(upsertFilter(filters, 'tag_id', 'eq', value))
                                }
                                placeholder={t('elegir')}
                            />
                        </Field>
                    </div>
                )}
            />
        </AppLayout>
    );
}
