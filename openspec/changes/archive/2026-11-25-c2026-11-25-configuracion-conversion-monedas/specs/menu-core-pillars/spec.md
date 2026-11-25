# Spec Delta

## ADDED Requirements

### Requirement: Ítem Conversión monedas en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Conversión
monedas (Functionality `exchange-rates`), ordenado inmediatamente después de
Monedas, que enlaza el Index del maestro de cotizaciones Admin.

#### Scenario: SA abre Conversión monedas desde el menú
- **WHEN** Super Admin abre Conversión monedas desde Configuración
- **THEN** navega al Index de exchange-rates
