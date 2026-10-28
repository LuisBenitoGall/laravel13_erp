## Purpose

Bloque breadcrumb bajo el topbar (`option` Link i18n + `suboption` texto); contrato optionKey/suboptionKey/optionHref; Index con suboption `dashboard`. ActionBar de vista se renderiza bajo este bloque cuando hay acciones.

## Requirements

### Requirement: Bloque breadcrumb option + suboption
El sistema SHALL mostrar, bajo el topbar del `AppLayout`, un bloque breadcrumb con el
título principal (`option`) y el secundario (`suboption`), resueltos siempre por claves
i18n (`optionKey`, `suboptionKey`). SHALL aceptar además el destino `optionHref` del
enlace del título principal (index de la funcionalidad). En pantallas Index,
`suboptionKey` SHALL ser `dashboard`. El título principal SHALL ser un enlace a
`optionHref`; el secundario SHALL ser texto no enlazado. SHALL NOT incluir campos
`module`/`slug` del header v1. La ActionBar de vista (bloque 3) SHALL renderizarse **bajo**
este breadcrumb cuando la página declare acciones (capability `shell-action-bar`); el
breadcrumb en sí no incluye esos botones.

#### Scenario: Breadcrumb create/edit
- **WHEN** una página Create/Edit declara optionKey, suboptionKey y optionHref
- **THEN** bajo el topbar se muestra t(optionKey) como enlace al index y t(suboptionKey)
  como texto secundario

#### Scenario: Breadcrumb index con Dashboard
- **WHEN** una página Index declara optionKey, suboptionKey `dashboard` y optionHref
- **THEN** se muestra el título de la funcionalidad y el secundario Dashboard (i18n)

#### Scenario: ActionBar bajo el breadcrumb
- **WHEN** la página declara acciones de vista
- **THEN** la ActionBar aparece debajo del breadcrumb, no dentro del bloque option/suboption

### Requirement: Migración de páginas AppLayout existentes
El sistema SHALL adaptar las páginas que ya usan `AppLayout` para declarar el contrato
del breadcrumb (optionKey, suboptionKey, optionHref) de forma que el bloque sea visible
y coherente, sin literales de UI.

#### Scenario: Páginas actuales con breadcrumb
- **WHEN** el usuario abre una pantalla ERP existente que usa AppLayout
- **THEN** ve el bloque breadcrumb según el contrato, con textos i18n
