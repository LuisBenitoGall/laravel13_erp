# Spec Delta: menu-core-pillars

## ADDED Requirements

### Requirement: Ítem Galería en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Galería
(Functionality `gallery`), ordenado inmediatamente después de Contenidos,
que enlaza el Index `/admin/gallery`.

#### Scenario: SA abre Galería desde el menú
- **WHEN** Super Admin abre Galería desde Configuración
- **THEN** navega al Index de gallery
