import AppLayout from '@/Layouts/AppLayout';
import CompanyForm from './components/CompanyForm';

export default function Create() {
    return (
        <AppLayout title="Nueva empresa">
            <CompanyForm submitUrl="/partners/companies" />
        </AppLayout>
    );
}
