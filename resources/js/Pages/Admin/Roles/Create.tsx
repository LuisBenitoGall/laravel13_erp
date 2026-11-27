import { FormEvent } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { FormActions } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';
import RoleMetadataFields from './components/RoleMetadataFields';

export default function Create() {
    const { t } = useTranslation();
    const { data, setData, processing, errors } = useForm({
        name: '',
        description: '',
        universal: false,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        router.post('/settings/roles', {
            name: data.name,
            description: data.description,
            universal: data.universal,
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'roles',
                suboptionKey: 'rol_crear',
                optionHref: '/settings/roles',
            }}
            backHref="/settings/roles"
        >
            <form
                onSubmit={submit}
                className="mx-auto max-w-4xl space-y-6 rounded-xl border border-slate-200 bg-white p-6"
            >
                <RoleMetadataFields
                    values={data}
                    errors={errors}
                    onNameChange={(value) => setData('name', value)}
                    onDescriptionChange={(value) => setData('description', value)}
                    onUniversalChange={(value) => setData('universal', value)}
                />

                <FormActions>
                    <Button type="button" variant="secondary" onClick={() => router.visit('/settings/roles')}>
                        {t('cancelar')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('guardar')}
                    </Button>
                </FormActions>
            </form>
        </AppLayout>
    );
}
