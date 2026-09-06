import ModuleForm from './components/ModuleForm';

type Props = {
    module: {
        id: number;
        name: string;
        slug: string;
        label: Record<string, string>;
        color: string;
        icon: string;
        level: string;
        status: string;
        explanation?: Record<string, string>;
    };
};

export default function Edit({ module }: Props) {
    return <ModuleForm module={module} />;
}
