import * as React from 'react';

import { cn } from '@/lib/utils';

/** Default maxLength for text inputs in the UI kit (mirror FormRequest `max:255`). */
export const DEFAULT_INPUT_MAX_LENGTH = 255;

export type InputProps = React.ComponentProps<'input'>;

const Input = React.forwardRef<HTMLInputElement, InputProps>(
    ({ className, type = 'text', maxLength = DEFAULT_INPUT_MAX_LENGTH, ...props }, ref) => {
        return (
            <input
                type={type}
                className={cn(
                    'flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-xs transition-colors',
                    'placeholder:text-slate-400',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-700/30 focus-visible:border-teal-700',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    'file:border-0 file:bg-transparent file:text-sm file:font-medium',
                    className,
                )}
                ref={ref}
                {...props}
                maxLength={maxLength}
            />
        );
    },
);
Input.displayName = 'Input';

export { Input };
