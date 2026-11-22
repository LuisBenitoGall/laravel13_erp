# Tasks

> Sin entidad BD nueva. E2E §4: no aplica. R-TEN-05: N/A (solo readout UI +
> prop de nombres). V-01..V-20: N/A.

## 1. Payload

- [x] 1.1 Ampliar `UserEditPageData` con `companies: [{ id, name }, …]` de los
  vínculos del user (orden por nombre); verificar en test feature que profile
  y/o company-users edit Inertia incluyen esa prop

## 2. Ficha rápida UI

- [x] 2.1 Actualizar `UserProfileReadout`: quitar filas NIF/fecha/activo/
  perfil público; emails solo address + botón copiar (`clipboard` +
  `email_copiado`); teléfonos sin cambio; sección empresas si `companies.length`
  (nombre, sin href); verificar visualmente o con testid que no hay esos
  campos ni observaciones
- [x] 2.2 Cablear `companies` desde `Pages/Users/Edit` al readout; i18n label
  de sección empresas (es/en/ca); verificar que sin companies no se renderiza
  la sección
- [x] 2.3 Suite filtrada verde + Pint en PHP tocado

## Workflow follow-up

- Auditoría 25 → 30-testing (sin 40) → `/opsx-archive`
- Ejecutar `/opsx:apply` para implementar
