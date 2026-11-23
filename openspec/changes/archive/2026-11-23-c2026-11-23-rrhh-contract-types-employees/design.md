# Design

## Context

Brief: `docs/prompts/c2026-11-23-rrhh-contract-types-employees.md`.
Ficha: `docs/modules/HR.md`. v1: `contracts` / `employees` (company solo vía
join); Viuda Vila no usa la funcionalidad (0 filas). Kernel exige
`BelongsToCompany` en negocio. Mapa de capabilities: respuesta al coste de
contexto post always-on adelgazado.

E2E §4: **N/A** (no toca login/cambio empresa ni flujos listados).

## Goals / Non-Goals

**Goals:**

- Dominio HR con ContractType + Employee conforme brief.
- Module `rrhh` Optional + menú + permisos + V-21.
- Mapa `docs/standards/capabilities-map.md` usable por 10/20.
- Glosario y module-map alineados.

**Non-Goals:**

- Fichajes, calendarios, planning, vehículos, jobs de vencimiento.
- Crear User desde empleo; empleado sin `user_companies`.
- Import v1; Spatie translatable en ContractType (oleada 1 = strings).

## Decisions

1. **Dominio y rutas**  
   `App\Domains\HR`; rutas `routes/domains/hr.php`, prefijo `/hr`, middleware
   auth + company + `module:rrhh`.  
   Alternativa descartada: absorber en Admin — module-map ya separa HR (capa 4).

2. **Module level Optional**  
   Activable SaaS; elegibilidad no auto; Mis módulos Encender/Apagar.  
   Alternativa Basic: forzaría siempre-on a todos los autorizados; no pedido.

3. **ContractType**  
   Tabla `contract_types`: `company_id`, `name`, `slug` (único por company,
   inmutable tras create — mismo patrón slug Admin), `duration` int días
   **nullable**, `with_holidays` bool, `status` Active/Inactive, softDeletes.  
   **Indefinido = A:** `duration === null` (no flag extra; no usar 0). UI:
   switch «Indefinido» que vacía/deshabilita días; temporal exige duration ≥ 1.
   Index muestra «Indefinido» cuando duration es null. Soft-delete: ocultar de
   selects de alta; FK históricos intactos (`withTrashed` al mostrar).

4. **Employee**  
   Tabla `employees`: `company_id`, `user_id`, `contract_type_id`,
   `workplace_id` nullable, `job_title`, `start_date`, `end_date` nullable,
   `weekly_hours` decimal, `time_control` bool, `year_salary` decimal(12,2)
   nullable, `status` enum string `scheduled|active|ended`, `observations`,
   softDeletes.  
   Validación: user ∈ `user_companies` de CurrentCompany; contract_type y
   workplace (si set) del mismo `company_id`.  
   **end_date:** obligatorio si el tipo tiene `duration != null`; opcional si
   indefinido. UI puede sugerir `start + duration` días.  
   Unicidad: ≤1 `active` por (`company_id`,`user_id`).  
   Renovar: active→ended + create nuevo. Status inicial: `scheduled` si
   `start_date` > hoy; si no `active`.

5. **Alta UI**  
   Index empleados de la company (periodos o “empleado actual” + drill
   historial). Create: selector User entre miembros `user_companies` (sin
   create User). Flujo renovar desde fila activa / historial user.

6. **Authz**  
   Policies + Spatie `contract-types.*` / `employees.*`; Policy Laravel
   viewAny→`.view`, view→`.viewAny` (kernel). V-21 Feature plantilla
   templates-backend §13.

7. **Activity log**  
   V-20: Auditable en ContractType y Employee.

8. **Mapa de capabilities**  
   Canon: `docs/standards/capabilities-map.md` (ya esbozado en propose);
   enlazado desde `docs/standards/README.md`. 20 no lo reescribe salvo alinear
   nombres de specs tras archive.

9. **Generador**  
   Preferir `php artisan make:domain-entity` / playbook para esqueletos HR
   cuando el generador soporte dominio HR; si no, scaffolding manual calcando
   templates.

## Risks / Trade-offs

- [Nombre `employees` = periodos, no “persona”] → Mitigation: UI/i18n
  “empleo/contrato”; glosario explícito.
- [Soft-delete tipo con FK] → Mitigation: selects `whereNull(deleted_at)`;
  show con trashed.
- [Solape scheduled vs active] → Mitigation: solo `active` cuenta para ≤1;
  scheduled futuros OK si no hay otro active (o rechazar segundo active al
  activarse — Action al create/renew).
- [Workplace Inactive] → Mitigation: validar exists Active o permitir histórico;
  preferir Active en altas nuevas.

## Migration Plan

1. Docs (ficha, glossary, map, capabilities) — en propose.
2. Migrations + enums + models BelongsToCompany.
3. Module/Functionality seed + permissions.
4. Actions/Policies/Controllers/routes.
5. Frontend Index/Create/Edit.
6. Tests. Rollback: reverse migrations; desactivar module.

## Open Questions

Ninguna bloqueante del brief. Detalle fino de columnas ServerTable / copy i18n
lo fija 20 siguiendo frontend.md y catálogos hermanos.
