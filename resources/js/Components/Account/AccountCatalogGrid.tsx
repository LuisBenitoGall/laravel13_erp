import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

export type AccountCatalogOption = {
    id: number;
    name: string;
    rate: string | number;
    duration: number | null;
    is_default: boolean;
};

type Props = {
    accounts: AccountCatalogOption[];
    value: number | string | null;
    onChange: (accountId: number) => void;
    disabled?: boolean;
};

export function AccountCatalogGrid({ accounts, value, onChange, disabled = false }: Props) {
    const { t } = useTranslation();
    const selectedId = value === null || value === '' ? null : Number(value);

    if (accounts.length === 0) {
        return <p className="text-sm text-slate-600">{t('cuenta_catalogo_vacio')}</p>;
    }

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" role="listbox" aria-label={t('cuentas')}>
            {accounts.map((account) => {
                const selected = selectedId === account.id;

                return (
                    <button
                        key={account.id}
                        type="button"
                        role="option"
                        aria-selected={selected}
                        disabled={disabled}
                        onClick={() => onChange(account.id)}
                        className={cn(
                            'rounded-xl border p-4 text-left transition',
                            selected
                                ? 'border-brand bg-brand-muted ring-2 ring-brand/30'
                                : 'border-slate-200 bg-white hover:border-slate-300',
                            disabled && 'cursor-not-allowed opacity-60',
                        )}
                    >
                        <div className="flex items-start justify-between gap-2">
                            <span className="font-medium text-slate-900">{account.name}</span>
                            {account.is_default ? (
                                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                    {t('por_defecto')}
                                </span>
                            ) : null}
                        </div>
                        <dl className="mt-3 space-y-1 text-sm text-slate-600">
                            <div className="flex justify-between gap-2">
                                <dt>{t('tarifa')}</dt>
                                <dd className="font-medium text-slate-800">{account.rate}</dd>
                            </div>
                            <div className="flex justify-between gap-2">
                                <dt>{t('duracion_dias')}</dt>
                                <dd className="font-medium text-slate-800">
                                    {account.duration ?? t('sin_datos')}
                                </dd>
                            </div>
                        </dl>
                    </button>
                );
            })}
        </div>
    );
}
