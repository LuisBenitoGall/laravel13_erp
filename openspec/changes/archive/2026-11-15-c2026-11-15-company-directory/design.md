# Design

## Context

Tras `c2026-11-15-company-sectors-attribution`, Company tiene N:N con
Sector vía pivot `company_sector`. Module `empresas` ya tiene Mis empresas
(`companies`). El brief pide un **Directorio** de descubrimiento (layout
escaparate), no un Index Tabulator.

## Goals / Non-Goals

**Goals**

- Functionality `company-directory` → `/admin/company-directory` y Show
  informativo.
- Acceso menú usable con `empresas.access` + `.view` sembrado a roles
  básicos.
- Aside: checkboxes sectores `Active`; filtro **OR** (`whereHas` sectors
  whereIn).
- Grid cards: logo, name, datos útiles; sort name asc/desc +
  `created_at` desc; paginación.
- Universo: `status=active`, NIF no vacío, ≥1 sector. Sin `is_public`.
- Excepción documentada a ServerTable.

**Non-Goals**

- CTA contactar / alta cliente-proveedor.
- `is_public` en Company.
- CRUD Mis empresas, catálogo Sector, tab company-sectors.
- Draft/closed en el directorio.
- Filtro AND multi-sector.

## Decisions

### D1 — Authz y menú

- Functionality slug `company-directory` bajo Module `empresas`.
- Permisos Spatie: al menos `company-directory.view` (y comodín módulo
  ya existente `empresas.access`).
- Sembrar `company-directory.view` a los mismos roles básicos que ya
  reciben el acceso práctico a Empresas (alineado Realign/seed vigente),
  de modo que en la práctica no se exija un permiso “extra” que frene
  a esos roles.
- Policy: `viewAny` / `view` del directorio **no** reutilizan el scope
  `CompanyPolicy::viewAny` de Mis empresas (`forUser`). El listado del
  directorio es catálogo filtrado por universo (Active+NIF+sector),
  acotado al perímetro Super Admin / sesión (sin filtrar por
  user_companies del actor como Mis empresas). Show: visible si la
  company está en el universo elegible (o 404).

### D2 — Query universo y OR

```text
Company::query()
  ->where('status', Active)
  ->whereNotNull('nif')->where('nif', '!=', '')
  ->whereHas('sectors')  // ≥1
  ->when($sectorIds, fn ($q) =>
      $q->whereHas('sectors', fn ($s) => $s->whereIn('sectors.id', $sectorIds))
  )
```

Sectores del aside: solo `Sector` Active, orden estable (code/name).

### D3 — UI: excepción ServerTable

- Superficie de **descubrimiento** (grid + aside), no Index operativo
  Tabulator. Declarada aquí como excepción a `frontend.md` / listados
  ServerTable.
- Interacción: query string Inertia (`sector_ids[]`, `sort`, `page`) o
  visita parcial equivalente; sin endpoint Tabulator `*/table`.

### D4 — Sort y paginación

- Sort: `name` asc | `name` desc | `created_at` desc (default acordado:
  name asc o más recientes — **default: name asc**).
- Paginación Laravel estándar (LengthAware).

### D5 — Card y Show

- Card: logo (si hay), name, tradename/NIF según datos útiles ya en
  Company; click → Show.
- Show: ficha informativa (identidad + sectores vinculados). **Sin**
  botones contactar / cliente / proveedor.

### D6 — Sin modelo CompanySector

- Relación Eloquent `sectors()` / pivot existente; no introducir modelo
  pivot.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| Confundir Directorio con Mis empresas | Rutas y Policy distintas; menú dos entradas |
| Listado “global” vs perímetro SA | Show/Index solo companies del universo; SA sigue sin companies no vinculadas en otras superficies; documentar que el Directorio es catálogo de descubrimiento Active+NIF+sector |
| Excepción ServerTable se propague | Nota explícita: solo esta superficie |

## Migration Plan

- Seed/Realign Functionality + permisos; MenuBuilder.
- Sin migración de esquema (pivot ya existe).

## Excepciones a standards

| Estándar | Excepción | Justificación |
|----------|-----------|---------------|
| `frontend.md` listados = `<ServerTable>` | Index Directorio = aside + grid cards | Superficie de descubrimiento (escaparate), no Index operativo; acordado en brief |

E2E Pest browser (rol 40): **no aplica** en este change (no toca flujo §4 e2e.md
obligatorio; cobertura Feature + UI smoke en 20/30).

## Open Questions

Ninguna abierta del brief; Brand/contactar → changes futuros.
