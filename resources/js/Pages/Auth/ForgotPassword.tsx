import { FormEvent } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';

export default function ForgotPassword() {
    const { t } = useTranslation();
    const { flash } = usePage().props as { flash?: { success?: string | null } };
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/forgot-password');
    }

    return (
        <AuthLayout>
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    {t('recuperar_contrasena')}
                </h1>
                <p className="text-sm text-slate-600">{t('recuperar_contrasena_subtitulo')}</p>

                {flash?.success && <p className="text-sm text-teal-700">{flash.success}</p>}

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="email">
                        {t('email')}
                    </label>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    {t('enviar_enlace')}
                </button>

                <p className="text-center text-sm">
                    <Link href="/login" className="text-teal-700 underline">
                        {t('volver_al_login')}
                    </Link>
                </p>
            </form>
        </AuthLayout>
    );
}
