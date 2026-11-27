## Purpose

Maestro Family por Company: CRUD ServerTable, slug auto desde nombre,
status Inactive por defecto, soft delete y sin vínculo a Product en este
change. Rutas bajo `/products/families` con Functionality `families`.

## Requirements

### Requirement: Family por Company
El sistema SHALL persistir familias pertenecientes a la Company de sesión
(`company_id`). Los listados y mutaciones SHALL estar aislados por tenant
(R-TEN-05).

#### Scenario: Aislamiento por company
- **WHEN** un actor de la company A lista Familias
- **THEN** solo ve familias de A (no las de otra company)

#### Scenario: No edita familia ajena
- **WHEN** un actor de la company A intenta actualizar una familia de B
- **THEN** la operación se rechaza (404/403 según contrato de tenancy)

### Requirement: Alta mínima nombre y slug auto
El sistema SHALL permitir crear una familia con únicamente nombre (string
llano). El slug SHALL generarse en servidor a partir del nombre (SlugService
o equivalente). El status inicial SHALL ser `inactive` asignado en servidor.
SHALL NOT exigir ni ofrecer status ni slug en el formulario de creación.

#### Scenario: Create Inactive con slug
- **WHEN** un actor autorizado crea una familia con nombre «Electrónica»
- **THEN** la familia queda persistida con status Inactive y slug derivado
  del nombre

### Requirement: Slug inmutable y único vivo por company
Tras el alta, el slug SHALL ser inmutable en update. La unicidad SHALL
aplicarse a filas no borradas (`(company_id, slug)` where `deleted_at` is
null). Soft-deleted MAY coexistir con un nuevo registro del mismo slug.

#### Scenario: Update no cambia slug
- **WHEN** un actor envía un update con un `slug` distinto al persistido
- **THEN** el slug almacenado no cambia (payload ignorado o validación
  rechazada)

#### Scenario: Soft-delete libera slug
- **WHEN** se soft-borra una familia con slug `electronica` y se crea otra
  con nombre que deriva al mismo slug
- **THEN** la nueva familia puede usar `electronica`

### Requirement: Soft delete y auditoría
El destroy SHALL ser soft delete. Family SHALL ser Auditable (creación,
actualización, borrado) con actor y timestamps visibles en UI de ficha.

#### Scenario: Destroy soft
- **WHEN** un actor autorizado elimina una familia
- **THEN** la fila permanece con `deleted_at` y deja de aparecer en listados
  vivos

### Requirement: Listado ServerTable y rutas /products/families
El listado SHALL usar `<ServerTable>` en `GET /products/families` y el resto
del recurso CRUD bajo ese prefijo (create/edit/update/destroy y endpoint
de tabla). Las rutas SHALL exigir Module `products` activo y permisos
`families.*` (+ comodín `products.access` según kernel).

#### Scenario: Index con permiso
- **WHEN** un actor con `families.view` (+ módulo) abre `/products/families`
- **THEN** ve el ServerTable de familias de su company

#### Scenario: Sin permiso
- **WHEN** un actor sin `families.view` intenta el index
- **THEN** recibe 403

### Requirement: Sin vínculo Product en este change
SHALL NOT añadir `family_id` (ni pivote) en Product, ni selectores de
familia en formularios de Product, en este change.

#### Scenario: Product sin familia
- **WHEN** se inspecciona el schema/UI de Product tras este change
- **THEN** no existe asociación a Family
