import AppLayout from '@/Layouts/AppLayout';

import FamilyForm from './components/FamilyForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'familias',
                suboptionKey: 'familia_nueva',
                optionHref: '/products/families',
            }}
            backHref="/products/families"
            actions={[]}
        >
            <FamilyForm mode="create" method="post" submitUrl="/products/families" />
        </AppLayout>
    );
}
