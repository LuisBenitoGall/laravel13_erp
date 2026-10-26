import * as React from 'react';
import { format, isValid, parseISO } from 'date-fns';

import { Input } from '@/Components/Form/Input';

export type DatePickerProps = Omit<React.ComponentProps<'input'>, 'type' | 'value' | 'onChange'> & {
    /** ISO date `yyyy-MM-dd` or null. */
    value?: string | null;
    onChange?: (value: string | null) => void;
};

function toInputValue(value?: string | null): string {
    if (!value) {
        return '';
    }
    const parsed = parseISO(value.length === 10 ? `${value}T00:00:00` : value);
    if (!isValid(parsed)) {
        return value.slice(0, 10);
    }

    return format(parsed, 'yyyy-MM-dd');
}

const DatePicker = React.forwardRef<HTMLInputElement, DatePickerProps>(
    ({ value, onChange, ...props }, ref) => {
        return (
            <Input
                ref={ref}
                type="date"
                value={toInputValue(value)}
                onChange={(e) => {
                    const next = e.target.value;
                    onChange?.(next === '' ? null : next);
                }}
                {...props}
            />
        );
    },
);
DatePicker.displayName = 'DatePicker';

export { DatePicker };
