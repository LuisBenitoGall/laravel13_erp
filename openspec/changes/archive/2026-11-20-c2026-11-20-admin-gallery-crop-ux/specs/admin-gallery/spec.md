# Spec Delta: admin-gallery

## ADDED Requirements

### Requirement: Ayuda de uso en modal crop
El modal de recorte de Galería SHALL mostrar ayuda i18n que indique que
el actor mueve la imagen y usa el zoom, y que la zona clara es el área
que se guarda (sin redimensionar la rejilla con asas).

#### Scenario: Actor abre recortar
- **WHEN** el actor abre el modal de recorte
- **THEN** ve el texto de ayuda de pan + zoom

### Requirement: Selectores de aspect ratio en modal crop
El modal SHALL permitir elegir los ratios 1:1, 4:3, 16:9 y libre,
aplicando el `aspect` correspondiente en `react-easy-crop` sin cambiar
de librería. El default al abrir SHALL ser 4:3.

#### Scenario: Cambiar a 1:1
- **WHEN** el actor selecciona ratio 1:1
- **THEN** la zona de crop usa aspect 1:1

#### Scenario: Default 4:3
- **WHEN** el actor abre el modal de recorte
- **THEN** el ratio activo es 4:3
