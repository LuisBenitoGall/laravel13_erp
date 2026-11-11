# Design

## Context

See proposal.md — Why. Tras `user-companies-tab` + strip HIGH
(correcciones 20): tab Empresas = ServerTable RO + detach + sesión;
**sin** update. El write path pre-strip existió en working tree (nunca
committed el strip) y es la referencia de restauración: Action
`UpdateUserCompany`, `UpdateUserCompanyRequest`, `UserCompanyPolicy::update`,
`updateForProfile` / `updateForCompanyUser`, rutas
`profile.companies.update` / `admin.company-users.companies.update`, modal
+ Pencil + `updateUrl` en `UserCompaniesPanel`, test Feature update, i18n
`editar_vinculo_empresa` / `vinculo_empresa_actualizado`.

## Goals / Non-Goals

**Goals:** autorizar y restaurar exactamente ese write path de
position/department; misma UX; mismas superficies; tests/docs/i18n.

**Non-Goals:** amend del change archivado; alta de vínculos; cambiar
`company_id`; Empleo; redisenar el panel; tocar emails/phones/images/docs;
cambiar detach/sesión.

**E2E:** no aplica (`e2e.md` §4).

**Excepciones a standards:** ninguna.

## Decisions

1. **Change nuevo, no amend**  
   Brief acordado: no reabrir/legalizar vía amend de
   `user-companies-tab`; delta sobre capability
   `user-companies-membership`.

2. **Restaurar, no reinventar**  
   Misma superficie: fila Pencil → Dialog con `position` + `department`
   (nullable strings, maxlength alineado a columnas varchar) → PUT/PATCH
   Inertia a rutas update profile/admin. Action actualiza solo esos dos
   campos en la fila `UserCompany`.  
   *Alt. descartada:* inline edit en Tabulator o página aparte (brief:
   mismo modal retirado).

3. **Authz**  
   `UserCompanyPolicy::update` = misma regla que `delete` /
   `canManageOwner` (self o `updateCompanyUser` sobre owner). Sin permiso
   Spatie nuevo.

4. **Controller / rutas**  
   Reañadir `updateForProfile` / `updateForCompanyUser` en
   `UserCompanyController`; nombres de ruta como pre-strip. `abort_unless`
   perímetro donde ya aplique el patrón de destroy.

5. **Front**  
   `updateUrl: (id) => string` prop otra vez; `Users/Edit` la pasa en
   ambos contextos. Quitar/ajustar el Feature que hoy exige **ausencia**
   de `updateUrl`/Dialog/`editar_vinculo_empresa`.

6. **Fuente de código**  
   Preferir transcript apply / historial IDE citados en el brief; si no
   está disponible, reimplementar 1:1 según este design (mismos nombres
   de clases/claves i18n).

7. **V-01..V-20**  
   Aplica: Policy update, Action, FormRequest, rutas, i18n, test Feature
   update + perímetro. No: migración nueva (columnas ya existen), menú,
   Module nuevo, E2E, BelongsToCompany en UserCompany.

## Risks / Trade-offs

- [divergencia vs transcript] → priorizar nombres/UX del brief; tests
  cierran contrato.  
- [confusion Empleo] → NonGoal explícito; solo metadatos del vínculo.

## Migration Plan

1. Backend update path.  
2. UI modal + props.  
3. Tests/i18n/Admin.md.  
Rollback: volver a strip (no deseable tras merge).

## Open Questions

Ninguna.
