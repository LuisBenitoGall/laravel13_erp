<!--
  Brief de handoff explore → 10-architecture. Se rellena SOLO con lo debatido en la
  conversación (nunca por inferencia); ante cualquier duda, preguntar antes de completar
  un campo. Protocolo completo: docs/agents/README.md § "Redactar el brief de handoff".
-->

Change: c2026-11-22-user-profile-readout
Rule: 10-architecture
Summary: Revisar la ficha rápida RO de usuario (`UserProfileReadout` en
  `/admin/profile` y Edit de Usuarios empresa): ocultar NIF/fecha/activo/
  perfil público; emails adicionales sin observación + copiar; teléfonos
  igual; empresas vinculadas (nombre, enlace vacío por ahora).
Goals:
- Tocar **solo** la ficha de vista rápida (`UserProfileReadout`); **no**
  modificar el formulario del tab Perfil.
- Ocultar en la ficha: NIF, fecha de nacimiento, activo y perfil público.
- Emails adicionales: listar la dirección **sin observaciones**; icono a la
  derecha para copiar al portapapeles con toast «Copiado» (o i18n equivalente).
- Teléfonos: dejarlos como están (incl. etiqueta tipo trabajo/casa).
- Incluir sección de empresas vinculadas **solo si hay alguna**: mostrar
  **solo el nombre**; el enlace a vista de empresa queda **vacío por ahora**
  (pendiente de una vista RO de empresas en un change futuro).
- Misma ficha en `/admin/profile` y en la edición de usuario (company-users).
NonGoals:
- Cambiar el formulario de Perfil (NIF, fecha, status, is_public, etc.).
- Crear la vista de solo lectura de empresas ni cablear `/admin/companies/...`
  ni `company-directory`.
- Cambiar el tab Empresas / ServerTable de vínculos.
- Rediseñar avatar, nombre o email principal de la cabecera de la ficha
  (salvo lo implícito al quitar el bloque de campos).
Skills:
- openspec-propose
- inertia-react-development
Notes:
- Origen: `/opsx-explore` revisión ficha rápida. Componente actual:
  `resources/js/Components/Users/UserProfileReadout.tsx`. Spec relacionada:
  `user-edit` (tab Perfil con resumen RO).
- Decisiones: (1) solo readout, no form; (2) empresas = nombre; link vacío
  hasta vista RO futura; (3) toast al copiar email; (4) sin empresas =
  ocultar sección.
- Hoy la ficha muestra NIF/fecha/activo/perfil público y emails con
  `description`/`label` como observación; no lista empresas.


## ADDED Requirements

### Requirement: Ficha rápida sin bloque NIF/fecha/activo/público
La ficha rápida RO de usuario SHALL NOT mostrar NIF, fecha de nacimiento,
activo ni perfil público. SHALL seguir mostrando cabecera (avatar, nombre,
email principal).

#### Scenario: Campos ocultos en readout
- **WHEN** el actor abre el tab Perfil en `/admin/profile` o en Edit de
  Usuarios empresa
- **THEN** la ficha rápida no muestra NIF, fecha de nacimiento, activo ni
  perfil público

### Requirement: Emails adicionales copiables sin observación
En la ficha rápida, la lista de emails adicionales SHALL mostrar solo la
dirección (sin observación/descripción) y un control a la derecha que copia
esa dirección al portapapeles con feedback toast.

#### Scenario: Email sin observación
- **WHEN** el user tiene un email adicional con descripción
- **THEN** la ficha muestra la dirección sin el texto de observación

#### Scenario: Copiar email
- **WHEN** el actor pulsa el icono de copiar de un email adicional
- **THEN** la dirección queda en el portapapeles y se muestra un toast de
  confirmación

### Requirement: Empresas vinculadas en ficha rápida
Si el user tiene empresas vinculadas, la ficha rápida SHALL listar sus
nombres. El enlace a la vista de empresa SHALL quedar vacío/no navegable en
este change (reserva para una vista RO futura). Si no hay empresas, SHALL NOT
mostrar la sección.

#### Scenario: Con empresas
- **WHEN** el user tiene una o más companies vinculadas
- **THEN** la ficha lista el nombre de cada una sin navegar a una vista de
  empresa aún

#### Scenario: Sin empresas
- **WHEN** el user no tiene companies vinculadas
- **THEN** la ficha no muestra la sección de empresas

---

## MODIFIED Requirements

### Requirement: Tab Perfil con resumen RO y formulario
El resumen RO de la izquierda (ficha rápida) SHALL reflejar el contenido
revisado (sin NIF/fecha/activo/público; emails sin observación + copiar;
teléfonos igual; empresas si hay). El formulario de la derecha SHALL
permanecer sin cambios de alcance en este change.

#### Scenario: Layout dos columnas sin tocar form
- **WHEN** el actor abre el tab Perfil
- **THEN** ve la ficha rápida actualizada a la izquierda y el mismo
  formulario de perfil a la derecha
