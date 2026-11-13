## Purpose

Placeholder «Mi cuenta» bajo el Module cuenta: representa la cuenta SaaS
de la company (billing/renovaciones futuros), no la ficha del User.

## Requirements

### Requirement: Página Mi cuenta próximamente
El sistema SHALL exponer bajo Module `cuenta` un destino de menú Mi cuenta
que muestra un estado próximamente (cuenta company / renovaciones). SHALL
NOT renderizar la ficha Edit del User ni redirigir a `/admin/profile`.

#### Scenario: Menú abre placeholder
- **WHEN** el usuario autorizado abre Mi cuenta desde el menú lateral
- **THEN** ve la página próximamente y la URL no es la ficha
  `/admin/profile`

#### Scenario: Visibilidad
- **WHEN** el actor tiene `cuenta.access` y el `.view` de la Functionality
  de Mi cuenta (`profile.view` mientras se conserve el slug)
- **THEN** el ítem Mi cuenta aparece bajo Cuenta
