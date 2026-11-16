## Purpose

Seeder idempotente del catálogo global PaymentMethod: doce métodos
canónicos (ids 1–12) con name ES/EN/CA y status Active, sin code/slug y
sin borrar filas ajenas.

## Requirements

### Requirement: Seeder PaymentMethods con set acordado
El sistema SHALL proveer un seeder idempotente que inserte/actualice los
12 métodos de pago acordados (ids fijos 1–12) con `name` en ES/EN/CA y
`status` Active. SHALL NOT introducir `code` ni `slug`. SHALL NOT borrar
registros de `payment_methods` ajenos a esa lista.

#### Scenario: Seed idempotente
- **WHEN** se ejecuta el seeder dos veces
- **THEN** existen los 12 ids canónicos con nombres traducidos; no se
  duplican filas

#### Scenario: Set incluye extras y fusión v1
- **WHEN** se revisa el contenido sembrado
- **THEN** hay una sola Transferencia, un solo Cheque/pagaré (fusión),
  Compensación y Bizum; no hay variantes bancarias duplicadas de
  Transferencia
