import * as React from 'react';

import { Input } from '@/Components/Form/Input';
import { cn } from '@/lib/utils';

export type NumberInputProps = Omit<React.ComponentProps<'input'>, 'type' | 'value' | 'onChange'> & {
    /**
     * UX decimal places hint (venta 3 / coste 4 / contable 2).
     * Business rounding stays in backend / `lib` — this control is display/input only.
     */
    decimals?: number;
    value?: number | string | null;
    onChange?: (value: string) => void;
};

const NumberInput = React.forwardRef<HTMLInputElement, NumberInputProps>(
    ({ className, decimals, value, onChange, inputMode = 'decimal', ...props }, ref) => {
        const display = value === null || value === undefined ? '' : String(value);

        return (
            <Input
                ref={ref}
                type="text"
                inputMode={inputMode}
                className={cn('font-mono', className)}
                data-decimals={decimals}
                {...props}
                value={display}
                onChange={(e) => onChange?.(e.target.value)}
            />
        );
    },
);
NumberInput.displayName = 'NumberInput';

export { NumberInput };
