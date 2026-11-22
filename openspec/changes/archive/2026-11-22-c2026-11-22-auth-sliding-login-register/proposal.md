# Proposal

## Why

Login y Register No Admin son cards sueltas sobre fondo gris. Queremos la
UX de panel deslizante Login↔Register (concepto del short CSS), adaptada
a marca/colores ERP (teal), sin cambiar el contrato de auth ni el
onboarding multipaso.

## What Changes

- UI unificada Login/Register con overlay/panel deslizante (teal ERP).
- Rutas `/login` y `/register` siguen válidas; modo inicial por ruta;
  POSTs existentes.
- i18n, LanguageSwitcher, legales, forgot-password, toggle de modo.
- `prefers-reduced-motion`.
- Forgot/Reset: AuthLayout estático (sin slide).

## Capabilities

### New Capabilities

- `auth-guest-ui`: presentación guest Login/Register (panel deslizante).

### Modified Capabilities

_(ninguna obligatoria; auth-registration sin cambio de reglas)_

## Impact

- Front: AuthLayout / AuthSlidingPanel, Login+Register (o página
  compartida), CSS/tokens teal.
- Controllers auth: props `mode` si se unifica página.
- Tests Feature assert fuente / Inertia component.
- E2E: N/A o smoke login (Feature basta).
