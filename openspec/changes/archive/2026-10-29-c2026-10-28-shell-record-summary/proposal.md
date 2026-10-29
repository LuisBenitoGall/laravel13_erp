## Why

Edit/show necesitan una **ficha de registro** fija (card blanca) con título,
identificador y metadatos, como en v1. Hoy no existe el componente; Index/Create no lo
usan. Hay que dejar la pieza de catálogo lista antes de dominios que la consuman.

## What Changes

- Componente `RecordSummary` (nombre a fijar en design): card, título + badge de
  identificador + array de campos i18n; tono de estado opcional.
- Contrato/helpers para campos frecuentes de auditoría (created_at/by, updated_at/by).
- Registro en `frontend.md`; sin migración masiva de Edit; sin totales ni lápiz inline.

## Capabilities

### New Capabilities

- `shell-record-summary`: componente y contrato de ficha de registro edit/show.

### Modified Capabilities

- _(ninguna spec de comportamiento de dominio)_ — solo catálogo frontend vía tasks.

## Impact

- `resources/js/Components/Records/RecordSummary.tsx` (o ruta acordada)
- Posible `lib/status-colors` mínimo o tone CSS local hasta mapa de enums
- `docs/standards/frontend.md`
- i18n claves de labels genéricos (creado, autor, …)
- Test de render del componente (no E2E de negocio)

## Excepciones a standards

Ninguna. Ficha módulo SaaS: N/A.
