import { FormEvent, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';

export default function Login() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const [showPassword, setShowPassword] = useState(false);

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/login');
    }

    return (
        <AuthLayout>
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    {t('entrar')}
                </h1>
                <p className="text-sm text-slate-600">{t('login_subtitulo')}</p>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="email">
                        {t('email')}
                    </label>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        autoComplete="username"
                    />
                    {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="password">
                        {t('contrasena')}
                    </label>
                    <Input
                        id="password"
                        type={showPassword ? 'text' : 'password'}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="current-password"
                    />
                    {errors.password && <p className="text-sm text-red-600">{errors.password}</p>}
                </div>

                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                    />
                    {t('recordarme')}
                </label>

                <button
                    type="button"
                    className="text-xs text-slate-500 underline"
                    onClick={() => setShowPassword((v) => !v)}
                >
                    {showPassword ? t('ocultar_contrasena') : t('mostrar_contrasena')}
                </button>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    {t('acceder')}
                </button>

                <div className="space-y-1 text-center text-sm text-slate-600">
                    <p>
                        <Link href="/forgot-password" className="text-teal-700 underline">
                            {t('olvidaste_contrasena')}
                        </Link>
                    </p>
                    <p>
                        {t('no_tienes_cuenta')}{' '}
                        <Link href="/register" className="text-teal-700 underline">
                            {t('registrate')}
                        </Link>
                    </p>
                </div>
            </form>
        </AuthLayout>
    );
}
