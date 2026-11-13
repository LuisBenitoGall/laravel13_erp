import AppLayout from '@/Layouts/AppLayout';

import CompanyForm from './components/CompanyForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'mis_empresas',
                suboptionKey: 'empresa_nueva',
                optionHref: '/admin/companies',
            }}
            actions={[]}
        >
            <CompanyForm mode="create" method="post" submitUrl="/admin/companies" />
        </AppLayout>
    );
}
