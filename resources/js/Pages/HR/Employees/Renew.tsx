import RecordSummary from '@/Components/Records/RecordSummary';
import AppLayout from '@/Layouts/AppLayout';
import type { EmployeeOptions, EmployeeRow } from '@/types/h_r';

import EmployeeForm from './components/EmployeeForm';

type Props = {
    employee: EmployeeRow;
    options: EmployeeOptions;
};

export default function Renew({ employee, options }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'empleados',
                suboptionKey: 'renovar',
                optionHref: '/rrhh/employees',
            }}
            backHref={`/rrhh/employees/${employee.id}/edit`}
            actions={[]}
        >
            <div className="w-full space-y-4">
                <RecordSummary
                    titleKey="empleo_renovar_titulo"
                    titleParams={{ name: employee.user_name ?? '' }}
                    fields={[]}
                />
                <EmployeeForm
                    mode="renew"
                    method="post"
                    submitUrl={`/rrhh/employees/${employee.id}/renew`}
                    options={options}
                    initial={employee}
                />
            </div>
        </AppLayout>
    );
}
