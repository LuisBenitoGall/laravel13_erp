import { Plus, Trash2 } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Field, Input } from '@/Components/Form';
import { Button } from '@/Components/ui/button';

type Props = {
    id: string;
    label: string;
    values: string[];
    addLabelKey: string;
    error?: string;
    inputType?: 'text' | 'url';
    onChange: (values: string[]) => void;
};

export default function StringListField({
    id,
    label,
    values,
    addLabelKey,
    error,
    inputType = 'text',
    onChange,
}: Props) {
    const { t } = useTranslation();
    const rows = values.length > 0 ? values : [''];

    function updateAt(index: number, value: string) {
        const next = [...rows];
        next[index] = value;
        onChange(next);
    }

    function removeAt(index: number) {
        const next = rows.filter((_, i) => i !== index);
        onChange(next.length > 0 ? next : ['']);
    }

    function addRow() {
        onChange([...rows, '']);
    }

    return (
        <div className="space-y-2" data-testid={id}>
            <Field label={label} htmlFor={`${id}-0`} error={error}>
                <div className="space-y-2">
                    {rows.map((value, index) => (
                        <div key={`${id}-${index}`} className="flex items-center gap-2">
                            <Input
                                id={`${id}-${index}`}
                                type={inputType}
                                value={value}
                                maxLength={255}
                                onChange={(event) => updateAt(index, event.target.value)}
                            />
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon-sm"
                                aria-label={t('eliminar')}
                                onClick={() => removeAt(index)}
                                disabled={rows.length === 1 && value === ''}
                            >
                                <Trash2 className="h-4 w-4" aria-hidden />
                            </Button>
                        </div>
                    ))}
                </div>
            </Field>
            <Button type="button" variant="outline" size="sm" onClick={addRow}>
                <Plus className="mr-1 h-4 w-4" aria-hidden />
                {t(addLabelKey)}
            </Button>
        </div>
    );
}
