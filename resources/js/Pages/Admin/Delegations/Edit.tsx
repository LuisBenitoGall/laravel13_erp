import { router } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import RecordEditLayout from '@/Components/Records/RecordEditLayout';
import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import { formatDate } from '@/lib/format';
import { patchJson } from '@/lib/http';
import { auditRecordSummaryFields } from '@/lib/recordSummary';

import DelegationForm from './components/DelegationForm';
import MatrixWorkplacesLinkedPanel, {
    type LinkedWorkplace,
} from './components/MatrixWorkplacesLinkedPanel';

type Props = {
    delegation: {
        id: number;
        name: string;
        tradename: string | null;
        nif: string | null;
        status: string;
        created_at?: string | null;
        updated_at?: string | null;
    };
    matrix: { id: number; name: string };
    linked_workplaces: LinkedWorkplace[];
};

export default function Edit({ delegation, matrix, linked_workplaces }: Props) {
    const { t, i18n } = useTranslation();
    const [tab, setTab] = useState('info');
    const [status, setStatus] = useState(delegation.status);

    const summaryFields = useMemo(
        () => [
            { labelKey: 'delegacion_matriz', value: matrix.name },
            ...auditRecordSummaryFields({
                created_at: formatDate(delegation.created_at ?? null, i18n.language) || undefined,
                updated_at: formatDate(delegation.updated_at ?? null, i18n.language) || undefined,
            }),
        ],
        [delegation.created_at, delegation.updated_at, i18n.language, matrix.name],
    );

    async function toggleStatus(checked: boolean) {
        const next = checked ? 'active' : 'closed';
        const response = await patchJson<{ status: string }>(
            `/admin/delegations/${delegation.id}/status`,
            { status: next },
        );
        setStatus(response.status);
    }

    const tabs = [
        {
            id: 'info',
            labelKey: 'informacion',
            panel: (
                <DelegationForm
                    mode="edit"
                    method="put"
                    submitUrl={`/admin/delegations/${delegation.id}`}
                    statusChecked={status === 'active'}
                    onStatusToggle={async (checked) => {
                        try {
                            await toggleStatus(checked);
                        } catch (error) {
                            toast.error(
                                error instanceof Error ? error.message : t('error_carga'),
                            );
                            throw error;
                        }
                    }}
                    initial={{
                        name: delegation.name,
                        tradename: delegation.tradename ?? '',
                        nif: delegation.nif ?? '',
                        status,
                    }}
                />
            ),
        },
        {
            id: 'workplaces',
            labelKey: 'centros_de_trabajo',
            panel: <MatrixWorkplacesLinkedPanel workplaces={linked_workplaces} />,
        },
    ];

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'delegaciones',
                suboptionKey: 'delegacion_editar',
                optionHref: '/admin/delegations',
                suboptionParams: { name: delegation.name },
            }}
            showBack
            backHref="/admin/delegations"
            actions={[
                {
                    type: 'delete',
                    onConfirm: () => {
                        router.delete(`/admin/delegations/${delegation.id}`);
                    },
                },
            ]}
        >
            <RecordEditLayout
                summary={
                    <RecordSummary
                        titleKey="delegacion_editar"
                        titleParams={{ name: delegation.name }}
                        fields={summaryFields}
                    />
                }
                tabs={tabs}
                activeTab={tab}
                onTabChange={setTab}
            />
        </AppLayout>
    );
}
