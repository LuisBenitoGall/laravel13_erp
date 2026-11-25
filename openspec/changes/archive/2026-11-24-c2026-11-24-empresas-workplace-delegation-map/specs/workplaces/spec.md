# Spec Delta — workplaces

## MODIFIED Requirements

### Requirement: CRUD Workplace con ServerTable
El sistema SHALL exponer CRUD completo de Workplace bajo
`/admin/workplaces`: Index con `<ServerTable>`, Create, Edit y Delete
(soft), con Policy y permisos Spatie `workplaces.*`. El Edit (y Create si
aplica) de un workplace de la matriz SHALL permitir gestionar el mapeo N:M a
Delegaciones hijas y la delegación preferida, sin alterar el resto del MVP.

#### Scenario: Index Tabulator
- **WHEN** el actor con `workplaces.view` abre el Index
- **THEN** ve un listado `<ServerTable>` de workplaces de la sesión

#### Scenario: Create Edit Delete
- **WHEN** el actor autorizado crea, edita o elimina un workplace
- **THEN** la mutación aplica solo al registro de su company de sesión

#### Scenario: Gestión de vínculos en ficha sede
- **WHEN** el actor autorizado edita un workplace de la matriz
- **THEN** puede añadir/quitar delegaciones vinculadas y la preferida
