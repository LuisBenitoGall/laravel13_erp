<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-helptip-auto-code
Rule: 10-architecture
Summary: Derivar el code del HelpTip por convención
  `{helpScope}.{fieldKey}` para no tocar React al crear cada Content
  Información; migrar `company-nif` → `companies.nif`.
Goals:
- Fórmula: `code = {helpScope}.{fieldKey}` (ej. `companies.nif`).
- Formulario declara `helpScope` una vez; Field resuelve fieldKey desde
  identidad estable (`name` / `htmlFor` normalizado); icono solo si
  existe Help Active con ese code.
- `helpCode` explícito queda como override (casos raros).
- Sin Content / Inactive → sin icono (campos sin tip no requieren nada).
- Migrar ancla actual `company-nif` → `companies.nif` (alias temporal
  si 10 lo considera necesario).
- Documentar la convención (glosario/frontend) para formularios nuevos
  y existentes a alinear.
NonGoals:
- Tips para kind Article.
- Autogenerar Contents al detectar Fields.
- Manifiesto JSON de anclas (opción C descartada).
- Sembrar masivo todos los tips v1.
Skills:
- openspec-propose
- inertia-react-development
- v1-reference
Notes:
- Origen: `/opsx-explore` tras helptip-company-nif (ya cableado
  explícito). Decisión Luis: opción **B** auto-derive.
- Ritual tip nuevo: crear Content Help con code `{scope}.{field}` +
  Active (+ export-help cross-env); sin PR front si el Field ya tiene
  identidad y el form tiene helpScope.
- Ejemplos debatidos: `companies.nif`, `companies.name` (solo si hay
  Content), `workplaces.featured`.
- `Field.help` textual ≠ ancla HelpTip.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Resolución automática de code HelpTip
El sistema SHALL resolver el code de ayuda de un Field como
`{helpScope}.{fieldKey}` cuando el formulario provee helpScope y el
Field tiene identidad de campo resoluble, salvo override por helpCode
explícito. SHALL mostrar el icono solo si existe Content Help Active
con ese code.

#### Scenario: Tip por convención
- **WHEN** un form tiene helpScope `companies`, el Field resuelve
  fieldKey `nif`, y existe Help Active `companies.nif`
- **THEN** el actor ve el HelpTip junto al NIF

#### Scenario: Sin Content
- **WHEN** el code derivado no tiene Help Active
- **THEN** no se muestra icono y la página no falla

#### Scenario: Override helpCode
- **WHEN** el Field declara helpCode explícito
- **THEN** se usa ese code en lugar del derivado

### Requirement: Migración company-nif → companies.nif
El sistema SHALL alinear el tip NIF de empresa con la convención
`companies.nif` (actualizar Content/fixture/seeder y el consumidor).

#### Scenario: NIF tras migración
- **WHEN** el actor abre Create/Edit empresa y `companies.nif` está
  Active
- **THEN** ve el tip en el campo NIF


## MODIFIED Requirements

### Requirement: Field helpCode para HelpTip
(El Field con helpCode del change helptip-company-nif.) SHALL además
soportar resolución automática vía helpScope del formulario cuando no
haya helpCode explícito, manteniendo el icono a la derecha del control
y el hint textual `help` independiente.

#### Scenario: helpScope sin helpCode
- **WHEN** un Field bajo helpScope no declara helpCode y el code
  derivado existe Active
- **THEN** muestra el HelpTip igual que con helpCode explícito
