# Proposal

## Why

Tras crear una company (onboarding o Mis empresas) el usuario cae en el
ERP sin guía: falta un hub de configuración de la **empresa en sesión**
y un asistente con los pasos que ya existen (identidad/logo + sede).

## What Changes

- Functionality `company-settings` bajo `empresas` →
  `/admin/company-settings` (menú Configuración de la empresa).
- Hub: progreso + asistente guiado + tabs de pasos **reales**:
  identidad/logo y ≥1 workplace.
- Post-create / company incompleta: modal (CTA hub + «Ahora no» →
  dashboard); banner persistente mientras incompleta.
- Readiness computada (hechos de dominio); sin soft-gates de negocio.
- Protocolo: nuevos pasos solo cuando Luis lo pida por módulo.

## Capabilities

### New Capabilities

- `company-settings-onboarding`: hub, wizard, modal, banner, readiness.

### Modified Capabilities

- `menu-core-pillars`: subítem Configuración de la empresa bajo
  `empresas`.

## Impact

- Seed Functionality + MenuBuilder; Policy `company-settings.*`.
- Pages Inertia hub/wizard; shared props shell (banner/modal).
- Reutilizar Update company / logo / Create workplace.
- Tests Feature menú, readiness, modal dismiss, banner.
- Admin.md + glosario si hace falta término.
