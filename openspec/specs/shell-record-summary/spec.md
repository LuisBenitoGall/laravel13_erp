## Purpose

Componente de catálogo `RecordSummary`: ficha card blanca para edit/show (título i18n, badge identificador, fields[] con tone); helper de auditoría opcional. Sin totales ni lápiz; opt-in por página.

## Requirements

### Requirement: Componente Record summary (card)
El sistema SHALL proveer un componente de ficha de registro (`RecordSummary`) en card
blanca con bordes redondeados que muestre un título (clave i18n), un identificador en
badge, y una lista de campos declarada por array (label i18n + valor), con soporte para
tono de estado cuando aplique. SHALL ofrecer un helper o contrato documentado para
campos habituales created_at, created_by, updated_at y updated_by sin exigirlos en toda
vista. SHALL destinarse a vistas edit/show (u otras similares), no a index ni create. El
componente SHALL ser opt-in por la página (no forzado por AppLayout). SHALL NOT incluir
box de totales ni acciones inline tipo lápiz. SHALL NOT migrar todas las páginas Edit
existentes en este capability: solo el componente y su registro en catálogo.

#### Scenario: Card con título, badge y campos
- **WHEN** se renderiza RecordSummary con titleKey, identifier y array de campos
- **THEN** se muestra la card con título traducido, badge del identificador y los pares
  label/valor

#### Scenario: Campo de estado con tono
- **WHEN** un campo se declara con tone de estado
- **THEN** el valor se muestra con énfasis visual de estado

#### Scenario: Sin totales ni lápiz
- **WHEN** se usa el componente
- **THEN** no aparece box de totales ni iconos de acción inline por campo

#### Scenario: Helper de auditoría opcional
- **WHEN** se usan los helpers de auditoría con created_at/created_by/updated_at/updated_by
- **THEN** se obtienen campos con labels i18n listos para concatenar al array de la vista
