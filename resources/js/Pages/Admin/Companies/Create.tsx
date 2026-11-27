import AppLayout from '@/Layouts/AppLayout';

import CompanyForm from './components/CompanyForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mis_empresas',
                suboptionKey: 'empresa_nueva',
                optionHref: '/companies',
            }}
            actions={[]}
        >
            <CompanyForm mode="create" method="post" submitUrl="/companies" />
        </AppLayout>
    );
}
