# Spec Delta

## Purpose

Jerarquía de Company mediante parent_id (un padre o ninguno, N niveles),
gestionada en un tab del Edit, con perímetro user_companies y sin ciclos.

## ADDED Requirements

### Requirement: Jerarquía Company con parent_id
El sistema SHALL permitir que una Company tenga como máximo un padre
(`parent_id` nullable) o ninguno. SHALL permitir anidamiento a varios
niveles. SHALL NOT permitir más de un padre ni ciclos en el árbol.

#### Scenario: Padre único o ninguno
- **WHEN** se asigna o quita el padre de una company
- **THEN** queda con un solo `parent_id` o null

#### Scenario: Tres niveles
- **WHEN** C tiene padre B y B tiene padre A
- **THEN** la estructura A→B→C es válida

#### Scenario: Sin ciclos
- **WHEN** se intenta asignar como padre a sí misma o a un descendiente
- **THEN** la operación se rechaza

### Requirement: Tab jerarquía en Edit (no en Create)
El Create de Company SHALL permanecer sin UI ni campos de jerarquía. El
Edit SHALL incluir un tab exclusivo para determinar padre e hijos
(pertenece / alberga).

#### Scenario: Create sin jerarquía
- **WHEN** un actor abre el Create de Company
- **THEN** no ve controles de padre ni hijos

#### Scenario: Tab en Edit
- **WHEN** un actor autorizado abre el Edit de una company
- **THEN** existe un tab dedicado a la jerarquía (padre e hijos), distinto
  de Info e Imágenes

### Requirement: Selección solo entre companies del usuario
En el tab de jerarquía, el actor SHALL poder seleccionar padre y gestionar
hijos únicamente entre companies presentes en su vínculo
(`user_companies`).

#### Scenario: Padre fuera de vínculo
- **WHEN** se intenta asignar un padre no vinculado al usuario
- **THEN** la operación se rechaza

#### Scenario: Selector acotado
- **WHEN** se abre el selector de padre o de empresas hijas elegibles
- **THEN** solo aparecen companies del vínculo del usuario (excluyendo
  las que romperían el árbol / ciclos según Policy)

### Requirement: Albergar y dejar de albergar hijos
Albergar SHALL consistir en asignar `parent_id` de la hija a la company
editada. Dejar de albergar SHALL poner `parent_id` null en la hija sin
eliminar la Company. SHALL NOT crear Companies nuevas desde este tab.

#### Scenario: Añadir hija
- **WHEN** el actor elige una company elegible como hija
- **THEN** esa company queda con padre = la editada

#### Scenario: Quitar hija
- **WHEN** el actor deja de albergar una hija
- **THEN** la hija permanece en el sistema con `parent_id` null
