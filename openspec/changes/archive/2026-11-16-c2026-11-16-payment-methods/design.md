# Design

## Context

Brief `c2026-11-16-payment-methods`. Catálogos hermanos: Units / Sectors /
Banks (global, Configuración, ServerTable + modales). v1 `payment_methods`
es global; no portar `code`/flags/`accounting_account_id` en este change.
Dirección B (pivot rico) documentada, no implementada.

## Goals / Non-Goals

**Goals**

- `PaymentMethod` global: `name` traducible + `status`.
- CRUD Configuración; Functionality + menú; docs.

**Non-Goals**

- `company_payment_methods`; `accounting_account_id`; `code`; flags v1;
  import legacy; seeder de datos de negocio obligatorios (opcional vacío
  o mínimo documentado).

**E2E:** no aplica.

**Excepciones a standards:** ninguna.

## Decisions

### D1 — Modelo

- Tabla `payment_methods`: `id`, `name` JSON (Spatie `HasTranslations`),
  `status` string + enum `PaymentMethodStatus` Active/Inactive, soft
  deletes, audit/timestamps.
- Sin `company_id`, sin `slug` obligatorio (name i18n basta; si UniqueSlug
  aporta poco sin code — **decisión: sin slug** salvo que el generador lo
  exija; preferir sin slug).
- Alternativa descartada: name string monolingüe; code canónico.

### D2 — CRUD UI

- Espejo Units/Sectors: Index `<ServerTable>` (name; Acciones
  toggle→Edit→Delete; sin columna Estado dedicada si el patrón del
  catálogo lo omite); Create/Edit **modales** con `LangTabs` (name por
  locale; default locale required).
- Active-first + name en sorts por defecto.
- Rutas `/admin/payment-methods` (+ `*/table`, status).

### D3 — Authz / menú

- Functionality slug `payment-methods` bajo Module `configuracion`
  (AdminOnly).
- Permisos canónicos `payment-methods.*` + Policy.
- Visibilidad menú = capas Configuración (SA / AND) como Bancos/Sectores.
- Label UI: «Métodos de pago».

### D4 — Vinculaciones §6

| V | ¿Sí? | Nota |
|---|------|------|
| V-07 | Sí | Functionality + permisos |
| V-08 | Sí | Ítem Configuración |
| V-09 | No | Module `configuracion` existente |
| R-TEN-05 | N/A | Catálogo global (excepción R-TEN-01) |
| resto | No | — |

### D5 — Futuro (solo nota)

- Tras `accounting_accounts`: pivot `company_payment_methods`
  (`company_id`, `payment_method_id`, `accounting_account_id` nullable)
  para filtrar UX y cuenta tenant. Documentos FK → `payment_methods.id`.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Sin `code` para import | Aceptado; legacy/resolver = change futuro si hace falta |
| Listas largas en docs sin pivot | Aceptado hasta company_payment_methods |

## Migration Plan

1. Migración + modelo + Functionality seed.
2. CRUD backend/UI + menú.
3. Docs + tests.

## Open Questions

Ninguna.
