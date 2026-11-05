# Spec Delta: platform-roles

## ADDED Requirements

### Requirement: CTA Crear rol navega a Create
El Index de roles de plataforma SHALL mostrar un control Crear rol en la
ActionBar que navega al flujo Create. SHALL completar el alta vía ese flujo
(ya no stub).

#### Scenario: CTA Crear abre Create
- **WHEN** Super Admin pulsa Crear rol en el Index de roles de plataforma
- **THEN** navega a la pantalla Create de roles de plataforma

### Requirement: Formulario Create de rol de plataforma (metadatos)
El sistema SHALL exponer un formulario Create de rol de plataforma con nombre
obligatorio, descripción opcional e indicador universal (sí/no). SHALL NOT
mostrar la matriz de permisos en Create. Tras un alta válida SHALL persistir el
rol con `company_id` NULL y redirigir a Edit para atribuir permisos. El mismo
patrón de formulario (metadatos + permisos en Edit) SHALL quedar preparado para
reutilizar en roles tenant en un change posterior; este change solo cablea
plataforma.

#### Scenario: Create solo metadatos
- **WHEN** Super Admin abre Create de rol de plataforma
- **THEN** ve nombre, descripción y universal, y no ve la matriz de permisos

#### Scenario: Alta y redirect a Edit
- **WHEN** Super Admin guarda un rol nuevo válido (universal o genérico)
- **THEN** el rol queda persistido con `company_id` NULL y es redirigido a Edit

#### Scenario: Permisos se atribuyen en Edit
- **WHEN** Super Admin llega a Edit tras crear un rol
- **THEN** puede marcar y guardar permisos en la matriz (contrato Edit existente)

## REMOVED Requirements

### Requirement: CTA Crear rol stub en Index
**Reason**: El incremento activa Create; el stub ya no aplica.
**Migration**: Sustituido por «CTA Crear rol navega a Create».

## MODIFIED Requirements

### Requirement: CRUD de roles de plataforma (Super Admin)
El sistema SHALL permitir a Super Admin listar, crear y editar roles Spatie con
`company_id` NULL, clasificados como universales (`universal = true`, usables por
tenants / defaults de empresa) o genéricos de plataforma (`universal = false`, no
usables por tenants). SHALL excluir el rol `Super Admin` del CRUD editable. SHALL
impedir borrar las plantillas universales `manager` y `guest`. SHALL permitir borrar
otros roles de plataforma solo si no tienen usuarios asignados. El alta Create
SHALL capturar metadatos (nombre, descripción, universal); la atribución de
permisos SHALL ocurrir en Edit.

#### Scenario: Listar roles de plataforma
- **WHEN** Super Admin abre el listado de roles de plataforma
- **THEN** ve roles con `company_id` NULL gestionables y no puede editar ni borrar el
  rol `Super Admin` vía este CRUD

#### Scenario: Crear rol universal o de plataforma
- **WHEN** Super Admin crea un rol indicando nombre, descripción opcional y si es
  universal o genérico de plataforma
- **THEN** el rol queda persistido con `company_id` NULL y la clasificación indicada
  (sin exigir permisos en el alta)

#### Scenario: Manager y guest no borrables
- **WHEN** Super Admin intenta eliminar la plantilla universal `manager` o `guest`
- **THEN** la operación se rechaza

#### Scenario: Super Admin inmutable
- **WHEN** cualquier actor intenta modificar o borrar el rol `Super Admin` vía el CRUD
  de roles de plataforma
- **THEN** la operación no está permitida

#### Scenario: Borrar rol no protegido sin asignaciones
- **WHEN** Super Admin elimina un rol de plataforma que no es `Super Admin`, `manager`
  ni `guest` y no tiene usuarios asignados
- **THEN** el rol se elimina

#### Scenario: No borrar rol con usuarios asignados
- **WHEN** Super Admin intenta eliminar un rol de plataforma no protegido que tiene
  usuarios en `model_has_roles`
- **THEN** la operación se rechaza
