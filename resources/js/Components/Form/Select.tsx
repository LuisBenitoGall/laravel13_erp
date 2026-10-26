import { useMemo, useState } from 'react';
import { ChevronsUpDown } from 'lucide-react';

import { Input } from '@/Components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { cn } from '@/lib/utils';

export type SelectOption = {
    value: string;
    label: string;
};

export type SelectProps = {
    id?: string;
    options: SelectOption[];
    value?: string | null;
    onChange?: (value: string) => void;
    /** Optional remote-search hook (no endpoint in this MVP). */
    onSearch?: (query: string) => void;
    loading?: boolean;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
};

export function Select({
    id,
    options,
    value,
    onChange,
    onSearch,
    loading = false,
    placeholder = '…',
    disabled = false,
    className,
}: SelectProps) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');

    const selected = options.find((o) => o.value === value);

    const filtered = useMemo(() => {
        const q = query.trim().toLowerCase();
        if (!q) {
            return options;
        }

        return options.filter(
            (o) => o.label.toLowerCase().includes(q) || o.value.toLowerCase().includes(q),
        );
    }, [options, query]);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <button
                    id={id}
                    type="button"
                    disabled={disabled}
                    className={cn(
                        'flex h-10 w-full items-center justify-between rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-xs',
                        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700/30 focus-visible:border-teal-700',
                        'disabled:cursor-not-allowed disabled:opacity-50',
                        className,
                    )}
                    aria-expanded={open}
                >
                    <span className={cn(!selected && 'text-slate-400')}>
                        {selected?.label ?? placeholder}
                    </span>
                    <ChevronsUpDown className="h-4 w-4 shrink-0 opacity-50" />
                </button>
            </PopoverTrigger>
            <PopoverContent className="p-2">
                <Input
                    type="text"
                    className="mb-2 h-9"
                    value={query}
                    placeholder="…"
                    onChange={(e) => {
                        const next = e.target.value;
                        setQuery(next);
                        onSearch?.(next);
                    }}
                    autoFocus
                />
                <ul className="max-h-56 overflow-auto" role="listbox">
                    {loading ? (
                        <li className="px-2 py-1.5 text-sm text-slate-500">…</li>
                    ) : filtered.length === 0 ? (
                        <li className="px-2 py-1.5 text-sm text-slate-500">—</li>
                    ) : (
                        filtered.map((option) => (
                            <li key={option.value}>
                                <button
                                    type="button"
                                    role="option"
                                    aria-selected={option.value === value}
                                    className={cn(
                                        'flex w-full rounded px-2 py-1.5 text-left text-sm hover:bg-slate-100',
                                        option.value === value && 'bg-teal-50 text-teal-900',
                                    )}
                                    onClick={() => {
                                        onChange?.(option.value);
                                        setOpen(false);
                                        setQuery('');
                                    }}
                                >
                                    {option.label}
                                </button>
                            </li>
                        ))
                    )}
                </ul>
            </PopoverContent>
        </Popover>
    );
}
