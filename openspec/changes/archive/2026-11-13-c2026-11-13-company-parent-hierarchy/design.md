# Design

## Context

See proposal.md — Why. `companies` sin padre; Edit tabs Info/Imágenes;
Create mínimo intacto (brief). Perímetro Mis Empresas = `forUser` /
`user_companies`. Brands/UTE NonGoal.

## Goals / Non-Goals

**Goals:** `parent_id`; anti-ciclo; tab Edit; selectors solo vínculo user;
Create sin tocar.

**Non-Goals:** Brand; UTE; centros internos; copy/read multi-company;
cambiar NIF/slug/logo Info.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Schema**  
   `parent_id` unsignedBigInteger nullable, FK `companies.id`,
   `nullOnDelete` (si se borra el padre, hijos quedan huérfanos —
   coherente con soft-delete: preferir **no** cascade hard; con SoftDeletes
   la FK apunta al id; decisión: `nullOnDelete` en FK física + al soft-delete
   del padre **no** se tocan hijos automáticamente en este change — documentar;
   *alternativa descartada:* cascade set null en Action DeleteCompany en este
   change — opcional task si Delete ya existe y conviene limpiar).  
   Índice en `parent_id`.

2. **Modelo**  
   `Company::parent()`, `Company::children()`. Helper
   `isDescendantOf` / ancestors walk para anti-ciclo (sin closure table:
   profundidad típica baja; N niveles sin tope).  
   *Alt. descartada:* closure/path enum (overkill Fase 0).

3. **Operaciones del tab**  
   - **Padre:** asignar / quitar `parent_id` de la company editada
     (`UpdateCompanyParent` o endpoint dedicado).  
   - **Hijos:** “albergar” = elegir companies del vínculo y ponerles
     `parent_id = company editada`; “dejar de albergar” = poner
     `parent_id = null` en el hijo (no borra la Company).  
   No crear Companies nuevas desde el tab (Create sigue aparte).  
   *Alt. descartada:* CRUD hijas inline / Create con parent.

4. **Validación**  
   Request: `parent_id` nullable, `exists:companies,id`, ≠ self, está en
   `Company::forUser($actor)`, y no es descendiente de la company (ni la
   company es ancestro del candidato al reparentar hijos). Misma regla al
   adjuntar hijo. Authz: `update` + `assertLinked` en ambas companies.

5. **UI**  
   Tab id p. ej. `hierarchy` / i18n jerarquía. Select padre (opción
   “ninguno”) + lista hijas actuales + control para añadir/quitar hijas
   elegibles (vínculo user, no self, no ancestros, no ya hijas de otro si
   eso crea conflicto — un hijo solo un padre: al asignar se mueve).  
   Props Edit: `parent`, `children`, `hierarchy_options` (candidatos).

6. **Create**  
   Cero cambios de UI/request; `parent_id` null al alta.

7. **V-01..V-20**  
   Migración, modelo, Actions/Requests, Policy, rutas, i18n, tests
   Feature (árbol, ciclo, perímetro, Create sin tab). No Module nuevo.

## Risks / Trade-offs

- [Huérfanos al borrar padre] → nullOnDelete / soft-delete sin reparent
  automático; aceptable Fase 0.  
- [Walk O(depth)] → OK sin closure.  
- [Mover hijo que ya tenía padre] → un solo padre: reassign silencioso o
  confirm UI; **decisión:** reassign al confirmar add-as-child.

## Migration Plan

1. Migración + modelo.  
2. Actions/Requests/rutas.  
3. Tab Edit + props.  
4. Tests + Admin.md.  
Rollback: drop columna / revert.

## Open Questions

Ninguna bloqueante (brief cerrado).
