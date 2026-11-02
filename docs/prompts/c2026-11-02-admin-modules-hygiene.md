<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-02-admin-modules-hygiene
Rule: 10-architecture
Summary: Cierre/higiene del CRUD Módulos tras la revisión explore: soft delete de Module
    en Index y Edit, retirar Functionalities genéricas y `ModuleForm` muerto, y mostrar
    level en solo lectura en Edit. El botón Nueva funcionalidad permanece en la ActionBar.
Goals:
- Proponer un **change nuevo** (no hay changes abiertos) de higiene/cierre del CRUD
  Admin Modules.
- **Delete de Module**: soft delete con confirmación; disponible en Acciones del Index
  y en la ActionBar del Edit.
- **Retirar** el flujo genérico de Functionalities (`Admin/Functionalities/*` páginas +
  rutas Index/Create/Edit genéricas / table genérica que ya no aportan; el CRUD vive
  bajo Module Edit). Sin reabrir menú genérico.
- **Eliminar** el componente legacy `ModuleForm.tsx` si ya no tiene callers.
- En Edit (tab Módulo): mostrar **level en solo lectura** (no editable; hoy no se ve).
- El botón **Nueva funcionalidad** se queda donde está (ActionBar del Edit); no moverlo
  al interior del tab.
NonGoals:
- No condicionar/mover el botón Nueva funcionalidad al tab Funcionalidades.
- No migrar forms a RHF+zod ni introducir `RecordEditLayout` / `FormActions` en este
  change.
- No reabrir el debate del toggle de Module solo en `optional` vs universal.
- No borrar permisos Spatie al soft-delete de Module/Functionality (misma política ya
  acordada para Functionalities).
- No Wysiwyg; no polish cosmético fuera de lo listado.
Skills:
- openspec-propose (change nuevo + artefactos)
- tenancy-rbac (Module delete / menú / Functionalities)
- inertia-react-development (Index/Edit Acciones)
Notes:
- Origen: `/opsx-explore` — revisión ejecutiva del CRUD Módulos “casi listo”; Luis
  pide brief con las mejoras salvo el relocado del botón Nueva.
- Delete: Luis confirma soft delete (opción 1: Index Acciones + Edit ActionBar +
  confirm). `DeleteModule` Action ya existe; falta UI y contrato de Acciones.
- Shell `admin`/`workspace`: no se debatieron reglas especiales de bloqueo al borrar;
  10 debe decidir A/B (permitir soft delete como el Inactive, o bloquear) y dejarlo
  explícito en design — no improvisar en 20.
- Idioma del agente: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Soft delete de Module en UI
El sistema SHALL permitir soft-delete de Module con confirmación desde (1) la columna
Acciones del Index y (2) la ActionBar del Edit. Tras borrar desde Index, SHALL volver
al Index (o permanecer en él sin la fila). Tras borrar desde Edit, SHALL redirigir al
Index de Módulos. SHALL NOT eliminar permisos Spatie al soft-delete.

#### Scenario: Delete desde Index
- **WHEN** el usuario confirma Eliminar en Acciones de una fila de Module
- **THEN** el Module se soft-deletea y deja de aparecer en el listado

#### Scenario: Delete desde Edit
- **WHEN** el usuario confirma Eliminar en la ActionBar de Modules/Edit
- **THEN** el Module se soft-deletea y la navegación termina en el Index de Módulos

### Requirement: Level visible readonly en Edit
En la pestaña Módulo del Edit, el sistema SHALL mostrar el `level` del Module como
información de solo lectura (sin control editable).

#### Scenario: Level visible no editable
- **WHEN** el usuario abre Edit de Module en la pestaña Módulo
- **THEN** ve el nivel (etiqueta i18n) y no puede cambiarlo en esa pantalla

### Requirement: Retirar Functionalities genéricas y ModuleForm legacy
El sistema SHALL NOT mantener un CRUD genérico usable de Functionalities
(Index/Create/Edit/table genéricos) ahora que el CRUD es anidado. SHALL eliminar
`ModuleForm.tsx` si no tiene callers. El botón Nueva funcionalidad en ActionBar del
Edit SHALL permanecer sin cambios de ubicación.

#### Scenario: Sin páginas genéricas de Functionalities
- **WHEN** se revisan rutas/páginas Admin de Functionalities genéricas
- **THEN** Index/Create/Edit genéricos (y table global si aplica) están retirados o
  inaccesibles; el flujo válido es Module Edit

#### Scenario: ModuleForm eliminado si huérfano
- **WHEN** no quedan imports de `ModuleForm`
- **THEN** el fichero se elimina del repo

#### Scenario: Nueva funcionalidad sigue en ActionBar
- **WHEN** el usuario está en Modules/Edit
- **THEN** el botón Nueva funcionalidad sigue en la ActionBar (no se exige que esté
  solo dentro del tab)
