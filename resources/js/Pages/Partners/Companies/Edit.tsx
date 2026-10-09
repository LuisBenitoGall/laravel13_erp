import AppLayout from '@/Layouts/AppLayout';
import CompanyForm from './components/CompanyForm';
import type { Company } from '@/types/partners';

export default function Edit({ company }: { company: Company }) {
    return (
        <AppLayout title={`Editar ${company.name}`}>
            <CompanyForm
                method="put"
                submitUrl={`/partners/companies/${company.id}`}
                initial={{
                    name: company.name,
                    slug: company.slug,
                    tradename: company.tradename ?? '',
                    nif: company.nif ?? '',
                    status: company.status,
                    logo: company.logo ?? '',
                }}
            />
        </AppLayout>
    );
}
