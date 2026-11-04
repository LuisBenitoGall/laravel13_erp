Usa el agente @.cursor/agents/agent-20-implementation.md guiado por
docs/agents/20-implementation.md.

Change: c2026-11-04-admin-accounting-account-types
Origen: informe 25-architecture-review (**RETURN TO IMPLEMENTATION**).
Contrato: `openspec/changes/c2026-11-04-admin-accounting-account-types/`
(proposal, design D3, specs/admin-accounting-account-types-catalog, tasks).
Si este prompt y los artefactos chocan, mandan los artefactos.
No modifiques proposal.md, design.md ni los deltas de specs/. No archives.
No escribas tests/Browser/. No amplíes alcance (sin cascada `select`, sin
AccountingAccount, sin status).

## Problema (HIGH)

El seeder PGC resuelve nombres con `__('fiscal.*')` + `App::setLocale('es')`,
pero el port de `lang/*/fiscal.php` está incompleto.

- Claves usadas por `AccountingAccountTypesSeeder`: **563**
- Claves **ausentes** en `lang/es/fiscal.php`: **64**
- Efecto: al seedear, Laravel deja el key como valor → `ucfirst` → nombres en BD
  tipo `Fiscal.deudas_grupo`, `Fiscal.gastos_cobertura`, etc.
- Evidencia UI: Index y Show (`/admin/accounting-account-types` y `…/show`)
  muestran esos literales. Incumple design **D3** y el scenario «codes/names
  coherentes» del seeder.

El smoke actual (`count > 50`, `code=1`) **no detecta** este fallo.

## Qué hacer

### 1. Completar i18n `fiscal.*` (obligatorio)

Añadir las **64 claves faltantes** en:

- `lang/es/fiscal.php` (canónico)
- `lang/en/fiscal.php` y `lang/ca/fiscal.php` (traducir o, si el design admite
  fallback, al menos texto legible; no dejar la clave cruda)

Lista exacta de claves usadas por el seeder y ausentes en ES:

```
socios_aportaciones_no_dinerarias_pendientes_pendiente_inscripcion
inmovilizaciones_materiales_curso
deudas_grupo
deudas_asociadas
deudas_empresas_vinculadas
acreedores_grupo
acreedores_asociadas
acreedores_vinculadas
otros_tributos
gastos_activos_financieros
perdidas_disponibles_venta
transferencia_venta
gastos_cobertura
perdidas_efectivo
perdidas_extranjero
transferencia_efectivo
transferencia_extranjero
gastos_conversion
diferencias_negativas
transferencia_positivas
impuestos_beneficios
ajustes_negativos
ingresos_permanentes
ingresos_bonificaciones
transferencia_permanentes
transferencia_bonificaciones
ajustes_imposicion_beneficios
transferencias_legados
transferencia_capital
transferencia_donaciones
transferencia_legados
gastos_definida
perdidas_actuariales
ajustes_largo
gastos_venta
perdidas_activos_venta
transferencia_beneficios_venta
gastos_previos
deterioro_grupo
deterioro_asociadas
ingresos_activos_financieros
beneficios_financieros_venta
transferencia_activos_venta
ingresos_cobertura
beneficios_efectivo
beneficios_extranjero
transferencia_perdidas_efectivo
transferencia_perdidas_extranjero
ingresos_conversion
diferencias_positivas
transferencia_negativas
ingresos_legados
ingresos_de
ingresos_definida
ganancias_actuariales
ajustes_definida
ingresos_venta
beneficios_activos_venta
transferencia_grupos_venta
ingresos_grupo_previos
recuperacion_grupo
recuperacion_asociadas
transferencia_previos_grupo
transferencia_previos_asociadas
```

Fuente de verdad de textos: v1 `lang/*/fiscal.php` en
`C:\laragon\www\laravel8_erp_viudavila` (skill `v1-reference`). Si una clave del
seeder v2 no existe en v1, alinear el **nombre de clave del seeder** con la de
v1 (preferible) o añadir la traducción correcta — no inventar semántica PGC.

Revisa también claves del seeder con caracteres no ASCII en el key (p. ej.
`…cartera_negociación`): deben coincidir exactamente con la entrada del array
fiscal o renombrarse en seeder + lang al unísono.

### 2. Corrección menor de valor ya presente (LOW, mismo pase)

En es/en/ca, la clave
`socios_aportaciones_no_dinerarias_pendientes_capital_social` tiene el valor
`…, capital_social` (underscore). Sustituir por texto legible
(`…, capital social`), alineado a v1 si existe.

### 3. Re-seed y verificación

- Tras completar claves: `php artisan db:seed --class=AccountingAccountTypesSeeder`
  en local (o el flujo de test).
- Comprobar que **cero** filas tienen `name` like `Fiscal.%` / `fiscal.%`.
- Smoke UI: Index + Show sin literales `Fiscal.`.

### 4. Endurecer test (obligatorio)

En `tests/Feature/Admin/AccountingAccountTypeTest.php`, tras el seed:

- Assert: ningún `AccountingAccountType` (no trashed) con `name` que empiece por
  `Fiscal.` o `fiscal.`.
- Mantener asserts existentes (`code=1`, count > 50, idempotencia).

Opcional útil: assert de que el número de claves `__('fiscal…')` del seeder ⊆
keys de `lang/es/fiscal.php` (unit/arch o feature de seed).

### 5. Doc menor (LOW, si tocas README)

README § Seeders dice Truncate; el seeder hace `delete()` + reset AI. Alinear la
frase al comportamiento real (sin cambiar la lógica del seeder salvo que
prefieras truncate real en MySQL).

## No tocar

- Migración / esquema / sin status.
- Create raíz `autoreference=0`; Update solo `name`; bloqueo delete con hijos.
- Rutas, menú, Show árbol desde BD, contrato Index (code/name/level).
- No abrir dominio Accounting ni endpoint `select`.

## Gate

- Suite feature accounting-account-types (+ menú si lo tocas) verde.
- `vendor/bin/pint --dirty --format agent` y `vendor/bin/phpstan analyse` si hay
  PHP tocado.
- Re-seed local sin nombres `Fiscal.*`.
- No archives; devolver a **25-architecture-review** para re-auditoría.
