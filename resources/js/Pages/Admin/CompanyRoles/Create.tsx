import { FormEvent } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import RoleMetadataFields from '@/Pages/Admin/Roles/components/RoleMetadataFields';

export default function Create() {
    const { t } = useTranslation();
    const { data, setData, processing, errors } = useForm({
        name: '',
        description: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        router.post('/company/roles', {
            name: data.name,
            description: data.description,
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles_de_empresa',
                suboptionKey: 'rol_empresa_crear',
                optionHref: '/company/roles',
            }}
            backHref="/company/roles"
        >
            <form
                onSubmit={submit}
                className="mx-auto max-w-4xl space-y-6 rounded-xl border border-slate-200 bg-white p-6"
            >
                <RoleMetadataFields
                    values={data}
                    errors={errors}
                    showUniversal={false}
                    onNameChange={(value) => setData('name', value)}
                    onDescriptionChange={(value) => setData('description', value)}
                />

                <div className="flex items-center justify-end gap-2">
                    <Button type="button" variant="secondary" onClick={() => router.visit('/company/roles')}>
                        {t('cancelar')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('guardar')}
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
