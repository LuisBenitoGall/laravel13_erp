import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import { router } from '@inertiajs/react';

type PagePropsWithI18n = {
    locale?: string;
    translations?: Record<string, string>;
};

function applyPageI18n(props: PagePropsWithI18n): void {
    const locale = props.locale ?? 'es';
    const translations = props.translations ?? {};

    if (!i18n.hasResourceBundle(locale, 'translation')) {
        i18n.addResourceBundle(locale, 'translation', translations, true, true);
    } else {
        i18n.addResourceBundle(locale, 'translation', translations, true, true);
    }

    if (i18n.language !== locale) {
        void i18n.changeLanguage(locale);
    }
}

void i18n.use(initReactI18next).init({
    lng: 'es',
    fallbackLng: 'es',
    resources: {},
    interpolation: { escapeValue: false },
});

router.on('navigate', (event) => {
    applyPageI18n(event.detail.page.props as PagePropsWithI18n);
});

export function bootstrapI18n(props: PagePropsWithI18n): void {
    applyPageI18n(props);
}

export default i18n;
