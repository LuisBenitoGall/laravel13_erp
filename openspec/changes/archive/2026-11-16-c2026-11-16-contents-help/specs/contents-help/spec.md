# Spec Delta

## Purpose

Contenidos de ayuda (Help) con ancla `code`: CRUD global bajo
Configuración, multiidioma, resolución cacheada y tip UI en pantallas
del ERP.

## ADDED Requirements

### Requirement: CRUD Content Help bajo Configuración
El sistema SHALL permitir listar, crear, editar y soft-delete Contents
de kind Help bajo Configuración, con permisos Spatie. Los Help de MVP
SHALL tener `company_id` null (globales). SHALL NOT usar `serialize()`.
Campos: name, code, title, excerpt, content multiidioma, referrer;
vídeo por URL opcional; status Active/Inactive.

#### Scenario: Alta Help global
- **WHEN** un actor con permiso crea un Content Help con code y textos
- **THEN** se persiste con `company_id` null y queda disponible por ancla

#### Scenario: Soft delete
- **WHEN** el actor elimina un Content Help
- **THEN** se aplica soft delete y deja de resolverse en anclas activas

#### Scenario: Code inmutable
- **WHEN** el actor edita un Help existente
- **THEN** el `code` no cambia

### Requirement: Ancla code y consumo HelpTip
El sistema SHALL identificar cada Help por `code` único (inmutable tras
alta) y SHALL exponer un componente de UI que, dado un `code`, muestre
title/excerpt (tip/popover) según locale, sin romper si el code no existe.

#### Scenario: Resolver ancla
- **WHEN** una pantalla renderiza una ancla cuyo Content está Active
- **THEN** el usuario ve el tip en su idioma

#### Scenario: Code inexistente
- **WHEN** se pide un code sin Content
- **THEN** no hay error de página; el tip no muestra contenido

### Requirement: Resolución eficiente sin JSON runtime
El sistema SHALL resolver Help desde BD (o cache derivado), invalidando
al guardar/borrar/cambiar status. SHALL NOT leer
`storage/json/contents.json` en cada request como fuente de verdad.

#### Scenario: Tras editar
- **WHEN** se actualiza un Help y se vuelve a mostrar una ancla
- **THEN** se ve el texto nuevo (cache invalidada o equivalente)

### Requirement: Mismo texto para todos los tenants
Los Help globales SHALL mostrar el mismo contenido con independencia de
la company de sesión.

#### Scenario: Cambio de empresa
- **WHEN** el actor cambia de company y abre una pantalla con ancla
- **THEN** el tip Help global es el mismo
