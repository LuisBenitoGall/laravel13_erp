import FunctionalityForm from './components/FunctionalityForm';

type Props = {
    modules: Array<{ id: number; slug: string; label: string | Record<string, string> }>;
    functionality: {
        id: number;
        module_id: number;
        name: string;
        slug: string;
        label: Record<string, string>;
        status: string;
    };
};

export default function Edit({ modules, functionality }: Props) {
    return <FunctionalityForm modules={modules} functionality={functionality} />;
}
