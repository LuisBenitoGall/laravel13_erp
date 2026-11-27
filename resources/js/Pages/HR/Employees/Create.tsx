import AppLayout from '@/Layouts/AppLayout';
import type { EmployeeOptions } from '@/types/h_r';

import EmployeeForm from './components/EmployeeForm';

type Props = {
    options: EmployeeOptions;
    default_user_id?: number | null;
};

export default function Create({ options, default_user_id = null }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'empleados',
                suboptionKey: 'empleo_nuevo',
                optionHref: '/rrhh/employees',
            }}
            backHref="/rrhh/employees"
            actions={[]}
        >
            <EmployeeForm
                mode="create"
                method="post"
                submitUrl="/rrhh/employees"
                options={options}
                defaultUserId={default_user_id}
            />
        </AppLayout>
    );
}
