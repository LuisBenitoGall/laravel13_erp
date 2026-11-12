# Proposal

## Why

El Create de Company pide `slug` y `logo` en UI y exige slug en el
FormRequest, pese a que `slug-generation` ya manda slug automático. Hace
falta un alta simple (name, tradename, nif, status), NIF único con formato,
y redirect a Edit. El pago por nueva empresa queda para un change futuro.

## What Changes

- Formulario Create: solo **name**, **tradename**, **nif**, **status**.
- Sin campo slug en Create; slug generado en Action (`UniqueSlug` desde
  name, con fallback si hace falta).
- Sin logo en Create (queda fuera de este alta).
- NIF: único (soft-delete aware), validación de formato (NIF/CIF/NIE/EIN
  alineada a la lógica v1 `validateNif`), `maxLength` UI + `max` Request
  razonables y alineados (R-FE-08).
- POST exitoso → redirect a Edit (ya es el patrón del controller).
- NonGoal: paso intermedio de pago.

## Capabilities

### New Capabilities

- `company-create`: alta simple de Company (campos, slug auto, NIF,
  redirect Edit).

### Modified Capabilities

- `slug-generation`: reafirmar que el Create de Company no edita slug
  (cerrar la deriva del formulario actual).

## Impact

- `CompanyForm` / `Create.tsx`; `StoreCompanyRequest`; `CreateCompany`.
- Rule/validador NIF reutilizable (sin portar aún `CompanyIdentifier`
  histórico completo).
- Tests Feature Partners create; ficha Partners.md.
- Onboarding: fuera de alcance salvo reutilizar validador NIF si conviene
  sin ampliar UX onboarding.
