import { useMemo, useState } from 'react';
import { Eye } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import type { CellComponent } from 'tabulator-tables';

import { fmt } from '@/Components/Table/formatters';
import ServerTable, { type ColumnDef, type RowAction } from '@/Components/Table/ServerTable';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';

export type UserEmploymentPeriodRow = {
    id: number;
    company_id: number;
    company_name: string | null;
    workplace_id: number | null;
    workplace_name: string | null;
    contract_type_id: number;
    contract_type_name: string | null;
    job_title: string | null;
    start_date: string;
    end_date: string | null;
    weekly_hours: string | number | null;
    year_salary: string | number | null;
    observations: string | null;
};

type Props = {
    tableEndpoint: string;
};

function emptyDash(cell: CellComponent): string {
    const value = cell.getValue();

    return value === null || value === undefined || value === '' ? '—' : String(value);
}

function hasObservations(row: UserEmploymentPeriodRow): boolean {
    return typeof row.observations === 'string' && row.observations.trim() !== '';
}

export default function UserEmploymentPeriodsPanel({ tableEndpoint }: Props) {
    const { t, i18n } = useTranslation();
    const [open, setOpen] = useState(false);
    const [selected, setSelected] = useState<UserEmploymentPeriodRow | null>(null);

    const columns = useMemo<ColumnDef[]>(
        () => [
            {
                title: t('empresa'),
                field: 'company_name',
                widthGrow: 2,
                headerFilterKind: 'string',
            },
            {
                title: t('centro_de_trabajo'),
                field: 'workplace_name',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: emptyDash,
            },
            {
                title: t('tipo_de_contrato'),
                field: 'contract_type_name',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: emptyDash,
            },
            {
                title: t('puesto'),
                field: 'job_title',
                widthGrow: 1,
                headerFilterKind: 'string',
                formatter: emptyDash,
            },
            {
                title: t('fecha_inicio'),
                field: 'start_date',
                width: 140,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('fecha_fin'),
                field: 'end_date',
                width: 140,
                valueKind: 'date',
                headerFilterKind: 'date',
                formatter: fmt.date(i18n.language),
            },
            {
                title: t('horas_semanales'),
                field: 'weekly_hours',
                width: 120,
                formatter: emptyDash,
            },
            {
                title: t('salario_anual'),
                field: 'year_salary',
                width: 130,
                valueKind: 'number',
                formatter: fmt.money(2),
            },
        ],
        [i18n.language, t],
    );

    const rowActions = (row: UserEmploymentPeriodRow): Array<RowAction<UserEmploymentPeriodRow>> => {
        if (!hasObservations(row)) {
            return [];
        }

        return [
            {
                id: 'observations',
                icon: Eye,
                labelKey: 'ver_observaciones',
                onClick: (current) => {
                    setSelected(current);
                    setOpen(true);
                },
            },
        ];
    };

    return (
        <>
            <ServerTable<UserEmploymentPeriodRow>
                endpoint={tableEndpoint}
                columns={columns}
                persistKey={`user.employment-periods.${tableEndpoint}`}
                exportable={false}
                rowActions={rowActions}
            />

            <Dialog
                open={open}
                onOpenChange={(next) => {
                    setOpen(next);
                    if (!next) {
                        setSelected(null);
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('observaciones')}</DialogTitle>
                    </DialogHeader>
                    <p className="whitespace-pre-wrap text-sm text-slate-700">
                        {selected?.observations}
                    </p>
                    <DialogFooter>
                        <Button type="button" variant="secondary" onClick={() => setOpen(false)}>
                            {t('cerrar')}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
