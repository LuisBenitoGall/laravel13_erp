## Context

Brief: `docs/prompts/c2026-10-26-shell-navigation.md`. Fallo real: SA + company →
`module:admin` 403. `MenuBuilder` solo eligibleIds; `PostLoginRedirect` →
`admin.modules.index`. Seed demo ≠ ACME.

## Goals / Non-Goals

**Goals:** aside logos; Dashboard; Configuración SA siempre + bypass AdminOnly;
flujo SA = resto; seed ACME.

**Non-Goals:** maestros Fase 1; Tienda/Consultas; ver-all companies; bypass capas para
Basic/Optional de tenants no-SA; KPIs dashboard.

## Decisions

1. **Bypass middleware solo AdminOnly + Super Admin**  
   En `EnsureModuleIsActive` (o checker): si user es Super Admin **y** el Module del
   slug es `ModuleLevel::AdminOnly` **y** está Active global → `true`.  
   Basic/Optional siguen exigiendo Active+autorizado+encendido también para SA (el SA
   opera en contexto de company; no “enciende el mundo” vía URL).  
   Alternativa descartada: SA salta `module:` en cualquier slug — demasiado amplio y
   choca con “ceñirse al resto” en módulos de negocio.

2. **MenuBuilder**  
   - Prefijo fijo: ítem Dashboard (`route('dashboard')`, label i18n).  
   - Cuerpo: módulos eligible + permisos (como hoy) **excluyendo** AdminOnly del filtro
     tenant (AdminOnly no se muestra a no-SA).  
   - Si SA: anexar/ensamblar grupo label `__('configuracion')` con Functionalities Active
     de todos los Modules Active con level AdminOnly (hoy `admin`), con hrefs existentes;
     **sin** exigir authorized∩enabled. Eliminar o absorber `splitPlatformGroup` en ese
     bloque.  
   Alternativa descartada: fake links a IVA/países.

3. **Agrupación cuenta / empresa**  
   Mínimo viable: etiquetar/agrupar funcionalidades tenant existentes (p. ej.
   `company-roles` bajo “Empresa”) vía mapa estático en MenuBuilder o meta en
   Functionality — **sin** migración de BD obligatoria. 20 implementa mapa slug→grupo
   (`cuenta`|`empresa`|`default`) solo para slugs existentes.  
   Alternativa descartada: columna `menu_group` en BD en este change (se puede anotar
   follow-up).

4. **AppLayout logos**  
   - ERP: asset/config (`config/branding.php` o `config('app.logo_url')`) — path público
     placeholder si no hay fichero.  
   - Company: `tenant.logo_url` ya compartido.  
   Orden: ERP → company → nav.

5. **PostLoginRedirect**  
   Dejar de ramificar SA a `admin.modules.index`. Todos → `dashboard` (tras company
   middleware / select). Flujo sin company: ya existe onboard create company — asegurar
   que SA cae ahí (middleware `company` / redirect auth), no a Admin.

6. **Seed ACME**  
   Idempotente: company name ACME, nif `00000000A`, slug estable (`acme`); Super Admin
   de `config/superadmin.php` vinculado + manager role + authorize/enable **workspace**
   (Basic). AdminOnly: **no** obligatorio encender `admin` en ACME (el bypass + menú SA
   lo cubren). Migrar referencias “Demo Company” / `demo` en seeder a ACME o crear ACME
   y mantener demo solo si tests lo requieren — preferencia: **ACME como company de
   trabajo del SA**; actualizar tests que hardcodeen demo si rompen.

7. **E2E**  
   Feature suficientes; E2E N/A salvo task opcional login SA → ve Configuración.

8. **Kernel / standards**  
   Design documenta excepción menú+middleware AdminOnly para SA; **no** cambia el
   perímetro de datos. Si hace falta una línea en kernel.md, PR de estándar en el mismo
   change solo si 10 lo considera normativo (preferible nota en menu-visibility spec).

## Risks / Trade-offs

- **[Riesgo]** SA entra a AdminOnly sin “encender” admin → OK producto; tenants no ven
  esas rutas (middleware + menú).  
- **[Riesgo]** Tests que esperan redirect SA a modules.index → actualizar.  
- **[Trade-off]** Grupos cuenta/empresa por mapa estático vs BD — suficiente Fase 0.5.

## Migration Plan

1. Middleware/checker SA AdminOnly.  
2. MenuBuilder + AppLayout + i18n.  
3. PostLoginRedirect + seed ACME.  
4. Tests + ajustar seed/demo.  
5. Docs ficha Admin breve.

## Open Questions

Ninguna bloqueante.

## Excepciones a standards

Excepción acotada a R-AUT-08 **solo** para Super Admin + ModuleLevel AdminOnly (menú y
`EnsureModuleIsActive`). Datos siguen R-TEN / kernel §3. Declarar en
`menu-visibility` spec; no abrir “ver todas las companies”.

## E2E

No aplica por defecto (CRUD/shell); Feature cubre escenarios críticos.
