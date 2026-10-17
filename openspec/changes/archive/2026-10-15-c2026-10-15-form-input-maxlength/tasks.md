## 1. Kit UI (Input)

- [x] 1.1 Asegurar util `cn` / deps shadcn necesarias según `libraries.md` (sin kits ajenos)
- [x] 1.2 Crear `resources/js/Components/ui/input.tsx` (shadcn) con `maxLength` default 255
- [x] 1.3 Exportar constante documentada del default (p. ej. `DEFAULT_INPUT_MAX_LENGTH`)
- [x] 1.4 Verificar que override de `maxLength` en props sustituye el default

## 2. Backend Auth (max alineado)

- [x] 2.1 Login: validación con `email`/`password` → `max:255` (FormRequest dedicado o reglas actuales)
- [x] 2.2 Forgot password: `email` → `max:255`
- [x] 2.3 Reset password: `email` → `max:255`; password con tope coherente con UI (255)
- [x] 2.4 Register: confirmar `max:255` en name/surname/email (y password alineado)
- [x] 2.5 Onboard company: confirmar nif `max:32`, name/tradename `max:255`

## 3. Frontend Auth (migración)

- [x] 3.1 Migrar `Login.tsx` campos texto → `Input` del kit
- [x] 3.2 Migrar `Register.tsx` campos texto → `Input` del kit
- [x] 3.3 Migrar `ForgotPassword.tsx` → `Input` del kit
- [x] 3.4 Migrar `ResetPassword.tsx` → `Input` del kit
- [x] 3.5 Migrar `OnboardCompany.tsx` campos texto → `Input` (nif con `maxLength={32}`)
- [x] 3.6 Dejar checkbox/file/hidden fuera del Input de texto (sin forzar el kit texto)

## 4. Canon + agentes

- [x] 4.1 Documentar en `docs/standards/frontend.md` la regla Input default 255 / override / FormRequest espejo / Textarea excluido
- [x] 4.2 Actualizar ejemplo en `docs/standards/templates-frontend.md` para usar el Input del kit
- [x] 4.3 Crear Cursor rule `.cursor/rules/` (form-input-maxlength) según design
- [x] 4.4 Ficha módulo / V-01..V-20 / module-map: no aplican (declarado en design)

## 5. Tests y CI

- [x] 5.1 Test Architecture: Auth sin `<input>` texto crudo; Input del kit con default 255
- [x] 5.2 Feature: rechazo por exceso de longitud en al menos un endpoint Auth (p. ej. login o register)
- [x] 5.3 Ajustar Feature Auth existentes si fallan por FormRequests nuevos
- [x] 5.4 Comprobar browser Auth existentes siguen pudiendo rellenar campos (ajuste mínimo si hace falta)
  - **40-e2e (2026-10-15):** PASS — `php artisan test tests/Browser/Auth` → 9 passed (0 failed). Sin ajustes de selectores/`fill` (mismos `name`). Sin suite E2E nueva (5.6 / design Decisión 8).
- [x] 5.5 R-TEN-05: no aplica (sin entidad BelongsToCompany nueva)
- [x] 5.6 E2E rol 40 nuevo: no aplica (`e2e.md` §4)

## 6. Calidad

- [x] 6.1 Pint / PHPStan en PHP tocado
- [x] 6.2 Types/lint front en componentes y páginas Auth tocadas
- [x] 6.3 Suite relevante verde (Architecture + Feature Auth)
