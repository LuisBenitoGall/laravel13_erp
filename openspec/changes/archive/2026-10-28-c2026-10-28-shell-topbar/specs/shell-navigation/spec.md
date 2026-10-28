## ADDED Requirements

### Requirement: Aside colapsable desde el topbar
El sistema SHALL permitir comprimir y expandir el aside del `AppLayout` mediante el botón
de collapse del topbar (bloque 1). En viewport móvil el aside SHALL iniciar colapsado.
Las reglas de contenido del aside (logos, Dashboard, menú tenant, Configuración SA)
permanecen las de este capability; este requisito solo añade el comportamiento de
colapso/expansión.

#### Scenario: Toggle aside desde topbar
- **WHEN** el usuario pulsa el botón de collapse del topbar
- **THEN** el aside pasa de expandido a colapsado o viceversa

#### Scenario: Default móvil colapsado
- **WHEN** el usuario carga el AppLayout en viewport móvil
- **THEN** el aside está colapsado hasta que lo expanda
