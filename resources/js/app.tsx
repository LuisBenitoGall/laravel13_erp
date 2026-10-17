import '../css/app.css';

import { createInertiaApp, type ResolvedComponent } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { I18nextProvider } from 'react-i18next';

import I18nSync from './Components/I18nSync';
import i18n, { bootstrapI18n } from './i18n';

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob<ResolvedComponent>('./Pages/**/*.tsx', { eager: true });

        return pages[`./Pages/${name}.tsx`];
    },
    setup({ el, App, props }) {
        bootstrapI18n(
            props.initialPage.props as {
                locale?: string;
                translations?: Record<string, string>;
            },
        );

        createRoot(el).render(
            <I18nextProvider i18n={i18n}>
                <App {...props}>
                    {({ Component, props: pageProps, key }) => (
                        <>
                            <I18nSync />
                            <Component {...pageProps} key={key} />
                        </>
                    )}
                </App>
            </I18nextProvider>,
        );
    },
});
