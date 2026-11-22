# Design

## Context

Mis módulos lista todos los Modules Active y permite Encender/Apagar también
Basic. `CompanyModuleActivationChecker` y `MenuBuilder` exigen
Active ∧ autorizado ∧ `company_modules` (salvo SA+AdminOnly). Brief:
`docs/prompts/c2026-11-22-company-modules-optional-only.md`.

Este change **corrige el contrato normativo** (kernel §5 +
`company-module-layers`) para alinearlo al producto debatido — no es un
atajo de implementación.

E2E §4: N/A. V-01..V-20 entidad nueva: N/A.

## Goals / Non-Goals

**Goals:**

- UI Mis módulos = solo Optional + Active global.
- Enable/Disable solo Optional; rechazo Basic/AdminOnly.
- Basic Active+autorizado = siempre on en menú/checker (sin fila
  `company_modules` obligatoria).
- AdminOnly: no en Mis módulos; SA bypass intacto.
- SA Mis módulos = company de sesión vinculada (ya vía `CurrentCompany`).
- Actualizar kernel §5 y delta spec.

**Non-Goals:**

- Rediseño de `company-module-authorizations`.
- CRUD catálogo Modules.

## Decisions

1. **«Basic siempre on» (mecanismo)**  
   En `CompanyModuleActivationChecker` y `MenuBuilder`: si el Module es
   `Basic`, Active global y autorizado para la company → **capas OK sin
   consultar** `company_modules`.  
   Optional: sigue Active ∧ autorizado ∧ encendido.  
   AdminOnly + SA: bypass existente.  
   Alternativa descartada: forzar filas `company_modules` indelebles para
   Basic — más estado y migraciones; el brief no exige persistir el
   encendido de Basic.

2. **Enable/Disable**  
   Al inicio de ambas Actions: si `level !== Optional` →
   `RuntimeException` (o 422). Index ya no ofrece UI; defensa en backend.

3. **Index query**  
   `Module::query()->where('status', Active)->where('level', Optional)`.
   Props `authorized` / `enabled` iguales; gris si !authorized.

4. **Kernel §5**  
   Reescribir el párrafo de activación: Mis módulos / capa 3 de toggle =
   Optional; Basic siempre disponibles si Active+autorizados; AdminOnly no
   se gestiona en Mis módulos (SA). Mantener elegibilidad Basic auto /
   Optional+AdminOnly manual.

5. **Tests**  
   - Index no incluye Basic/AdminOnly.  
   - Enable Basic/AdminOnly falla.  
   - Tras «Apagar» histórico de Basic, checker/menú siguen OK si autorizado.  
   - Optional no autorizado no enable; Optional autorizado enable/disable OK.

6. **SA perímetro**  
   Sin cambio de modelo: Mis módulos usa `CurrentCompany`; SA no elige
   otra company en esa pantalla. Documentar en spec; no ampliar a selector
   multi-company.

## Risks / Trade-offs

- [Filas `company_modules` huérfanas de Basic] → Mitigation: inocuas; checker
  las ignora para Basic.
- [Managers sin Basic autorizado] → Mitigation: `AuthorizeBasicModulesForCompany`
  sigue en alta/activación de company; fuera de alcance si falta auth.
- [AdminOnly en elegibilidad SA] → Mitigation: NonGoal de rediseño; pueden
  seguir autorizándose; simplemente no aparecen en Mis módulos.

## Migration Plan

1. Kernel + spec delta.  
2. Checker + MenuBuilder.  
3. Actions + Controller Index.  
4. UI.  
5. Tests. Sin migración BD.

## Open Questions

Ninguna bloqueante del brief.
