## ADDED Requirements

### Requirement: Contrato de verificación de acceso tenant por módulo futuro
El sistema de estándares del proyecto SHALL exigir que cada módulo nuevo que exponga
rutas HTTP activables aporte verificación Feature de acceso tenant: módulo no
disponible (capas) bloquea; usuario sin permiso de Functionality recibe 403; usuario
con capas satisfechas y permiso puede acceder. Este change SHALL publicar el contrato
en playbook (V-21 + §7) y una plantilla Pest reutilizable. Los módulos futuros SHALL
rellenar esa verificación al implementarse; este change SHALL NOT exigir rellenar la
batería para todos los módulos ya existentes.

#### Scenario: Contrato publicado
- **WHEN** se completa la implementación de este change
- **THEN** existen en playbook el ítem V-21 y el checkbox de §7 correspondientes, y una
  plantilla Pest documentada para copiar en módulos futuros

#### Scenario: Módulo futuro sin la verificación
- **WHEN** un change de módulo con rutas HTTP activables no aporta los tests de acceso
  tenant exigidos por V-21
- **THEN** el gate de revisión (25/30) o el DoD del playbook lo marca incompleto
