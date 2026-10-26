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

type RolePayload = {
    id: number;
    name: string;
    description: string | null;
    protected_local: boolean;
    local_manager: boolean;
    local_guest: boolean;
    permissions_readonly: boolean;
    name_readonly: boolean;
    permission_names: string[];
};

type Props = {
    role: RolePayload;
    matrix: ModuleBlock[];
};

export default function Edit({ role, matrix }: Props) {
    return <CompanyRoleForm role={role} matrix={matrix} />;
}
