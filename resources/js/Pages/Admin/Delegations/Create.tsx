import AppLayout from '@/Layouts/AppLayout';

import DelegationForm from './components/DelegationForm';

type GeoOption = { id: number; name: string };
type WorkplaceOption = { id: number; name: string };

type Props = {
    countries: GeoOption[];
    matrix_workplaces: WorkplaceOption[];
};

export default function Create({ countries, matrix_workplaces }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'delegaciones',
                suboptionKey: 'delegacion_nueva',
                optionHref: '/companies/delegations',
            }}
            backHref="/companies/delegations"
            actions={[]}
        >
            <DelegationForm
                mode="create"
                method="post"
                submitUrl="/companies/delegations"
                countries={countries}
                matrixWorkplaces={matrix_workplaces}
            />
        </AppLayout>
    );
}
