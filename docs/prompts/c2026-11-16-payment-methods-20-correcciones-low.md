Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-16-payment-methods
Origen: informe 25-architecture-review (**APPROVED WITH OBSERVATIONS**).
Corrección **LOW** (no hay CRITICAL/HIGH). No reabran diseño.
Contrato: `openspec/changes/c2026-11-16-payment-methods/`
(design Non-Goals; spec «SHALL NOT exigir code ni campos contables»).
Si este prompt y los artefactos chocan, mandan los artefactos.
No reescribas proposal.md, design.md ni specs.
No archives. E2E N/A.

## Contexto

CRUD catálogo global PaymentMethod OK (`PaymentMethodTest` 13/13).
Queda un LOW de cobertura: el Feature de UI lee `Index.tsx` y niega
columna `status` / afirma toggle+LangTabs, pero **no** aserta que el
formulario no exponga NonGoals del design (`code`,
`accounting_account_id` / campos contables).

El código actual ya cumple (migración/UI sin esos campos); solo falta
el assert de regresión.

## Qué hacer (LOW)

### 1. Ampliar assert NonGoal en Feature UI

En `tests/Feature/Admin/PaymentMethodTest.php`, en el test que lee
`resources/js/Pages/Admin/PaymentMethods/Index.tsx` (el de «sin columna
Estado…»), añadir expects de que el source **no** contenga literales
NonGoal, p. ej.:

- `code` como campo de formulario / columna (evitar falsos positivos
  frágiles: preferir strings inequívocos del alcance, p. ej.
  `accounting_account`, `accounting_account_id`, o labels/keys i18n
  contables si existieran; si `code` es demasiado genérico en el
  archivo, usar un patrón acotado tipo `name="code"` / `field: 'code'` /
  `data.code` — elige el que encaje con el source real y no flaquee).
- `accounting_account_id` (o `accounting_account`).

No inventes campos en la UI solo para testearlos: el assert es de
ausencia.

No cambies migración, modelo, Actions, Policy, Controller, Index ni
i18n salvo bug descubierto al ampliar el assert.

## Verificación

```text
php artisan test --compact tests/Feature/Admin/PaymentMethodTest.php
```

Pint dirty PHP si tocaste PHP (solo el test).

## Fuera de alcance

- `company_payment_methods` / pivot / `accounting_account_id` real.
- Seeder de datos de negocio.
- Slug / `code` canónico.
- Archive / reabrir design.
- E2E 40.

## Gate de salida

- [ ] Feature UI niega NonGoals (`code` acotado + contable) además de
      columna Estado / toggle / LangTabs.
- [ ] `PaymentMethodTest` verde; sin cambios de producto innecesarios.
- [ ] Pint OK si aplica.
- [ ] Volver a 25 solo si Luis lo pide; si no, seguir a 30-testing.
