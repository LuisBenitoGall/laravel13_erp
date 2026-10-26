import * as React from 'react';

import { Textarea as UiTextarea, type TextareaProps } from '@/Components/ui/textarea';

const Textarea = React.forwardRef<HTMLTextAreaElement, TextareaProps>((props, ref) => {
    return <UiTextarea ref={ref} {...props} />;
});
Textarea.displayName = 'FormTextarea';

export { Textarea };
export type { TextareaProps };
