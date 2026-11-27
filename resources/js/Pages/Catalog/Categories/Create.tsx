import AppLayout from '@/Layouts/AppLayout';
import type { CategoryParentOption } from '@/types/catalog';

import CategoryForm from './components/CategoryForm';

type Props = {
    parent_options: CategoryParentOption[];
};

export default function Create({ parent_options }: Props) {
    return (
        <AppLayout
            breadcrumb={{
                optionKey: 'categorias',
                suboptionKey: 'categoria_nueva',
                optionHref: '/products/categories',
            }}
            backHref="/products/categories"
            actions={[]}
        >
            <CategoryForm
                mode="create"
                method="post"
                submitUrl="/products/categories"
                parentOptions={parent_options}
            />
        </AppLayout>
    );
}
