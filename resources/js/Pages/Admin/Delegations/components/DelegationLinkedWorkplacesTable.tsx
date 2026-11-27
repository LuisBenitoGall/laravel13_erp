import { router } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { putJson } from '@/lib/http';

export type LinkedWorkplaceRow = {
    id: number;
    name: string;
    cp: string | null;
    town_name: string | null;
    province_name: string | null;
    country_name: string | null;
    created_at: string | null;
    is_preferred_seat: boolean;
};

type Props = {
    delegationId: number;
    canUpdatePreferred?: boolean;
};

export default function DelegationLinkedWorkplacesTable({
    delegationId,
    canUpdatePreferred = true,
}: Props) {
    const { t, i18n } = useTranslation();
    const [tableKey, setTableKey] = useState(0);

    const returnTo = `/companies/delegations/${delegationId}/edit?tab=workplaces`;

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('nombre'),
                field: 'name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('codigo_postal'),
                field: 'cp',
                width: 110,
                headerFilterKind: 'string',
            },
            {
                title: t('poblacion'),
                field: 'town_name',
                widthGrow: 1,
                headerSort: false,
                headerFilterKind: 'string',
            },
            {
                title: t('provincia'),
                field: 'province_name',
                widthGrow: 1,
                headerSort: false,
                headerFilterKind: 'string',
            },
            {
                title: t('pais'),
                field: 'country_name',
                widthGrow: 1,
                headerSort: false,
                headerFilterKind: 'string',
            },
            {
                title: t('fecha_alta'),
                field: 'created_at',
                widthGrow: 1,
                valueKind: 'date',
                headerSort: false,
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('sede_preferida'),
                field: 'is_preferred_seat',
                width: 130,
                formatter: (cell) => (cell.getValue() ? t('si') : t('no')),
            },
        ],
        [i18n.language, t],
    );

    const rowActions = (row: LinkedWorkplaceRow): Array<RowAction<LinkedWorkplaceRow>> => [
        ...(canUpdatePreferred
            ? [
                  {
                      id: 'toggle-preferred-seat',
                      kind: 'toggle' as const,
                      // Title/aria: función del switch (no es activar/desactivar el centro).
                      labelKey: 'sede_preferida',
                      checked: row.is_preferred_seat,
                      onToggle: async (_current, checked) => {
                          try {
                              await putJson(
                                  `/companies/delegations/${delegationId}/preferred-workplace`,
                                  {
                                      workplace_id: checked ? row.id : null,
                                  },
                              );
                              setTableKey((key) => key + 1);

                              return { is_preferred_seat: checked };
                          } catch (error) {
                              toast.error(
                                  error instanceof Error ? error.message : t('error_carga'),
                              );
                              throw error;
                          }
                      },
                  },
              ]
            : []),
        {
            id: 'edit',
            icon: Pencil,
            labelKey: 'editar',
            onClick: (current) =>
                router.visit(
                    `/companies/workplaces/${current.id}/edit?return_to=${encodeURIComponent(returnTo)}`,
                ),
        },
    ];

    return (
        <section
            className="space-y-3 rounded-xl border border-slate-200 bg-white p-6"
            data-testid="delegation-linked-workplaces"
        >
            <div>
                <h2 className="text-sm font-semibold text-slate-900">{t('sus_centros')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('sus_centros_ayuda')}</p>
                <p className="mt-1 text-sm text-slate-500">{t('sede_preferida_ayuda')}</p>
            </div>

            <ServerTable<LinkedWorkplaceRow>
                key={tableKey}
                endpoint={`/companies/delegations/${delegationId}/workplaces/table`}
                columns={columns}
                persistKey={`admin.delegations.${delegationId}.workplaces`}
                exportable={false}
                rowActions={rowActions}
            />
        </section>
    );
}
