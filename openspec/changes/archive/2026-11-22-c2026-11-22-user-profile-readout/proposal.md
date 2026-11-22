# Proposal

## Why

La ficha rápida RO de usuario (`UserProfileReadout`) muestra datos redundantes
con el formulario (NIF, fecha, activo, perfil público), emails con observación
y no lista empresas vinculadas. Hay que alinearla al uso real de consulta rápida
sin tocar el formulario del tab Perfil.

## What Changes

- Ocultar en la ficha: NIF, fecha de nacimiento, activo y perfil público.
- Emails adicionales: solo dirección + icono copiar (toast); sin observación.
- Teléfonos: sin cambio de comportamiento.
- Sección empresas vinculadas solo si hay alguna: nombre; enlace vacío/no
  navegable (reserva para vista RO futura).
- Pasar a la ficha la lista de companies del user desde el payload de Edit
  (profile y company-users).
- **No** modificar el formulario de Perfil ni el tab Empresas.

## Capabilities

### New Capabilities
- _(ninguna)_

### Modified Capabilities
- `user-edit`: contenido del resumen RO del tab Perfil (ficha rápida).

## Impact

- Frontend: `Components/Users/UserProfileReadout.tsx`, `Pages/Users/Edit.tsx`
  (props a la ficha).
- Backend: `UserEditPageData` (o equivalente) expone `companies: [{ id, name }]`
  para el readout.
- i18n: label sección empresas + reutilizar clave de email copiado si existe.
- Tests: feature/smoke del payload + assertion de props / componente si aplica.
- Sin migraciones, sin rutas nuevas, sin vista RO de empresas.
- E2E §4: no aplica.
