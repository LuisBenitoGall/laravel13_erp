# Tasks

## 1. Datos + naming

- [x] 1.1 Migración `workplaces` (company_id, name, slug, address, cp,
      town_id, featured, status string, soft deletes, audit). Verificar
      `migrate`.
- [x] 1.2 Modelo `Workplace` + `BelongsToCompany` + enum status + factory
      + relations town/company. Verificar factory/unit.
- [x] 1.3 Glosario: Centro de trabajo → `Workplace` (≠ Worksite/Workspace).
      Verificar `glossary.md`.
- [x] 1.4 `module-map.md` + fila `docs/modules/Admin.md`. Verificar docs.

## 2. Authz + menú (V-07 / V-08)

- [x] 2.1 Seed/Realign: Functionality `workplaces` bajo `empresas`;
      permisos canónicos; `.view` a roles básicos. Verificar BD/seed.
- [x] 2.2 MenuBuilder: Centros de trabajo → `/admin/workplaces`. Verificar
      menú Feature.
- [x] 2.3 Policy + Gate registro. Verificar 403 sin permiso.

## 3. Backend CRUD

- [x] 3.1 Actions Create/Update/Delete (+ lógica featured único / primer
      featured). Verificar Feature.
- [x] 3.2 FormRequests (tenant exists town; name required; cp max).
      Verificar validación Feature.
- [x] 3.3 Controller + rutas `/admin/workplaces` (+ table, status si
      aplica). Verificar `route:list` + Feature table.
- [x] 3.4 R-TEN-05: aislamiento cruzado company A/B. Verificar Feature.

## 4. Frontend

- [x] 4.1 Index `<ServerTable>` (name, town/cp, featured, status,
      Acciones). Verificar UI.
- [x] 4.2 Create/Edit: cascada país→provincia→town + address/cp +
      featured + status; sin campos NonGoal. Verificar UI.
- [x] 4.3 i18n es/en/ca. Verificar keys.
- [x] 4.4 Pint + PHPStan + Features workplaces en verde.

## Workflow follow-up

- Revisión Luis → `/opsx-apply` → 25 → 30 archive.
- E2E 40: no aplica.
- Address polimórfica / user_workplaces / campos v1 extra: changes futuros.

## Correcciones post-apply (UI + contrato)

- [x] Form: Dirección 75% + CP 25%; Estado 25% + Switch Principal.
- [x] UI `featured` → label Principal; design/spec/glossary actualizados
      (default facturación/pedidos = semántica; consumo = change futuro).
- [x] Default Switch Principal Create: true sin Active; false si ≥1 Active.
- [x] FormActions derecha (R-FE-09) + Volver Create/Edit → Index workplaces.
