## Why

Los formularios Auth permiten teclear sin tope en el navegador (`maxLength` ausente),
mientras el backend solo a veces declara `max`. Hace falta un límite global en el kit de
UI (default 255), validación FormRequest alineada, migración de Auth y enforcement para
que formularios futuros no repitan el fallo.

## What Changes

- Componente `Input` del kit (shadcn/ui en `Components/ui/`) con `maxLength={255}` por
  defecto; overrides explícitos cuando el campo es más corto.
- Migrar **todas** las vistas Auth con campos de texto a ese componente (nada de
  `<input>` HTML suelto para texto).
- Alinear validación backend (FormRequest o extracción a FormRequest) con el mismo `max`
  que el `maxLength` del Input en cada campo Auth.
- Documentar el patrón en `frontend.md` / templates para formularios futuros.
- Enforcement CI (test de arquitectura) si aparece input de texto fuera del patrón.
- Rule de agentes Cursor que obligue a usar el Input del kit con maxLength (default u
  override).
- **Fuera de alcance:** Textarea / Wysiwyg; skill como única solución (el rule refuerza,
  no sustituye runtime + CI).

## Capabilities

### New Capabilities

- `form-input-maxlength`: Input del kit con default 255, overrides, alineación
  FormRequest, Auth migrado, CI y rule de agentes; Textarea excluido.

### Modified Capabilities

- (ninguna — Auth se cubre como escenarios de la capability nueva; no se reabre el
  contrato de registro/onboarding salvo el uso del Input)

## Impact

- `resources/js/Components/ui/input.tsx` (+ `cn`/deps shadcn si faltan)
- Páginas `resources/js/Pages/Auth/*` con inputs de texto
- FormRequests / validaciones Auth (login, forgot, reset, register, onboard…)
- `docs/standards/frontend.md`, `templates-frontend.md`
- `.cursor/rules/` (rule de formularios/inputs)
- `tests/Architecture/` (enforcement)
- Tests Feature Auth afectados por `max` nuevos
