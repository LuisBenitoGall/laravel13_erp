import FunctionalityForm from './components/FunctionalityForm';

type Props = {
    modules: Array<{ id: number; slug: string; label: string | Record<string, string> }>;
};

export default function Create({ modules }: Props) {
    return <FunctionalityForm modules={modules} />;
}
