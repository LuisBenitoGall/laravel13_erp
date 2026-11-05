import RoleForm from './components/RoleForm';

type ModuleBlock = {
    slug: string;
    label: string;
    level: string;
    color: string;
    icon: string;
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
    can_copy: boolean;
    copy_companies: Array<{ id: number; name: string }>;
};

export default function Edit({ role, matrix, can_copy, copy_companies }: Props) {
    return (
        <RoleForm
            role={role}
            matrix={matrix}
            can_copy={can_copy}
            copy_companies={copy_companies}
        />
    );
}
