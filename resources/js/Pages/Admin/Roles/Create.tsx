import RoleForm from './components/RoleForm';

type ModuleBlock = {
    slug: string;
    label: string;
    level: string;
    functionalities: Array<{
        slug: string;
        label: string;
        permissions: Array<{ name: string; action: string }>;
    }>;
};

type Props = {
    matrix: ModuleBlock[];
    matrix_universal: ModuleBlock[];
};

export default function Create({ matrix, matrix_universal }: Props) {
    return <RoleForm matrix={matrix} matrix_universal={matrix_universal} />;
}
