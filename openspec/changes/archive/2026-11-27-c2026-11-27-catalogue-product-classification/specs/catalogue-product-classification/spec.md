# Spec Delta

## Purpose

Clasificación opcional de Product en Edit/General: Family 0..1, Categories
0..N, Tags 0..N, con alta in situ de tags Active cuando el actor tiene
`tags.create`.

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
SHALL permitir crear un tag nuevo desde la ficha Edit (in situ) cuando el
actor tenga `tags.create`; el tag creado SHALL quedar Active. Sin
`tags.create`, SHALL poder seleccionar tags existentes (con permiso de
update del producto) pero SHALL NOT crear nuevos.

#### Scenario: Seleccionar tags existentes
- **WHEN** un actor con permiso de update elige tags existentes y guarda
- **THEN** el producto queda asociado a esos tags

#### Scenario: Crear tag in situ con permiso
- **WHEN** un actor con `tags.create` introduce un nombre de tag nuevo en
  la ficha y confirma
- **THEN** se crea un Tag Active en su company disponible para asociar al
  producto

#### Scenario: Crear tag in situ sin permiso
- **WHEN** un actor sin `tags.create` intenta crear un tag nuevo desde la
  ficha
- **THEN** la creación se rechaza (403 o UI sin acción de crear)

### Requirement: Clasificación en tab General
La UI de familia, categorías y tags SHALL vivir en el tab General de la
ficha Edit de Product. SHALL NOT añadir un tab Clasificación. SHALL NOT
exigir clasificación en el formulario de Create de este change.

#### Scenario: Campos en General
- **WHEN** un actor abre Edit → General
- **THEN** ve controles de familia, categorías y tags junto al resto de
  campos generales

#### Scenario: Create sin clasificación
- **WHEN** un actor abre Create de producto
- **THEN** el formulario sigue siendo solo nombre y tipo
