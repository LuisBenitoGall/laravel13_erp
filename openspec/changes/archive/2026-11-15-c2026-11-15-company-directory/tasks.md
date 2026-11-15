# Tasks

## 1. Catálogo Functionality + authz + menú

- [x] 1.1 Seed/Realign: Functionality `company-directory` bajo Module
      `empresas` (label Directorio); permisos canónicos; otorgar
      `company-directory.view` a roles básicos alineados con acceso
      práctico a Empresas. Verificar BD/seed.
- [x] 1.2 MenuBuilder: subítem Directorio → `/admin/company-directory`
      junto a Mis empresas (sin sustituirla). Verificar menú Feature /
      snapshot.
- [x] 1.3 Glosario: término Directorio / `company-directory` (descubrimiento
      Active+NIF+sector). Verificar fila en `glossary.md`.

## 2. Backend Index + Show

- [x] 2.1 Rutas admin Index + Show (`/admin/company-directory`,
      `/admin/company-directory/{company}`) + controller delgado. Verificar
      `route:list`.
- [x] 2.2 Query universo: Active + NIF no vacío + `whereHas('sectors')`;
      filtro `sector_ids` OR; sort name asc/desc + created_at desc;
      paginación. Verificar Feature.
- [x] 2.3 Aside data: sectores Active ordenados. Verificar props.
- [x] 2.4 Policy/authorize distinta de Mis empresas `forUser`: Index/Show
      del directorio por `company-directory.view` (+ capas módulo); Show
      404 si company fuera del universo. Verificar 403/404 Feature.
- [x] 2.5 Sin modelo `CompanySector`; usar relación `sectors()`. Verificar
      código.

## 3. Frontend Inertia

- [x] 3.1 Page Index: aside checkboxes multi-sector + grid cards (logo,
      name, datos útiles) + sort + paginación; query string Inertia;
      **sin** ServerTable. Verificar UI/source.
- [x] 3.2 Page Show informativa (identidad + sectores); sin CTA contactar
      ni cliente/proveedor. Verificar UI/source.
- [x] 3.3 i18n es/en/ca (menú, labels, sort, vacío). Verificar keys.

## 4. Docs + tests de cierre

- [x] 4.1 Actualizar `docs/modules/Admin.md` (menú Empresas: Mis empresas +
      Directorio; excepción grid). Verificar ficha.
- [x] 4.2 Feature: menú entrada; universo excluye draft/sin NIF/sin sector;
      filtro OR; Show sin CTAs; Pint + PHPStan verdes.
- [x] 4.3 Regresión: Mis empresas Index/ServerTable y `forUser` intactos.
      Verificar Feature existente verde.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica (design).
- Contactar / cliente-proveedor: change futuro.
