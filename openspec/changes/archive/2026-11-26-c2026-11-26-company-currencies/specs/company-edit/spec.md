# Spec Delta

## ADDED Requirements

### Requirement: Sección Monedas en Edit Info
El tab Información del Edit de Company SHALL incluir una sección Monedas
(default + permitidas del catálogo Active) con guardado propio de sync.
SHALL NOT añadir un tab dedicado Monedas en este change. Create SHALL
permanecer sin atribución de monedas.

#### Scenario: Sección dentro de Información
- **WHEN** un actor autorizado abre Edit Company
- **THEN** ve Monedas dentro del tab Información, no como tab hermano de
  Imágenes/Jerarquía/Sectores

#### Scenario: Guardar monedas en Edit
- **WHEN** el actor guarda default y permitidas desde la sección Monedas
- **THEN** el pivote de esa Company queda sincronizado
