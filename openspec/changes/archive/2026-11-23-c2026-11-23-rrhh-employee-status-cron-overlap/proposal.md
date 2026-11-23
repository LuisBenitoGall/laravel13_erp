# Proposal

## Why

Los periodos laborales solo fijan `status` al crear; un `scheduled` no pasa a
`active` al llegar su fecha, ni un `active` con `end_date` vencida se cierra
solo. Además, alta/renovación pueden solapar fechas con un `active` que ya
tiene fin. Hay que automatizar transiciones y endurecer el no-solape (misma
company).

## What Changes

- Comando Artisan + `Schedule::daily` multi-tenant: `scheduled`→`active` y
  `active`→`ended` por caducidad (`end_date < hoy`); indefinidos no caducan.
- Regla de no solape (opción A): frente al `active` de la misma company,
  `nuevo.start_date` > `active.end_date` si el activo tiene fin.
- Renovar indefinido: permitido; anterior `ended` con fin = día previo al
  nuevo inicio.
- Tests Pest: cron, solape, renovación indefinido, aislamiento entre companies.
- Actualizar ficha HR (V-16) y delta `hr-employees`.

## Capabilities

### New Capabilities

_(ninguna)_

### Modified Capabilities

- `hr-employees`: cron de estados; no solape vs active; renovar indefinido
  con cierre D−1.

## Impact

- `app/Domains/HR` (Actions Create/Renew + Action sync estados),
  `app/Console/Commands`, `routes/console.php`.
- Validación FormRequest / mensajes i18n de solape.
- `docs/modules/HR.md` (V-16 sí).
- Sin UI nueva obligatoria (salvo errores de validación en formularios ya
  existentes). E2E N/A.
