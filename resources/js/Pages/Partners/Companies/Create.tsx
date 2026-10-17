import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';

import CompanyForm from './components/CompanyForm';

export default function Create() {
    const { t } = useTranslation();

    return (
        <AppLayout title={t('empresa_nueva')}>
            <CompanyForm submitUrl="/partners/companies" />
        </AppLayout>
    );
}
