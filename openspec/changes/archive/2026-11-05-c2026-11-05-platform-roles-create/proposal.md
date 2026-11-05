# Proposal

## Why

El Index de roles de plataforma ya existe, pero el CTA «Crear rol» sigue stub
(`disabled`). Create/store backend y página parcial existen; hay que **activar**
el alta alineada a v1 (metadatos en Create → permisos en Edit) y dejar el
formulario reutilizable para roles tenant más adelante.

## What Changes

- CTA Crear del Index navega a Create (ya no stub).
- Formulario Create de roles **globales** (`company_id` NULL) al estilo v1:
  nombre, descripción, interruptor universal; **sin** matriz de permisos en el
  alta.
- Tras guardar: redirect a Edit (ya ocurre en controller) donde se atribuyen
  permisos (matriz existente).
- Componente de formulario pensado para reutilizar en roles de empresa (mismo
  shell; distinta matriz/catálogo de permisos en un change posterior).
- Actualizar spec `platform-roles`: retirar stub; contrato Create v1-like.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `platform-roles`: Create usable desde Index; campos Create = metadatos (+
  universal); matriz solo en Edit.

## Impact

- `Admin/Roles/Index.tsx`, `Create.tsx`, `RoleForm` (o split Create vs Edit).
- Tests Feature que exigen CTA stub disabled.
- Company roles: **no** cablear Create tenant en este change (solo diseño
  compartible).
- E2E: no aplica (`e2e.md` §4).
