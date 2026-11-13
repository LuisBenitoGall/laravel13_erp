# Spec Delta

## Purpose

Admin es el único dominio PHP del ERP funcional y `/admin` el prefijo URL
de todas las Functionalities; absorbe Partners/Banks y elimina prefijos
legacy sin capa de compatibilidad.

## ADDED Requirements

### Requirement: Admin único dominio funcional
El ERP funcional SHALL residir en un único dominio PHP `Admin`. SHALL NOT
mantener dominios PHP separados (`Partners`, `Banks`) para el código
absorbido en este change. Company y Bank SHALL vivir bajo Admin.

#### Scenario: Código Company y Banks en Admin
- **WHEN** se revisa el árbol de dominios tras el change
- **THEN** Company y Bank están bajo `App\Domains\Admin` y no existen
  `App\Domains\Partners` ni `App\Domains\Banks` como dominios propios

### Requirement: Prefijo URL /admin para Functionalities
Toda Functionality del ERP SHALL exponerse bajo el prefijo URL `/admin`.
Mis Empresas SHALL ser `/admin/companies`. Company-roles y la ficha
profile (self) SHALL estar bajo `/admin/...`. El grupo `/admin` SHALL
permitir aplicar middleware de acceso de forma uniforme.

#### Scenario: Mis Empresas
- **WHEN** un actor abre Mis Empresas
- **THEN** la URL es `/admin/companies` (u hojas bajo ese recurso)

#### Scenario: Sin prefijos partners/company
- **WHEN** se listan rutas de Functionalities
- **THEN** no quedan prefijos `/partners` ni `/company` para esas
  funcionalidades

#### Scenario: Profile bajo admin
- **WHEN** el actor abre la ficha Edit de sí mismo desde el avatar
- **THEN** la URL está bajo `/admin/profile` (no `/profile` en la raíz)

### Requirement: Corte limpio de rutas legacy
El sistema SHALL NOT mantener redirects desde `/partners/*` o
`/company/*`.

#### Scenario: Legacy
- **WHEN** se solicita una antigua URL `/partners/companies`
- **THEN** no hay redirect oficial a `/admin/companies` (sin compat layer)
