# Design

## Context

Brief: `docs/prompts/c2026-11-24-empresas-workplace-delegation-map.md`.
Delegaciones CRUD archivado: Company hija, sesión = matriz, workplaces de
alta en la hija. Workplace de menú Centros = `BelongsToCompany` de sesión
(matriz). Oleada 3 (pedidos) usará este mapa como default.

E2E §4: **N/A**.

## Goals / Non-Goals

**Goals:** N:M matriz-workplace ↔ delegaciones; preferida opcional; UI Edit
Workplace; tests.

**NonGoals:** Pedidos/albaranes/facturas; listados grupo; switcher; fusionar
entidades; mapeo desde workplaces cuyo `company_id` es la propia delegación
(oleada 1 crea sede “legal” en la hija — fuera de este mapa operativo de
matriz).

## Decisions

1. **Alcance de Workplace**  
   Solo workplaces con `company_id` = CurrentCompany (matriz). Las sedes
   creadas *dentro* de una Delegación no entran en este mapeo en oleada 2
   (son domicilio de esa society). Alternativa descartada: mapear también
   sedes-hija — confunde operativo vs legal y el Index workplaces no las
   lista en sesión matriz.

2. **Pivote**  
   Tabla p. ej. `workplace_delegation` / `delegation_workplace`:
   `workplace_id`, `company_id` (FK companies = delegación),
   `is_preferred` bool default false, unique (`workplace_id`,`company_id`),
   índices. Validar en Action: `Company.parent_id === workplace.company_id`
   y `workplace.company_id === CurrentCompany`.

3. **Preferida**  
   Al marcar una, desmarcar otras del mismo workplace (transacción). Cero
   preferidas = válido. No hay preferida global por delegación.

4. **UI**  
   En Edit Workplace (y opcionalmente Create si ya existe company): lista
   multi-select de delegaciones hijas Active (o todas no soft-deleted) +
   control “Preferida” (radio/select entre las seleccionadas). Sync replace
   del set de IDs en store/update.

5. **Lectura inversa**  
   Relation `Company::workplacesViaMap()` / `Workplace::delegations()` para
   futuros docs; en oleada 2 basta exponer en Edit. Panel en Edit Delegación
   “sedes que me usan” = nice-to-have; **no obligatorio** si el Edit
   Workplace cubre el write path (task opcional).

6. **Permisos**  
   Reutilizar `workplaces.update` (y create si se mapea al crear). No hace
   falta Functionality nueva salvo que 20 prefiera habilidad dedicada —
   preferencia: sin Functionality nueva (menos menú).

7. **Tests**  
   Sync N:M; rechazo delegación de otra matriz; preferida única; workplace
   de otra company 403.

## Risks / Trade-offs

- [Sedes solo en delegación sin mapa desde matriz] → Mitigation: oleada 1 ya
  exige 1 workplace en la hija; el operativo del grupo vive en sedes de
  matriz. Documentado.
- [Nombre de tabla] → 20 elige kebab glosario (`workplace_delegation`).

## Migration Plan

1. Migration pivote. 2. Relations + Sync action. 3. UI Edit. 4. Tests.

## Open Questions

Ninguna bloqueante (panel inverso en Delegación = opcional en tasks).
