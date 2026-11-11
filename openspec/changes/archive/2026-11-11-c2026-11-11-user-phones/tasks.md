# Tasks

## 1. Schema + modelo

- [x] 1.1 Migración `phones` (morphs, e164, country_id nullable FK,
      ext/type/label, flags, notes, softDeletes, unique owner+e164,
      índices). Verificar migrate fresca.
- [x] 1.2 Modelo `App\Models\Phone` + enum `PhoneType` +
      `User::phones()` morphMany + factory. Verificar relation/factory.
- [x] 1.3 Cargar phones reales en `UserEditPageData` (y readout). Verificar
      props Edit.

## 2. Backend dominio

- [x] 2.1 Policy `Phone` (create/update/delete/markPrimary) = self o
      updateCompanyUser sobre owner. Verificar 403.
- [x] 2.2 Actions Create/Update/Delete/MarkPrimary + FormRequests (e164
      unique por owner, type enum, country_id forzado null, primary
      desmarca resto). Verificar Feature alta/edit/rechazo/delete/primary.
- [x] 2.3 Controller `Domains/Admin` + rutas profile + company-users
      (store/update/destroy/mark-primary). Verificar route:list.
- [x] 2.4 Test perímetro (R-TEN-05 analog): actor no puede mutar phones de
      user fuera de company de sesión; SA no bypass perímetro. Verificar
      Feature.

## 3. Frontend tab

- [x] 3.1 Columna derecha: cards + modal create/edit (campos acordados);
      quitar placeholder. Verificar UI.
- [x] 3.2 Acciones tel, copy, WhatsApp (si flag), estrella primario, edit,
      delete confirm; badge verificado solo informativo. Verificar
      source/comportamiento.
- [x] 3.3 i18n claves teléfonos / tipos / badges / errores. Verificar keys.

## 4. Docs + calidad

- [x] 4.1 Actualizar `docs/modules/Admin.md` (User phones). Verificar fila.
- [x] 4.2 Pint + PHPStan + Feature afectados en verde.

## Workflow follow-up

- `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
