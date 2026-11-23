<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-23-user-edit-empleos-tab
Rule: 10-architecture
Summary: Tab Empleos de solo lectura en la ficha Edit de User (self y ajena) con
  todos los periodos laborales del usuario en todas sus companies; ocultar el tab
  si no hay contratos; enmienda al kernel que permita lecturas cross-company
  ancladas a un User cuando el change/spec lo declare.
Goals:
- Añadir tab Empleos en `Users/Edit` (perfil self y company-users ajena).
- Listar TODOS los contratos/periodos `Employee` del User editado, sin filtrar
  por empresa en sesión (opción A; solo lectura; sin mutar contratos desde aquí).
- Columnas: empresa, centro de trabajo, tipo de contrato, puesto, fecha inicio,
  fecha fin, horas semanales, salario.
- Columna acciones: solo un modal para leer observaciones si las hay; sin editar
  ni cancelar contrato.
- No mostrar el tab si el User no tiene ningún periodo de empleo.
- Gate del tab / datos: basta poder abrir la ficha (self o company-users); no
  exigir `employees.view` aparte. Sin columna de estado (`scheduled|active|ended`).
- Dejar constancia en el kernel (y R-* de architecture que corresponda) de que
  habrán consultas de solo lectura que mezclan datos de varias companies cuando
  están ancladas a un User (u hub explícito) y el change/spec lo declare; no
  equivale a “SA ve todas las empresas”.
NonGoals:
- Editar, renovar, cancelar o crear periodos desde este tab (eso sigue en HR).
- Columna estado del periodo en esta tabla.
- Exigir permiso `employees.view` para ver el tab.
- Cambiar el listado HR/Employees ni el historial scoped a company en Edit de
  empleo.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
Notes:
- Origen: `/opsx-explore` tras oleada RRHH + cron/overlap archivados. Hoy
  `Users/Edit` tiene tabs profile|password|contacts|images|docs|companies; no
  hay Empleos. `Employee` es `BelongsToCompany`; el precedente UI más cercano es
  el tab Empresas (vínculos del User sin scope de sesión).
- **Tensión con el kernel actual**: `kernel.md` / R-TEN-05 / R-TEN-09 tratan
  cross-tenant como bug y prohíben “ver todas”. Lo debatido (lectura anclada al
  User, todas sus companies) **contradice** esa redacción plana; el change DEBE
  enmendar el estándar (no improvisar la excepción solo en código). Señalado
  aquí; 10-architecture redacta el matiz canónico.
- Change **nuevo** (no hay changes activos). Capacidades tocadas: `user-edit` +
  delta `hr-employees` (proyección lectura) + standards kernel/architecture.


## ADDED Requirements

### Requirement: Tab Empleos en ficha User (solo lectura, multi-company)
La ficha Edit de User (self y company-users) SHALL ofrecer un tab Empleos cuando
el User tenga al menos un periodo `Employee`. El tab SHALL listar todos los
periodos del User en todas las companies (sin filtrar por CurrentCompany), en
solo lectura. Columnas: empresa, centro de trabajo, tipo de contrato, puesto,
fecha inicio, fecha fin, horas semanales, salario. Acciones: únicamente modal
de observaciones si hay texto. SHALL NOT permitir editar ni cancelar el
contrato desde este tab. SHALL NOT mostrar el tab si no hay periodos. Acceso al
contenido del tab SHALL seguir el acceso a la ficha (no exigir `employees.view`).
SHALL NOT incluir columna de estado del periodo.

#### Scenario: Tab visible con periodos
- **WHEN** el actor abre la ficha Edit de un User (self o ajena) que tiene ≥1
  periodo Employee en cualquier company
- **THEN** aparece el tab Empleos con esos periodos (todas las companies)

#### Scenario: Tab oculto sin periodos
- **WHEN** el User no tiene ningún periodo Employee
- **THEN** el tab Empleos no se muestra

#### Scenario: Solo lectura observaciones
- **WHEN** un periodo tiene observaciones
- **THEN** en acciones se puede abrir un modal de solo lectura con ese texto;
  sin acciones de editar/cancelar contrato

#### Scenario: Sin observaciones
- **WHEN** un periodo no tiene observaciones
- **THEN** no hay acción de editar/cancelar; no se ofrece modal de
  observaciones con contenido

### Requirement: Lecturas cross-company ancladas a User (kernel)
El kernel / architecture SHALL documentar que están permitidas consultas de
**solo lectura** que proyecten filas de varias companies cuando están ancladas
a un User (u otro hub explícito) y el change/spec lo declara. SHALL NOT
interpretarse como bypass de perímetro de Super Admin ni como mutación
cross-tenant.

#### Scenario: Excepción documentada
- **WHEN** un change declara una proyección de lectura anclada a User con
  periodos de varias companies
- **THEN** el estándar la contempla como excepción explícita, no como bug
  R-TEN-05 genérico

---

## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs (user-edit)
La lista de tabs extensibles de la ficha Edit SHALL incorporar Empleos según las
reglas anteriores (condicional a existencia de periodos; self y ajena).

#### Scenario: Misma ficha self y company-users
- **WHEN** el actor abre `/admin/profile` o el Edit de company-users de un User
  con periodos
- **THEN** ambas rutas pueden mostrar el tab Empleos con el mismo criterio de
  datos multi-company en solo lectura
