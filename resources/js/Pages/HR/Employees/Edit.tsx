import { router } from '@inertiajs/react';
import { RefreshCw } from 'lucide-react';
import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { EmployeeOptions, EmployeeRow } from '@/types/h_r';

import EmployeeForm from './components/EmployeeForm';

type Props = {
    employee: EmployeeRow;
    history: EmployeeRow[];
    options: EmployeeOptions;
};

export default function Edit({ employee, history, options }: Props) {
    const { t, i18n } = useTranslation();

    const summaryFields = useMemo(
        () => [
            { labelKey: 'tipo_de_contrato', value: employee.contract_type_name ?? '—' },
            ...auditRecordSummaryFields({
                created_at: formatDate(employee.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(employee.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [employee.contract_type_name, employee.created_at, employee.updated_at, i18n.language],
    );

    const statusLabel = (status: EmployeeRow['status']): string => {
        if (status === 'active') {
            return t('activo');
        }

        return status === 'scheduled' ? t('programado') : t('finalizado');
    };

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'empleados',
                suboptionKey: 'editar',
                optionHref: '/rrhh/employees',
            }}
            backHref="/rrhh/employees"
            actions={[
                ...(employee.can?.renew
                    ? [
                          {
                              type: 'button' as const,
                              labelKey: 'renovar',
                              icon: RefreshCw,
                              onClick: () => router.visit(`/rrhh/employees/${employee.id}/renew`),
                          },
                      ]
                    : []),
                ...(employee.can?.delete
                    ? [
                          {
                              type: 'delete' as const,
                              onConfirm: () => {
                                  router.delete(`/rrhh/employees/${employee.id}`);
                              },
                          },
                      ]
                    : []),
            ]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_empleo"
                    titleParams={{ name: employee.user_name ?? '' }}
                    fields={summaryFields}
                />
                <EmployeeForm
                    mode="edit"
                    method="put"
                    submitUrl={`/rrhh/employees/${employee.id}`}
                    options={options}
                    initial={employee}
                />

                <section className="rounded-xl border border-slate-200 bg-white p-6">
                    <h3 className="mb-3 text-sm font-semibold text-slate-900">
                        {t('historial_laboral')}
                    </h3>
                    <table className="w-full text-left text-sm" data-testid="employee-history">
                        <thead>
                            <tr className="border-b border-slate-200 text-slate-500">
                                <th className="py-2 pr-4 font-medium">{t('tipo_de_contrato')}</th>
                                <th className="py-2 pr-4 font-medium">{t('fecha_inicio')}</th>
                                <th className="py-2 pr-4 font-medium">{t('fecha_fin')}</th>
                                <th className="py-2 font-medium">{t('estado')}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {history.map((period) => (
                                <tr
                                    key={period.id}
                                    className={
                                        period.id === employee.id
                                            ? 'border-b border-slate-100 bg-slate-50'
                                            : 'border-b border-slate-100'
                                    }
                                >
                                    <td className="py-2 pr-4">
                                        {period.contract_type_name ?? '—'}
                                    </td>
                                    <td className="py-2 pr-4">
                                        {formatDate(period.start_date, i18n.language)}
                                    </td>
                                    <td className="py-2 pr-4">
                                        {formatDate(period.end_date, i18n.language) || '—'}
                                    </td>
                                    <td className="py-2">{statusLabel(period.status)}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </section>
            </div>
        </AppLayout>
    );
}
