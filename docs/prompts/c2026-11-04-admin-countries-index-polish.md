<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-countries-index-polish
Rule: 10-architecture
Summary: Polish UI de Indexes geográficos (Países / Provincias / Poblaciones): ancho
  de Acciones al contenido, iconos distintos para drill vs Edit, quitar columna
  Estado redundante con el toggle. Corregir causa raíz en ServerTable (ignora
  `action.icon` y fija width 130).
Goals:
- Proponer change nuevo de polish (no reabrir CRUD/seeder/cascada).
- **Acciones**: ancho de columna ajustado al contenido (hoy `ServerTable` fija
  `width: 130` y alinea a la derecha → hueco vacío visible).
- **Iconos**: provincias (y poblaciones en Provincias Index) no pueden compartir
  icono con Edit. Icono idóneo para drill geográfico: Lucide **`Map`** (provincias)
  y **`MapPin`** (poblaciones); Edit sigue **`Pencil`**. (En código ya se pasa
  `ListTree`, pero ServerTable **no lo renderiza**: hardcodea SVG lápiz en todos
  los botones no-danger — hay que honrar `action.icon`.)
- **Columna Estado**: eliminarla de los tres Indexes (Países, Provincias,
  Poblaciones); el toggle en Acciones basta. Status en modales Create/Edit se
  mantiene.
- Aplicar las mismas correcciones en Provincias y Poblaciones (punto 4).
NonGoals:
- Cambiar campos de negocio, seeder, cascada, menú, permisos.
- Quitar el Switch de status en formularios Create/Edit.
- Rediseñar Acciones de Modules/Roles/Permissions salvo el arreglo genérico de
  ServerTable necesario para iconos/ancho (efecto colateral aceptable y deseable).
Skills:
- openspec-propose (change nuevo)
- inertia-react-development / tailwindcss-development (Indexes + ServerTable)
Notes:
- Origen: `/opsx-explore` tras seed de menú; Luis adjunta captura Index Países.
- Evidencia: imagen — Acciones ancha con botones a la derecha; dos lápices
  consecutivos; columna Activo junto al toggle.
- Causa raíz iconos: `ServerTable.tsx` formatter Acciones ignora `RowAction.icon`
  y pinta siempre el path SVG de Pencil (excepto danger=trash).
- Causa raíz ancho: `width: 130` fijo en columna Acciones; con 4 controles
  (toggle+3) el layout deja vacío a la izquierda (`hozAlign: 'right'`).
- 10/20: decidir cálculo de ancho (p. ej. en función del nº de acciones, o
  minWidth + fit / sin width fijo + nowrap) en design; contrato = “ajustado al
  contenido, sin hueco muerto evidente”.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Columna Acciones ajustada al contenido
En Indexes que usan ServerTable con `rowActions`, la columna Acciones SHALL tener
un ancho coherente con el número/tamaño de controles visibles, sin espacio vacío
desproporcionado a un lado del grupo de acciones.

#### Scenario: Acciones en Index Países
- **WHEN** Super Admin ve el Index de Países con toggle + provincias + edit + delete
- **THEN** la columna Acciones no muestra un hueco vacío amplio respecto al grupo
  de controles

### Requirement: Icono de drill distinto de Edit
Las acciones de navegación a provincias (desde país) y a poblaciones (desde
provincia) SHALL usar un icono distinto del de Edit. SHALL usarse Lucide `Map` para
provincias y `MapPin` para poblaciones; Edit permanece con `Pencil`. ServerTable
SHALL renderizar el `icon` declarado en `RowAction` (no un lápiz hardcodeado).

#### Scenario: País — provincias ≠ editar
- **WHEN** se muestran Acciones de una fila de país
- **THEN** el botón de provincias usa icono Map y el de editar usa Pencil

#### Scenario: Provincia — poblaciones ≠ editar
- **WHEN** se muestran Acciones de una fila de provincia
- **THEN** el botón de poblaciones usa icono MapPin y el de editar usa Pencil

### Requirement: Sin columna Estado en Indexes geográficos
Los Indexes de Países, Provincias y Poblaciones SHALL NOT mostrar columna de
estado textual si el status ya se controla con el toggle en Acciones.

#### Scenario: Index Países sin columna Estado
- **WHEN** se carga el Index de Países
- **THEN** no hay columna Estado; el toggle de Acciones sigue disponible

#### Scenario: Indexes Provincias y Poblaciones alineados
- **WHEN** se cargan los Indexes de Provincias y de Poblaciones
- **THEN** tampoco muestran columna Estado; mismas reglas de Acciones/ancho/iconos
  aplicables
