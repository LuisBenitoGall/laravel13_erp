<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-06-company-roles-create
Rule: 10-architecture
Summary: Adaptar el formulario de **creación** de roles de empresa (tenant) al
  patrón ya hecho para roles de plataforma: alta por metadatos, sin matriz en
  Create, sin concepto/UI de universal. CTA Index → Create; post-guardar → Edit.
  La edición (matriz ¬AdminOnly, copy) queda para un change posterior.
Goals:
- Create de rol tenant alineado al Create de rol plataforma (metadatos:
  nombre + descripción opcional; Guardar/Cancelar).
- El rol creado SHALL quedar con `company_id` = company de sesión y
  `universal = false` siempre (los roles de tenant **nunca** son universales).
- SHALL NOT mostrar en Create el checkbox/switch «universal» **ni ninguna
  mención** al concepto de rol universal (ocultar del todo; el tenant no debe
  saber que existe — confirmado Luis).
- SHALL NOT mostrar la matriz de permisos en Create.
- Autorización Create: cualquier rol del tenant con permiso
  `company-roles.create` (confirmado Luis).
- Tras Guardar: **redirect a Edit** del rol creado (confirmado Luis).
- CTA «Crear» del Index/dashboard de roles empresa SHALL llevar al Create
  correcto (básico; confirmar enlace).
- Reutilizar en lo razonable el patrón/componentes del Create plataforma,
  adaptado al tenant (sin campo universal).
NonGoals:
- Rediseñar/pulir el **Edit** de roles empresa (matriz async, layout v1, copy a
  otras companies del usuario) — Luis: «luego iremos con la edición».
- Detalle de copy entre companies del tenant (regla de producto para Edit).
- Cambiar materialización de universales, CRUD plataforma SA, ni reglas
  manager/guest locales salvo lo mínimo que Create ya respete (nombres
  reservados).
- Incluir módulos AdminOnly en cualquier matriz tenant (regla para Edit; Create
  no lleva matriz).
- Explicar o exponer “universal” al tenant en UI/copy/i18n de este flujo.
Skills:
- openspec-propose
- tenancy-rbac (roles Spatie teams / company_id)
Notes:
- Origen: `/opsx-explore` roles de empresa; brief Create primero; Edit después.
- Modelo acordado (recap Luis):
  1) SA: roles plataforma (`company_id` NULL) globales y universales.
  2) Universales materializan al alta de company; tenant no los administra.
  3) Con `company-roles.create` se crean roles solo de su company; permisos en
     Edit desde módulos disponibles (autorizados ∩ activos ∩ ¬AdminOnly).
  4) Custom tenant nunca universales; copy a otras companies = Edit (luego).
- Dudas explore cerradas Luis: (1) redirect Edit; (2) permiso
  `company-roles.create`; (3) CTA Index → Create correcto; (4) universal
  oculto del todo (ni sospecha de roles similares en otros tenants).
- Hallazgo explore: Create plataforma = metadatos sin matriz. Create company
  hoy = `CompanyRoleForm` con matriz; `CreateCompanyRole` ya fuerza
  `universal = false`.
- Idioma: español. Ficha: `docs/agents/10-architecture.md`.


## ADDED Requirements

### Requirement: Create rol empresa solo metadatos
El sistema SHALL exponer un formulario Create de rol de la company de sesión
con nombre obligatorio y descripción opcional, sin control ni mención de
universal y sin matriz de permisos. Tras un alta válida SHALL persistir el rol
con `company_id` de la sesión y `universal = false`, y redirigir al Edit de
ese rol. El acceso a Create SHALL exigir `company-roles.create`.

#### Scenario: Alta tenant sin universal ni matriz
- **WHEN** un usuario con `company-roles.create` abre Create de rol de empresa
- **THEN** ve nombre y descripción, no ve universal ni matriz de permisos

#### Scenario: Persistencia local no universal y redirect Edit
- **WHEN** guarda un rol nuevo válido
- **THEN** el rol queda con `company_id` de la company de sesión y
  `universal = false`, y es redirigido al Edit de ese rol

### Requirement: CTA Crear del Index roles empresa
El Index de roles de empresa SHALL ofrecer un control Crear que navega al
Create de rol de empresa (no stub a destino incorrecto).

#### Scenario: CTA Crear navega a Create
- **WHEN** un usuario autorizado pulsa Crear en el Index de roles empresa
- **THEN** navega al formulario Create de rol de empresa


## MODIFIED Requirements

### Requirement: CRUD de roles custom por empresa (manager)
El escenario de creación SHALL alinearse al Create por metadatos (sin exigir
asignación de permisos en el alta) y autorización por `company-roles.create`;
el resto del CRUD (Edit/matriz/borrado) no se rediseña en este change salvo lo
necesario para que Create compile y redirija a Edit.
