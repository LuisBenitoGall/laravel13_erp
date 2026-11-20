<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-20-helptip-company-nif
Rule: 10-architecture
Summary: Integrar `<HelpTip code>` junto a campos de formulario (icono
  info a la derecha, click → popover), como v1; primer consumidor = NIF
  en Create/Edit empresa (`company-nif`). Contemplar export/promoción
  de Contents Help fijos a otros entornos.
Goals:
- UX HelpTip: icono info a la derecha del control (Input, Select,
  Checkbox, etc.); click abre tip con `code`; solo Contents kind
  Información (Help) Active vía mapa existente.
- Aplicar en formularios Create y Edit de empresa el tip
  `company-nif` junto al campo NIF (Luis ya creó ese Content).
- Patrón reutilizable para más anclas en módulos futuros (kit Field u
  equivalente; decide 10).
- Diseñar cómo exportar/promover Contents Help de codes fijos a
  producción/otros entornos (catálogo estable cross-env).
NonGoals:
- Tips para kind Article / Contenido.
- Sembrar masivo todos los codes v1 en este change (salvo lo que 10
  decida para export/`company-nif`).
- Galería imágenes / Edit Content (otros changes).
- Rediseñar el mapa cache HelpTip de cero (reutilizar infra).
Skills:
- openspec-propose
- v1-reference
- inertia-react-development
- tenancy-rbac
Notes:
- Origen: `/opsx-explore` + captura v1 (icono naranja «i» a la derecha
  del NIF; popover título + excerpt).
- Componente ya existe: `resources/js/Components/HelpTip.tsx` + shared
  `help_tips`; hoy no está cableado en Form/Field ni en CompanyForm.
- Field kit tiene prop `help` = texto hint bajo el campo (≠ HelpTip);
  no confundir con `helpCode` / ancla.
- Primer ancla: code `company-nif` en Create+Edit empresa
  (`CompanyForm` campo nif).
- Export producción: Luis pide considerarlo (codes fijos globales);
  10 propone mecanismo (seeder por code, artisan export/import, etc.)
  sin inventar fuera del design.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: HelpTip anclado a campos de formulario
El sistema SHALL permitir asociar un `code` de Content Help a un campo
de formulario mostrando un icono de información a la derecha del
control; al activarlo SHALL mostrar el tip (título/excerpt) del locale.
SHALL NOT mostrar tip si el code no existe o no está Active. Aplica
solo a kind Información (Help).

#### Scenario: Tip con code Active
- **WHEN** el campo tiene helpCode de un Help Active y el actor abre el
  icono
- **THEN** ve el popover con el contenido del tip en su idioma

#### Scenario: Code ausente o Inactive
- **WHEN** el helpCode no resuelve a un Help usable
- **THEN** no se muestra icono/tip (o no rompe la página)

### Requirement: company-nif en Create/Edit empresa
Los formularios Create y Edit de Company SHALL mostrar el HelpTip con
code `company-nif` junto al campo NIF.

#### Scenario: NIF Create
- **WHEN** el actor abre Create empresa y existe Content Help
  `company-nif` Active
- **THEN** ve el icono info junto al NIF y el tip al activarlo

#### Scenario: NIF Edit
- **WHEN** el actor abre Edit empresa con el mismo code Active
- **THEN** el tip está disponible igual que en Create

### Requirement: Promoción de Contents Help entre entornos
El design SHALL definir un mecanismo para exportar/importar o sembrar
Contents Help de codes fijos (p. ej. `company-nif`) de forma reproducible
hacia producción u otros entornos, sin depender de copiar BD a mano.

#### Scenario: Contrato de promoción documentado
- **WHEN** se revisa el design del change
- **THEN** existe una decisión explícita (comando/seeder/artefacto) para
  promover Help por code


## MODIFIED Requirements

### Requirement: Kit Form Field (si aplica)
(Si 10 integra la ancla en el kit.) El `Field` (o wrapper acordado)
SHALL aceptar un código de ayuda opcional distinto del hint textual
`help`, colocando el icono a la derecha del control.

#### Scenario: helpCode en Field
- **WHEN** un Field declara helpCode
- **THEN** el control muestra el icono HelpTip a su derecha sin sustituir
  el hint `help` bajo el campo
