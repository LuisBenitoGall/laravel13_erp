import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';

import AppLayout from '@/Layouts/AppLayout';
import type { Company } from '@/types/partners';

import CompanyForm from './components/CompanyForm';

export default function Edit({
    company,
    members_url,
}: {
    company: Company;
    members_url?: string;
}) {
    const { t } = useTranslation();

    return (
        <AppLayout title={t('empresa_editar', { name: company.name })}>
            {members_url && (
                <div className="mb-4">
                    <Link href={members_url} className="text-sm font-medium text-teal-700 hover:underline">
                        {t('gestionar_miembros_y_roles')}
                    </Link>
                </div>
            )}
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
