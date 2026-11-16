# Spec Delta

## Purpose

Favoritos de vistas del ERP (`Preference`): el usuario guarda el path
actual desde el topbar con un nombre y los ve como CTAs en el dashboard,
acotados a user + company de sesión (máximo 20), con color de Module.

## ADDED Requirements

### Requirement: Guardar Preference desde el topbar
El sistema SHALL permitir, desde el icono de tres puntos del topbar,
abrir un formulario (popover) para guardar la vista actual como
Preference con `name` y `url` como path sin dominio, asociada al user y
a la company de sesión. SHALL NOT superar 20 Preferences por par
user+company.

#### Scenario: Alta favorito
- **WHEN** el actor en una vista del ERP introduce un nombre y guarda
- **THEN** se crea un Preference con path actual, user_id y company_id
  de sesión

#### Scenario: Tope 20
- **WHEN** el actor ya tiene 20 favoritos en esa company e intenta otro
- **THEN** no se crea y se informa del límite

### Requirement: Dashboard CTAs de favoritos
El Dashboard SHALL listar las Preferences del user para la company de
sesión como CTAs enlazados al path guardado, con color (e icono si
aplica) del Module asociado cuando `module_id` esté resuelto, y permitir
eliminar un favorito.

#### Scenario: Ver y abrir
- **WHEN** el actor abre el dashboard con favoritos guardados
- **THEN** ve CTAs con el nombre elegido que navegan al path guardado

#### Scenario: Eliminar
- **WHEN** el actor elimina un CTA de favorito
- **THEN** el Preference desaparece del dashboard

### Requirement: Favoritos por tenant de sesión
Las Preferences SHALL filtrarse siempre por `company_id` de la sesión
activa además de `user_id`.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company de sesión
- **THEN** el dashboard muestra solo los favoritos de esa company

#### Scenario: Aislamiento
- **WHEN** un actor intenta borrar un Preference de otra company o de
  otro user
- **THEN** la operación falla (403/404)
