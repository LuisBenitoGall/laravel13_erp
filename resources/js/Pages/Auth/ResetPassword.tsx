import { FormEvent } from 'react';
import { useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';

export default function ResetPassword({ email, token }: { email: string; token: string }) {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: '',
        password_confirmation: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/reset-password');
    }

    return (
        <AuthLayout>
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    {t('nueva_contrasena')}
                </h1>

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

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="password">
                        {t('contrasena')}
                    </label>
                    <Input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && <p className="text-sm text-red-600">{errors.password}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="password_confirmation">
                        {t('confirmar')}
                    </label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                    />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    {t('guardar')}
                </button>
            </form>
        </AuthLayout>
    );
}
