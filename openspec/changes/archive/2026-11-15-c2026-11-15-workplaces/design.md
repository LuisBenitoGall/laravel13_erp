# Design

## Context

Brief `c2026-11-15-workplaces`. v1: tabla `workplaces` con dirección en la
misma fila. v2: geografía Country/Province/Town ya en Admin; Module
`empresas` con Mis empresas + Directorio. Dominio único Admin
(module-map).

## Goals / Non-Goals

**Goals**

- `Workplace` + `BelongsToCompany`; CRUD ServerTable; menú Empresas.
- Dirección embebida MVP; `featured` + status enum string.
- V-07/V-08; R-TEN-05; glosario ≠ Worksite/Workspace.

**Non-Goals**

- Address polimórfica; logo/web/contactos/nif/zone/description.
- user_workplaces; cost centers; workplaces de terceros en pedidos.
- Google Places; módulo SaaS nuevo (V-09 no).

## Decisions

### D1 — Naming y dominio

- Código/tabla: `Workplace` / `workplaces`. UI: «Centro de trabajo».
- Dominio PHP **Admin**; actualizar module-map (lista entidades).
- Functionality slug `workplaces` bajo Module `empresas` (Basic).

### D2 — Esquema MVP

```
workplaces:
  id, company_id, name, slug
  address nullable, cp nullable (max 16), town_id nullable FK towns
  featured boolean default false
  status string (WorkplaceStatus: Active|Inactive)
  audit + softDeletes + timestamps
```

- `slug` auto (mismo patrón Company/Sector).
- País/provincia **no** se persisten: solo UI cascada → `town_id`.
- Dirección nullable (como v1); no bloquea alta mínima por name.

### D3 — `featured` (UI: Principal)

- Columna/código: `featured` (boolean). UI i18n: **Principal** (no «Destacado»).
- Semántica: centro **principal** de la company = dirección por defecto para
  facturación, pedidos y usos futuros que necesiten un domicilio operativo
  default (consumo en documentos = changes posteriores).
- Como máximo **un** featured/principal por `company_id`.
- Al marcar `featured=true`, Action desmarca el anterior de esa company.
- Create UI/backend default del Switch Principal:
  - `true` si no existe ningún workplace **Active** de la company
    (incluye el primer centro).
  - `false` si ya existe ≥1 workplace Active.
- El primer workplace de la company (tabla vacía) sigue forzándose
  `featured=true` en la Action Create.
- Principal puede desactivarse (permitir cero featured en MVP).
- Alternativa descartada: exigir siempre ≥1 featured (fricción delete).

### D4 — Status

- Enum PHP `WorkplaceStatus` sobre columna `string` (no ENUM MySQL).
- Toggle Active/Inactive en Index (patrón Units/Sectors) + editable en form
  (Select estado ~25% ancho junto al Switch Principal).

### D5 — Authz y rutas

- Permisos canónicos `workplaces.view|viewAny|create|update|delete` +
  comodín `empresas.access` (ya existe).
- Policy: permiso Spatie + `belongsToCurrentCompany` / scope global.
- Rutas `/admin/workplaces` (+ `*/table`, status toggle si aplica).
- Sembrar `.view` a roles básicos alineados con Empresas (menú usable).

### D6 — UI

- Index: `<ServerTable>` (name, población/cp, Principal/`featured`, status,
  Acciones).
- Create/Edit: páginas Inertia; layout form (lg+):
  - Dirección ~75% + CP ~25% en la misma fila.
  - Cascada país→provincia→town.
  - Estado ~25% + Switch **Principal** (`featured`) al lado.
- No modales (form largo). Delete: soft delete con confirmación (patrón
  catálogos).
- Acciones del form (Cancelar→Guardar) con `Form/FormActions` a la derecha
  (R-FE-09). Volver en Create/Edit → `/admin/workplaces` (`backHref`).

### D7 — Vinculaciones playbook §6

| V | ¿Sí? | Nota |
|---|------|------|
| V-07 | Sí | Functionality `workplaces` + permisos |
| V-08 | Sí | Menú Empresas → Centros de trabajo |
| V-09 | No | Reutiliza Module `empresas` |
| resto | No | — |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Confusión Worksite/Workspace | Glosario + nombres estrictos |
| Cascada geografía sin componente reutilizable | Selects + endpoints/props existentes del catálogo; documentar |
| Cero featured | Aceptado en MVP; reglas de pedidos = change futuro |

## Migration Plan

1. Migración → modelo → authz/menú → CRUD → tests.
2. Rollback: drop `workplaces`; quitar Functionality del Realign.

## Excepciones a standards

Ninguna.

E2E rol 40: **no aplica** (no flujo §4 e2e.md crítico).

## Open Questions

Ninguna.