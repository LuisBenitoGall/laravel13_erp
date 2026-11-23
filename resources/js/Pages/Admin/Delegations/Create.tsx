import AppLayout from '@/Layouts/AppLayout';

import DelegationForm from './components/DelegationForm';

type GeoOption = { id: number; name: string };

type Props = {
    countries: GeoOption[];
};

export default function Create({ countries }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'delegaciones',
                suboptionKey: 'delegacion_nueva',
                optionHref: '/admin/delegations',
            }}
            backHref="/admin/delegations"
            actions={[]}
        >
            <DelegationForm
                mode="create"
                method="post"
                submitUrl="/admin/delegations"
                countries={countries}
            />
        </AppLayout>
    );
}
