# Proposal

## Why

El ERP sigue usando un placeholder (`/images/erp-logo.svg`), el `favicon.ico` de Laravel y acentos `teal-700` genéricos. El kit Coopmercio ya define marca y colores (`#10A37F` / `#633D56`). Hay que anclar logos, favicon, nombre y paleta de producto, con el logo del ERP presidiendo el aside por encima del de la company.

## What Changes

- Importar al árbol `public/` el set Coopmercio necesario (favicon completo + SVG de símbolo y horizontal color).
- Sustituir el logo placeholder del ERP por el wordmark Coopmercio (ampliado) y el símbolo (rail / espacio reducido).
- Cablear favicon, apple-touch-icon y `site.webmanifest` en el shell Blade Inertia (`app.blade.php`).
- Actualizar `config/branding.php` (y defaults de `APP_NAME` / `.env.example`) a Coopmercio y a las URLs de assets reales.
- Endurecer el contrato del aside: logo ERP siempre encima del logo de empresa en aside expandido y en aside móvil abierto; en rail, símbolo ERP (y logo/inicial de company reducido).
- Alinear el título del documento (`<title>` / `config('app.name')`) con la marca Coopmercio.
- Adoptar los colores del logo como tokens de marca del ERP (`#10A37F` primary, `#633D56` secondary) en `@theme` / CSS y sustituir los acentos `teal-*` hardcodeados del shell, formularios, auth y componentes UI por esos tokens.

## Capabilities

### New Capabilities
- `erp-branding`: identidad visual del producto (nombre Coopmercio, assets públicos, favicon/manifest, URLs de logo ampliado y símbolo, tokens de color de marca).

### Modified Capabilities
- `shell-navigation`: precisar variantes de logo ERP (ampliado vs símbolo en rail) y reafirmar jerarquía ERP → company en expandido y móvil.
- `auth-guest-ui`: el panel guest usa la paleta de marca Coopmercio (primary del kit), no el teal genérico previo.

## Impact

- Assets: `public/favicon.*`, `public/apple-touch-icon.png`, `public/site.webmanifest`, `public/images/branding/*` (sustituye `public/images/erp-logo.svg` como default).
- Config: `config/branding.php`, `config/app.php` default name / `.env.example` (`APP_NAME`, opcional `APP_LOGO_URL` / `APP_MARK_URL`).
- Backend share: `HandleInertiaRequests` (`branding.logo_url` + `branding.mark_url` + colores si se exponen).
- Frontend: `resources/css/app.css` (`@theme` brand), `AppLayout`, componentes/`Pages` que usan `teal-*`, `AuthLayout` / sliding auth.
- Vista raíz: `resources/views/app.blade.php` (links de iconos); manifest `theme_color`.
- Sin cambios de tenancy, menú de módulos, permisos ni upload de logo de company.
- Sin dependencias Composer/npm nuevas.
- Color por módulo (R-AUT-07) y logo por tenant se mantienen; no hay theming por company.
