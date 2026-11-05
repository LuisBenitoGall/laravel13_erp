import { ArrowUp } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';

type Props = {
    /** Píxeles de scroll antes de mostrar el botón. */
    threshold?: number;
    className?: string;
};

/**
 * Botón flotante inferior-derecha para volver al inicio de la página.
 * Reutilizable en cualquier vista larga (Edit roles, documentos, etc.).
 */
export default function ScrollToTop({ threshold = 320, className }: Props) {
    const { t } = useTranslation();
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const onScroll = () => {
            setVisible(window.scrollY > threshold);
        };

        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, [threshold]);

    if (!visible) {
        return null;
    }

    return (
        <Button
            type="button"
            size="icon"
            variant="secondary"
            className={cn(
                'fixed bottom-6 right-6 z-40 size-11 rounded-full border border-slate-200 bg-white shadow-md',
                'hover:bg-slate-50',
                className,
            )}
            aria-label={t('volver_arriba')}
            title={t('volver_arriba')}
            onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
        >
            <ArrowUp className="size-5" aria-hidden />
        </Button>
    );
}
