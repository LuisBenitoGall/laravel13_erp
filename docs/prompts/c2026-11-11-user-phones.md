<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-11-user-phones
Rule: 10-architecture
Summary: Activar la columna de teléfonos del tab Emails y teléfonos de la ficha de
  usuario (cards + modal alta/edición), con tabla polimórfica `phones` reutilizable
  (UI/API solo User en este change). Authz igual que emails.
Goals:
- Tab `emails_y_telefonos`: columna derecha de teléfonos operable (izquierda emails
  ya hecha en `c2026-11-11-user-emails`).
- Schema `phones` polimórfico según migration aportada: `morphs('phoneable')`,
  `e164`, `country_id` nullable FK, `ext`, `type`, `label`, `is_primary`,
  `is_whatsapp`, `is_verified`, `verified_at`, `notes`, softDeletes, timestamps;
  unique `(phoneable_type, phoneable_id, e164)`; índices owner+primary,
  owner+type, e164.
- Morph listo desde el día 1; UI y rutas/API de este change **solo** para
  `User` (ficha perfil / company-users edit).
- Para usuarios: `country_id` siempre `null` (sin selector en UI).
- Catálogo `type` (claves): `mobile`, `landline`, `fax`, `other`.
- UI cards (estilo capturas Luis): cabecera + **+ Teléfono**; card con número,
  badges Primario / WhatsApp / No verificado, tipo; acciones `tel:`, copy,
  WhatsApp (`wa.me` si flag), estrella primario, **editar**, eliminar (confirm).
- Alta y edición vía **modal** (campos: número/e164*, extensión, tipo, etiqueta,
  notas, toggles WhatsApp y Primario).
- Al marcar primario: la Action desmarca el resto del mismo owner.
- Authz: igual emails — self, o ajeno con `company-users.update` + perímetro
  CurrentCompany.
- Patrón Domains/Admin como emails (Policy/Action/FormRequest/controller;
  modelo Phone; relación morph en User; props Edit + readout).
NonGoals:
- Verificación SMS/llamada (sin acción de verificar; badge “No verificado”
  estático; `is_verified`/`verified_at` existen en schema para el futuro).
- UI/API de teléfonos para Company u otros phoneables (solo schema morph).
- Selector o persistencia de `country_id` distinto de null en usuarios.
- Cambiar emails adicionales ni el email principal.
- Avatar, Empresas tab, permisos Spatie nuevos distintos a la authz de ficha.
Skills:
- openspec-propose
- tenancy-rbac
- inertia-react-development
- testing-best-practices (CRUD User phones; unique owner+e164; primary único;
  self/ajeno; soft delete; morph solo User en runtime)
Notes:
- Origen: `/opsx-explore` tras archivar user-emails; migration de otro proyecto
  pegada por Luis; UI = dos capturas (cards + modal “Nuevo teléfono”).
- Estado: emails vivos (`ContactsEmailsPanel`); `phones` prop del Edit aún
  lista vacía / placeholder.
- Alcance acciones acordado: opción **C** (incluye Edit); verificar fuera.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Schema polimórfico phones
El sistema SHALL crear la tabla `phones` polimórfica (`phoneable`) con los campos
e índices de la migration aportada (incl. softDeletes y unique por owner+e164).
`country_id` SHALL ser nullable. Para teléfonos de User, `country_id` SHALL
persistirse como null.

#### Scenario: Alta de teléfono de User
- **WHEN** un actor autorizado crea un teléfono en la ficha de un User
- **THEN** se persiste con `phoneable` = ese User, `country_id` null, y el resto
  de campos del modal

#### Scenario: e164 duplicado en el mismo owner
- **WHEN** se intenta crear o editar un teléfono con un e164 ya existente en el
  mismo phoneable
- **THEN** la operación se rechaza con error de validación

### Requirement: Columna teléfonos en tab contactos (cards)
El tab Emails y teléfonos SHALL mostrar en la columna derecha los teléfonos del
User en cards (no tabla), con botón de alta y acciones tel, copy, WhatsApp (si
flag), primario, editar y eliminar.

#### Scenario: Listado en cards
- **WHEN** el user tiene teléfonos
- **THEN** cada uno se muestra en card con número, tipo, badges de primario /
  WhatsApp / no verificado según flags, y las acciones acordadas

#### Scenario: Sin verificación operativa
- **WHEN** se muestra una card
- **THEN** el badge de verificación es informativo; no hay flujo SMS/llamada de
  verificación

### Requirement: Alta y edición de teléfono por modal
El sistema SHALL permitir crear y editar un teléfono vía modal con: e164
obligatorio, extensión, tipo (catálogo `mobile`|`landline`|`fax`|`other`),
etiqueta, notas, toggles WhatsApp y Primario. Authz: self o
`company-users.update` + perímetro.

#### Scenario: Alta válida
- **WHEN** un actor autorizado envía datos válidos no duplicados
- **THEN** se persiste el Phone y aparece en el listado

#### Scenario: Edición válida
- **WHEN** un actor autorizado guarda cambios de un teléfono existente
- **THEN** se actualizan los campos y el listado refleja el resultado

### Requirement: Primario único por owner
Al marcar un teléfono como primario, el sistema SHALL desmarcar cualquier otro
primario del mismo phoneable en la misma operación (Action).

#### Scenario: Marcar primario
- **WHEN** se marca un teléfono como primario (alta, edición o acción estrella)
- **THEN** ese queda primario y el resto del mismo owner dejan de serlo

### Requirement: Eliminar teléfono
El sistema SHALL permitir eliminar un teléfono (con confirmación; soft delete).

#### Scenario: Eliminar
- **WHEN** el actor autorizado confirma eliminar
- **THEN** el teléfono deja de aparecer en el listado (soft-deleted)


## MODIFIED Requirements

### Requirement: Vista Edit de usuario unificada con tabs
El tab Emails y teléfonos SHALL dejar de tratar la columna derecha como
placeholder: SHALL mostrar la columna de teléfonos operable según este change;
la columna emails permanece según `c2026-11-11-user-emails`.

#### Scenario: Ambas columnas operables
- **WHEN** se abre el tab Emails y teléfonos
- **THEN** la columna emails y la de teléfonos son usables según sus respectivos
  contracts
