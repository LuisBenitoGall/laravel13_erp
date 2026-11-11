<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-company-membership-edit
Rule: 10-architecture
Summary: Change nuevo (no amend) para recuperar el botón Editar y el modal
  de `position`/`department` del tab Empresas que existían antes del strip
  HIGH de `user-companies-tab`. Sin redisenar: restaurar el mismo write
  path. Código pre-strip recuperable (nunca hubo commit del strip).
Goals:
- Autorizar por contrato OpenSpec la edición de `position` y `department`
  del vínculo `user_companies` en el tab Empresas.
- Recuperar el botón (Pencil) y modal Dialog que se retiraron en el
  RETURN 25 / strip HIGH — mismas superficies, misma UX, mismos campos.
- Restaurar Action/Request/Policy `update`/rutas/`updateUrl`/tests/i18n/
  fila `Admin.md` del write path, alineados al comportamiento que ya
  funcionaba antes del strip.
- Dejar listado ServerTable + detach + sesión intactos (no reabrir ese
  alcance).
NonGoals:
- Amend del design/specs de `c2026-11-11-user-companies-tab` (change
  nuevo preferido y acordado).
- Alta de vínculos desde este tab.
- Cambiar company del vínculo; solo `position` y `department`.
- Entidad Empleo / RRHH.
- Redisenar el panel o inventar otra UX distinta del modal retirado.
- emails / phones / images / docs.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (update position/department; self/ajeno;
  modal + Pencil; sin regress listado/detach/sesión)
Notes:
- Origen: tras strip HIGH el usuario echa en falta el botón; se acuerda
  change **nuevo** (no amend) y recuperar exactamente lo que había.
- Pre-strip (nunca committed el strip): `UpdateUserCompany`,
  `UpdateUserCompanyRequest`, `UserCompanyPolicy::update`,
  `updateForProfile` / `updateForCompanyUser`, rutas
  `profile.companies.update` / `admin.company-users.companies.update`,
  modal + Pencil en `UserCompaniesPanel`, test de update, i18n
  `editar_vinculo_empresa` / `vinculo_empresa_actualizado`. Fuente de
  restauración: transcript del apply
  (`agent-transcripts/.../b46c5139-...jsonl`, Write ~L1390–1391) y/o
  historial local del IDE.
- Authz / superficies / campos = los del write path retirado (no
  reinventar).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Edición de posición y departamento del vínculo
El sistema SHALL permitir actualizar `position` y `department` (nullable)
de un vínculo `user_companies` desde el tab Empresas, vía el mismo
patrón que existía antes del strip (botón Editar + modal), en las
mismas superficies (profile y admin company-users).

#### Scenario: Abrir modal desde la fila
- **WHEN** el actor autorizado pulsa Editar en una fila del ServerTable
  de Empresas
- **THEN** se abre el modal con posición y departamento del vínculo

#### Scenario: Guardar position y department
- **WHEN** el actor autorizado guarda el modal con valores (o null)
  de position/department
- **THEN** el vínculo se actualiza y la tabla refleja los nuevos valores

#### Scenario: Sin cambiar la company del vínculo
- **WHEN** se edita un vínculo
- **THEN** solo cambian position/department; la company del membership
  no se modifica


## MODIFIED Requirements

### Requirement: Tab Empresas de la ficha de usuario
El tab Empresas SHALL seguir listando y permitiendo detach según
`user-companies-membership`, y ADEMÁS SHALL exponer la edición de
position/department descrita arriba (ya no solo lectura en UI).

#### Scenario: Tab con listado, edit y detach
- **WHEN** se abre el tab Empresas con permiso de gestión
- **THEN** hay ServerTable, acción Editar (modal) y acción eliminar
  vínculo
