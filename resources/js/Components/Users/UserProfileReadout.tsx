import { UserRound } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { formatPhoneDisplay } from '@/lib/format';
import { cn } from '@/lib/utils';

export type UserProfileReadoutContact = {
    value?: string;
    label?: string | null;
    email?: string;
    description?: string | null;
};

export type UserProfileReadoutUser = {
    name: string;
    surname: string | null;
    email: string | null;
    nif: string | null;
    birth_date: string | null;
    status: boolean;
    is_public: boolean;
};

export type UserProfileReadoutProps = {
    user: UserProfileReadoutUser;
    emails?: UserProfileReadoutContact[];
    phones?: UserProfileReadoutContact[];
    avatarUrl?: string | null;
    birthDateFormatted?: string | null;
    className?: string;
};

export default function UserProfileReadout({
    user,
    emails = [],
    phones = [],
    avatarUrl = null,
    birthDateFormatted,
    className,
}: UserProfileReadoutProps) {
    const { t } = useTranslation();
    const fullName = [user.name, user.surname].filter(Boolean).join(' ');

    return (
        <aside
            className={cn('rounded-xl border border-slate-200 bg-slate-50/80 p-5', className)}
            data-testid="user-profile-readout"
        >
            <div className="flex flex-col items-center gap-3 border-b border-slate-200 pb-5 text-center">
                {avatarUrl ? (
                    <img
                        src={avatarUrl}
                        alt=""
                        className="h-20 w-20 rounded-full object-cover"
                    />
                ) : (
                    <div
                        className="flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-slate-500"
                        aria-hidden
                    >
                        <UserRound className="h-10 w-10" />
                    </div>
                )}
                <div>
                    <p className="text-base font-semibold text-slate-900">{fullName || t('usuario')}</p>
                    {user.email ? <p className="mt-0.5 text-sm text-slate-600">{user.email}</p> : null}
                </div>
            </div>

            <dl className="mt-4 space-y-3 text-sm">
                <ReadoutRow label={t('nif')} value={user.nif} />
                <ReadoutRow
                    label={t('fecha_nacimiento')}
                    value={birthDateFormatted || user.birth_date}
                />
                <ReadoutRow
                    label={t('activo')}
                    value={user.status ? t('si') : t('no')}
                />
                <ReadoutRow
                    label={t('perfil_publico')}
                    value={user.is_public ? t('si') : t('no')}
                />
            </dl>

            <section className="mt-5 border-t border-slate-200 pt-4">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {t('emails')}
                </h3>
                {emails.length === 0 ? (
                    <p className="mt-2 text-sm text-slate-500">{t('sin_emails')}</p>
                ) : (
                    <ul className="mt-2 space-y-1 text-sm text-slate-800">
                        {emails.map((item, index) => {
                            const address = item.email ?? item.value ?? '';
                            const note = item.description ?? item.label;

                            return (
                                <li key={`${address}-${index}`}>
                                    {note ? `${note}: ${address}` : address}
                                </li>
                            );
                        })}
                    </ul>
                )}
            </section>

            <section className="mt-4">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {t('telefonos')}
                </h3>
                {phones.length === 0 ? (
                    <p className="mt-2 text-sm text-slate-500">{t('sin_telefonos')}</p>
                ) : (
                    <ul className="mt-2 space-y-1 text-sm text-slate-800">
                        {phones.map((item) => {
                            const formatted = formatPhoneDisplay(item.value);

                            return (
                                <li key={item.value}>
                                    {item.label ? `${item.label}: ${formatted}` : formatted}
                                </li>
                            );
                        })}
                    </ul>
                )}
            </section>
        </aside>
    );
}

function ReadoutRow({ label, value }: { label: string; value?: string | null }) {
    return (
        <div className="flex flex-col gap-0.5">
            <dt className="text-xs font-medium uppercase tracking-wide text-slate-500">{label}</dt>
            <dd className="text-slate-900">{value && value !== '' ? value : '—'}</dd>
        </div>
    );
}
