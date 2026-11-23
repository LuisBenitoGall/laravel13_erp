# ERP v2 (SaaS) — instrucciones para agentes

> Puente multi-herramienta. **No duplica** normativa ni guidelines Boost.
> Canon: `CLAUDE.md` (proyecto + bloque Boost) · Cursor: `.cursor/rules/` ·
> Standards: `docs/standards/` · Roles: `docs/agents/`.

## Lecturas (on-demand, en este orden)

1. `CLAUDE.md` — resumen + **única** copia de `<laravel-boost-guidelines>`.
2. `docs/standards/kernel.md` — invariantes (si tocas tenancy/RBAC/menú).
3. `docs/agents/README.md` — roles y brief explore→10.
4. Solo lo del change: `openspec/changes/<change>/` + specs citadas (no el
   catálogo entero de `openspec/specs/`).

## Roles

| Si vas a… | Ficha | Comando |
|---|---|---|
| Proponer / diseñar | `docs/agents/10-architecture.md` | `/opsx-propose` |
| Implementar tasks | `docs/agents/20-implementation.md` | `/opsx-apply` |
| Auditar apply (solo lectura) | `docs/agents/25-architecture-review.md` | — |
| E2E Pest browser | `docs/agents/40-e2e.md` | — |
| DoD + archive | `docs/agents/30-testing.md` | `/opsx-archive` |
| Toolchain / MCP / deps | `docs/agents/agent-setup.md` | `/setup` |

Explore no implementa ni ejecuta OpenSpec. Flujo: debate → brief
`docs/prompts/<Change>.md` → **sesión nueva** 10-architecture.

## Anti-patrones de contexto (coste)

- No pegar ni “leer todas” las specs / standards en un hilo.
- No re-inyectar aquí el bloque Boost: si `boost:update` lo vuelve a escribir
  en este fichero, **bórralo** y deja solo el de `CLAUDE.md`.
- Skills y CodeGraph on-demand; always-on Cursor = `00-project` + `01-kernel` +
  `laravel-boost` + `codegraph`.
