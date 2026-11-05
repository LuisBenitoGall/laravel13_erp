# Design

## Context

Brief canónico. Causa: `forMatrix` y `TogglePlatformRoleFunctionality::ACTIONS`
hardcodean 5 sufijos. Edit/toggles de `platform-roles-edit` ya están
archivados; este change solo amplía el **set** de la matriz plataforma.

## Goals / Non-Goals

**Goals:**

- Matriz muestra canónicos + custom por Functionality (mismo UI/toggles).
- «Todas las acciones» = todos los listados de esa card (incl. custom).
- `{*.access}` fuera de la card Functionality.

**Non-Goals:**

- `CompanyRolePermissionCatalog` / Edit company-roles.
- Cambiar alta de custom en catálogo Permisos.
- Meter comodín Module en la card Functionality.
- E2E; ficha módulo nueva.

## Decisions

### D1 — Fuente del set por Functionality

Para slug F, permisos de la card =

```
Permission::query()
  ->where('name', 'like', F.'.%')
  ->where('name', '!=', F.'.access')  // por si existiera; access es de Module
```

Orden de presentación: primero los 5 canónicos en orden fijo
(`viewAny`…`delete`) si existen; luego el resto de sufijos ordenados por
`name` (o sufijo). No inventar filas custom que no existan en BD.

**Descartado:** seguir hardcodeando 5 y “añadir custom aparte en otra UI”.

### D2 — Toggle Functionality («Todas las acciones»)

`TogglePlatformRoleFunctionality` deja de usar `ACTIONS` fijo: obtiene el
mismo set que D1 para ese slug (existentes en BD) y give/revoke todos.
No `findOrCreate` de custom inexistentes; canónicos pueden seguir
`findOrCreate` solo si 20 lo necesita por robustez — preferencia: solo
permisos ya en catálogo (alineado a “custom se crean en Admin Permisos”).

### D3 — Toggle individual

Sin cambio de API: ya recibe `permission` name. Asegurar que
`moduleSlugForPermission` resuelve por slug Functionality (ya lo hace) y que
custom no AdminOnly filtrados pasan `filterPermissionNames`.

### D4 — Labels UI

Canónicos: seguir mostrando la acción (`viewAny`, …). Custom: mostrar el
sufijo tras el punto (`limpiar-algunas-empresas`) o el name completo — 
**preferencia:** sufijo legible (mismo patrón que action). i18n solo si ya hay
clave; si no, sufijo crudo aceptable en este change.

### D5 — Spec

ADDED requisito custom (del brief) + MODIFIED «Matriz de permisos…» para
dejar de decir “solo las 5 acciones”. Escenarios previos se conservan;
«Toggle Functionality completa» queda interpretado como set visible.

### D6 — Vinculaciones

V-07/08/09 no. R-TEN-05 N/A (catálogo global / roles plataforma). E2E N/A.

## Risks / Trade-offs

- [Custom huérfanos sin Functionality] → no aparecen (prefijo no matchea slug).
- [Nombre `companies.access`] → excluido de card; no es permiso Functionality.

## Migration Plan

Solo código + tests. N/A datos.

## Open Questions

Ninguna.
