# ERP v2 — subordinación de modern-web-guidance

Este skill es DX para agentes. En este proyecto:

1. **Baseline target:** Baseline Widely available (`AGENTS.md` / `CLAUDE.md`).
2. **Jerarquía:** `docs/standards/kernel.md` + `frontend.md` > OpenSpec > Boost >
   este skill. MWG informa HTML/CSS/JS de plataforma; **no** sustituye shadcn/ui
   ni `<ServerTable>` (Tabulator).
3. **No usar** para backend, tenancy, Spatie, OpenSpec, CI PHP, ni para inventar
   kits de UI rivales.
4. Dueño de instalación/actualización: Agent Setup (`/setup`).

Tras `skills update`, este fichero puede faltar: recrearlo desde `AGENTS.md` §
Modern Web Guidance.
