import { type CSSProperties, type ReactNode } from 'react';

import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { cn } from '@/lib/utils';

type AuthLayoutProps = {
    children: ReactNode;
    variant?: 'static' | 'sliding';
};

export default function AuthLayout({ children, variant = 'static' }: AuthLayoutProps) {
    const isSliding = variant === 'sliding';

    return (
        <div
            className={cn(
                'relative flex min-h-screen items-center justify-center px-4 py-10',
                isSliding
                    ? 'bg-[radial-gradient(ellipse_at_top,_var(--auth-canvas-accent)_0%,_#f1f5f9_55%,_#e2e8f0_100%)]'
                    : 'bg-slate-100',
            )}
            style={
                isSliding
                    ? ({
                          '--auth-primary': 'var(--color-brand)',
                          '--auth-primary-hover': 'var(--color-brand-hover)',
                          '--auth-canvas-accent': 'color-mix(in srgb, var(--color-brand) 18%, transparent)',
                      } as CSSProperties)
                    : undefined
            }
        >
            <div className="absolute right-4 top-4 z-30">
                <LanguageSwitcher />
            </div>
            {children}
        </div>
    );
}
