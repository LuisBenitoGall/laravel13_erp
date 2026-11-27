<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-27-catalogue-product-classification
Rule: 10-architecture
Summary: Vincular Product con taxonomía en el tab General de Edit:
  Family 0..1, Categories 0..N, Tags 0..N (todo opcional); tags con alta
  in situ (Active) si el actor tiene `tags.create`.
Goals:
- Persistencia del vínculo Product ↔ taxonomía por Company (R-TEN-05):
  - **Family:** como máximo **una**, opcional (0..1).
  - **Category:** **N**, opcional (0..N).
  - **Tag:** **N**, opcional (0..N).
- UI en el **tab General** de la ficha Edit de Product (no tab
  Clasificación nuevo): select familia; multi categorías; multi tags.
- **Tags in situ:** además de seleccionar existentes, poder **crear** un
  tag nuevo desde la ficha y asignarlo al producto en el mismo flujo.
  - Solo tags (no crear Family/Category in situ).
  - Exige permiso **`tags.create`** (además de poder actualizar el
    producto).
  - El tag creado in situ queda con status **Active**.
- Schema/pivotes/FK lo cierra 10 (p. ej. `family_id` vs pivote 1:1;
  `product_category` / `product_tag`).
- Tests Pest: asignación/desasignación; opcionales; in situ con/sin
  `tags.create`; Active al crear; R-TEN-05 (no vincular maestros de otra
  company); aislamiento.
- Actualizar ficha Catalog / glosario / module-map si hace falta.
NonGoals:
- Family ↔ Category N:N (change futuro ya anotado).
- Crear Family o Category in situ desde la ficha.
- Tab «Clasificación» dedicado.
- Index thumbs / filtros por taxonomía en Index (salvo lo mínimo que 10
  necesite para el form).
- Traducciones; atributos; precios; tarifas.
- Ampliar Create (alta solo nombre+tipo) con clasificación: **no
  debatido** → decide 10 (sesgo explore: solo Edit/General).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
- inertia-react-development
- form-layout-polish
- testing-best-practices
Notes:
- Origen: `/opsx-explore` post Family/Category/Tag maestros archivados.
  Plan: clasificación ficha → (luego) Family↔Category N:N.
- Decisiones Luis (2026-11-27): (1) Family 0..1; Categories/Tags 0..N;
  todo opcional; (2) in situ **solo tags**; (3) authz alta in situ =
  `tags.create`; (4) tag nuevo = **Active**; (5) UI en **tab General**.
- Maestros ya existen: CRUD `/products/families|categories|tags`.
- Kit Form: no hay combobox creatable hoy; 10/20 eligen patrón UI
  (p. ej. multi + create) sin deps fuera de `libraries.md` si posible.
- Misión explore: **solo brief** → `/opsx-propose` en sesión nueva.


## ADDED Requirements

### Requirement: Family opcional 0..1 en Product
El sistema SHALL permitir asociar a un Product como máximo una Family de
la misma company, o ninguna. SHALL NOT exigir familia para guardar.

#### Scenario: Sin familia
- **WHEN** un actor guarda el producto sin familia
- **THEN** el producto queda sin familia asociada

#### Scenario: Una familia
- **WHEN** un actor elige una familia de su company y guarda
- **THEN** el producto queda asociado a esa familia

#### Scenario: Familia ajena rechazada
- **WHEN** un actor intenta asociar una familia de otra company
- **THEN** la operación se rechaza

### Requirement: Categories opcionales 0..N en Product
El sistema SHALL permitir asociar cero o muchas Categories de la misma
company a un Product. SHALL NOT exigir categorías. SHALL NOT permitir
crear categorías in situ desde la ficha.

#### Scenario: Varias categorías
- **WHEN** un actor selecciona varias categorías propias y guarda
- **THEN** el producto queda asociado a esas categorías

#### Scenario: Quitar categorías
- **WHEN** un actor deselecciona todas y guarda
- **THEN** el producto queda sin categorías

### Requirement: Tags opcionales 0..N y alta in situ
El sistema SHALL permitir asociar cero o muchos Tags de la misma company.
SHALL permitir crear un tag nuevo desde la ficha Edit (in situ) y
asignarlo al producto cuando el actor tenga `tags.create`. El tag creado
in situ SHALL quedar Active. Sin `tags.create`, SHALL poder seleccionar
tags existentes (si tiene permiso de update del producto) pero SHALL NOT
crear nuevos.

#### Scenario: Seleccionar tags existentes
- **WHEN** un actor con permiso de update elige tags existentes y guarda
- **THEN** el producto queda asociado a esos tags

#### Scenario: Crear tag in situ con permiso
- **WHEN** un actor con `tags.create` introduce un nombre de tag nuevo en
  la ficha y confirma
- **THEN** se crea un Tag Active en su company y queda asociado al
  producto (en el flujo acordado por 10)

#### Scenario: Crear tag in situ sin permiso
- **WHEN** un actor sin `tags.create` intenta crear un tag nuevo desde la
  ficha
- **THEN** la creación se rechaza (403 o UI sin acción de crear)

### Requirement: Clasificación en tab General
La UI de familia, categorías y tags SHALL vivir en el tab General de la
ficha Edit de Product. SHALL NOT añadir un tab Clasificación en este
change.

#### Scenario: Campos en General
- **WHEN** un actor abre Edit → General
- **THEN** ve controles de familia, categorías y tags junto al resto de
  campos generales


## MODIFIED Requirements

### Requirement: Edit con tabs y tab General (catalogue-products)
El tab General SHALL permitir editar, además de nombre y status, la
clasificación opcional Family 0..1, Categories 0..N y Tags 0..N según los
requisitos de este change. Tabs Imágenes y Documentos permanecen.

#### Scenario: Activar desde Edit
- **WHEN** un actor autorizado pone status Active en el tab General y guarda
- **THEN** el producto queda Active sin alterar el tipo

#### Scenario: Guardar clasificación en General
- **WHEN** un actor actualiza familia/categorías/tags en General y guarda
- **THEN** los vínculos quedan persistidos sin cambiar el tipo del
  producto
