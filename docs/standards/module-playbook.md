# Playbook de módulo — pauta de trabajo y checklists

> Documento normativo. Es la **pauta obligatoria** para construir cualquier módulo o entidad.
> El skill `/new-module` de Claude Code ejecuta este playbook de forma guiada.
> Reglas de arquitectura: [architecture.md](architecture.md) · Mapa de dominios:
> [module-map.md](module-map.md) · Templates: [templates-backend.md](templates-backend.md) y
> [templates-frontend.md](templates-frontend.md).

## 0. Principio general

Un módulo NUNCA se empieza "escribiendo código". El orden es siempre:

```
Ficha del módulo → Propuesta OpenSpec → Datos → Backend por entidad →
Frontend por entidad → Vinculaciones → Registro del módulo → Verificación (DoD)
```

Cada paso tiene su checklist. **No se marca un paso sin completar el anterior**, salvo
iteración consciente documentada en la propuesta.

---

## Matriz de artefactos por entidad (referencia rápida)

Vista de conjunto de "qué archivo, cuándo" — el detalle de cada fila (reglas `R-*`,
templates) vive en §3–§5. Es la base de qué genera el comando `make:domain-entity`
([tooling.md](tooling.md) §1) y de qué debe seguir comprobando el backstop de detección
de huecos ([tooling.md](tooling.md) §2).

| Artefacto | ¿Cuándo? |
|---|---|
| Migration | Siempre |
| Model + traits (`BelongsToCompany`, `Auditable`, `SoftDeletes`) | Siempre |
| Factory | Siempre (la usan todos los tests) |
| Enum de estado + `canTransitionTo()` | Solo si la entidad tiene ciclo de vida |
| Seeder (catálogo/datos base) | Solo si necesita datos semilla |
| Policy | Siempre |
| `Functionality` (entrada de menú + 5 permisos) | Siempre que sea navegable — la registra el generador (auto-provisión, [tooling.md](tooling.md) §1), no un `PermissionSeeder` a mano |
| FormRequests `Store*`/`Update*` | Siempre que haya escritura |
| `TableRequest` (contrato `*/table`) | Siempre que haya listado |
| Actions (CRUD + ciclo de vida) | Siempre |
| Eventos de dominio + Listeners | Solo si la checklist de vinculaciones (§6, V-01..V-20) responde "sí" — no es generable, es negocio |
| Resource(s) | Siempre |
| Controller | Siempre |
| Entrada en `routes/domains/<dominio>.php` | Siempre |
| Jobs/Notifications | Solo si hay envío asíncrono |
| PDF/Excel | Solo si el documento lo exige |
| Activity log | Si es documento/maestro relevante |
| Tipos TS (`.d.ts`) | Siempre |
| Página React Index (`ServerTable`) | Siempre que haya listado |
| Páginas React Create/Edit | Siempre que haya escritura |
| Página React Show | Solo si Edit no basta como detalle |
| Claves i18n es/en | Siempre |
| Tests CRUD + auth + aislamiento de tenant | Siempre |
| Test por vinculación | Uno por cada "sí" de §6 (V-01..V-20) |

No hay vistas Blade por módulo: el único Blade de todo el proyecto es la vista raíz del
shell de Inertia (`resources/views/app.blade.php`); todo lo demás son páginas React. Los
middleware de autorización ad-hoc por módulo no figuran en esta matriz porque están
prohibidos (R-AUT-04): lo único que un módulo aplica es el middleware ya existente
`EnsureModuleIsActive:<módulo>` (R-AUT-05), nunca uno propio nuevo.

La fila `Migration`/`Model` de esta matriz asume `company_id` (R-TEN-01). La única
excepción hoy documentada son las entidades de catálogo global `Module`/`Functionality`
del dominio Admin (R-AUT-07) — sin `company_id`, con su propia matriz de permisos/menú.

---

## 1. Ficha del módulo (antes de proponer)

Se crea `docs/modules/<módulo>.md` con:

- [ ] **Propósito** y alcance (qué entra y qué NO entra en esta iteración).
- [ ] **Entidades** que introduce (con su dominio de `app/Domains/`).
- [ ] **Dependencias**: qué dominios necesita ya construidos (consultar
      [module-map.md](module-map.md); si crea una dependencia nueva, actualizar el mapa).
- [ ] **Checklist de vinculaciones** (§6) respondida pregunta a pregunta.
- [ ] **Librerías** que necesita más allá del canon ([libraries.md](libraries.md)); si
      requiere una librería nueva, justificarla y añadirla al canon en el mismo PR.
- [ ] **Permisos** que declara (`<módulo>.<acción>`) y a qué roles seed se asignan.
- [ ] **Referencia v1**: modelos/controladores equivalentes del ERP viejo
      (`C:\laragon\www\laravel8_erp_viudavila_github`) y reglas de negocio a portar
      (consultar `info_modulos.txt` y `openspec/inventory/` del proyecto viejo).
- [ ] **Migración de datos v1→v2**: qué tablas de v1 alimentan este módulo (o "no aplica").

## 2. Propuesta OpenSpec

- [ ] Crear la propuesta con `/opsx:propose` (proposal + specs delta + design si hay
      decisiones + tasks). La ficha del módulo es su insumo.
- [ ] Las tasks de la propuesta se estructuran siguiendo las checklists §3–§7 de este playbook.
- [ ] Decisiones que contradigan los estándares → sección explícita "Excepciones a standards"
      en el design (si no la hay, los estándares mandan).

## 3. Capa de datos (por entidad)

Orden: primero TODAS las entidades del módulo a nivel de datos, luego backend. Así afloran
los problemas de modelado antes de escribir lógica.

- [ ] **Migración** según template: `company_id` + índices compuestos `(company_id, ...)`
      (R-TEN-01), decimales correctos (R-BD-02), soft deletes + `created_by`/`updated_by`
      (R-BD-03), FKs con índice y `onDelete` explícito (R-BD-06).
- [ ] **Modelo** en `app/Domains/<Dominio>/Models/`: `BelongsToCompany`, `SoftDeletes`,
      `Auditable`, `$fillable`, casts (decimales, enums, JSON), relaciones, scopes nombrados.
- [ ] **Enums de estado** con `canTransitionTo()` si la entidad tiene ciclo de vida (R-CAP-06).
- [ ] **Factory** realista (estados variados, `for(company)`) — obligatoria, la usan todos
      los tests.
- [ ] **Seeder** si la entidad necesita datos base (tipos, catálogos) + registro en
      `DatabaseSeeder`.
- [ ] Si la entidad es traducible: columnas JSON + `HasTranslations` (R-BD-08).
- [ ] Revisar contra el esquema v1 equivalente: ¿falta alguna columna con semántica de
      negocio? (los `*_old_num`, flags, etc. se decide conscientemente si se portan).

## 4. Backend (por entidad)

- [ ] **Policy** (template): tenancy + permiso Spatie por habilidad; registrar si no hay
      auto-discovery. Habilidades extra del ciclo de vida (`confirm`, `invoice`...) incluidas.
- [ ] **Permisos** del módulo añadidos a su `PermissionSeeder` (R-AUT-02) — nunca sueltos.
- [ ] **FormRequests** `Store*`/`Update*`: `authorize()` → Policy; reglas tenant-safe
      (R-TEN-07); mensajes traducidos.
- [ ] **Actions**: una por caso de uso mutador, transaccional, dispara eventos (R-CAP-02).
      CRUD básico: `Create<X>`, `Update<X>`, `Delete<X>`; más las de ciclo de vida.
- [ ] **Numeración**: si el documento se numera, SOLO vía `NumberingService` dentro de la
      transacción (R-BD-05).
- [ ] **Eventos de dominio** por cada hecho relevante para otros dominios (R-DOM-04) +
      registrar **listeners** de este módulo a eventos ajenos.
- [ ] **TableRequest + endpoint de tabla** para cada listado (contrato Tabulator, ver
      [frontend.md](frontend.md) §3): filtros declarados, sort permitido, export.
- [ ] **Resource(s)**: props de Index (fila de tabla), props de Edit/Show (detalle). Nunca
      modelos crudos (R-CAP-05).
- [ ] **Controller** delgado (R-CAP-01): `index/create/store/show/edit/update/destroy` +
      `table` + acciones de ciclo de vida (una por verbo).
- [ ] **Rutas** en `routes/domains/<dominio>.php`: nombre `<módulo>.<entidad>.<acción>`,
      middleware `auth` + `EnsureModuleIsActive:<módulo>` (R-AUT-05).
- [ ] **Jobs/Notifications** si aplica: `company_id` explícito (R-TEN-04); envíos por cola
      (R-TRV-01).
- [ ] **PDF/Excel** si aplica: plantilla + Action de generación por cola; numeración e
      importes con helpers de `Support` (importe en letras, formato es-ES).
- [ ] **Activity log** en la entidad si es documento/maestro relevante (R-TRV-02).

## 5. Frontend (por entidad)

- [ ] **Tipos TS** en `resources/js/types/<dominio>.d.ts`, espejo del Resource.
- [ ] **Página Index**: `ServerTable` (Tabulator) + filtros + botón crear + acciones de fila
      según permisos (patrón en [frontend.md](frontend.md) §5.1).
- [ ] **Páginas Create/Edit**: `RecordEditLayout` + react-hook-form + zod **espejo del
      FormRequest** (misma regla, mismo mensaje) + tabs si el registro es complejo.
- [ ] **Show** solo si el negocio lo pide (por defecto Edit hace de detalle).
- [ ] **Componentes específicos** del módulo en `Pages/<Dominio>/<Entidad>/components/`;
      si un componente se reutiliza entre dominios, se promociona a `Components/` y se
      documenta en el catálogo ([frontend.md](frontend.md) §4).
- [ ] **i18n**: todas las cadenas con clave (es + en mínimo); cero literales (R-TRV-05).
- [ ] **Permisos en UI**: `usePermissions()` para ocultar acciones no permitidas (la Policy
      sigue siendo la barrera real).
- [ ] **Entrada de menú** en el árbol data-driven con permiso + módulo requerido.
- [ ] **Formato**: importes/fechas SIEMPRE con `lib/format` (es-ES, decimales por contexto
      3/4/2), nunca `toFixed` suelto.

## 6. Checklist de vinculaciones (la parte que no se puede olvidar)

Responder **todas** las preguntas en la ficha del módulo. Cada "sí" genera tareas concretas
(evento, listener, test de integración) que deben aparecer en las tasks de OpenSpec:

| # | Pregunta | Si la respuesta es sí… |
|---|---|---|
| V-01 | ¿Mueve o reserva **stock**? | Evento → listeners de Inventory; probar kardex y consolidado |
| V-02 | ¿Genera **asientos contables**? | Evento → LedgerGenerator (R-LED-01/02); test de cuadre |
| V-03 | ¿Genera o consume **efectos/vencimientos**? | Integración con Treasury (generateForInvoice, estados) |
| V-04 | ¿Usa **numeración/series** de documento? | Patrón por empresa + `NumberingService` + test concurrencia |
| V-05 | ¿Emite **PDF/Excel**? | Plantilla, branding por tenant, cola, adjuntos |
| V-06 | ¿Dispara **notificaciones/realtime**? | Notification + canal Reverb + preferencia del usuario |
| V-07 | ¿Introduce **permisos o roles** nuevos? | PermissionSeeder + matriz rol→permiso en la ficha |
| V-08 | ¿Añade **entradas de menú**? | Árbol data-driven + permiso + módulo |
| V-09 | ¿Es un **módulo activable** (plan SaaS)? | Alta en catálogo de módulos + `EnsureModuleIsActive` |
| V-10 | ¿Depende de **precios/tarifas**? | Resolución de tarifa (empresa/obra) vía Action de Catalog |
| V-11 | ¿Se vincula a **obras** (worksites)? | FK + filtros por obra + tarifa de obra si procede |
| V-12 | ¿Usa **datos traducibles**? | Columnas JSON translatable + TabsIdiomas en el form |
| V-13 | ¿Afecta a **fiscalidad** (IVA, SII/VERI*FACTU)? | Revisión dominio Tax; campos SII desde el diseño |
| V-14 | ¿Interviene en el **portal cliente**? | Policies de rol cliente + páginas Portal + notificación comercial |
| V-15 | ¿Necesita **migración de datos v1**? | Comando idempotente + post-checks (filosofía pipeline legacy) |
| V-16 | ¿Tareas **programadas** (cron)? | Scheduler multi-tenant (R-TRV-04) |
| V-17 | ¿Procesos en **cola**? | company_id explícito (R-TEN-04) + reintentos/fallos definidos |
| V-18 | ¿Toca el **onboarding** de un tenant nuevo? | Actualizar asistente/seeds de onboarding |
| V-19 | ¿Expone **API externa** (Sanctum)? | Versionado, tokens, rate limit, docs |
| V-20 | ¿Auditoría **activity log**? | Configurar logAttributes + pantalla de historial si procede |

## 7. Tests (por entidad — mínimos innegociables)

- [ ] **Feature CRUD**: index/store/update/destroy felices + validación fallida.
- [ ] **Feature autorización**: sin permiso → 403 en cada ruta.
- [ ] **Feature aislamiento de tenant** (R-TEN-05): usuario de empresa A no ve/edita/borra
      datos de empresa B. **Obligatorio siempre**.
- [ ] **Unit de Actions** con lógica (cálculos de línea, transiciones de estado, numeración).
- [ ] **Unit de enums**: transiciones válidas e inválidas.
- [ ] **Integración de vinculaciones**: un test por cada "sí" de §6 (ej.: confirmar pedido
      reserva stock; registrar factura crea asiento cuadrado y efectos).
- [ ] Los **tests de arquitectura** globales pasan ([tooling.md](tooling.md)).

## 8. Registro del módulo (una vez por módulo)

- [ ] Alta en el catálogo de módulos (`Module` seed) si es activable (V-09).
- [ ] `PermissionSeeder` del módulo ejecutado en el seed global.
- [ ] Entradas de menú añadidas al árbol.
- [ ] Ficha `docs/modules/<módulo>.md` actualizada a "como quedó" (no "como se planeó").
- [ ] [module-map.md](module-map.md) actualizado si cambiaron dependencias/eventos.
- [ ] Specs OpenSpec sincronizadas (`/opsx:sync` o archivado del change).

## 9. Definition of Done del módulo

Un módulo está terminado cuando:

1. Todas las checklists §3–§8 completas (el skill `/new-module` las verifica).
2. `composer test` verde, incluidos aislamiento de tenant y vinculaciones.
3. PHPStan y Pint verdes.
4. Recorrido manual del flujo principal en navegador (crear → editar → listar → borrar y
   el ciclo de vida completo del documento si lo hay).
5. i18n sin claves faltantes en es/en.
6. Ficha del módulo y module-map al día.
7. Change de OpenSpec archivado (`/opsx:archive`).

---

## Apéndice: orden recomendado dentro de un módulo

1. Entidades maestras simples primero (catálogos del módulo).
2. Después la entidad principal (documento) con su ciclo de vida.
3. Vinculaciones al final, cada una con su test de integración.
4. Migración de datos v1 como último paso, cuando el esquema esté estable.
