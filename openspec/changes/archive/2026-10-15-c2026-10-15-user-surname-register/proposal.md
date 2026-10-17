## Why

El registro self-service ya recoge `name`, pero el modelo de dominio contempla
`users.surname` (columna existente) y el producto exige apellidos en el alta. Sin ese
campo obligatorio, el User queda incompleto respecto al contrato de identidad personal.

## What Changes

- Campo **Apellidos** (`surname`) obligatorio en el formulario de registro (paso 1),
  inmediatamente después de Nombre.
- Validación `required` + persistencia en `User` vía el flujo existente
  (`RegisterUserRequest` → `RegisterUser`).
- Actualización de tests Feature (y ajuste del E2E de registro si cubre el formulario).
- Sin migración nueva: la columna `users.surname` ya existe (`nullable` en BD; el
  registro la exige en aplicación).
- Sin cambio de pantallas de perfil/edición ni de onboarding de Company.

## Capabilities

### New Capabilities

- (ninguna)

### Modified Capabilities

- `auth-registration`: el escenario de registro de User exige también `surname`
  obligatorio (además de name, email, password y aceptación legal).

## Impact

- `resources/js/Pages/Auth/Register.tsx`
- `app/Http/Requests/Auth/RegisterUserRequest.php`
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Support/Auth/RegisterUser.php`
- `app/Models/User.php` (`$fillable`)
- Tests: `tests/Feature/Auth/RegisterTest.php` (+ browser de registro si aplica)
- Spec main `openspec/specs/auth-registration/spec.md` (vía delta al archivar)
- Glosario: añadir término `surname` / Apellidos si aún no figura
