import * as React from 'react';

import { Input as UiInput, type InputProps } from '@/Components/ui/input';

/**
 * Form kit Input — thin wrapper around `ui/input` (R-FE-08 / maxLength 255).
 */
const Input = React.forwardRef<HTMLInputElement, InputProps>((props, ref) => {
    return <UiInput ref={ref} {...props} />;
});
Input.displayName = 'FormInput';

export { Input };
export type { InputProps };
