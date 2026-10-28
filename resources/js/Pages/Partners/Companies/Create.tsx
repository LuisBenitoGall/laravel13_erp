import AppLayout from '@/Layouts/AppLayout';

import CompanyForm from './components/CompanyForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'empresas',
                suboptionKey: 'empresa_nueva',
                optionHref: '/partners/companies',
            }}
            actions={[]}
        >
            <CompanyForm submitUrl="/partners/companies" />
        </AppLayout>
    );
}
