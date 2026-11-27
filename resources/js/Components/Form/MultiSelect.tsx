import { useMemo, useState } from 'react';
import { ChevronsUpDown, X } from 'lucide-react';

import { Input } from '@/Components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';

export type MultiSelectOption = {
    value: string;
    label: string;
};

export type MultiSelectProps = {
    id?: string;
    options: MultiSelectOption[];
    value?: string[];
    onChange?: (value: string[]) => void;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
    /** Show “create” row when query has no exact match. */
    creatable?: boolean;
    createLabel?: (query: string) => string;
    onCreate?: (name: string) => Promise<MultiSelectOption | null> | MultiSelectOption | null;
    creating?: boolean;
    emptyLabel?: string;
    searchPlaceholder?: string;
};

export function MultiSelect({
    id,
    options,
    value = [],
    onChange,
    placeholder = '…',
    disabled = false,
    className,
    creatable = false,
    createLabel,
    onCreate,
    creating = false,
    emptyLabel = '—',
    searchPlaceholder = '…',
}: MultiSelectProps) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const [busy, setBusy] = useState(false);

    const selected = useMemo(
        () => options.filter((o) => value.includes(o.value)),
        [options, value],
    );

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) {
            return options;
        }

        return options.filter(
            (o) => o.label.toLowerCase().includes(q) || o.value.toLowerCase().includes(q),
        );
    }, [options, query]);

    const trimmedQuery = query.trim();
    const exactMatch = options.some(
        (o) => o.label.toLowerCase() === trimmedQuery.toLowerCase(),
    );
    const showCreate =
        creatable && !!onCreate && trimmedQuery.length > 0 && !exactMatch && !busy && !creating;

    const toggle = (optionValue: string) => {
        if (value.includes(optionValue)) {
            onChange?.(value.filter((v) => v !== optionValue));
        } else {
            onChange?.([...value, optionValue]);
        }
    };

    const remove = (optionValue: string) => {
        onChange?.(value.filter((v) => v !== optionValue));
    };

    const handleCreate = async () => {
        if (!onCreate || !trimmedQuery) {
            return;
        }

        setBusy(true);
        try {
            const created = await onCreate(trimmedQuery);
            if (created) {
                if (!value.includes(created.value)) {
                    onChange?.([...value, created.value]);
                }
                setQuery('');
            }
        } finally {
            setBusy(false);
        }
    };

    return (
        <div className={cn('space-y-2', className)}>
            {selected.length > 0 && (
                <ul className="flex flex-wrap gap-1.5" aria-label={placeholder}>
                    {selected.map((option) => (
                        <li
                            key={option.value}
                            className="inline-flex max-w-full items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-sm text-slate-800"
                        >
                            <span className="truncate">{option.label}</span>
                            {!disabled && (
                                <button
                                    type="button"
                                    className="shrink-0 rounded p-0.5 text-slate-500 hover:bg-slate-200 hover:text-slate-800"
                                    aria-label={`× ${option.label}`}
                                    onClick={() => remove(option.value)}
                                >
                                    <X className="h-3.5 w-3.5" />
                                </button>
                            )}
                        </li>
                    ))}
                </ul>
            )}

            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        id={id}
                        type="button"
                        disabled={disabled}
                        className={cn(
                            'flex h-10 w-full items-center justify-between rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-xs',
                            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 focus-visible:border-brand',
                            'disabled:cursor-not-allowed disabled:opacity-50',
                        )}
                        aria-expanded={open}
                        aria-haspopup="listbox"
                    >
                        <span className={cn(selected.length === 0 && 'text-slate-400')}>
                            {selected.length > 0
                                ? `${selected.length}`
                                : placeholder}
                        </span>
                        <ChevronsUpDown className="h-4 w-4 shrink-0 opacity-50" />
                    </button>
                </PopoverTrigger>
                <PopoverContent className="p-2" align="start">
                    <Input
                        type="text"
                        className="mb-2 h-9"
                        value={query}
                        placeholder={searchPlaceholder}
                        onChange={(e) => setQuery(e.target.value)}
                        autoFocus
                    />
                    <ul className="max-h-56 overflow-auto" role="listbox" aria-multiselectable>
                        {filtered.length === 0 && !showCreate ? (
                            <li className="px-2 py-1.5 text-sm text-slate-500">{emptyLabel}</li>
                        ) : (
                            filtered.map((option) => {
                                const isSelected = value.includes(option.value);

                                return (
                                    <li key={option.value}>
                                        <button
                                            type="button"
                                            role="option"
                                            aria-selected={isSelected}
                                            className={cn(
                                                'flex w-full rounded px-2 py-1.5 text-left text-sm hover:bg-slate-100',
                                                isSelected && 'bg-brand-muted text-brand-ink',
                                            )}
                                            onClick={() => toggle(option.value)}
                                        >
                                            {option.label}
                                        </button>
                                    </li>
                                );
                            })
                        )}
                        {showCreate && (
                            <li>
                                <button
                                    type="button"
                                    className="mt-1 flex w-full rounded border border-dashed border-brand/40 px-2 py-1.5 text-left text-sm text-brand-ink hover:bg-brand-muted"
                                    onClick={() => void handleCreate()}
                                    disabled={busy || creating}
                                >
                                    {createLabel?.(trimmedQuery) ?? trimmedQuery}
                                </button>
                            </li>
                        )}
                    </ul>
                </PopoverContent>
            </Popover>
        </div>
    );
}
