import { createContext, useContext, type ReactNode } from 'react';

const FormHelpScopeContext = createContext<string | null>(null);

type FormHelpScopeProps = {
    /** Slug estable del formulario (ej. `companies`). */
    value: string;
    children: ReactNode;
};

/**
 * Declara el helpScope del formulario una vez.
 * Los `Field` hijos derivan `code = {helpScope}.{fieldKey}` si no hay `helpCode`.
 */
export function FormHelpScope({ value, children }: FormHelpScopeProps) {
    return <FormHelpScopeContext.Provider value={value}>{children}</FormHelpScopeContext.Provider>;
}

export function useFormHelpScope(): string | null {
    return useContext(FormHelpScopeContext);
}
