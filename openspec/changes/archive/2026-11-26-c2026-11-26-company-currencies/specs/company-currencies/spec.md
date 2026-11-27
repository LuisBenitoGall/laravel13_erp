# Spec Delta

## Purpose

Asociación de monedas de uso a cada Company (matriz o Delegación): conjunto
permitido más una moneda por defecto, sincronizable sin exigirla en el alta.

## ADDED Requirements

### Requirement: Pivote monedas de uso por Company
El sistema SHALL persistir por Company un conjunto de monedas del catálogo
global y, cuando el conjunto no esté vacío, exactamente una marcada como
default. La default SHALL pertenecer al conjunto. SHALL NOT exigir monedas
en Create Company ni Create Delegación.

#### Scenario: Sync con default y permitidas
- **WHEN** un actor autorizado sincroniza para una Company default EUR y
  permitidas EUR y USD
- **THEN** esa Company tiene esas filas y solo EUR con default

#### Scenario: Sync vacío
- **WHEN** un actor autorizado sincroniza `currency_ids` vacío
- **THEN** la Company queda sin monedas de uso

#### Scenario: Create sin moneda
- **WHEN** se crea una Company o Delegación
- **THEN** el alta tiene éxito sin filas de monedas de uso

#### Scenario: Default debe estar en el set
- **WHEN** se intenta sincronizar un default que no está en `currency_ids`
- **THEN** el sistema rechaza la operación

### Requirement: Matriz y Delegación independientes
Cada Company SHALL mantener su propio conjunto y default. SHALL NOT heredar
automáticamente las monedas de la matriz a una Delegación.

#### Scenario: Defaults distintos
- **WHEN** la matriz tiene default EUR y una Delegación hija default USD
- **THEN** cada una conserva su configuración sin sincronización cruzada

### Requirement: Aislamiento por Company
Las lecturas y mutaciones de monedas de uso SHALL respetar el perímetro de
la Company (R-TEN-05): un actor no SHALL alterar monedas de una Company fuera
de su vínculo / perímetro de Delegaciones de la sesión.

#### Scenario: No cruzar tenants
- **WHEN** un actor con acceso solo a company A intenta sync de monedas de
  company B ajena
- **THEN** se deniega la operación
