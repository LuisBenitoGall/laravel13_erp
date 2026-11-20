# Spec Delta

## REMOVED Requirements

### Requirement: Edit placeholder
**Reason**: Sustituido por el Edit real en capability `contents-edit`
(cabecera RecordSummary + formulario por kind).
**Migration**: Tras Continuar / Edit desde Index, navegar a la página
Edit real (`contents-edit`); eliminar copy «próximamente».

## MODIFIED Requirements

### Requirement: Create Content mínimo
El sistema SHALL permitir crear un Content con solo nombre y tipo
(Información | Contenido). SHALL NOT mostrar status en el Create.
SHALL persistir status Inactive por defecto. SHALL generar `code`
único. Tras guardar SHALL redirigir al Edit **real** (no placeholder).
SHALL persistir `company_id` null.

#### Scenario: Alta Continuar
- **WHEN** el actor completa nombre y tipo y continúa
- **THEN** se crea el Content Inactive con company_id null y navega al
  Edit real del registro
