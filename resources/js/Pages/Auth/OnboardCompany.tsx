import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';

export default function OnboardCompany() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        nif: '',
        name: '',
        tradename: '',
        logo: null as File | null,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/onboarding/company', {
            forceFormData: data.logo !== null,
        });
    }

    return (
        <AuthLayout>
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    {t('tu_empresa')}
                </h1>
                <p className="text-sm text-slate-600">{t('onboarding_empresa_subtitulo')}</p>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="nif">
                        {t('nif')}
                    </label>
                    <Input
                        id="nif"
                        name="nif"
                        maxLength={32}
                        value={data.nif}
                        onChange={(e) => setData('nif', e.target.value)}
                    />
                    {errors.nif && <p className="text-sm text-red-600">{errors.nif}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="name">
                        {t('nombre')}
                    </label>
                    <Input
                        id="name"
                        name="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && <p className="text-sm text-red-600">{errors.name}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="tradename">
                        {t('nombre_comercial')}
                    </label>
                    <Input
                        id="tradename"
                        name="tradename"
                        value={data.tradename}
                        onChange={(e) => setData('tradename', e.target.value)}
                    />
                    {errors.tradename && <p className="text-sm text-red-600">{errors.tradename}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="logo">
                        {t('logo_opcional')}
                    </label>
                    <input
                        id="logo"
                        name="logo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.gif,.webp,.svg"
                        className="w-full text-sm"
                        onChange={(e) => setData('logo', e.target.files?.[0] ?? null)}
                    />
                    {errors.logo && <p className="text-sm text-red-600">{errors.logo}</p>}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    {t('crear_empresa_y_entrar')}
                </button>
            </form>
        </AuthLayout>
    );
}
