<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-16-payment-methods-seeder
Rule: 10-architecture
Summary: Seeder idempotente de métodos de pago del catálogo global
  (name ES/EN/CA + status Active), a partir de v1 deduplicado +
  Compensación y Bizum. Upsert por id fijo; sin slug ni code.
Goals:
- `PaymentMethodsSeeder` con set acordado (12 métodos), traducciones
  ES/EN/CA, status Active.
- Idempotente: upsert por **id fijo**; no borra filas ajenas a la lista.
- Base v1 limpia: una sola Transferencia; Cheque Pagaré + Pagaré
  fusionados en «Cheque/pagaré»; nombres limpios (p. ej. Domiciliación
  bancaria, Confirming).
- Extras: Compensación, Bizum.
NonGoals:
- Añadir `code` o `slug` al modelo/tabla.
- `company_payment_methods` / cuentas contables.
- Import legacy por code.
- Cambiar el CRUD UI de payment-methods.
Skills:
- openspec-propose
- v1-reference
- testing-best-practices
Notes:
- Origen: `/opsx-explore` tras `c2026-11-16-payment-methods` (archivado).
- Modelo actual: solo `name` (HasTranslations) + `status`.
- Patrón referencia: Units/Banks (upsert por id); no Sectors (slug).
- Set canónico (ids 1–12, orden acordado):
  1 Contado en efectivo · 2 Cheque/pagaré · 3 Confirming ·
  4 Domiciliación bancaria · 5 Recibo · 6 Talón · 7 Transferencia ·
  8 Tarjeta de crédito · 9 A concretar · 10 Abono de tarjetas en banco ·
  11 Compensación · 12 Bizum.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Seeder PaymentMethods con set acordado
El sistema SHALL proveer un seeder idempotente que inserte/actualice los
12 métodos de pago acordados (ids fijos 1–12) con `name` en ES/EN/CA y
`status` Active. SHALL NOT introducir `code` ni `slug`. SHALL NOT borrar
registros de `payment_methods` ajenos a esa lista.

#### Scenario: Seed idempotente
- **WHEN** se ejecuta el seeder dos veces
- **THEN** existen exactamente los 12 ids canónicos con nombres
  traducidos; no se duplican filas

#### Scenario: Set incluye extras y fusión v1
- **WHEN** se revisa el contenido sembrado
- **THEN** hay una sola Transferencia, un solo Cheque/pagaré (fusión),
  Compensación y Bizum; no hay variantes TRUBS/TRSAN


## MODIFIED Requirements

### Requirement: Catálogo global PaymentMethod
(El catálogo Admin PaymentMethod name+status.) SHALL quedar poblable
vía seeder de datos iniciales según el set acordado, sin ampliar el
modelo MVP (sin code/slug).

#### Scenario: Tras seed el Index muestra el set
- **WHEN** un actor abre el Index tras ejecutar el seeder
- **THEN** ve los métodos canónicos Active con name traducido
