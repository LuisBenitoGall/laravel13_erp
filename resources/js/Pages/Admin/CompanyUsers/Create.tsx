import { FormEvent } from 'react';
import { router, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Checkbox, Field, Input, Select, Switch } from '@/Components/Form';
import { Button } from '@/Components/ui/button';
import AppLayout from '@/Layouts/AppLayout';

type RoleOption = {
    value: string;
    label: string;
};

type Props = {
    roleOptions: RoleOption[];
};

export default function Create({ roleOptions }: Props) {
    const { t } = useTranslation();
    const { data, setData, processing, errors } = useForm({
        name: '',
        surname: '',
        email: '',
        status: true,
        is_public: true,
        send_credentials: true,
        role: roleOptions[0]?.value ?? '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        router.post('/admin/company-users', {
            name: data.name,
            surname: data.surname || null,
            email: data.email,
            status: data.status,
            is_public: data.is_public,
            send_credentials: data.send_credentials,
            role: data.role,
        });
    }

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'usuarios_empresa',
                suboptionKey: 'crear_usuario',
                optionHref: '/admin/company-users',
            }}
            backHref="/admin/company-users"
        >
            <form
                onSubmit={submit}
                className="mx-auto max-w-4xl space-y-6 rounded-xl border border-slate-200 bg-white p-6"
            >
                <div className="grid gap-4 md:grid-cols-2">
                    <Field label={t('nombre')} htmlFor="name" required error={errors.name}>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            maxLength={255}
                            autoComplete="given-name"
                        />
                    </Field>
                    <Field label={t('apellidos')} htmlFor="surname" error={errors.surname}>
                        <Input
                            id="surname"
                            value={data.surname}
                            onChange={(e) => setData('surname', e.target.value)}
                            maxLength={255}
                            autoComplete="family-name"
                        />
                    </Field>
                </div>

                <Field label={t('email')} htmlFor="email" required error={errors.email}>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        maxLength={255}
                        autoComplete="email"
                    />
                </Field>

                <Field label={t('rol')} htmlFor="role" required error={errors.role}>
                    <Select
                        id="role"
                        options={roleOptions}
                        value={data.role}
                        onChange={(value) => setData('role', value)}
                        placeholder={t('seleccionar_rol')}
                    />
                </Field>

                <div className="flex flex-row flex-wrap items-center gap-x-8 gap-y-3">
                    <Switch
                        id="status"
                        checked={data.status}
                        onCheckedChange={(checked) => setData('status', checked)}
                        label={t('activo')}
                    />
                    <Checkbox
                        id="send_credentials"
                        checked={data.send_credentials}
                        onCheckedChange={(checked) => setData('send_credentials', checked)}
                        label={t('enviar_credenciales')}
                    />
                    <Checkbox
                        id="is_public"
                        checked={data.is_public}
                        onCheckedChange={(checked) => setData('is_public', checked)}
                        label={t('perfil_publico')}
                    />
                </div>

                <div className="flex items-center justify-end gap-2">
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={() => router.visit('/admin/company-users')}
                    >
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
