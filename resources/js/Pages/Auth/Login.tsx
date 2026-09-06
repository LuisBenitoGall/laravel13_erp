import { FormEvent, useState } from 'react';
import { useForm } from '@inertiajs/react';

export default function Login() {
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
        <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
            <form
                onSubmit={submit}
                className="w-full max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-8 shadow-sm"
            >
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">Entrar</h1>
                <p className="text-sm text-slate-600">Sesión Laravel — sin registro público.</p>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="email">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        className="w-full rounded-md border border-slate-300 px-3 py-2"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        autoComplete="username"
                    />
                    {errors.email && <p className="text-sm text-red-600">{errors.email}</p>}
                </div>

                <div className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor="password">
                        Contraseña
                    </label>
                    <input
                        id="password"
                        type={showPassword ? 'text' : 'password'}
                        className="w-full rounded-md border border-slate-300 px-3 py-2"
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
                    Recordarme
                </label>

                <button
                    type="button"
                    className="text-xs text-slate-500 underline"
                    onClick={() => setShowPassword((v) => !v)}
                >
                    {showPassword ? 'Ocultar' : 'Mostrar'} contraseña
                </button>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 disabled:opacity-50"
                >
                    Acceder
                </button>
            </form>
        </div>
    );
}
