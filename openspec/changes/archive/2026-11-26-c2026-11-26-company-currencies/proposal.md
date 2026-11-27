# Proposal

## Why

Tras el maestro global de cotizaciones, cada Company (matriz o Delegación)
necesita declarar en qué monedas opera y cuál es la de libros/default. Sin
ese vínculo no hay base para defaults de documentos ni para el onboarding de
identidad operativa. El change cierra la opción A acordada (N + 1 default).

## What Changes

- Pivote `company_currencies` (`company_id`, `currency_id`, `is_default`) con
  Action de sync: set de permitidas + exactamente una default cuando el set
  no está vacío; Create Company/Delegación sin exigir moneda.
- Relación Eloquent en `Company` + UI sección «Monedas» dentro del tab
  Información de Edit Company y Edit Delegación (sin tab propio).
- Hub `company-settings`: paso real «Monedas»; readiness = nif + logo + ≥1
  workplace Active + **moneda por defecto**. Soft-gates (modal/banner) sin
  muro.
- Glosario, ficha Admin y module-map actualizados.
- Tests Pest (sync/default, Create sin moneda, Edit matriz/delegación,
  readiness, R-TEN-05).

## Capabilities

### New Capabilities

- `company-currencies`: monedas de uso por Company (pivote N:N + default;
  sync; sección Edit; aislamiento tenant).

### Modified Capabilities

- `company-settings-onboarding`: paso Monedas + readiness ampliada.
- `company-edit`: sección Monedas en tab Información (sin tab dedicado).
- `company-delegations`: misma sección Monedas en Edit Delegación Info.

## Impact

- Dominio Admin: migration pivote, relación `Company`, Action/FormRequest
  sync, props Inertia Edit Company/Delegación + hub company-settings,
  `CompanySetupReadiness` (3 pasos), i18n.
- Consume `Currency` Active; no modifica catálogo Currency ni ExchangeRate.
- Sin Functionality/menú nuevos (reutiliza update company/delegación /
  company-settings).
- E2E §4: N/A (flujo no catalogado).
- Sin BREAKING de API pública; **BREAKING** blando de readiness: companies
  ya “completas” pasan a incompletas hasta configurar default (banner).
