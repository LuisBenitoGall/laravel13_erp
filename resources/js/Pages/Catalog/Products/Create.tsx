import AppLayout from '@/Layouts/AppLayout';

import ProductForm from './components/ProductForm';

export default function Create() {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'catalogo',
                suboptionKey: 'producto_nuevo',
                optionHref: '/products',
            }}
            backHref="/products"
            actions={[]}
        >
            <ProductForm mode="create" method="post" submitUrl="/products" />
        </AppLayout>
    );
}
