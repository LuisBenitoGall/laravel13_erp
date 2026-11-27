import AppLayout from '@/Layouts/AppLayout';

import TagForm from './components/TagForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'etiquetas',
                suboptionKey: 'etiqueta_nueva',
                optionHref: '/products/tags',
            }}
            backHref="/products/tags"
            actions={[]}
        >
            <TagForm mode="create" method="post" submitUrl="/products/tags" />
        </AppLayout>
    );
}
