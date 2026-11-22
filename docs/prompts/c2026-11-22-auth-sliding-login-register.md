<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-auth-sliding-login-register
Rule: 10-architecture
Summary: Login y registro No Admin en un panel deslizante (estilo short
  CSS Login↔Register), adaptado a marca/colores ERP (teal), sin tocar
  onboarding multipaso.
Goals:
- Unificar la presentación de Login y Register en un contenedor con
  overlay/panel que se desliza entre modos Entrar y Registrarse
  (patrón confirmado del short).
- Adaptar a **estilo y colores del ERP** (teal primario ya usado en
  auth/botones; tipografía y atmósfera coherentes con el producto —
  10/design concreta tokens; evitar estética genérica “purple glass”).
- Mantener rutas `/login` y `/register` (deep-link define el modo
  inicial); POSTs Inertia/`useForm` existentes sin cambiar contrato de
  backend salvo lo mínimo de UI.
- i18n completo; LanguageSwitcher; enlaces legales / forgot-password /
  cruzados login↔register integrados en el panel.
- Accesibilidad: `prefers-reduced-motion` (sin slide o transición
  reducida).
NonGoals:
- Onboarding post-registro (OnboardCompany, SelectCompany, alta
  empresa, AccountCatalogGrid, renovación cuenta) — siguen pantallas
  dedicadas / no este patrón.
- Forgot/Reset con el mismo slide (pueden heredar AuthLayout estático;
  10 decide si solo Login+Register).
- Cashier/Stripe, redes sociales OAuth (no debatido).
- Cambios de reglas de validación de registro/login salvo UI.
Skills:
- openspec-propose
- inertia-react-development
- frontend-design
Notes:
- Origen: `/opsx-explore`; usuario confirmó panel deslizante y
  adaptación a estilo/colores ERP.
- Hoy: `AuthLayout` fondo slate + cards Login/Register separadas.
- Referencia visual: short YouTube `zuh7CoGFRfk` (Power of CSS —
  login/register slide); no clonar HTML/CSS del video, adaptar concepto.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.
- Tras brief: usuario lanza 10-architecture en sesión nueva.


## ADDED Requirements

### Requirement: Panel deslizante Login / Register
El sistema SHALL presentar Login y Register fuera del Admin como un
único panel con transición deslizante entre modos Entrar y Registrarse,
usando la marca y colores del ERP (teal). Las rutas `/login` y
`/register` SHALL seguir siendo válidas y determinar el modo inicial.

#### Scenario: Abrir login
- **WHEN** el visitante abre `/login`
- **THEN** ve el panel en modo Entrar con opción de pasar a Registrarse
  mediante la transición del panel

#### Scenario: Abrir register
- **WHEN** el visitante abre `/register`
- **THEN** ve el panel en modo Registrarse con opción de pasar a Entrar
  mediante la transición del panel

#### Scenario: Enviar formularios
- **WHEN** el visitante completa y envía Entrar o Registrarse
- **THEN** se procesa igual que hoy vía las rutas de auth existentes


## MODIFIED Requirements

_(ninguno obligatorio — 10 puede delta auth-ui / guest si existe)_
