import CompanyRoleForm from './components/CompanyRoleForm';

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
};

export default function Create({ matrix }: Props) {
    return <CompanyRoleForm matrix={matrix} />;
}
