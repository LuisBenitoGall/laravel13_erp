# Design

## Context

Brief: `docs/prompts/c2026-11-24-empresas-delegaciones-kernel-polish.md`.
Archivados: `empresas-delegaciones-crud`, `empresas-workplace-delegation-map`.
Glosario ya tiene Delegación / mapa sede–delegación; kernel §2 aún no distingue
matriz vs hija ni invariantes de docs. Edit Delegación: `DelegationWorkplacesPanel`
(sedes `company_id` = hija); falta panel inverso RO.

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Constitución en standards/always-on; panel RO + copy; tests panel.

**NonGoals:** Pedidos/albaranes/facturas UI; almacenes/productos; cambiar
switcher, alta sin account, o escritura del mapeo en Edit Workplace.

## Decisions

1. **Orden: standards primero, luego UI**  
   Tasks 1 = kernel + architecture + glossary (si falta matiz) + `01-kernel.mdc`
   (+ CLAUDE solo si frase plana contradice). Este change **es** la ampliación
   de estándar (alineado a “change explícito” del kernel), no excepción en
   código.

2. **Texto canónico (borrador para 20; 10 fija en design al apply)**  
   Nueva subsección bajo User/Company (o “Grupo societario”) con los 6 puntos
   del brief Goals. Bloque “Invariantes aplazadas (documentos comerciales)”
   claramente marcado **SHALL al implementar Sales/Orders; no código en este
   change**. R-TEN nuevo o matiz (p. ej. R-TEN-11): Delegación no es sesión vía
   switcher; gate SaaS / `company_accounts` anclado a matriz de sesión.

3. **Panel RO**  
   Prop Inertia en `DelegationController@edit`: query pivote
   `workplace_delegation` where `company_id` = delegación, join workplaces
   where `workplaces.company_id` = `parent_id` de la delegación. Campos: id,
   name, is_preferred. Componente p. ej. `MatrixWorkplacesLinkedPanel` RO
   (sin form put). Empty state i18n. Copy Helptip o párrafo sobre (a) sedes de
   esta Delegación vs (b) sedes de la matriz vinculadas.

4. **Escritura del mapa**  
   Sigue solo en Edit Workplace (`WorkplaceDelegationsPanel`). El panel RO no
   enlaza obligatoramente al Edit de cada sede (nice-to-have link; no
   requerido por brief).

5. **Specs OpenSpec**  
   Comportamiento UI → delta `company-delegations` (+ nota en
   `workplace-delegation-map`). El texto del kernel no es delta spec de
   capability; vive en tasks de docs.

6. **Tests**  
   Feature: Edit Inertia prop / página con vínculo preferido y empty; no
   mutación desde RO. No test “lee el markdown del kernel” — verificación =
   diff de ficheros en review + checklist task.

## Risks / Trade-offs

- [Duplicar glosario vs kernel] → Kernel = invariantes; glosario = naming;
  cross-link, no dos verdades divergentes.
- [R-TEN-10 ya existe (lectura User)] → No reutilizar número; añadir R-TEN-11
  (o el siguiente libre) para grupo/delegación-sesión.

## Migration Plan

1. Standards + always-on. 2. Prop + panel RO + i18n. 3. Tests. Sin migration BD.

## Open Questions

Ninguna bloqueante del brief.

## Excepciones a standards

Ninguna: se **amplía** el estándar para reflejar el producto ya acordado.
