import { Building2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

export type CompanyProfileReadoutCompany = {
    name: string;
    tradename: string | null;
    nif: string | null;
    status: string;
    logo_url?: string | null;
};

type Props = {
    company: CompanyProfileReadoutCompany;
    className?: string;
};

const statusLabelKeys: Record<string, string> = {
    draft: 'estado_borrador',
    active: 'estado_activa',
    closed: 'estado_cerrada',
};

export default function CompanyProfileReadout({ company, className }: Props) {
    const { t } = useTranslation();
    const statusKey = statusLabelKeys[company.status] ?? company.status;

    return (
        <aside
            className={cn('rounded-xl border border-slate-200 bg-slate-50/80 p-5', className)}
            data-testid="company-profile-readout"
        >
            <div className="flex flex-col items-center gap-3 border-b border-slate-200 pb-5 text-center">
                <div className="flex h-24 w-40 items-center justify-center overflow-hidden rounded-md border border-slate-200 bg-white">
                    {company.logo_url ? (
                        <img
                            src={company.logo_url}
                            alt=""
                            className="h-full w-full object-contain"
                        />
                    ) : (
                        <Building2 className="h-10 w-10 text-slate-400" aria-hidden />
                    )}
                </div>
                <div>
                    <p className="text-base font-semibold text-slate-900">{company.name}</p>
                    {company.tradename ? (
                        <p className="mt-0.5 text-sm text-slate-600">{company.tradename}</p>
                    ) : null}
                </div>
            </div>

            <dl className="mt-4 space-y-3 text-sm">
                <ReadoutRow label={t('nif')} value={company.nif} />
                <ReadoutRow label={t('estado')} value={t(statusKey)} />
            </dl>
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
