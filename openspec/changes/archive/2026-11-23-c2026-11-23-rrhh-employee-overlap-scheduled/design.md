# Design

## Context

Brief: `docs/prompts/c2026-11-23-rrhh-employee-overlap-scheduled.md`.
Hoy `AssertEmployeeNoOverlap` solo comprueba `active` con `end_date` y
`nuevo.start > end`. Indefinidos hacen early-return. Create cierra actives solo
si el nuevo nace `active`. Caso real: dos `scheduled` solapados en la misma
company.

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** Opción B por intervalos vs scheduled+active; matiz cierre actives;
tests; delta spec.

**NonGoals:** Limpieza BD local; solape vs ended; cambios de cron; Create sin
cerrar actives; UI ficha User.

## Decisions

1. **Definición de solape (intervalos cerrados en fechas)**  
   Periodo existente `[Es, Ee]` con `Ee = null` ⇒ +∞. Nuevo `[Ns, Ne]` igual.  
   Solapan si `Ns ≤ Ee` (o Ee ∞) **y** `Es ≤ Ne` (o Ne ∞).  
   Equivale a: no solapan si el nuevo empieza **después** del fin del otro, o
   termina **antes** del inicio del otro.  
   Alternativa descartada: seguir solo con `Ns > Ee` (opción A) — no cubre
   solapes cuando el nuevo tiene fin anterior al existente pero se pisan, ni
   scheduled.

2. **Universo de periodos**  
   Query misma `user_id` + `company_id`, `status IN (scheduled, active)`,
   `withoutGlobalScope('company')`. Ignorar soft-deleted (default Eloquent).

3. **Exclusiones del assert (matiz cierre)**  
   - Parámetro `exceptEmployeeId` (Renew: id del current que se cierra).  
   - Parámetro o flag `ignoreActivesBeingClosed` / lista de ids: cuando Create
     determina que el nuevo status es `Active`, excluir todos los `active`
     actuales de ese user+company (se cerrarán justo después).  
   Implementación preferida: firma  
   `handle(userId, companyId, newStart, newEnd, ?exceptIds = [])`  
   y Create pasa los ids de actives actuales si `initialFor === Active`; Renew
   pasa `[current->id]` (y si Create anidado vuelve a assert, los actives ya
   están ended — OK).  
   Cuidado: Renew llama assert **antes** de cerrar y luego `CreateEmployee`
   (que vuelve a assert). Tras el update a ended, Create no debe ver el
   current; si Create además cierra actives, no debe haber otros. Pasar
   `exceptEmployeeId` en Renew y, en Create tras Renew, el active ya es ended.

4. **FormRequests**  
   Store/Renew deben pasar también `end_date` al assert (hoy solo start).
   Misma regla en Action y Request (o solo Action si Request delega igual).

5. **i18n**  
   Reusar/adaptar `empleo_solape_con_activo` (mensaje genérico de solape con
   fecha de referencia) — sin inventar copy de producto; 20 puede ajustar clave
   si el texto dice solo “activo”.

6. **Tests**  
   - Rechazo scheduled+scheduled (fechas del caso ACME).  
   - OK día siguiente al fin.  
   - Cross-company OK.  
   - Renew indefinido / Create que nace active con active previo → OK + D−1.  
   Actualizar test que solo cubría opción A si queda obsoleto.

## Risks / Trade-offs

- [Create scheduled que solapa un active con fin futuro] → Mitigation: B
  rechaza (correcto; hoy opción A también si active tiene end y start ≤ end).
- [Create scheduled vs active indefinido] → Solape infinito ⇒ rechazar; para
  reemplazar hay que Renew o Create que nazca active (cierra). Aceptado.
- [Doble assert Renew→Create] → except + status ended evitan falso positivo.

## Migration Plan

Solo código + tests + spec. Sin migration. Datos locales solapados quedan
hasta corrección manual (NonGoal).

## Open Questions

Ninguna bloqueante.
