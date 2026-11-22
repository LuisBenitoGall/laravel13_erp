<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-foundation-hardening
Rule: 10-architecture
Summary: Remediación prioritaria de la auditoría de cimientos: imágenes
  (y galería) fuera de disco público sin auth; login exige user activo;
  desactivar user = status global (no entra al ERP), coherente con login.
Goals:
- **F-01:** Imágenes de usuario no servibles por URL estática anónima.
  Mismo nivel de protección que docs (disco privado + ruta autenticada, o
  mecanismo equivalente con authz).
- **F-05:** Galería `MediaAsset` — mismo problema de disco `public`; incluir
  en la misma oleada de storage (privado / signed / ruta app con auth).
- **F-02:** Login SHALL rechazar usuarios con `users.status` inactivo; nadie
  inactivo entra al ERP.
- **F-03 = A (decisión Luis):** Desactivar desde Usuarios empresa (fila o
  masivo) = `users.status` global false → el user no puede acceder al ERP
  (alineado con F-02). No modelar status por membership en este change.
  Si hace falta, dejar claro en UI/copy que la acción es global.
- Basarse en el informe
  `docs/audits/c2026-11-22-foundation-security-audit.md`.
NonGoals:
- F-04 (reducir PII quick-view all-users) — oleada posterior salvo que 10
  lo meta sin ampliar mucho.
- F-06 (scope galería solo CurrentCompany) — posterior; este change se
  centra en no exponer ficheros sin auth.
- F-07/F-08 (props `can` decorativas / FormRequest authorize true) —
  higiene posterior.
- F-09 registro público / F-10 BelongsToCompany null / F-11 Pest sockets —
  fuera salvo nota DX en design.
- Empezar módulos de negocio (ventas, stock, etc.).
Skills:
- openspec-propose
- tenancy-rbac
- laravel-best-practices
Notes:
- Origen: `/opsx-explore` auditoría cimientos + Security Review.
- Decisiones: F-02 login+status; F-03 = A (status global = fuera del ERP);
  F-01 explicado y aceptado como alto a remediar; F-05 mismo patrón storage.
- Hoy: `UploadUserImages` → disk `public`; login `Auth::attempt` sin status;
  `UpdateCompanyUserStatus` ya es global (se confirma como producto).


## ADDED Requirements

### Requirement: Imágenes de usuario no públicas anónimas
El sistema SHALL servir las imágenes de usuario de forma que un GET anónimo
a una URL estática predecible no entregue el fichero. SHALL aplicar authz
equivalente a la del resto de la ficha del user (self / company-users según
contexto).

#### Scenario: Sin login no descarga imagen
- **WHEN** un cliente no autenticado solicita la URL de una imagen de user
- **THEN** no obtiene el binario de la imagen (401/403/404 o redirección a
  login; no 200 anónimo del fichero)

### Requirement: MediaAsset galería no pública anónima
Los ficheros de galería Admin SHALL NOT ser descargables de forma anónima
vía URL estática de disco público sin control de acceso.

#### Scenario: Sin login no descarga media de galería
- **WHEN** un cliente no autenticado solicita la URL estática de un
  MediaAsset
- **THEN** no obtiene el binario (mismo criterio de protección que imágenes
  de user)

### Requirement: Login exige user activo
El sistema SHALL denegar el login cuando `users.status` es inactivo. SHALL
NOT crear sesión autenticada usable para un user inactivo.

#### Scenario: Inactivo no entra
- **WHEN** un user con status inactivo envía credenciales correctas en login
- **THEN** no accede al ERP (error de credenciales o mensaje de cuenta
  inactiva; sin sesión operativa)

### Requirement: Desactivar user es global y bloquea acceso
Desactivar un user desde Usuarios empresa (acción de fila o masiva) SHALL
persistir `users.status` inactivo a nivel global. Ese user SHALL quedar
imposibilitado de login al ERP (requisito de login activo).

#### Scenario: Desactivar desde company-users
- **WHEN** un actor autorizado desactiva un user en Usuarios empresa
- **THEN** el status global del user queda inactivo y ese user no puede
  iniciar sesión

---

## MODIFIED Requirements

### Requirement: (login / auth session — si existe spec)
El flujo de login existente SHALL incorporar la comprobación de status
activo descrita arriba.

#### Scenario: Activo sí entra
- **WHEN** un user activo envía credenciales correctas
- **THEN** el login sigue el flujo post-login vigente (companies /
  onboarding)
