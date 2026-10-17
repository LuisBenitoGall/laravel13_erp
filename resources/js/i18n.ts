import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import { router } from '@inertiajs/react';

export type PagePropsWithI18n = {
    locale?: string;
    translations?: Record<string, string>;
};

export function applyPageI18n(props: PagePropsWithI18n): void {
    const locale = props.locale ?? 'es';
    const translations = props.translations ?? {};

    i18n.addResourceBundle(locale, 'translation', translations, true, true);

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

function syncFromInertiaPage(page: { props: unknown }): void {
    applyPageI18n(page.props as PagePropsWithI18n);
}

// Same-URL redirects (e.g. POST /locale → back()) use replace and skip `navigate`.
router.on('success', (event) => {
    syncFromInertiaPage(event.detail.page);
});

router.on('navigate', (event) => {
    syncFromInertiaPage(event.detail.page);
});

export function bootstrapI18n(props: PagePropsWithI18n): void {
    applyPageI18n(props);
}

export default i18n;
