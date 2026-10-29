import type { ReactNode } from 'react';
import { useTranslation } from 'react-i18next';

import { cn } from '@/lib/utils';

export type RecordSummaryFieldTone = 'default' | 'muted' | 'status';

export type RecordSummaryField = {
    labelKey: string;
    value: ReactNode;
    tone?: RecordSummaryFieldTone;
};

export type RecordSummaryProps = {
    /** Clave i18n del tipo de documento/entidad (p. ej. pedido_venta). */
    titleKey: string;
    /** Identificador visible ya formateado (número, slug…). */
    identifier: string;
    fields: RecordSummaryField[];
};

const valueToneClass: Record<RecordSummaryFieldTone, string> = {
    default: 'text-slate-900',
    muted: 'text-slate-500',
    status: 'font-medium text-amber-700',
};

export default function RecordSummary({ titleKey, identifier, fields }: RecordSummaryProps) {
    const { t } = useTranslation();

    return (
        <section
            className="mb-4 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm"
            data-testid="record-summary"
        >
            <div className="mb-3 flex flex-wrap items-center gap-2">
                <h2 className="text-base font-semibold text-slate-900">{t(titleKey)}</h2>
                <span
                    className="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800"
                    data-testid="record-summary-identifier"
                >
                    {identifier}
                </span>
            </div>

            {fields.length > 0 ? (
                <dl className="flex flex-wrap gap-x-6 gap-y-2">
                    {fields.map((field, index) => (
                        <div
                            key={`${field.labelKey}-${index}`}
                            className="flex min-w-0 items-baseline gap-1.5 text-sm"
                        >
                            <dt className="shrink-0 text-slate-500">{t(field.labelKey)}:</dt>
                            <dd className={cn(valueToneClass[field.tone ?? 'default'])}>
                                {field.value}
                            </dd>
                        </div>
                    ))}
                </dl>
            ) : null}
        </section>
    );
}
