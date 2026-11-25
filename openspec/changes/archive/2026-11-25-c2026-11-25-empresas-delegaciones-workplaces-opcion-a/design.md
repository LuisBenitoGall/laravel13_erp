# Design

## Context

Brief: `docs/prompts/c2026-11-25-empresas-delegaciones-workplaces-opcion-a.md`.
Hoy `CreateDelegation` llama `createWorkplace->handle($workplaceData, $delegation)`.
Edit Delegación: panel RO matriz + `DelegationWorkplacesPanel` CRUD hijo.
Kernel § Grupo punto 4 describe Workplace de la Delegación. Pivote
`workplace_delegation` ya soporta N:N + preferida.

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Opción A end-to-end (datos, alta, Edit, migración, kernel/UI llana).

**NonGoals:** Pedidos; opción B; quitar pivote/preferida; cambiar switcher o
`company_accounts`.

## Decisions

1. **Dueño único**  
   Todo Workplace activo del grupo: `company_id` = matriz (`parent_id` null del
   árbol). Invariante enforceable en CreateWorkplace cuando el caller es
   Delegación flow: pasar `$matrix`, no `$delegation`.

2. **CreateDelegation**  
   - Path A (formulario actual de centro nuevo): `CreateWorkplace(..., $matrix)`
     luego attach pivote `(workplace, delegation, is_preferred=true)` si es el
     primer vínculo.  
   - Path B (complementario): `workplace_ids[]` existentes de la matriz →
     sync/attach pivote; validar `company_id` = matrix.  
   - Al terminar el alta: `count(vínculos) >= 1`.  
   - UI Create: mantener campos de centro nuevo **o** multi-select existentes
     (o ambos); copy llano (“Centro de trabajo de la empresa” / “Usar centros
     ya creados”).

3. **Edit Delegación**  
   Una sola sección “Sus centros”: mismos datos que el panel RO actual
   (`matrixLinkedWorkplaces`), enlaces a `/admin/workplaces/{id}/edit`. Empty
   state → CTA a Centros de trabajo o a Create con foco. Eliminar rutas
   `delegations.workplaces.*` y componentes CRUD hijo. Quitar jerga “mapa N:M”
   / “N:M” del copy usuario.

4. **Migración**  
   Command o migration data:  
   `Workplace` where company is Delegation (`parent_id` not null) → set
   `company_id = parent_id`; upsert `workplace_delegation` (company_id =
   antigua dueña). Colisiones `featured`: si la matriz ya tiene featured,
   el migrado pierde featured (o design elige keep-first). Soft-deleted:
   migrar igual o dejar (preferir migrar company_id para no dejar huérfanos
   lógicos). Arch test / feature: post-migrate cero workplaces con
   `company_id` in (ids de delegaciones) entre no soft-deleted.

5. **Standards**  
   Kernel: fusionar puntos 3–4 en “Workplace pertenece a la matriz; se asocia
   N:N a Delegaciones”. Glosario: eliminar/ajustar “Workplace de la
   Delegación”. R-TEN-11 / always-on: alinear. Invariantes docs comerciales:
   intactas.

6. **Tests**  
   Feature CreateDelegation (matriz+pivote); Edit sin CRUD; migration;
   SyncWorkplaceDelegations sigue OK.

## Risks / Trade-offs

- [Dos featured tras migrar] → Mitigation: regla explícita en migration.  
- [Usuarios con hubitos a rutas delegations/workplaces] → 404; OK.  
- [Empleados con workplace_id de sede migrada] → mismo id de fila; solo cambia
  company_id; verificar BelongsToCompany en queries HR (sesión = matriz → OK).

## Migration Plan

1. Standards. 2. CreateDelegation + quitar CRUD hijo. 3. UI. 4. Data migrate.
5. Tests.

## Open Questions

Ninguna bloqueante (featured collision = decisión 20 según “keep matrix
featured”).
