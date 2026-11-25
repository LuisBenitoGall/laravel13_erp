import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';

export type LinkedWorkplace = {
    id: number;
    name: string;
    is_preferred: boolean;
};

type Props = {
    workplaces: LinkedWorkplace[];
};

/** RO: centros de la empresa asociados a esta Delegación. Edición del vínculo en Centros de trabajo. */
export default function MatrixWorkplacesLinkedPanel({ workplaces }: Props) {
    const { t } = useTranslation();

    return (
        <section
            className="space-y-3 rounded-xl border border-slate-200 bg-white p-6"
            data-testid="delegation-linked-workplaces"
        >
            <div>
                <h2 className="text-sm font-semibold text-slate-900">{t('sus_centros')}</h2>
                <p className="mt-1 text-sm text-slate-600">{t('sus_centros_ayuda')}</p>
            </div>

            {workplaces.length === 0 ? (
                <div className="space-y-3">
                    <p className="text-sm text-slate-500">{t('sus_centros_vacio')}</p>
                    <Button asChild variant="outline" size="sm">
                        <Link href="/admin/workplaces">{t('ir_a_centros_de_trabajo')}</Link>
                    </Button>
                </div>
            ) : (
                <ul className="divide-y divide-slate-200 rounded-lg border border-slate-200">
                    {workplaces.map((workplace) => (
                        <li
                            key={workplace.id}
                            className="flex flex-wrap items-center justify-between gap-2 px-4 py-3"
                        >
                            <div className="flex flex-wrap items-center gap-2">
                                <Link
                                    href={`/admin/workplaces/${workplace.id}/edit`}
                                    className="text-sm font-medium text-slate-900 underline-offset-2 hover:underline"
                                >
                                    {workplace.name}
                                </Link>
                                {workplace.is_preferred ? (
                                    <span className="rounded bg-amber-100 px-1.5 py-0.5 text-xs text-amber-800">
                                        {t('delegacion_preferida')}
                                    </span>
                                ) : null}
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
