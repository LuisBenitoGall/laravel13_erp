## Purpose

Catálogo global Admin de métodos de pago (`PaymentMethod`): name
traducible y status, con CRUD bajo Configuración. Base para facturación;
sin selección por company ni cuenta contable en este capability.

## Requirements

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

### Requirement: CRUD Configuración / Métodos de pago
El sistema SHALL exponer CRUD completo bajo `/admin/payment-methods`:
Index `<ServerTable>`, Create/Edit (modales con LangTabs) y Delete, con
Functionality `payment-methods`, Policy y permisos Spatie canónicos
`payment-methods.*`.

#### Scenario: Entrada Configuración
- **WHEN** Super Admin (o actor con capas/permisos de Configuración)
  obtiene el menú
- **THEN** ve Métodos de pago bajo Configuración y puede abrir el Index
  ServerTable

#### Scenario: Toggle y soft delete
- **WHEN** el actor autorizado cambia status o elimina un método
- **THEN** el registro queda Inactive o soft-deleted según la acción

### Requirement: Default Active-first en Index Métodos de pago
El Index SHALL ordenar por defecto Active-first y luego name (locale),
salvo que el cliente envíe sorts explícitos.

#### Scenario: Sort por defecto
- **WHEN** el Index carga sin sorts del cliente
- **THEN** aplica Active-first + name
