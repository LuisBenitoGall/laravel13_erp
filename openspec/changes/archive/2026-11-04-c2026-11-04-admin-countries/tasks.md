# Tasks

## 1. Datos — Country / Province / Town

- [x] 1.1 Migraciones globales `countries`, `provinces`, `towns` (columnas v1 +
      `status` string; softDeletes; FKs; sin `company_id`); verificar migrate
- [x] 1.2 Models `Country`, `Province`, `Town` + enum status Active/Inactive +
      SoftDeletes + relaciones; factories
- [x] 1.3 Seeder geografía desde datos v1 con **IDs preservados** y map 1/0→
      active/inactive; documentar invocación; smoke: counts / país España (o
      equivalente v1) existe con mismo id
- [x] 1.4 Test cross-company: mismo catálogo visible desde company A y B (sustituto
      R-TEN-05 para `--global`)

## 2. Backend Country

- [x] 2.1 Policy + FormRequests + Actions (CRUD + toggle status); soft delete
      bloqueado si hay provincias activas/no trashed (D7)
- [x] 2.2 Controller Index/table/store/update/destroy/status + Resource; rutas
      `admin/countries*`
- [x] 2.3 Feature: authz 403, listado, create/update unique name, toggle, delete

## 3. Backend Province (anidado a Country)

- [x] 3.1 Policy (`countries.*`) + FormRequests + Actions (CRUD + toggle); delete
      bloqueado si hay towns
- [x] 3.2 Controller Index/table scoped by country + store/update/destroy/status;
      rutas anidadas
- [x] 3.3 Feature: listado solo del país padre; CRUD; 403 sin permiso

## 4. Backend Town (anidado a Province)

- [x] 4.1 Policy (`countries.*`) + FormRequests + Actions (CRUD + toggle)
- [x] 4.2 Controller Index/table scoped by province + mutaciones; rutas anidadas
- [x] 4.3 Feature: listado solo de la provincia; CRUD; 403

## 5. Cascada JSON

- [x] 5.1 Endpoints options provincias por country y towns por province (Active,
      orden name); feature asserts payload
- [x] 5.2 Authz mínima (`countries.viewAny` o política documentada en design)

## 6. Frontend + i18n

- [x] 6.1 Pages Index Countries / Provinces / Towns: ServerTable + Acciones
      (toggle → drill hijo si aplica → Edit modal → Delete); ActionBar Nuevo;
      breadcrumb Configuración
- [x] 6.2 Modales Create/Edit campos v1 (Country: name, code, alfa2, alfa3, flag
      nullable, status; Province/Town: name, status); kit Form v2
- [x] 6.3 i18n es/en/ca (países, provincias, poblaciones, acciones, errores)

## 7. Menú / Functionality / ficha

- [x] 7.1 Seed Functionality `countries` (módulo admin) + Spatie `countries.*` vía
      CreateFunctionality si no existe
- [x] 7.2 `MenuBuilder` CONFIGURATION_SLUGS + route map → Index countries; feature
      menú SA incluye Países
- [x] 7.3 Actualizar `docs/modules/Admin.md` + `glossary.md` (Country, Province,
      Town/población)
- [x] 7.4 V-07 sí (permisos countries); V-08 sí (menú); V-09 N/A (módulo admin);
      E2E N/A

## 8. Calidad

- [x] 8.1 Pint + PHPStan en PHP tocado; suite feature geografía verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40).
