# Tasks

## 1. Seeder

- [x] 1.1 Sustituir la constante `SECTORS` en `SectorsSeeder` por las 19
      filas NACE A–S (slug + es/en/ca) del design. Verificar source: sin
      T/U; 19 entradas.
- [x] 1.2 Ejecutar `php artisan db:seed --class=SectorsSeeder` y verificar
      que las 19 slugs existen con name en es/en/ca y Active.
- [x] 1.3 (Opcional) Feature test: seeder idempotente + 19 slugs NACE
      presentes. Verificar pest.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 si aplica → 30 archive.
- E2E 40: no aplica.
- Limpieza slugs legacy: change futuro si se desea.
