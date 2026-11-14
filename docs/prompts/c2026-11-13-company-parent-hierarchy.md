<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-13-company-parent-hierarchy
Rule: 10-architecture
Summary: Modelar la jerarquía de Company con `parent_id` (0 o 1 padre, N
  niveles). Create sin cambios. En Edit, un tab exclusivo para padre e
  hijos; selección solo entre companies vinculadas al usuario. Políticas
  anti-ciclo y de coherencia. Brands y UTE fuera (van después).
Goals:
- Campo `parent_id` nullable (FK a `companies`): cada Company tiene como
  máximo un padre, o ninguno; anidamiento a N niveles (A→B→C…).
- Políticas: no ciclos (padre ≠ self ni descendiente); padre/hijos solo
  entre companies del vínculo del actor (`user_companies` / perímetro
  forUser).
- Create de Company: **sin cambios** (sigue como está; sin UI de
  jerarquía).
- Edit: **tab exclusivo** para determinar padre e hijos (pertenece /
  alberga).
- Selector de padre (y de lo que aplique a hijos) limitado a enterprises
  vinculadas al usuario.
NonGoals:
- Brand / marcas (change posterior; modelo mental ya debatido: anchor +
  descendientes).
- UTE / JointVenture (v1 vale; no en este change).
- Centros de trabajo / coste u órganos internos.
- Multi-lectura agregada / copia cross-company de proveedores (debatido
  como concepto; no implementar aquí).
- Cambiar reglas de NIF/slug/logo del Create/Edit Info actuales salvo lo
  imprescindible para `parent_id`.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices
Notes:
- Origen: `/opsx-explore` conceptualización empresas (Inditex / pirámide).
- Company = entidad jurídica con NIF = centro de facturación; internos ≠
  Company.
- Sesión: CurrentCompany = company del user con NIF; write una; copy/read
  multi = futuro.
- Estado: `companies` plana (sin parent); Edit con tabs Info/Imágenes;
  Create mínimo name/tradename/nif/status.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Jerarquía Company con parent_id
El sistema SHALL permitir que una Company tenga como máximo un padre
(`parent_id` nullable) o ninguno. SHALL permitir anidamiento a varios
niveles (la hija de una hija). SHALL NOT permitir más de un padre ni
ciclos en el árbol.

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
En el tab de jerarquía, el actor SHALL poder seleccionar padre (y
vincular/gestionar hijos según el design) únicamente entre companies
presentes en su vínculo (`user_companies`).

#### Scenario: Padre fuera de vínculo
- **WHEN** se intenta asignar un padre no vinculado al usuario
- **THEN** la operación se rechaza

#### Scenario: Selector acotado
- **WHEN** se abre el selector de padre (o de empresas hijas elegibles)
- **THEN** solo aparecen companies del vínculo del usuario (excluyendo
  las que romperían el árbol / ciclos según Policy)


## MODIFIED Requirements

### Requirement: Edit Company con tabs incluye jerarquía
(El Edit actual con tabs Info e Imágenes.) SHALL añadir el tab de
jerarquía descrito arriba, sin alterar el Create.

#### Scenario: Tabs Info, Imágenes y jerarquía
- **WHEN** un actor abre el Edit tras este change
- **THEN** ve los tabs de Info e Imágenes y el nuevo tab de jerarquía
