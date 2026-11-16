# Spec Delta

## MODIFIED Requirements

### Requirement: Catálogo global PaymentMethod
El sistema SHALL persistir métodos de pago como catálogo global Admin
(`PaymentMethod`, sin `company_id`) con `name` traducible (JSON / Spatie)
y `status` Active/Inactive (columna string + enum PHP), con soft delete.
SHALL NOT exigir `code` ni campos contables/legacy. El catálogo SHALL
quedar poblable vía seeder de datos iniciales (ids canónicos) sin ampliar
el modelo MVP.

#### Scenario: Campos MVP
- **WHEN** un actor autorizado crea o edita un método de pago
- **THEN** puede informar name multi-idioma y status; no ve code ni
  campos contables/legacy

#### Scenario: Tras seed el Index muestra el set
- **WHEN** un actor abre el Index tras ejecutar el seeder canónico
- **THEN** ve los métodos canónicos Active con name traducido
