export type I18nSharedProps = {
    locale: string;
    supportedLocales: string[];
    /** Locale principal del catálogo (`LocaleResolver::default()` / config i18n.default). */
    defaultLocale: string;
    translations: Record<string, string>;
};
