# Tasks

## 1. Registro normativo

- [x] 1.1 Glosario: distinguir Delegación preferida (por sede, `is_preferred`)
      vs Sede preferida (por Delegación, `is_preferred_seat`)
- [x] 1.2 Actualizar `docs/modules/Admin.md` (Edit Delegación → ServerTable +
      B2; V-07/V-08 no; R-TEN-05 perímetro; E2E N/A)

## 2. Datos — pivote

- [x] 2.1 Migración: `workplace_delegation.is_preferred_seat` bool default
      false + índice `(company_id, is_preferred_seat)`; migrate OK
- [x] 2.2 Relación/pivot Eloquent expone `is_preferred_seat`; no cambiar
      semántica de `is_preferred`

## 3. Backend

- [x] 3.1 Action `SetDelegationPreferredWorkplace` (transacción; ≤1 seat;
      null limpia; valida vínculo; no toca `is_preferred`)
- [x] 3.2 FormRequest + `PUT .../delegations/{delegation}/preferred-workplace`
- [x] 3.3 `GET .../delegations/{delegation}/workplaces/table` (join geo;
      name, cp, town, province, country, created_at, is_preferred_seat;
      authz + perímetro)
- [x] 3.4 Feature Pest: table columnas; set/clear seat; ≤1; workplace no
      vinculado 422/403; perímetro; `is_preferred` intacto tras set seat

## 4. Frontend + i18n

- [x] 4.1 Tab Centros: `<ServerTable>` + columnas acordadas; toggle/acción
      sede preferida; Edit row → Workplace Edit; empty state + copy ayuda
      (sin CTA botón; decisión producto)
- [x] 4.2 Eliminar lista HTML `MatrixWorkplacesLinkedPanel` (o dejar de
      usarla); quitar prop `linked_workplaces` del Edit si ya no aplica
- [x] 4.3 i18n es/en/ca: `sede_preferida` (≠ `delegacion_preferida`);
      columnas/ayuda

## 5. Calidad

- [x] 5.1 Pint (`--dirty`) + PHPStan en PHP tocado; features del change en verde

## Workflow follow-up

- Revisión Luis → `/opsx-apply` (20) → 25 → 30 archive (sin 40; E2E N/A).
