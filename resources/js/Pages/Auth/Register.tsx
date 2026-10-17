import { FormEvent } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';

export default function Register() {
    const { t } = useTranslation();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        surname: '',
        email: '',
        password: '',
        password_confirmation: '',
        terms: false as boolean,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/register');
    }

    const fields = [
        ['name', t('nombre'), 'text'],
        ['surname', t('apellidos'), 'text'],
        ['email', t('email'), 'email'],
        ['password', t('contrasena'), 'password'],
        ['password_confirmation', t('confirmar_contrasena'), 'password'],
    ] as const;

    return (
        <AuthLayout>
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">
                    {t('crear_cuenta')}
                </h1>
                <p className="text-sm text-slate-600">{t('registro_subtitulo')}</p>

                {fields.map(([field, label, type]) => (
                    <div key={field} className="space-y-1">
                        <label className="block text-sm font-medium" htmlFor={field}>
                            {label}
                        </label>
                        <Input
                            id={field}
                            type={type}
                            value={data[field]}
                            onChange={(e) => setData(field, e.target.value)}
                        />
                        {errors[field] && <p className="text-sm text-red-600">{errors[field]}</p>}
                    </div>
                ))}

                <label className="flex items-start gap-2 text-sm">
                    <input
                        id="terms"
                        name="terms"
                        type="checkbox"
                        className="mt-1"
                        checked={data.terms}
                        onChange={(e) => setData('terms', e.target.checked)}
                    />
                    <span>
                        {t('acepto_el')}{' '}
                        <Link href="/legal/notice" className="text-teal-700 underline">
                            {t('aviso_legal_enlace')}
                        </Link>{' '}
                        {t('y_los')}{' '}
                        <Link href="/legal/terms" className="text-teal-700 underline">
                            {t('terminos')}
                        </Link>
                        .
                    </span>
                </label>
                {errors.terms && <p className="text-sm text-red-600">{errors.terms}</p>}

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    {t('continuar')}
                </button>

                <p className="text-center text-sm text-slate-600">
                    {t('ya_tienes_cuenta')}{' '}
                    <Link href="/login" className="text-teal-700 underline">
                        {t('entrar')}
                    </Link>
                </p>
            </form>
        </AuthLayout>
    );
}
