import { type ReactNode } from 'react';

import LanguageSwitcher from '@/Components/LanguageSwitcher';

export default function AuthLayout({ children }: { children: ReactNode }) {
    return (
        <div className="relative flex min-h-screen items-center justify-center bg-slate-100 px-4">
            <div className="absolute right-4 top-4">
                <LanguageSwitcher />
            </div>
            {children}
        </div>
    );
}
