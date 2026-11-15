import { FormEvent, useMemo, useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Field, FormActions, Select } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

export type HierarchyCompanyOption = {
    id: number;
    name: string;
};

type Props = {
    companyId: number;
    parent: HierarchyCompanyOption | null;
    children: HierarchyCompanyOption[];
    hierarchyOptions: {
        parents: HierarchyCompanyOption[];
        children: HierarchyCompanyOption[];
    };
};

export default function CompanyHierarchyPanel({
    companyId,
    parent,
    children,
    hierarchyOptions,
}: Props) {
    const { t } = useTranslation();
    const [childToAdd, setChildToAdd] = useState<string>('');

    const parentForm = useForm<{ parent_id: string }>({
        parent_id: parent ? String(parent.id) : '',
    });

    const parentOptions = useMemo(
        () => [
            { value: '', label: t('jerarquia_sin_padre') },
            ...hierarchyOptions.parents.map((option) => ({
                value: String(option.id),
                label: option.name,
            })),
        ],
        [hierarchyOptions.parents, t],
    );

    const childOptions = useMemo(
        () =>
            hierarchyOptions.children.map((option) => ({
                value: String(option.id),
                label: option.name,
            })),
        [hierarchyOptions.children],
    );

    function submitParent(event: FormEvent) {
        event.preventDefault();
        parentForm.put(`/admin/companies/${companyId}/parent`, {
            preserveScroll: true,
        });
    }

    function attachChild(event: FormEvent) {
        event.preventDefault();
        if (childToAdd === '') {
            return;
        }

        router.post(
            `/admin/companies/${companyId}/children`,
            { child_id: Number(childToAdd) },
            {
                preserveScroll: true,
                onSuccess: () => setChildToAdd(''),
            },
        );
    }

    function detachChild(childId: number) {
        if (!window.confirm(t('jerarquia_quitar_hijo_confirm'))) {
            return;
        }

        router.delete(`/admin/companies/${companyId}/children/${childId}`, {
            preserveScroll: true,
        });
    }

    return (
        <div className="grid gap-6 lg:grid-cols-2">
            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="text-sm font-semibold text-slate-900">{t('jerarquia_pertenece')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('jerarquia_padre_ayuda')}</p>
                <form onSubmit={submitParent} className="mt-4 space-y-4">
                    <Field
                        label={t('jerarquia_empresa_padre')}
                        htmlFor="company-parent"
                        error={parentForm.errors.parent_id}
                    >
                        <Select
                            id="company-parent"
                            options={parentOptions}
                            value={parentForm.data.parent_id}
                            onChange={(value) => parentForm.setData('parent_id', value)}
                            placeholder={t('elegir')}
                        />
                    </Field>
                    <FormActions>
                        <Button type="submit" disabled={parentForm.processing}>
                            {t('guardar')}
                        </Button>
                    </FormActions>
                </form>
            </section>

            <section className="rounded-xl border border-slate-200 bg-white p-5">
                <h2 className="text-sm font-semibold text-slate-900">{t('jerarquia_alberga')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('jerarquia_hijos_ayuda')}</p>

                <ul className="mt-4 space-y-2">
                    {children.length === 0 ? (
                        <li className="text-sm text-slate-500">{t('jerarquia_sin_hijos')}</li>
                    ) : (
                        children.map((child) => (
                            <li
                                key={child.id}
                                className="flex items-center justify-between gap-3 rounded-md border border-slate-100 px-3 py-2"
                            >
                                <span className="text-sm text-slate-800">{child.name}</span>
                                <Button
                                    type="button"
                                    variant="secondary"
                                    onClick={() => detachChild(child.id)}
                                >
                                    {t('jerarquia_quitar_hijo')}
                                </Button>
                            </li>
                        ))
                    )}
                </ul>

                <form onSubmit={attachChild} className="mt-4 space-y-4">
                    <Field label={t('jerarquia_anadir_hijo')} htmlFor="company-child-add">
                        <Select
                            id="company-child-add"
                            options={childOptions}
                            value={childToAdd}
                            onChange={setChildToAdd}
                            placeholder={t('elegir')}
                        />
                    </Field>
                    <Button type="submit" disabled={childToAdd === '' || childOptions.length === 0}>
                        {t('jerarquia_anadir_hijo')}
                    </Button>
                </form>
            </section>
        </div>
    );
}
