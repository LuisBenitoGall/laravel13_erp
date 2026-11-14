# Tasks

## 1. Functionality + authz

- [x] 1.1 Seed/Realign: Functionality `company-sectors` bajo `empresas`
      (sin ítem de menú); permisos canónicos. Verificar BD/seed.
- [x] 1.2 `SyncCompanySectorsRequest` (y controller si aplica): authorize
      con `company-sectors.update` + vínculo. Verificar Feature 403 sin
      update.
- [x] 1.3 Props Edit `can.view_company_sectors` /
      `can.update_company_sectors`. Verificar payload.

## 2. Frontend

- [x] 2.1 Mover `CompanySectorsPanel` a tab `sectors`; quitar de Info;
      tab visible solo con `.view`; read-only sin `.update`. Verificar
      UI/source.
- [x] 2.2 i18n label tab. Verificar keys.
- [x] 2.3 Create sin panel sectores (regresión). Verificar.

## 3. Docs + tests

- [x] 3.1 Admin.md / ficha: tab + `company-sectors.view|update`. Verificar.
- [x] 3.2 Feature: view-only / update sync / sin view sin tab; Pint +
      PHPStan verdes.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Directorio por sector: change futuro.
