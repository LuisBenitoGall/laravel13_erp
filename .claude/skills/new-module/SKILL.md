---
name: new-module
description: Ejecuta el playbook normativo (docs/standards/module-playbook.md) para arrancar un módulo o una entidad del ERP v2 de forma guiada, sin saltarse ninguna pieza ni vinculación. Usar cuando el usuario quiera crear/arrancar un módulo, añadir una entidad a un módulo existente, o pida "seguir la pauta". Args opcionales - nombre del módulo o "Dominio Entidad".
---

# Skill: new-module

Guía la creación de un módulo (o de una entidad suelta) aplicando el playbook. Eres el
garante de que **ninguna casilla se salta**. Documentos normativos:

- Pauta y checklists: `docs/standards/module-playbook.md` (LEER ENTERO antes de empezar)
- Reglas: `docs/standards/architecture.md` · Mapa: `docs/standards/module-map.md`
- Templates: `docs/standards/templates-backend.md`, `docs/standards/templates-frontend.md`
- Naming: `docs/standards/glossary.md` · Librerías: `docs/standards/libraries.md`

## Procedimiento

### Paso 1 — Contexto
1. Lee el playbook y el module-map. Determina si el encargo es un MÓDULO nuevo o una
   ENTIDAD dentro de un módulo existente (pregunta si es ambiguo).
2. Localiza la referencia v1 en `C:\laragon\www\laravel8_erp_viudavila_github` (modelos,
   controladores, reglas de negocio; consulta `info_modulos.txt` y `openspec/inventory/`
   del proyecto viejo). Resume qué hace v1 y qué se decide portar.
3. Verifica el naming de todas las entidades contra el glosario. Término nuevo → proponer
   traducción y AÑADIRLA a `glossary.md` antes de escribir código.

### Paso 2 — Ficha del módulo
Crea/actualiza `docs/modules/<módulo>.md` con TODAS las secciones del playbook §1,
incluida la **checklist de vinculaciones §6 respondida pregunta a pregunta (V-01…V-20)**,
cada una con sí/no y, si sí, qué evento/listener/test la cubre. Presenta la ficha al
usuario y espera su conformidad antes de seguir.

### Paso 3 — Propuesta OpenSpec
Invoca el skill `opsx:propose` usando la ficha como insumo. Las tasks deben calcar las
checklists §3–§7 del playbook (una sección de tasks por entidad: datos → backend →
frontend → tests) más las tareas de vinculación (una por cada "sí" de §6) y las de
registro del módulo (§8). Dependencias nuevas entre dominios → actualizar
`module-map.md` en la misma propuesta.

### Paso 4 — Implementación (cuando el usuario aplique el change)
Por cada entidad, en este orden y sin saltos:
1. Datos (playbook §3) — partir de los templates, no de memoria.
2. Backend (§4) — si existe `php artisan make:domain-entity`, usarlo; si no, crear cada
   artefacto siguiendo `templates-backend.md` a mano.
3. Frontend (§5) — reutilizar el catálogo de componentes (`frontend.md` §4); no crear
   duplicados.
4. Tests (§7) — el **test de aislamiento de tenant es innegociable** en cada entidad.

Al terminar cada sección, marca las casillas en las tasks del change (no marcar nada no
verificado).

### Paso 5 — Cierre
Verifica el Definition of Done (§9) punto por punto y repórtalo explícitamente:
suite/pint/phpstan verdes, recorrido manual del flujo principal, i18n completo, ficha y
module-map al día. Después sugiere `/opsx:archive`.

## Reglas del skill
- Si el usuario pide "saltarse algo", registra la excepción en la ficha del módulo y en el
  design del change (jerarquía normativa del README de standards); no la silencies.
- Si detectas que un estándar es erróneo/incompleto: detente, propón la corrección del
  estándar primero, y continúa cuando esté decidida.
- No marques completada una casilla que no hayas verificado con código/tests reales.
