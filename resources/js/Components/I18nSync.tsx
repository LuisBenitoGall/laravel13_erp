import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';

import { applyPageI18n, type PagePropsWithI18n } from '@/i18n';

/**
 * Keeps i18next in sync with Inertia shared props.
 * Needed because same-URL visits after POST /locale skip the `navigate` event.
 */
export default function I18nSync() {
    const { locale, translations } = usePage<PagePropsWithI18n>().props;

    useEffect(() => {
        // Depend on locale: translations arrive in the same Inertia payload.
        applyPageI18n({ locale, translations });
    }, [locale, translations]);

    return null;
}
