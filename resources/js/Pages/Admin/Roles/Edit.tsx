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

type RolePayload = {
    id: number;
    name: string;
    description: string | null;
    universal: boolean;
    protected_template: boolean;
    manager_template: boolean;
    permissions_readonly: boolean;
    permission_names: string[];
};

type Props = {
    role: RolePayload;
    matrix: ModuleBlock[];
    matrix_universal: ModuleBlock[];
};

export default function Edit({ role, matrix, matrix_universal }: Props) {
    return <RoleForm role={role} matrix={matrix} matrix_universal={matrix_universal} />;
}
