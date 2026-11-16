# Spec Delta

## ADDED Requirements

### Requirement: Ítem Métodos de pago en Configuración
El sistema SHALL incluir bajo el Module `configuracion` un ítem Métodos
de pago (Functionality `payment-methods`) que enlaza el Index del
catálogo PaymentMethod Admin, sin sustituir los catálogos existentes.

#### Scenario: SA abre Métodos de pago desde el menú
- **WHEN** Super Admin abre Métodos de pago desde Configuración
- **THEN** navega al Index de payment-methods
