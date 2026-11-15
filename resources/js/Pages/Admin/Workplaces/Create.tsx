import AppLayout from '@/Layouts/AppLayout';

import WorkplaceForm from './components/WorkplaceForm';

type GeoOption = { id: number; name: string };

type Props = {
    countries: GeoOption[];
    default_featured: boolean;
};

export default function Create({ countries, default_featured }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'centros_de_trabajo',
                suboptionKey: 'nuevo_centro_trabajo',
                optionHref: '/admin/workplaces',
            }}
            backHref="/admin/workplaces"
            actions={[]}
        >
            <WorkplaceForm
                mode="create"
                method="post"
                submitUrl="/admin/workplaces"
                countries={countries}
                defaultFeatured={default_featured}
            />
        </AppLayout>
    );
}
