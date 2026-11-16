# Tasks

## 1. Seeder

- [x] 1.1 Crear `PaymentMethodsSeeder` con ids 1–12, name ES/EN/CA,
      status Active; upsert por id; no borrar ajenos; restore soft-delete
      de esos ids. Verificar `db:seed --class=PaymentMethodsSeeder`.
- [x] 1.2 Documentar invocación en `docs/modules/Admin.md` (no
      DatabaseSeeder / Index no importa). Verificar ficha.

## 2. Tests + calidad

- [x] 2.1 Test: seed ×2 → 12 ids; JSON name es/en/ca; Active; sin
      columnas code/slug; filas ajenas intactas si se pre-crean. Verificar
      Pest.
- [x] 2.2 Pint + PHPStan verdes en lo tocado.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Pivot company / code legacy: changes futuros.
