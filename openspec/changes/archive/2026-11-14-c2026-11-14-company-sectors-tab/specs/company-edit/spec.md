# Spec Delta

## ADDED Requirements

### Requirement: Tab Sectores fuera de Info
El Edit de Company SHALL exponer la atribución de sectores en un tab
dedicado `sectors`, no dentro del tab Info. Create SHALL permanecer sin
atribución de sectores.

#### Scenario: Tabs incluyen Sectores
- **WHEN** un actor con `company-sectors.view` abre el Edit
- **THEN** existen tabs Info, Imágenes, Jerarquía y Sectores (según
  permisos de cada uno)

#### Scenario: Info sin panel sectores
- **WHEN** se renderiza el tab Info
- **THEN** no incluye el panel de checkboxes de sectores
