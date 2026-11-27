import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { DayTypeRow } from '@/types/calendar';

import DayTypeForm from './components/DayTypeForm';

type Props = {
    day_type: DayTypeRow;
};

export default function Edit({ day_type }: Props) {
    const { i18n } = useTranslation();

    const summaryFields = useMemo(
        () =>
            auditRecordSummaryFields({
                created_at: formatDate(day_type.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(day_type.updated_at ?? null, i18n.language) || undefined,
            }),
        [day_type.created_at, day_type.updated_at, i18n.language],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_jornada',
                suboptionKey: 'editar',
                optionHref: '/rrhh/day-types',
            }}
            backHref="/rrhh/day-types"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_tipo_jornada"
                    titleParams={{ name: day_type.name }}
                    fields={summaryFields}
                />
                <DayTypeForm
                    mode="edit"
                    method="put"
                    submitUrl={`/rrhh/day-types/${day_type.id}`}
                    initial={day_type}
                />
            </div>
        </AppLayout>
    );
}
