# Tasks

## 1. Datos + catálogo

- [x] 1.1 Migración `sectors` (slug, name JSON, status, soft deletes) +
      modelo/factory/enum. Verificar migrate.
- [x] 1.2 Migración pivot Company↔Sector + relations. Verificar.
- [x] 1.3 Glosario `Sector` + fila Admin.md. Verificar docs.

## 2. CRUD Configuración

- [x] 2.1 Functionality `sectors` + permisos + RealignMenuCatalog /
      seeder menú Configuración. Verificar ítem menú SA.
- [x] 2.2 Policy + Actions + Requests + Controller + rutas
      `/admin/sectors` (table, status, resource). Verificar Feature authz.
- [x] 2.3 Index ServerTable + modales Create/Edit con LangTabs (name);
      Active-first. Verificar UI/Feature.
- [x] 2.4 Seeder datos iniciales traducidos (ES/EN/CA). Verificar seed.

## 3. Atribución Company

- [x] 3.1 Action `SyncCompanySectors` + endpoint/request desde Edit;
      solo Active + company vinculada. Verificar Feature.
- [x] 3.2 UI Edit Company: multi-select sectores. Create intacto.
      Verificar UI.

## 4. Calidad

- [x] 4.1 Pint + PHPStan + Feature sectors (+ sync) en verde.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Brand: change posterior.
