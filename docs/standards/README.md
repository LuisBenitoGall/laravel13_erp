# Estándares de desarrollo — índice normativo

Esta carpeta es la **base normativa** del ERP v2: toda pieza de código del proyecto debe
cumplirla. Actúa como guardrail para cualquier desarrollo, humano o asistido por IA.

## Documentos

| Documento | Qué norma |
|---|---|
| [architecture.md](architecture.md) | La constitución backend: estructura de dominios, tenancy, autorización, capas, BD, ledger, naming, anti-patrones prohibidos. Reglas `R-*` citables |
| [module-playbook.md](module-playbook.md) | **La pauta de trabajo**: proceso y checklists para construir cada módulo/entidad sin olvidar ninguna pieza ni vinculación. Definition of Done |
| [module-map.md](module-map.md) | Mapa de los 14 dominios, matriz de dependencias permitidas y cadenas de eventos canónicas. Documento vivo |
| [libraries.md](libraries.md) | Canon cerrado de librerías backend/frontend y política de versiones |
| [frontend.md](frontend.md) | Design system, contrato `<ServerTable>` (Tabulator), catálogo de componentes genéricos, 4 patrones de página |
| [templates-backend.md](templates-backend.md) | Código canónico comentado: migración, modelo, enum, factory, policy, requests, action, controller, resource, rutas, tests |
| [templates-frontend.md](templates-frontend.md) | Código canónico React/TS: tipos, Index con ServerTable, formulario RHF, patrón documento |
| [glossary.md](glossary.md) | Glosario ES↔EN normativo (Obra→Worksite, Efecto→Effect…) con equivalencias v1 |
| [e2e.md](e2e.md) | Estándar de tests de navegador: Pest 4 browser testing, qué flujos se cubren, convenciones de selectores y datos |
| [tooling.md](tooling.md) | Guardrails mecánicos: generador `make:domain-entity`, tests de arquitectura, calidad estática, CI |

La **metodología de agentes** (roles 10-architecture / 20-implementation / 30-testing,
transversal a Claude/Cursor/Codex) vive en [../agents/](../agents/README.md).

## Jerarquía normativa

1. **architecture.md** manda sobre todo lo demás.
2. Un design de OpenSpec puede declarar una excepción justificada ("Excepciones a
   standards"); sin esa sección explícita, los estándares prevalecen.
3. Si un estándar resulta erróneo o incompleto: se corrige el estándar primero (PR propio)
   y después se continúa el desarrollo que lo destapó.
4. Estos documentos evolucionan por PR como el código; [module-map.md](module-map.md) y el
   catálogo de componentes se actualizan en el mismo PR que los altera.

## Cómo se usa (flujo de un módulo)

```
/new-module  →  ficha docs/modules/<módulo>.md (vinculaciones §6 del playbook)
             →  /opsx:propose (propuesta OpenSpec conforme a estándares)
             →  make:domain-entity por cada entidad (esqueletos conformes)
             →  desarrollo siguiendo playbook §3–§7 (checklists)
             →  DoD §9 + /opsx:archive
```

Contexto de negocio y decisiones de producto: [../vision-v2.md](../vision-v2.md).
Fichas por módulo (se crean al arrancar cada módulo): `docs/modules/`.
