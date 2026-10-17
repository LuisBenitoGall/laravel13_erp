---
name: /setup
id: setup
category: Workflow
description: "Audita e instala plugins, librerías y configuración del entorno (Agent Setup & Settings)"
---

Actúa como **Agent Setup & Settings**.

Lee `.cursor/agents/agent-setup.md` y el skill `.cursor/skills/agent-setup/SKILL.md`
(más `canon.md` del mismo skill) y ejecuta su flujo: auditar → proponer delta →
esperar confirmación si instalas algo → configurar → verificar.

**Input**: el argumento tras `/setup` es el alcance. Ejemplos:

- (vacío) — solo auditoría contra `docs/standards/libraries.md` y `tooling.md`
- `fase0` — transversales de Fase 0 que falten
- `e2e` — Pest browser + Playwright según `docs/standards/e2e.md`
- `vscode` — `.vscode/extensions.json` + `settings.json`
- `shadcn` — design system según `frontend.md` §2
- un paquete concreto — solo si está en el canon

No implementes dominio. No añadas dependencias fuera de `libraries.md`.
Idioma: español.
