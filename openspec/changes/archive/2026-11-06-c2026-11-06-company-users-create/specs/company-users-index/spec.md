## MODIFIED Requirements

### Requirement: Index Usuarios empresa
El sistema SHALL listar en ServerTable los users presentes en `user_companies`
para la company de sesión. SHALL ofrecer acciones de fila activar/desactivar
(status del user), eliminar (quitar vínculo a la company de sesión) y editar
navegando al Edit stub del usuario (cabecera mínima; formulario completo
diferido). SHALL mostrar control Crear usuario que navega al Create real
(`company-users.create`). El acceso al Index/table SHALL exigir
`company-users.view` (no `company-users.viewAny`).

#### Scenario: Filas = user_companies de sesión
- **WHEN** el usuario abre Usuarios empresa con company de sesión S
- **THEN** la tabla muestra solo users vinculados a S en `user_companies`

#### Scenario: Aislamiento entre companies
- **WHEN** el usuario tiene sesión en company A
- **THEN** no ve en Usuarios empresa los users vinculados solo a company B

#### Scenario: Crear es máscara
- **WHEN** el usuario autorizado pulsa Crear usuario
- **THEN** navega al formulario Create real (ya no es máscara de alta)

#### Scenario: Editar es máscara
- **WHEN** el usuario usa la acción Editar en una fila
- **THEN** navega al Edit stub del usuario (cabecera; sin formulario completo)

#### Scenario: Activar o desactivar
- **WHEN** el usuario autorizado conmuta el status de un user de la tabla
- **THEN** el `status` del user queda persistido

#### Scenario: Eliminar quita vínculo de sesión
- **WHEN** el usuario autorizado confirma eliminar en Usuarios empresa
- **THEN** se elimina el vínculo `user_companies` con la company de sesión y el
  User global no se borra por esa acción

#### Scenario: Sin permiso view
- **WHEN** un usuario sin `company-users.view` y sin Super Admin fuerza el
  Index o el endpoint `table` de Usuarios empresa
- **THEN** recibe 403
