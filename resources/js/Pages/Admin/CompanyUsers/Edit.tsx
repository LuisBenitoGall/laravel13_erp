import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

type Props = {
    user: {
        id: number;
        name: string;
        surname: string | null;
        email: string | null;
    };
};

export default function Edit({ user }: Props) {
    const { t } = useTranslation();
    const fullName = [user.name, user.surname].filter(Boolean).join(' ');

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'usuarios_empresa',
                suboptionKey: 'editar_usuario',
                optionHref: '/admin/company-users',
            }}
            backHref="/admin/company-users"
        >
            <div className="mx-auto max-w-4xl space-y-2 rounded-xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">{fullName || t('usuario')}</h1>
                {user.email ? <p className="text-sm text-slate-600">{user.email}</p> : null}
                <p className="text-sm text-slate-500">{t('edicion_usuario_stub')}</p>
            </div>
        </AppLayout>
    );
}
