## 1. Dependencias y Spatie Teams

- [x] 1.1 Instalar `spatie/laravel-permission` ^6 y `spatie/laravel-translatable` (canon
      `libraries.md`); publicar config/migraciones
- [x] 1.2 Activar Teams: `teams => true`, `team_foreign_key => company_id`; verificar
      columnas nullable en roles/permisos pivotes
- [x] 1.3 Registrar `Gate::before` para rol Super Admin (R-AUT-03); documentar manejo de
      team_id NULL según docs de la versión instalada
- [x] 1.4 Instalar `tabulator-tables` (+ tipos) en npm si aún no está; base shadcn/ui
      mínima necesaria para AppLayout/forms (sin clonar scaffold shadcn-admin)

## 2. Generador: flag `--global` (modifica domain-entity-generator)

- [x] 2.1 Añadir flag `--global` al comando y stubs (migration/model/policy/request/
      factory/test) según `tooling.md` §1
- [x] 2.2 Test del generador: entidad scratch con `--global` — sin `company_id`/
      BelongsToCompany; test generado de visibilidad compartida; cleanup
- [x] 2.3 Actualizar arch `BelongsToCompany` con `ignoring` para catálogos globales
      generados / documentar convención

## 3. Entidad Module (datos → backend → frontend → tests)

- [x] 3.1 Ejecutar `make:domain-entity Admin Module --global --status --translatable`
      (o equivalente ya con `--global`)
- [x] 3.2 Ajustar migration/model: campos `name`, `slug`, `label`, `color`, `icon`,
      `level`, `status`, `explanation`; enums `ModuleLevel`, `ModuleStatus`
- [x] 3.3 Completar Policy/FormRequests/Actions/Resource/Controller/TableRequest;
      rutas bajo `auth` (+ `module:admin` cuando el checker lo permita sin CompanyModules)
- [x] 3.4 Frontend: Index con `ServerTable`, Create/Edit con form + LangTabs; tipos TS;
      i18n es/en
- [x] 3.5 Feature tests: CRUD + 403 sin permiso + visibilidad compartida (sustituto
      R-TEN-05); unit enums si aplica
- [x] 3.6 (V-12) Verificar campos traducibles y UI de idiomas

## 4. Entidad Functionality (datos → backend → frontend → tests)

- [x] 4.1 Ejecutar `make:domain-entity Admin Functionality --global --status
      --translatable`
- [x] 4.2 Ajustar: `module_id` FK, `name`/`slug` (slug inmutable en Update), `label`,
      `status`; Relation a Module
- [x] 4.3 Action Create: `Permission::firstOrCreate` de los 5 permisos + asegurar comodín
      `<module-slug>.access` del Module padre (R-AUT-07 / V-07)
- [x] 4.4 Update: rechazar cambio de `slug` (validación + test)
- [x] 4.5 Frontend Index/Create/Edit + ServerTable + i18n
- [x] 4.6 Feature tests: CRUD, provisión de permisos al crear, slug inmutable, 403,
      visibilidad compartida

## 5. Seeds, Super Admin y registro del módulo (V-07, V-09, §8)

- [x] 5.1 Seeder rol `Super Admin` con `company_id = NULL`
- [x] 5.2 Seeder usuario superadmin (credenciales config/env) + assignRole Super Admin
- [x] 5.3 Seed Module `admin` (level AdminOnly, Active) + Functionalities de gestión
      (`modules`, `functionalities` o slugs glosario acordados) — dispara provisión de
      permisos
- [x] 5.4 Comodín `admin.access` presente; `PermissionSeeder`/DatabaseSeeder cableados
- [x] 5.5 Test: Super Admin bypasa Policy/permiso; rol tiene `company_id` NULL

## 6. Menú data-driven (V-08 / R-AUT-08 parcial)

- [x] 6.1 Servicio/builder de menú: Module visible si comodín OR algún permiso de sus
      Functionalities; Functionality visible si comodín del módulo OR su `viewAny`
- [x] 6.2 Shared prop Inertia (p. ej. vía `HandleInertiaRequests`) — sin JSON en storage
- [x] 6.3 Tests: escenario "solo viewAny de Functionality sin comodín"; escenario
      "comodín implica todas las Functionalities"
- [x] 6.4 Documentar en código/comentario la excepción: sin filtro CompanyModules

## 7. AppLayout, ServerTable y login (`admin-ui-login`)

- [x] 7.1 `Layouts/AppLayout.tsx`: sidebar Module/Functionality (color/icon), topbar,
      hueco logo tenant; flash/toasts
- [x] 7.2 `Components/Table/ServerTable.tsx` + tema `tabulator-erp.css`; usarlo en Index
      Module y Functionality
- [x] 7.3 Kit forms mínimo (RHF + zod espejo FormRequest) para Create/Edit
- [x] 7.4 Login/logout (sesión Laravel); rutas invitados vs `auth`; sin registro/reset
- [x] 7.5 Feature/browser-smoke: login superadmin → ve AppLayout + menú; Index Module
      muestra ServerTable

## 8. Backstop, calidad y DoD

- [x] 8.1 `artisan module:audit Admin Module` y `Admin Functionality` en verde (primera
      prueba real del backstop)
- [x] 8.2 Arch tests verdes con entidades reales (FormRequests en Http/Requests, etc.)
- [x] 8.3 `composer test`, `vendor/bin/pint --test`, `vendor/bin/phpstan analyse` verdes
- [x] 8.4 Actualizar ficha `docs/modules/Admin.md` a "como quedó"; module-map solo si
      cambió algo (esperado: sin cambio de dependencias)
- [x] 8.5 `openspec validate c2026-07-17-admin-catalog-permissions` limpio (30-testing al
      archivar)
