## Context

Brief: `docs/prompts/c2026-10-17-platform-roles.md`. Ya existen Spatie Teams,
`EnsureCompanyDefaultRoles` (manager/guest locales), `SyncManagerPermissionsForCompany`,
Super Admin (`company_id` NULL), catálogo Admin. Falta el entorno 1: UI y modelo de
roles de plataforma (universal vs genérico), alineado a v1 Configuración→Roles sin
clonar stack ni techo por checkbox de negocio.

## Goals / Non-Goals

**Goals:**
- CRUD Super Admin de roles con `company_id` NULL + `universal` + `description`.
- Plantillas universales `manager`/`guest` no borrables; `Super Admin` inmutable.
- Matriz de permisos; universales sin AdminOnly; techo tenant = módulo completo.
- Materializar universales en cada Company al crearla; sync manager local intacto.

**Non-Goals:**
- Asignación User↔rol en UI (excepto onboarding vigente).
- Roles custom del manager de tenant.
- Consola Platform (billing/tenants).
- Cambiar tres capas ni Gate::before de Super Admin.
- Recortar acciones sueltas del acceso de empresa desde plantilla universal.

## Decisions

1. **Modelo de filas Spatie**  
   - Plataforma / plantilla: `company_id` NULL.  
   - `universal = true`: usable por tenants (defaults); se materializa como rol local
     (`mismo name`, `company_id` = empresa) al crear Company.  
   - `universal = false`: genérico de plataforma; **no** materializa ni es asignable a
     tenants.  
   - Locales de empresa: `company_id` set; fuera de este CRUD (UI tenant = change
     posterior).  
   Alternativa descartada: un solo rol global asignado a todos los tenants sin copia
   local — rompería Teams y el sync actual por company.

2. **Columnas** `roles.universal` (bool, default false), `roles.description`
   (string/text nullable). Sin tabla nueva.  
   Alternativa descartada: tabla `role_templates` — duplicaría Spatie.

3. **Protegidos**  
   - `Super Admin`: no listar como editable; cualquier update/delete rechazado.  
   - Plantillas `manager` y `guest` (`company_id` NULL, `universal`): no delete; name
     inmutable. Description y (según UX abajo) matriz sí/no.  
   Alternativa descartada: soft-delete de manager/guest — contradice el brief.

4. **Matriz de permisos (UX)**  
   - **Genéricos de plataforma**: editable; incluye módulos AdminOnly.  
   - **Universales (incl. manager/guest)**: omitir AdminOnly.  
   - **Plantilla `manager`**: matriz **solo lectura** (o mensaje i18n): los permisos
     efectivos viven en el rol local vía `SyncManagerPermissionsForCompany`; la
     plantilla **no** es techo.  
   - **Plantilla `guest`**: matriz editable en plantilla (por defecto vacía); al
     materializar se copian permisos de la plantilla al local (snapshot).  
   - **Otros universales nuevos**: matriz editable (sin AdminOnly); al materializar,
     copiar `role_has_permissions` de plantilla → local.  
   Alternativa descartada: matriz de manager como techo del sync (Luis: acceso modular
   es total).

5. **Materialización de universales nuevos**  
   - Al **crear Company**: `EnsureCompanyDefaultRoles` itera plantillas
     `universal=true AND company_id IS NULL` (excl. Super Admin) → `findOrCreate`
     local + copiar permisos (excepto `manager`: permisos solo vía sync posterior).  
   - Universales creados **después**: solo companies **futuras** al nacer.  
   - Comando/artisan opcional `roles:materialize-universals` para backfill en
     companies existentes (idempotente).  
   Alternativa descartada: push automático a todas las companies al guardar plantilla
   (coste + sorpresa; fuera de alcance mínimo).

6. **Destroy**  
   Permitido para roles plataforma no protegidos (`Super Admin`, `manager`, `guest`).
   Si el rol tiene asignaciones `model_has_roles`, rechazar o exigir confirmación
   explícita — **rechazar** si tiene users (más seguro; UI muestra motivo).

7. **Autorización / menú**  
   Solo Super Admin opera este CRUD (`Gate::before` basta; Policy niega al resto).
   Functionality `roles` en módulo `admin` (AdminOnly) para menú y permisos futuros
   `roles.viewAny|…`. Seed en `AdminCatalogSeeder`.

8. **UI**  
   Listado `<ServerTable>` contrato `*/table`. Edit: cabecera (name, description,
   universal) + pestañas/secciones por Module → Functionalities → checkboxes
   `viewAny/view/create/update/delete`. Referencia visual v1; stack Inertia/React.
   i18n claves es snake_case (canon vigente).

9. **E2E**  
   N/A: CRUD Admin, no flujo de `e2e.md` §4. Cobertura Feature (+ Arch si aplica).

10. **Glosario / ficha**  
    Añadir términos Rol universal / Rol de plataforma; actualizar `docs/modules/Admin.md`.
    module-map: sin dependencia nueva (Admin sigue leyendo Partners solo donde ya lo hace).

## Risks / Trade-offs

- **[Riesgo]** Confundir plantilla (`company_id` NULL) con rol local en queries.
  **Mitigación**: scopes `platform()` / `universalTemplates()`; UI solo plataforma;
  tests explícitos.
- **[Riesgo]** Crear universal nuevo no aparece en companies ya existentes.
  **Mitigación**: documentado + comando backfill; aceptado en Decision 5.
- **[Trade-off]** Matriz manager solo lectura puede sorprender vs v1 editable.
  **Mitigación**: copy i18n claro; techo modular acordado con negocio.
- **[Riesgo]** Borrar rol plataforma con users asignados.
  **Mitigación**: delete bloqueado si hay pivot.

## Migration Plan

1. Migración columnas + seed plantillas manager/guest universales + Functionality `roles`.
2. Actions/Policy/HTTP + UI.
3. Extender `EnsureCompanyDefaultRoles` + comando backfill.
4. Tests Feature; actualizar ficha Admin + glossary.
5. Rollback: drop columns; plantillas NULL se pueden dejar (innocuas) o borrar en down
   solo si no hay FKs de negocio.

## Open Questions

Ninguna bloqueante (decisiones 4–6 cierran el brief). Revisión humana (Luis) del DoR
antes de `/opsx-apply`.

## Excepciones a standards

Ninguna.

## E2E

No aplica (`e2e.md` §4).
