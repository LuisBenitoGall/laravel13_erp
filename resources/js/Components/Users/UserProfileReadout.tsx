import { Copy, UserRound } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Button } from '@/Components/ui/button';
import { copyText } from '@/lib/clipboard';
import { formatPhoneDisplay } from '@/lib/format';
import { cn } from '@/lib/utils';

export type UserProfileReadoutContact = {
    value?: string;
    label?: string | null;
    email?: string;
    description?: string | null;
};

export type UserProfileReadoutCompany = {
    id: number;
    name: string;
    /** Reserva para vista RO futura; hoy siempre null/omitido. */
    href?: string | null;
};

export type UserProfileReadoutUser = {
    name: string;
    surname: string | null;
    email: string | null;
};

export type UserProfileReadoutProps = {
    user: UserProfileReadoutUser;
    emails?: UserProfileReadoutContact[];
    phones?: UserProfileReadoutContact[];
    companies?: UserProfileReadoutCompany[];
    avatarUrl?: string | null;
    className?: string;
};

export default function UserProfileReadout({
    user,
    emails = [],
    phones = [],
    companies = [],
    avatarUrl = null,
    className,
}: UserProfileReadoutProps) {
    const { t } = useTranslation();
    const fullName = [user.name, user.surname].filter(Boolean).join(' ');

    async function handleCopyEmail(address: string): Promise<void> {
        const ok = await copyText(address);
        if (ok) {
            toast.success(t('email_copiado'));
        }
    }

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

            <section className="mt-5" data-testid="user-profile-readout-emails">
                <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {t('emails')}
                </h3>
                {emails.length === 0 ? (
                    <p className="mt-2 text-sm text-slate-500">{t('sin_emails')}</p>
                ) : (
                    <ul className="mt-2 space-y-1.5 text-sm text-slate-800">
                        {emails.map((item, index) => {
                            const address = item.email ?? item.value ?? '';

                            return (
                                <li
                                    key={`${address}-${index}`}
                                    className="flex items-center justify-between gap-2"
                                >
                                    <span className="min-w-0 truncate">{address}</span>
                                    <Button
                                        type="button"
                                        size="icon-sm"
                                        variant="ghost"
                                        className="shrink-0"
                                        aria-label={t('copiar')}
                                        onClick={() => {
                                            void handleCopyEmail(address);
                                        }}
                                    >
                                        <Copy className="h-3.5 w-3.5" />
                                    </Button>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </section>

            <section className="mt-4" data-testid="user-profile-readout-phones">
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

            {companies.length > 0 ? (
                <section className="mt-4" data-testid="user-profile-readout-companies">
                    <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {t('empresas_vinculadas')}
                    </h3>
                    <ul className="mt-2 space-y-1 text-sm text-slate-800">
                        {companies.map((company) => (
                            <li key={company.id}>
                                {company.href ? (
                                    <a href={company.href} className="text-brand underline">
                                        {company.name}
                                    </a>
                                ) : (
                                    <span>{company.name}</span>
                                )}
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}
        </aside>
    );
}
