import AppLayout from '@/Layouts/AppLayout';

import ContractTypeForm from './components/ContractTypeForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'tipos_de_contrato',
                suboptionKey: 'tipo_contrato_nuevo',
                optionHref: '/rrhh/contract-types',
            }}
            backHref="/rrhh/contract-types"
            actions={[]}
        >
            <ContractTypeForm mode="create" method="post" submitUrl="/rrhh/contract-types" />
        </AppLayout>
    );
}
