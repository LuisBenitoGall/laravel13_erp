<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-26-company-currencies
Rule: 10-architecture
Summary: Asociar a cada Company (matriz o Delegación) monedas de uso con
  opción A: N permitidas + 1 default. UI en Edit (sección en Información,
  sin tab propio). Create puede quedar sin moneda; el hub company-settings
  (onboarding) incorpora el paso Monedas y la readiness lo exige para la
  company en sesión.
Goals:
- Persistencia por Company: monedas permitidas + exactamente una default
  (cuando haya monedas configuradas); la default siempre pertenece al set
  permitido. Misma regla para matriz y Delegación (ambas son Company).
- Modelo tipo pivote `company_currencies` (company_id, currency_id,
  is_default); naming inglés normativo lo fija 10; monedas del catálogo
  global `Currency` Active.
- UI Edit Company y Edit Delegación: sección «Monedas» dentro del tab
  Información (no tab propio): select default + checks de permitidas;
  mostrar code/símbolo (+ nombre); ayuda corta (“documentos nuevos usarán
  esta moneda por defecto”); aviso ligero si Currency Inactive.
- Create Company / Create Delegación: **no** exigir moneda; se completa en
  Edit (y, para la matriz en sesión, vía onboarding).
- Hub `company-settings`: nuevo paso real «Monedas» en el asistente/tabs;
  readiness computada pasa a exigir también moneda por defecto de la
  company en sesión (además de nif + logo + ≥1 workplace Active). Soft-gates
  existentes (modal «Ahora no» + banner) se mantienen — sin muro duro.
- Glosario + ficha Admin (+ module-map si aplica) actualizados.
- Tests Pest: pivote/default único, Edit matriz/delegación, Create sin
  moneda, readiness/onboarding con paso monedas, aislamiento por company
  (R-TEN-05).
NonGoals:
- Tab propio «Monedas» en Edit (aplazado si el panel crece).
- Tipos de cambio / snapshot en documentos comerciales; redondeo de
  importes convertidos; consumir ExchangeRate en UI de monedas de company
  (p. ej. aviso “falta par EUR↔USD”).
- API FX; bid/ask; multi-tipo; moneda por workplace.
- Heredar automáticamente monedas de la matriz a Delegaciones (cada
  Company es independiente).
- Multi-libro contable; historial de cambios de default.
- Cambiar el catálogo global `Currency` más allá de consumirlo.
- Muro total del ERP si falta moneda (solo guía onboarding como hoy).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Conversión monedas (maestro global ya
  archivado). Orden de producto: (1) conversión ✓ → (2) este change →
  (3) docs default+snapshot → (4) API.
- Opción A acordada 2026-11-25: N + 1 default por Company; descartadas B
  (solo `companies.currency_id`) y C (solo matriz hereda a hijas).
- UI: explore recomendó sección en `info` (no tab); Luis no pidió tab;
  Create sin moneda + onboarding sí (2026-11-26).
- Onboarding: hub solo de la company en sesión (= matriz; Delegación no
  es sesión). Delegaciones configuran monedas solo en su Edit.
- Chip en RecordSummary / columna Index: recomendación explore; 10 decide
  si entra en este change.
- Permisos: reutilizar update de company/delegación / company-settings
  salvo que 10 vea Functionality aparte necesaria (no debatido).
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Monedas de uso por Company (opción A)
El sistema SHALL permitir asociar a cada Company (matriz o Delegación) un
conjunto de monedas permitidas del catálogo global `Currency` y, cuando
exista configuración, exactamente una moneda por defecto que SHALL
pertenecer a ese conjunto. SHALL NOT exigir moneda en el Create.

#### Scenario: Configurar en Edit
- **WHEN** un actor autorizado edita una Company o Delegación y guarda
  default EUR más permitidas EUR y USD
- **THEN** esa Company tiene default EUR y permitidas {EUR, USD}

#### Scenario: Create sin moneda
- **WHEN** se crea una Company o Delegación sin configurar monedas
- **THEN** el alta tiene éxito y no hay filas de monedas aún

#### Scenario: Matriz y Delegación independientes
- **WHEN** la matriz tiene default EUR y la Delegación USA default USD
- **THEN** cada una conserva su propio set; no hay herencia automática

### Requirement: Sección Monedas en Edit (sin tab propio)
El sistema SHALL exponer en el tab Información de Edit Company y Edit
Delegación una sección Monedas (default + permitidas), sin añadir un tab
dedicado en este change.

#### Scenario: Sin tab Monedas
- **WHEN** el actor abre Edit Company o Edit Delegación
- **THEN** ve Monedas dentro de Información, no como tab hermano de
  hierarchy/sectors/workplaces

### Requirement: Paso Monedas en company-settings
El hub Configuración de la empresa SHALL incluir un paso real Monedas
para la company en sesión. La readiness computada SHALL exigir moneda por
defecto además de nif, logo y ≥1 workplace Active. Soft-gates (modal
«Ahora no», banner) SHALL seguir aplicando sin bloqueo total del ERP.

#### Scenario: Readiness incompleta sin default
- **WHEN** la company en sesión tiene nif, logo y workplace Active pero
  no moneda por defecto
- **THEN** readiness incompleta y el shell puede mostrar el banner hacia
  el hub

#### Scenario: Completar monedas en el hub
- **WHEN** el actor completa el paso Monedas con una default válida en
  company-settings
- **THEN** ese criterio de readiness queda satisfecho


## MODIFIED Requirements

### Requirement: Asistente con pasos reales / readiness (company-settings-onboarding)
La readiness del hub SHALL pasar de nif + logo + ≥1 workplace Active a
incluir también moneda por defecto de la company en sesión. El asistente
SHALL ofrecer el paso Monedas junto a identidad y sedes.

#### Scenario: Completo sin banner (actualizado)
- **WHEN** la company tiene nif, logo, ≥1 workplace Active y moneda por
  defecto
- **THEN** no se muestra el banner de configuración incompleta
