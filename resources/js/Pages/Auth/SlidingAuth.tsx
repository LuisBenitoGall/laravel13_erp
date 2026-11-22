import { FormEvent, type ReactNode, useEffect, useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import AuthLayout from '@/Layouts/AuthLayout';
import { cn } from '@/lib/utils';

type AuthMode = 'login' | 'register';

type SlidingAuthProps = {
    mode: AuthMode;
};

type RegisterData = {
    name: string;
    surname: string;
    email: string;
    password: string;
    password_confirmation: string;
    terms: boolean;
};

type RegisterField = readonly [keyof RegisterData & string, string, string];

function syncUrl(next: AuthMode): void {
    const path = next === 'login' ? '/login' : '/register';
    if (window.location.pathname !== path) {
        window.history.replaceState(window.history.state, '', path);
    }
}

export default function SlidingAuth({ mode: initialMode }: SlidingAuthProps) {
    const { t } = useTranslation();
    const [mode, setMode] = useState<AuthMode>(initialMode);
    const isRegister = mode === 'register';

    useEffect(() => {
        setMode(initialMode);
    }, [initialMode]);

    const loginForm = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const registerForm = useForm({
        name: '',
        surname: '',
        email: '',
        password: '',
        password_confirmation: '',
        terms: false as boolean,
    });

    const [showPassword, setShowPassword] = useState(false);

    function switchMode(next: AuthMode): void {
        setMode(next);
        syncUrl(next);
    }

    function submitLogin(e: FormEvent): void {
        e.preventDefault();
        loginForm.post('/login');
    }

    function submitRegister(e: FormEvent): void {
        e.preventDefault();
        registerForm.post('/register');
    }

    const registerFields: readonly RegisterField[] = [
        ['name', t('nombre'), 'text'],
        ['surname', t('apellidos'), 'text'],
        ['email', t('email'), 'email'],
        ['password', t('contrasena'), 'password'],
        ['password_confirmation', t('confirmar_contrasena'), 'password'],
    ];

    const panelTransition = 'transition-transform duration-[600ms] ease-in-out motion-reduce:transition-none';
    const fadeTransition = 'transition-all duration-[600ms] ease-in-out motion-reduce:transition-none';

    return (
        <AuthLayout variant="sliding">
            <div
                data-testid="auth-sliding-panel"
                data-mode={mode}
                className="relative w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-lg shadow-slate-900/5"
            >
                {/* Mobile: sin slide cruzado */}
                <div className="md:hidden">
                    {!isRegister ? (
                        <section className="p-8">
                            <LoginForm
                                idPrefix="m"
                                t={t}
                                form={loginForm}
                                showPassword={showPassword}
                                setShowPassword={setShowPassword}
                                onSubmit={submitLogin}
                                footer={
                                    <div className="space-y-2 border-t border-slate-100 pt-4 text-center">
                                        <p className="text-sm text-slate-600">{t('no_tienes_cuenta')}</p>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="w-full"
                                            onClick={() => switchMode('register')}
                                        >
                                            {t('registrate')}
                                        </Button>
                                    </div>
                                }
                            />
                        </section>
                    ) : (
                        <section className="p-8">
                            <RegisterForm
                                idPrefix="m"
                                t={t}
                                form={registerForm}
                                fields={registerFields}
                                onSubmit={submitRegister}
                                footer={
                                    <div className="space-y-2 border-t border-slate-100 pt-4 text-center">
                                        <p className="text-sm text-slate-600">{t('ya_tienes_cuenta')}</p>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            className="w-full"
                                            onClick={() => switchMode('login')}
                                        >
                                            {t('entrar')}
                                        </Button>
                                    </div>
                                }
                            />
                        </section>
                    )}
                </div>

                {/* Desktop: desplazamiento entrecruzado forms ↔ overlay */}
                <div className="relative hidden min-h-[36rem] md:block">
                    <section
                        aria-hidden={isRegister}
                        inert={isRegister || undefined}
                        className={cn(
                            'absolute inset-y-0 left-0 z-[2] flex w-1/2 flex-col justify-center bg-white px-10 py-8',
                            fadeTransition,
                            isRegister
                                ? 'pointer-events-none translate-x-full opacity-0'
                                : 'translate-x-0 opacity-100',
                        )}
                    >
                        <LoginForm
                            idPrefix="d"
                            t={t}
                            form={loginForm}
                            showPassword={showPassword}
                            setShowPassword={setShowPassword}
                            onSubmit={submitLogin}
                        />
                    </section>

                    <section
                        aria-hidden={!isRegister}
                        inert={!isRegister || undefined}
                        className={cn(
                            'absolute inset-y-0 left-0 z-[1] flex w-1/2 flex-col justify-center overflow-y-auto bg-white px-10 py-8',
                            fadeTransition,
                            isRegister
                                ? 'pointer-events-auto z-[5] translate-x-full opacity-100'
                                : 'pointer-events-none translate-x-0 opacity-0',
                        )}
                    >
                        <RegisterForm
                            idPrefix="d"
                            t={t}
                            form={registerForm}
                            fields={registerFields}
                            onSubmit={submitRegister}
                        />
                    </section>

                    <div
                        className={cn(
                            'absolute inset-y-0 left-1/2 z-[100] w-1/2 overflow-hidden',
                            panelTransition,
                            isRegister ? '-translate-x-full' : 'translate-x-0',
                        )}
                    >
                        <div
                            className={cn(
                                'relative flex h-full w-[200%] bg-[var(--auth-primary)] text-white',
                                panelTransition,
                                isRegister ? 'translate-x-0' : '-translate-x-1/2',
                            )}
                        >
                            <div
                                className={cn(
                                    'flex w-1/2 flex-col items-center justify-center px-10 text-center',
                                    panelTransition,
                                    isRegister ? 'translate-x-0' : '-translate-x-[20%]',
                                )}
                            >
                                <div className="max-w-xs space-y-4">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        {t('auth_sliding_welcome_titulo')}
                                    </h2>
                                    <p className="text-sm text-brand-foreground/95">{t('auth_sliding_welcome_texto')}</p>
                                    <p className="text-sm font-medium">{t('ya_tienes_cuenta')}</p>
                                    <button
                                        type="button"
                                        className="inline-flex h-9 w-full items-center justify-center rounded-md border border-white/40 bg-white/10 px-3 text-sm font-medium text-white transition-colors hover:bg-white/20"
                                        onClick={() => switchMode('login')}
                                    >
                                        {t('entrar')}
                                    </button>
                                </div>
                            </div>

                            <div
                                className={cn(
                                    'flex w-1/2 flex-col items-center justify-center px-10 text-center',
                                    panelTransition,
                                    isRegister ? 'translate-x-[20%]' : 'translate-x-0',
                                )}
                            >
                                <div className="max-w-xs space-y-4">
                                    <h2 className="text-2xl font-semibold tracking-tight">
                                        {t('auth_sliding_invite_titulo')}
                                    </h2>
                                    <p className="text-sm text-brand-foreground/95">{t('auth_sliding_invite_texto')}</p>
                                    <p className="text-sm font-medium">{t('no_tienes_cuenta')}</p>
                                    <button
                                        type="button"
                                        className="inline-flex h-9 w-full items-center justify-center rounded-md border border-white/40 bg-white/10 px-3 text-sm font-medium text-white transition-colors hover:bg-white/20"
                                        onClick={() => switchMode('register')}
                                    >
                                        {t('registrate')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthLayout>
    );
}

type Translate = (key: string) => string;

type LoginFormProps = {
    idPrefix: string;
    t: Translate;
    form: ReturnType<typeof useForm<{ email: string; password: string; remember: boolean }>>;
    showPassword: boolean;
    setShowPassword: (updater: (v: boolean) => boolean) => void;
    onSubmit: (e: FormEvent) => void;
    footer?: ReactNode;
};

function LoginForm({ idPrefix, t, form, showPassword, setShowPassword, onSubmit, footer }: LoginFormProps) {
    return (
        <form onSubmit={onSubmit} className="space-y-4">
            <div>
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">{t('entrar')}</h1>
                <p className="mt-1 text-sm text-slate-600">{t('login_subtitulo')}</p>
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium" htmlFor={`${idPrefix}-login-email`}>
                    {t('email')}
                </label>
                <Input
                    id={`${idPrefix}-login-email`}
                    type="email"
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    autoComplete="username"
                />
                {form.errors.email && <p className="text-sm text-red-600">{form.errors.email}</p>}
            </div>

            <div className="space-y-1">
                <label className="block text-sm font-medium" htmlFor={`${idPrefix}-login-password`}>
                    {t('contrasena')}
                </label>
                <Input
                    id={`${idPrefix}-login-password`}
                    type={showPassword ? 'text' : 'password'}
                    value={form.data.password}
                    onChange={(e) => form.setData('password', e.target.value)}
                    autoComplete="current-password"
                />
                {form.errors.password && <p className="text-sm text-red-600">{form.errors.password}</p>}
            </div>

            <label className="flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    checked={form.data.remember}
                    onChange={(e) => form.setData('remember', e.target.checked)}
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

            <Button type="submit" disabled={form.processing} className="w-full">
                {t('acceder')}
            </Button>

            <p className="text-center text-sm text-slate-600">
                <Link href="/forgot-password" className="text-brand underline">
                    {t('olvidaste_contrasena')}
                </Link>
            </p>

            {footer}
        </form>
    );
}

type RegisterFormProps = {
    idPrefix: string;
    t: Translate;
    form: ReturnType<typeof useForm<RegisterData>>;
    fields: readonly RegisterField[];
    onSubmit: (e: FormEvent) => void;
    footer?: ReactNode;
};

function RegisterForm({ idPrefix, t, form, fields, onSubmit, footer }: RegisterFormProps) {
    return (
        <form onSubmit={onSubmit} className="space-y-3">
            <div>
                <h1 className="text-2xl font-semibold tracking-tight text-slate-900">{t('crear_cuenta')}</h1>
                <p className="mt-1 text-sm text-slate-600">{t('registro_subtitulo')}</p>
            </div>

            {fields.map(([field, label, type]) => (
                <div key={field} className="space-y-1">
                    <label className="block text-sm font-medium" htmlFor={`${idPrefix}-register-${field}`}>
                        {label}
                    </label>
                    <Input
                        id={`${idPrefix}-register-${field}`}
                        type={type}
                        value={form.data[field] as string}
                        onChange={(e) => form.setData(field, e.target.value)}
                    />
                    {form.errors[field] && <p className="text-sm text-red-600">{form.errors[field]}</p>}
                </div>
            ))}

            <label className="flex items-start gap-2 text-sm">
                <input
                    id={`${idPrefix}-terms`}
                    name="terms"
                    type="checkbox"
                    className="mt-1"
                    checked={form.data.terms}
                    onChange={(e) => form.setData('terms', e.target.checked)}
                />
                <span>
                    {t('acepto_el')}{' '}
                    <Link href="/legal/notice" className="text-brand underline">
                        {t('aviso_legal_enlace')}
                    </Link>{' '}
                    {t('y_los')}{' '}
                    <Link href="/legal/terms" className="text-brand underline">
                        {t('auth_sliding_terminos')}
                    </Link>
                </span>
            </label>
            {form.errors.terms && <p className="text-sm text-red-600">{form.errors.terms}</p>}

            <Button type="submit" disabled={form.processing} className="w-full">
                {t('continuar')}
            </Button>

            {footer}
        </form>
    );
}
