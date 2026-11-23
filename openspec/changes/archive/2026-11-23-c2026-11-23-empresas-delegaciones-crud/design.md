# Design

## Context

Brief: `docs/prompts/c2026-11-23-empresas-delegaciones-crud.md`.
Company ya tiene `parent_id`, tab Jerarquía (no crea hijas), CreateCompany
siempre llama `AssignDefaultCompanyAccount`. Topbar `CompanySwitcher` + rutas
`companies.select` / `companies.switch` usan companies del vínculo sin filtrar
hijas. Module `empresas` sin Functionality `delegations`.

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** CRUD Delegaciones; alta diferenciada sin company_accounts; ≥1
workplace; creador vinculado; switcher solo matrices; menú.

**NonGoals:** Sociedad en pedidos; listados docs del grupo; N:M workplace↔delegación
amplio; atajos “entrar como delegación”; cambiar Create Mis empresas salvo
reutilización interna.

## Decisions

1. **Modelo = Company hija**  
   Sin tabla Delegacion. Index: `Company::where('parent_id', CurrentCompany::id())`
   (y soft-delete habitual). La sesión **debe** ser matriz (`parent_id` null);
   si no, 403/redirect al intentar CRUD delegaciones.

2. **Action `CreateDelegation`**  
   Transacción dedicada (no el mismo FormRequest/ruta que CreateCompany):
   - Valida name, tradename?, nif, status + datos del **primer Workplace**
     (mínimo para cumplir ≥1; reutilizar validación Workplace existente).
   - Crea Company con `parent_id` = CurrentCompany (ignorar parent del client).
   - Reutiliza trozos de CreateCompany: slug, default roles, attach creator,
     manager, authorize Basic si Active, sync manager perms.
   - **Omite** `AssignDefaultCompanyAccount`.
   - Crea Workplace Active con `company_id` = nueva delegación.
   Alternativa descartada: flag en CreateCompany — más fácil confundir flujos.

3. **Edit / Delete**  
   Edit ficha propia (campos empresa + gestión workplaces de esa company, o
   enlace a sedes filtradas). Delete = soft delete Company hija si Policy OK
   (sin usuarios bloqueantes según reglas Company existentes, o las que ya
   apliquen). No inventar borrado en cascada de matriz.

4. **Authz**  
   Functionality slug `delegations` (kebab) bajo `empresas`; permisos Spatie
   estándar; Policy sobre Company acotada a “es hija de CurrentCompany”.
   Seeder/RealignMenuCatalog + sync manager.

5. **Switcher / select / switch**  
   Filtrar `available_companies` (Inertia share) y listados de
   `companies.select` a `parent_id IS NULL`. `companies.switch` / store:
   rechazar id con `parent_id` no null (aunque esté en user_companies).
   Post-login auto-sesión: si solo quedan matrices visibles, OK; si el user
   solo estuviera vinculado a delegaciones (raro), design: no auto-entrar en
   hija — mostrar select vacío/mensaje (edge); no inventar bypass.

6. **Menú**  
   Functionality `delegations` visible en aside Empresas; href Index
   `/admin/delegations` (o `/admin/company-delegations` — 20 elige alineado a
   rutas admin).

7. **Jerarquía tab**  
   Sigue pudiendo albergar companies existentes; no es el Create de
   Delegación. Documentar en Notes de ficha si hace falta.

8. **Tests**  
   Feature: create sin company_accounts; parent_id correcto; workplace≥1;
   switcher/share sin hijas; switch a hija → error; create Mis empresas sigue
   con account.

## Risks / Trade-offs

- [Creator vinculado a delegación pero switcher no la muestra] → Aceptado
  (oleada 1); vínculo sirve a perímetro futuro / docs.
- [EnsureCompanyAccountIsActive si alguien fuerza sesión hija] → Sin fila
  Active → 403; coherente.
- [Workplace solo en alta vs CRUD sedes] → Alta obliga 1; edición posterior
  vía workplaces de esa company_id (tenant = delegación) choca con “no
  sesión hija”. **Decisión oleada 1:** el primer workplace se crea en el
  alta; editar sedes de la delegación desde el Edit de Delegación (acciones
  con `company_id` explícito de la hija, sin cambiar CurrentCompany), o
  listar workplaces de hijas en ese Edit. No abrir sesión en la hija.

## Migration Plan

1. Functionality + permisos + menú.  
2. CreateDelegation + routes/controllers/policy.  
3. UI Index/Create/Edit.  
4. Filtro switcher/select/switch.  
5. Tests. Sin migration BD nueva (usa companies + workplaces).

## Open Questions

Ninguna bloqueante del brief.
