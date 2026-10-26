## Context

Brief: `docs/prompts/c2026-10-26-company-role-assignment.md`. Ya existen
`AssignCompanyRole` (solo manager|guest), `CompanyRoleGate`, Members UI y roles custom
locales (`company-custom-roles`). Hay que unir asignación + customs sin segunda pantalla.

## Goals / Non-Goals

**Goals:** asignar cualquier rol local; un rol por user/company; UI Members ampliada;
mismo gate.

**Non-Goals:** CRUD roles; invitar users; roles plataforma; Form kit; Platform console;
múltiples roles simultáneos por company.

## Decisions

1. **Extender Action, no crear otra**  
   `AssignCompanyRole::handle` valida que exista `Role` con `name = $role` y
   `company_id = $company->id` (modelo dominio `Role` / Spatie con team). Rechazar si
   `company_id` NULL (plataforma) o distinto.  
   Alternativa descartada: Action `AssignCustomCompanyRole` separada.

2. **Un rol local a la vez**  
   Con team id = company: obtener roles del user cuyo `company_id` = company (o todos
   los roles en ese team) y `removeRole` de cada uno distinto al objetivo; luego
   `assignRole`. No tocar roles de otras companies ni Super Admin (team null).  
   Alternativa descartada: N roles concurrentes (UI select único lo contradice).

3. **Detección del rol actual en index**  
   No solo manager/guest: primer (o único) rol del user en el team de la company;
   exponer `role` string (name) + props `roles: [{ name, label? }]` para el select.  
   Alternativa descartada: segunda query N+1 sin eager — cargar roles del team una vez.

4. **FormRequest**  
   `Rule::in` dinámico desde roles locales de la company (pluck name), no enum fijo.

5. **UI**  
   Seguir en `Members.tsx`; options = prop `roles` del servidor. i18n: manager/guest
   con claves existentes; customs muestran `name` o `description` si hay.  
   Alternativa descartada: página nueva bajo workspace/company-roles.

6. **E2E**  
   N/A (`e2e.md` §4). Feature tests bastan.

7. **Ficha Admin**  
   Quitar “asignación User↔rol” del No entra (salvo matices: solo vinculados; no
   plataforma).

## Risks / Trade-offs

- **[Riesgo]** User con dos roles locales por bug previo. **Mitigación**: al asignar,
  remove all local roles of that company first.
- **[Riesgo]** Asignar custom sin permisos deja user “ciego”. **Mitigación**: aceptado
  (es el punto del recorte); manager responsable.
- **[Trade-off]** Label de custom = name técnico hasta i18n rica.

## Migration Plan

Sin migración BD. Orden: Action → Request → Controller props → UI → tests → ficha.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Ninguna.

## E2E

No aplica.
