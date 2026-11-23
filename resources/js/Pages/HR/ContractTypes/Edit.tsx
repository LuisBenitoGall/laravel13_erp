import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { auditRecordSummaryFields } from '@/lib/recordSummary';
import type { ContractTypeRow } from '@/types/h_r';

import ContractTypeForm from './components/ContractTypeForm';

type Props = {
    contract_type: ContractTypeRow;
};

export default function Edit({ contract_type }: Props) {
    const { i18n } = useTranslation();

    const summaryFields = useMemo(
        () =>
            auditRecordSummaryFields({
                created_at:
                    formatDate(contract_type.created_at ?? null, i18n.language) || undefined,
                updated_at:
                    formatDate(contract_type.updated_at ?? null, i18n.language) || undefined,
            }),
        [contract_type.created_at, contract_type.updated_at, i18n.language],
    );

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_contrato',
                suboptionKey: 'editar',
                optionHref: '/hr/contract-types',
            }}
            backHref="/hr/contract-types"
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="edicion_tipo_contrato"
                    titleParams={{ name: contract_type.name }}
                    fields={summaryFields}
                />
                <ContractTypeForm
                    mode="edit"
                    method="put"
                    submitUrl={`/hr/contract-types/${contract_type.id}`}
                    initial={contract_type}
                />
            </div>
        </AppLayout>
    );
}
