<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-04-admin-vat-types
Rule: 10-architecture
Summary: Portar a v2 el maestro de tipos de IVA (v1 IvaType → VatType) bajo
  Configuración, con cambio de modelo: cada tipo pertenece a un Country
  (obligatorio); soft delete; UI listado plano con columna País; seed España.
Goals:
- Dominio **Admin** (Functionality de Configuración, mismo criterio que monedas/
  unidades); glosario `VatType`; tabla/rutas en inglés (`vat_types` /
  `admin/vat-types`).
- Functionality `vat-types` (o slug que fije 10 alineado a menú) bajo módulo
  `admin`; menú **Configuración → Tipos de IVA** (label i18n).
- Esquema: `country_id` **required** FK a countries (selector: **todos** los
  países, Active e Inactive); name; rate/iva (%); `equivalence_surcharge`
  **nullable**; status Active/Inactive string+enum; softDeletes (historial
  comercial).
- UI: Index ServerTable **plano** con columna País + filtro (opción A); Create/
  Edit **modales**; Acciones toggle + Edit + Delete; sin columna Estado;
  default **Active-first** + name (sin sort cliente).
- Seeder: importar tipos del JSON/dump v1 asignados a **España** (`countries.id`
  = 66, code ES); preservar IDs de tipos si el JSON los trae; README § Seeders.
- Permisos Spatie `vat-types.*` (o el slug elegido).
- Sin import al abrir Index; sin pivots cliente/proveedor v1.
NonGoals:
- IvaTypeCustomer / IvaTypeProvider / ámbitos comerciales.
- Consumidores (líneas factura, productos).
- Dominio Tax como carpeta de código en este change (mapa puede anotar que Tax
  consumirá VatType; CRUD vive en Admin/Configuración).
- Tenancy / `company_id` en VatType.
- Livewire / DataTables / Blade.
Skills:
- openspec-propose
- tenancy-rbac
- v1-reference (`IvaTypeController`, `iva_types`, `storage/json/iva-types.json`)
- inertia-react-development (patrón Currencies + select Country)
Notes:
- Origen: `/opsx-explore`; v1 `/admin/iva-types` plano; v2 **por país**.
- Respuestas: (1) Index A listado+columna País; (2) modales; (3) seed España;
  (4) country_id siempre; (5) Active-first sí; (6) RE nullable.
- Decimales: alinear a standards (venta 3 / contable 2 — 10 fija precisión de
  columnas rate/RE).
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Catálogo global VatType por Country
El sistema SHALL persistir tipos de IVA (`VatType`) como catálogo global sin
`company_id`, con soft delete y status Active/Inactive. Cada VatType SHALL
pertenecer a un Country obligatorio. Campos: name, tipo/rate de IVA, equivalence
surcharge nullable. El selector de país en alta/edición SHALL listar todos los
países (Active e Inactive).

#### Scenario: Sin tenancy
- **WHEN** se consulta el catálogo desde dos companies distintas
- **THEN** se ven los mismos registros globales

#### Scenario: Country obligatorio
- **WHEN** se intenta crear un VatType sin country_id
- **THEN** la validación falla

### Requirement: CRUD Configuración / Tipos de IVA
El sistema SHALL exponer bajo Configuración un Index ServerTable plano con
columna País (y filtro), Acciones (toggle, Edit modal, Delete confirm) y
Create/Edit por modal. Autorización vía Functionality de vat-types y permisos
asociados. Index SHALL NOT mostrar columna Estado. Sin sorts del cliente:
Active-first y luego name.

#### Scenario: SA abre Tipos de IVA
- **WHEN** un usuario autorizado abre Configuración → Tipos de IVA
- **THEN** ve el Index ServerTable de vat types con país visible

#### Scenario: Default Active-first
- **WHEN** se solicita el `table` sin sorts
- **THEN** Active primero y, dentro de cada grupo, por name

#### Scenario: Sin permiso
- **WHEN** un usuario sin viewAny fuerza la URL del Index
- **THEN** recibe 403

### Requirement: Seeder tipos IVA España
El sistema SHALL incluir un seeder que cargue los tipos v1 asignados a Country
España (id 66), documentado en README § Seeders, sin ejecutarse al abrir Index.

#### Scenario: Seed en entorno limpio (con geografía)
- **WHEN** existe Country España y se ejecuta el seeder de vat types
- **THEN** existen los tipos portados con country_id = España


## MODIFIED Requirements

### Requirement: Ítem Tipos de IVA en Configuración
El menú Configuración SHALL incluir Tipos de IVA hacia el Index del catálogo
VatType.

#### Scenario: SA ve Tipos de IVA en Configuración
- **WHEN** un Super Admin obtiene el menú Configuración
- **THEN** ve la entrada Tipos de IVA
