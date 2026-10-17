## ADDED Requirements

### Requirement: Sincronización de permisos del manager según autorización de módulos
El sistema SHALL mantener el rol `manager` de cada company con los permisos de las
Functionalities de módulos Basic y Optional autorizados para esa company, excluyendo
AdminOnly; la sincronización ocurre al autorizar/desautorizar (incluida la auto-autorización
Basic), no al encender/apagar.

#### Scenario: Autorizar módulo Optional añade permisos al manager
- **WHEN** Super Admin autoriza un Module Optional (Active) para una company
- **THEN** el rol `manager` de esa company obtiene los permisos de las Functionalities
  de ese módulo

#### Scenario: Desautorizar módulo quita permisos del manager
- **WHEN** se retira la autorización de un Module para una company
- **THEN** esos permisos dejan de estar en el rol `manager` de esa company

#### Scenario: AdminOnly no entra en el manager
- **WHEN** un Module es AdminOnly
- **THEN** el sync del manager no le asigna permisos de ese módulo aunque existiera
  autorización (solo Super Admin opera AdminOnly vía bypass)

#### Scenario: Encender o apagar no cambia role_has_permissions
- **WHEN** se enciende o apaga un módulo autorizado en `company_modules`
- **THEN** los permisos del rol `manager` no se modifican por ese hecho

#### Scenario: Auto-autorizar Basic sincroniza manager
- **WHEN** se auto-autorizan los módulos Basic al crear una company activa
- **THEN** el rol `manager` de esa company queda sincronizado con los permisos de esos
  módulos Basic
