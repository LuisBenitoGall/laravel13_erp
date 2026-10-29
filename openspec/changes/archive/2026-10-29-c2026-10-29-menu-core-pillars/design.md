## Context

Brief: `docs/prompts/c2026-10-29-menu-core-pillars.md`. `MenuBuilder` ya tiene Dashboard,
tenant por capas, Configuración SA con **todas** las Functionalities AdminOnly, y
`FUNCTIONALITY_GROUPS` (`company-roles` → empresa). Pantallas: modules, roles (+ matriz
en Edit), partners companies, members por company.

## Goals / Non-Goals

**Goals:** pilares Empresas/Usuarios/Configuración; Mis empresas label; Permisos→Roles;
Usuarios empresa→Members sesión; sin pantallas nuevas.

**Non-Goals:** menú PDF completo; CRUD permisos custom; listado usuarios global nuevo;
abrir AdminOnly a no-SA.

## Decisions

1. **Mis empresas**  
   Functionality `companies` (o la que alimenta `partners.companies.index`): label i18n
   `mis_empresas` / agrupación padre `empresas`. Perímetro sin cambio.  
   Otras AdminOnly hoy bajo Configuración (`company-module-authorizations`, etc.) **no**
   se renombran a Mis empresas; siguen en Configuración o se dejan como están salvo que
   estorben el pilar — preferencia: **Configuración SA muestra solo** (orden): Módulos,
   Roles, Permisos (+ opcional Functionalities si ya existe y es herramienta SA; si
   satura el menú, ocultar del bloque Configuración en este change dejando ruta directa).
   Decisión concreta: bloque Configuración lista **modules, roles, permissions** (y
   `functionalities` si se considera parte de “estructura de módulos”; default **incluir
   functionalities** junto a modules como herramienta SA, labels sin renombrar a pilares
   del PDF). `companies` **sale** del bloque Configuración y pasa a grupo Empresas como
   Mis empresas (tenant o SA: companies index ya respeta perímetro).

2. **Permisos (opción C)**  
   Nueva Functionality slug `permissions` (Active, bajo módulo `admin` AdminOnly) **o**
   ítem sintético solo en MenuBuilder. Preferencia: **Functionality `permissions`** en
   seed (idempotente) con href → `admin.roles.index` (entrada a Roles donde está la
   matriz vía Edit). Label `permisos`. No CRUD Spatie Permission standalone.

3. **Usuarios empresa (ambigüedad explore → decisión 10)**  
   Explore no eligió destino. Decisión: mismo espíritu que C — **entrada de menú** con
   href temporal a `partners.companies.members.index` de la **company de sesión**
   (`CurrentCompany`). Functionality slug `company-users` (o similar) en módulo Basic
   `workspace` (o grupo Usuarios), label `usuarios_empresa`. Sin listado global nuevo.  
   Alternativa descartada: ocultar subítem (rompería el pilar Usuarios del brief).

4. **Agrupación menú**  
   Extender `FUNCTIONALITY_GROUPS` / bloques:
   - `empresas` ← companies (Mis empresas)
   - `usuarios` ← company-users (+ company-roles puede quedarse en empresa o moverse;
     **no mover** company-roles en este change salvo que quede huérfano)
   - `configuracion` ← SA AdminOnly filtrado

5. **Nivel 3 solo SA**  
   Sin cambio de Gate: Configuración solo si `isSuperAdmin`; AdminOnly no en tenant
   eligibleIds (ya).

6. **E2E**  
   N/A. Feature: SA ve Configuración con permisos→roles; no-SA no ve Configuración;
   label Mis empresas; menú Usuarios empresa apunta a members.

## Risks / Trade-offs

- **[Riesgo]** Permisos y Roles dos ítems → misma área → Mitigation: copy i18n claro;
  change futuro CRUD permisos.  
- **[Riesgo]** Members requiere `{company}` → Mitigation: id sesión; si null, no ítem.  
- **[Trade-off]** Filtrar ítems Configuración puede “esconder” company-modules —
  quedan por URL; menú más limpio.

## Migration Plan

1. Seed labels + functionalities `permissions` / `company-users`.  
2. MenuBuilder grupos + hrefFor + filtro Configuración.  
3. i18n.  
4. Tests menú.

## Open Questions

Ninguna bloqueante (Usuarios empresa resuelto arriba).

## Excepciones a standards

Ninguna. CRUD permisos al margen de funcionalidades×7 = futuro (declarado NonGoal).

## E2E

No aplica.
