import { Link, router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

type Member = {
    id: number;
    name: string;
    email: string;
    role: 'manager' | 'guest' | null;
};

export default function Members({
    company,
    members,
}: {
    company: { id: number; name: string; slug: string };
    members: Member[];
}) {
    const { t } = useTranslation();

    return (
        <AppLayout title={`${t('miembros')} · ${company.name}`}>
            <div className="mb-4 flex items-center justify-between">
                <p className="text-sm text-slate-600">{t('miembros_subtitulo')}</p>
                <Link
                    href={`/partners/companies/${company.id}/edit`}
                    className="text-sm text-teal-700 hover:underline"
                >
                    {t('volver_a_empresa')}
                </Link>
            </div>

            <div className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-2 font-medium">
                                {t('nombre')}
                            </th>
                            <th className="px-4 py-2 font-medium">
                                {t('email')}
                            </th>
                            <th className="px-4 py-2 font-medium">
                                {t('rol')}
                            </th>
                            <th className="px-4 py-2 font-medium">
                                {t('accion')}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {members.map((member) => (
                            <tr key={member.id} className="border-t border-slate-100">
                                <td className="px-4 py-2">{member.name}</td>
                                <td className="px-4 py-2 text-slate-600">{member.email}</td>
                                <td className="px-4 py-2">
                                    {member.role === 'manager'
                                        ? t('manager')
                                        : member.role === 'guest'
                                          ? t('invitado')
                                          : '—'}
                                </td>
                                <td className="px-4 py-2">
                                    <select
                                        className="rounded-md border border-slate-300 px-2 py-1"
                                        value={member.role ?? ''}
                                        onChange={(e) => {
                                            const role = e.target.value;
                                            if (!role) {
                                                return;
                                            }
                                            router.put(
                                                `/partners/companies/${company.id}/members/${member.id}/role`,
                                                { role },
                                            );
                                        }}
                                    >
                                        <option value="" disabled>
                                            {t('elegir')}
                                        </option>
                                        <option value="manager">
                                            {t('manager')}
                                        </option>
                                        <option value="guest">
                                            {t('invitado')}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}
