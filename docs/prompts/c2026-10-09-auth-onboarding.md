<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-10-09-auth-onboarding
Rule: 10-architecture
Summary: Cerrar el hueco de Fase 0 del onboarding mínimo self-service: registro en dos
pasos (User → Company), recuperación de contraseña, slug automático con sufijo timestamp
ante colisión, y aceptación de aviso/términos (vistas propias placeholder).
Goals:
- Paso 1 — Registro de User: email (único), password + confirmación, name. Sin
  verificación de email en este change. Tras registrarse, sesión iniciada pero sin acceso
  al ERP hasta completar Company.
- Paso 2 — Alta de Company (obligatoria para entrar al ERP): `nif` obligatorio y único
  (rechazar si ya existe); al menos `name` o `tradename`; logo opcional
  (jpg, webp, png, gif, svg); status Active si los datos son válidos. Reutilizar el flujo
  ya existente de CreateCompany (manager, user_companies, auto-Basic, sync permisos).
- Slug: generación automática para Company (y como regla general para cualquier concepto
  que lo requiera); ante duplicidad, sufijo timestamp hasta unicidad. El usuario no edita
  el slug en el registro.
- Usuario que completó paso 1 pero no paso 2: puede cerrar sesión; al volver a login sin
  company se le obliga otra vez al paso 2; mientras esté logueado sin company, pantalla
  bloqueante de “crea tu empresa” (sin ERP).
- Recuperación de contraseña (“olvidé mi password”) por email, en este change.
- Checkbox obligatorio de aceptación de aviso y términos en el registro; crear vistas
  propias placeholder (aún no existen; texto provisional aceptable).
- Enlace desde login hacia registro y hacia reset password.
NonGoals:
- Verificación de email.
- Consola Platform, billing/planes, roles custom del manager.
- Wizard de onboarding amplio (series, plan contable, almacén, SEPA…) de la visión.
- Red comercial / engorde Company más allá de lo indicado.
- Textos legales definitivos (solo placeholders de vistas propias).
Skills:
- laravel-patterns
- laravel-specialist
- frontend-design
Notes:
- Prerrequisitos archivados: login, SelectCompany, Company shell, company-roles
  (manager/guest + sync).
- Hoy `StoreCompanyRequest` permite nif nullable; el onboarding endurece nif required +
  unique (el CRUD admin puede alinearse o quedar con reglas distintas — 10-architecture
  decide sin relajar el unique de nif en registro).
- Almacenamiento del logo: 10-architecture elige disco/public path conforme a standards
  existentes; formatos acordados.
- Helper de slug único (+ timestamp) reutilizable en Support si encaja.


## ADDED Requirements

### Requirement: Registro en dos pasos (User luego Company)
El sistema SHALL permitir el alta self-service en dos pasos: primero User, después
Company; sin Company activa vinculada no hay acceso al ERP.

#### Scenario: Registro de usuario
- **WHEN** un visitante completa email único, password con confirmación y name, y acepta
  aviso y términos
- **THEN** se crea el User, inicia sesión y se le conduce al paso de crear Company (sin
  entrar al ERP)

#### Scenario: Usuario sin company tras login
- **WHEN** un User autenticado no tiene ninguna company activa vinculada
- **THEN** no accede al ERP y se le muestra el flujo bloqueante de alta de Company

#### Scenario: Alta de company en onboarding
- **WHEN** el usuario envía nif (único), al menos name o tradename, opcionalmente logo
  válido, y los datos pasan validación
- **THEN** se crea la Company en status Active, con slug autogenerado, queda como manager
  vinculado, y entra al ERP con esa empresa en sesión

#### Scenario: NIF duplicado
- **WHEN** el nif indicado ya existe en companies
- **THEN** se rechaza el alta con error de validación

#### Scenario: Logo opcional con formatos permitidos
- **WHEN** se adjunta un logo
- **THEN** solo se aceptan jpg, webp, png, gif o svg; si no se adjunta, la company se
  crea igual

---

### Requirement: Slug automático con sufijo timestamp
El sistema SHALL generar el slug automáticamente (sin edición en el registro) y, si ya
existe, añadir sufijo timestamp hasta que sea único. Esta regla aplica a Company en este
change y queda como convención para otros conceptos con slug.

#### Scenario: Colisión de slug
- **WHEN** el slug derivado del name/tradename ya está en uso
- **THEN** se persiste un slug con sufijo timestamp que no colisione

---

### Requirement: Recuperación de contraseña
El sistema SHALL permitir solicitar y completar un reset de contraseña por email desde el
flujo de autenticación.

#### Scenario: Solicitud de reset
- **WHEN** un usuario indica su email en “olvidé mi contraseña”
- **THEN** el sistema envía (o encola) el enlace de reset según el mecanismo Laravel de
  password reset

#### Scenario: Completar reset
- **WHEN** el usuario sigue el enlace válido y define una nueva contraseña
- **THEN** puede iniciar sesión con la nueva contraseña

---

### Requirement: Aviso y términos (vistas placeholder)
El sistema SHALL exigir aceptación de aviso y términos en el registro y SHALL exponer
vistas propias del proyecto para consultarlos (contenido placeholder hasta textos
definitivos).

#### Scenario: Sin aceptación no hay registro
- **WHEN** el visitante intenta registrarse sin marcar la aceptación
- **THEN** la validación falla

#### Scenario: Consulta de textos
- **WHEN** el visitante abre el enlace de aviso o términos
- **THEN** ve una vista propia del proyecto (placeholder)


## MODIFIED Requirements

No aplica de forma sustancial a capabilities previas; el login existente se enlaza con
registro y reset. El contrato de “sin company no hay ERP” ya vigente se reutiliza para el
paso 2 pendiente.
