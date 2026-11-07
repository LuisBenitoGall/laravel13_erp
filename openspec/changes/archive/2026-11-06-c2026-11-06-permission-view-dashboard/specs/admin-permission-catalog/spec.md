## MODIFIED Requirements

### Requirement: Index Admin de permisos Spatie
El sistema SHALL exponer un Index Admin de permisos Spatie (ServerTable) con el nombre
del permiso, la funcionalidad asociada vía prefijo del name, y Acciones. El acceso SHALL
exigir el permiso Spatie `permissions.view` (Super Admin bypass vía Gate::before).

#### Scenario: Usuario autorizado abre Index
- **WHEN** un usuario con `permissions.view` (o Super Admin) abre el Index de permisos
- **THEN** ve el listado ServerTable de permisos Spatie del guard web

#### Scenario: Listado muestra vínculo a Functionality
- **WHEN** el Index carga filas de permisos cuyo name sigue `{functionalitySlug}.*`
- **THEN** cada fila muestra el name Spatie y la funcionalidad derivada de ese prefijo

#### Scenario: Sin permiso viewAny
- **WHEN** un usuario sin `permissions.view` y sin Super Admin fuerza la URL del Index
- **THEN** recibe 403
