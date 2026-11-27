import AppLayout from '@/Layouts/AppLayout';

import DayTypeForm from './components/DayTypeForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_jornada',
                suboptionKey: 'tipo_jornada_nuevo',
                optionHref: '/rrhh/day-types',
            }}
            backHref="/rrhh/day-types"
            actions={[]}
        >
            <DayTypeForm mode="create" method="post" submitUrl="/rrhh/day-types" />
        </AppLayout>
    );
}
