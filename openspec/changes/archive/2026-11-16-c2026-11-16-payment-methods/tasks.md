# Tasks

## 1. Datos + docs

- [x] 1.1 Migración `payment_methods` (name JSON, status string, soft
      deletes, audit). Verificar `migrate`.
- [x] 1.2 Modelo `PaymentMethod` + `HasTranslations` + enum status +
      factory. Verificar factory.
- [x] 1.3 Glosario Método de pago → `PaymentMethod`; fila Admin.md;
      `module-map` (entidad Admin). Verificar docs.

## 2. Authz + menú (V-07 / V-08)

- [x] 2.1 Seed/Realign: Functionality `payment-methods` bajo
      `configuracion`; permisos canónicos. Verificar BD/seed.
- [x] 2.2 MenuBuilder: Métodos de pago → `/admin/payment-methods`.
      Verificar menú Feature.
- [x] 2.3 Policy. Verificar 403 sin permiso.

## 3. Backend + UI CRUD

- [x] 3.1 Actions + FormRequests (name locales; status) + Controller +
      rutas (`table`, status, resource). Verificar Feature.
- [x] 3.2 Index `<ServerTable>` (name; Acciones toggle→Edit→Delete;
      Active-first). Verificar UI/Feature.
- [x] 3.3 Modales Create/Edit con LangTabs (name). Verificar UI.
- [x] 3.4 i18n es/en/ca + Pint + PHPStan + Features en verde.

## 4. Corrección 25 (LOW)

- [x] 4.1 Feature UI: Index niega NonGoals (`field: 'code'` / `name="code"` /
      `data.code` + `accounting_account` / `accounting_account_id`) además de
      columna Estado / toggle / LangTabs. Verificar `PaymentMethodTest`.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- `company_payment_methods` + `accounting_account_id`: tras
  `accounting_accounts`.
