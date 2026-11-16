import AppLayout from '@/Layouts/AppLayout';

import WorkplaceForm from './components/WorkplaceForm';

type GeoOption = { id: number; name: string };

type Props = {
    countries: GeoOption[];
    default_featured: boolean;
    return_to?: string | null;
};

export default function Create({ countries, default_featured, return_to = null }: Props) {
    const backHref = return_to || '/admin/workplaces';

    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'centros_de_trabajo',
                suboptionKey: 'nuevo_centro_trabajo',
                optionHref: '/admin/workplaces',
            }}
            backHref={backHref}
            actions={[]}
        >
            <WorkplaceForm
                mode="create"
                method="post"
                submitUrl="/admin/workplaces"
                countries={countries}
                defaultFeatured={default_featured}
                returnTo={return_to}
            />
        </AppLayout>
    );
}
