# ERP v2 — Análisis del sistema anterior y proyección de la nueva versión

> Documento generado el 2026-07-13 a partir del análisis exhaustivo de
> `C:\laragon\www\laravel8_erp_viudavila_github` (ERP Viuda Vila, Laravel 9 + Blade/Livewire).
> Sirve como base de conocimiento y blueprint para esta nueva versión
> (Laravel 12+ / Inertia / React / MySQL).

---

## 1. Qué es el sistema anterior (v1)

ERP completo para **Viuda Vila** (distribución/suministros de construcción, España).
Producción: https://admin.viudavila.com/

**Escala real del sistema:**

| Métrica | Valor |
|---|---|
| Modelos Eloquent | ~232 |
| Migraciones | 453 |
| Controladores | 238 (`OrderController` 5.364 líneas, `InvoiceController` 4.483) |
| Rutas con nombre | ~1.249 (web) + ~150 endpoints DataTables (api) |
| Vistas Blade | ~905 |
| Componentes Livewire | 25 (`OrderComponent` ~1.917 líneas) |
| Middleware | 108 (~50 pares `*Get`/`*Post` por módulo) |
| Listados DataTables server-side | ~123 |

**Stack v1:** Laravel 9, PHP 8.2, Blade + Livewire 2 + jQuery/Bootstrap 4 (tema INSPINIA),
yajra/laravel-datatables, Spatie Permission, dompdf, maatwebsite/excel, Pusher (chat),
darryldecode/cart (tienda). MySQL con strict mode desactivado.

---

## 2. Dominios funcionales

### 2.1 Ventas (núcleo)
- **Presupuestos → Pedidos → Albaranes → Facturas**, con estados, versiones (`OrderVersion` +
  `ItemRevision` con auditoría vía `ItemObserver`), borradores, pedidos parciales y agrupaciones.
- Modelo `Order` **unificado**: un solo modelo con flags `is_budget` / `is_order` / `is_group` /
  `draft` / `from_shop` / `direct_fab` y **triple numeración** (`budget_num`, `order_num`,
  `purchase_num`, cada una con `*_num_cardinal`, `*_pattern_id`, `*_old_num`).
- Líneas (`Item`): hasta 4 descuentos en cascada, ítems porcentuales, alquiler por jornadas,
  doble almacén (proveedor/cliente), precio coste + venta + neto + precio cliente final, margen.
- Previsiones de facturación (`OrderPrevisions`), tracking, equipos comerciales, docs adjuntos.

### 2.2 Compras
- Pedido de compra (mismo modelo `Order` con `provider_id`/`purchase_num`), compra desde venta
  (envío directo de fábrica con cliente final), **recepciones** (`ReceivingController`) con
  validación línea a línea, aviso por sobre-recepción, recalculo de **precio de coste real y
  comercial** en recepción (repercusión de portes debidos), catálogo de proveedor
  (`ProductProviders`: referencia proveedor ↔ referencia catálogo).

### 2.3 Facturación
- Factura de venta desde pedido / albarán / agrupación; **facturación agrupada** por albaranes,
  por obras o todos (validación de cohesión cliente/proveedor/obra en `GroupedInvoiceService`).
- **Abonos** (`refund` total/parcial) y **facturas rectificativas** (serie propia del usuario,
  reintegro de stock, soporte concurso de acreedores: serie 04, positivos sin IVA).
- **Series** (`InvoiceSerie`, con semántica de negocio: 09 vendedores, 00 dirección, 06 tienda,
  04 concurso) + **patrones de numeración** por empresa (`*Patterns` en ~16 tipos de documento,
  serializados en PHP, `yearly_reset`, un `featured` por empresa).
- Factura de compra completa con asiento y efecto de pago automáticos.
- Tickets de caja (`Cash`), pagas y señales (descontadas del efecto, métodos limitados:
  contado/recibo/tarjeta), arqueo (`CashBalance`).

### 2.4 Tesorería (efectos y remesas)
- **Efecto** (`Effect`): vencimiento bancario. Estados: no aceptado (1) → aceptado (10) →
  remesado (20) → cobrado (30) / impagado (40). Tipos: efectivo, cheque, pagaré, domiciliación.
  Generación automática al registrar factura (`Effect::generateForInvoice`, N plazos con días de
  pago 1/2 y meses exentos según condiciones del cliente), agrupación de efectos (inmutables:
  se eliminan y recrean), efectos manuales de cliente y proveedor, vinculación N:N con facturas.
- **Remesas SEPA** (`RemittanceXmlService`): cobros → **pain.008.001.02** (adeudo directo, con
  mandatos FRST/RCUR, CORE) y pagos → **pain.001.001.03** (transferencia). Validación XSD,
  identificador de acreedor AT-02 (ISO 7064 MOD 97-10), referencia RF ISO 11649, validación
  IBAN/BIC, avisos de plazo no bloqueantes. Liquidación con asiento (`RemittanceSeatService`) e
  impagados con R-codes SEPA (`RemittanceImpagadoService`).

### 2.5 Contabilidad
- Plan contable por empresa (`AccountingAccount` con `company_target`, cuentas featured por
  ámbito: ventas con IVA / sin IVA / intracomunitario / extracomunitario), tipos, saldos anuales.
- **Ledger como fuente de verdad**: `InvoiceLedgerGenerator` (idempotente) → `SeatBuilder` →
  `Seat::saveSeat()` (transaccional, cuadre debe=haber con tolerancia 0,01).
- Asientos automáticos: ventas (tipo 30 + autofactura intracomunitaria "AA"), compras, caja
  (`CashSeatService`), tesorería al cobrar/pagar efecto (`EffectSeatService`, diario DG),
  liquidación de remesa. Asientos manuales + modelos de asiento (`SeatModels`).
- Amortizaciones, gastos, balance, extractos con debe/haber/saldo.
- Precondiciones contables validadas antes de operar (`AccountingPreconditionsService`).

### 2.6 Stock / catálogo
- Multi-almacén (`Store` por empresa), kardex (`Stock` con saldo acumulado — sin soft delete,
  histórico intocable), consolidado (`StockTotal`), **reservas** (`StockReserve`), movimientos
  tipificados con signo, roturas, inventario, lotes, números de serie.
- Producto: unidades múltiples (stock/venta/compra con coeficientes), IVA compra/venta,
  cuentas contables de compra/venta, traducciones JSON, tags/familias/categorías/atributos,
  MOQ, fulltext search.
- **Precios**: histórico versionado (`ProductPrices`, status vigente) con coste real / medio /
  último / comercial + **5 tarifas de venta**; **tarifas tipológicas** A/B/C aplicables a
  empresa u **obra**, con exclusiones por producto.

### 2.7 Multi-empresa, terceros y RBAC
- `Company` = tenant y a la vez cliente/proveedor (multi-rol). Empresa activa en
  `session('currentCompany')`, usada en casi todas las queries.
- **`CustomerProviders`**: relación N:N cliente↔proveedor con toda la parametrización comercial
  y fiscal (descuento por defecto, riesgo, días de pago, ámbito, IRPF, cuentas, campos SII).
- **Obras** (`Obra`): entidad clave del negocio; vinculadas a empresas con vigencias, dirección
  única, contactos con cargo, comercial asignado, tarifa por obra. La facturación se puede
  agrupar por obra.
- NIF histórico con vigencias (`CompanyIdentifier` + `NifResolver`): el documento congela el
  NIF válido a su fecha.
- Spatie Permission con roles nombrados `company_id/rol`; Super Admin bypass; **módulos
  activables por empresa** (`Module`/`CompanyModules`) que condicionan menú y middleware
  (`module_setted`). UTEs, grupos de empresas, sectores, centros de coste, áreas de negocio,
  centros de trabajo.

### 2.8 Portal de clientes / Tienda B2B
- Integrado en el mismo admin (no hay storefront separado): la sesión discrimina cliente vs
  Viuda Vila (`currentCompany` ≠ empresa matriz ⇒ carga `currentCommercial`).
- Catálogo con filtros (familias, categorías, tags, marcas), carrito, checkout que desemboca en
  el motor estándar de pedidos (borrador + notificaciones), presupuestos de cliente
  convertibles a pedido, precios según tarifa de obra/cliente.
- Roles de empleados del cliente (Spatie): Encargado, Cap de producció, Cap d'obra, Cap de
  grup, Estudis, Administratiu obra — matriz de permisos por cargo (compras / presupuestos /
  histórico). El cliente gestiona sus usuarios, solicita obras y cambios de domicilio.
- Notificaciones al cliente **y** al comercial; chat por obra (comercial↔cliente, Pusher).
- Logo del cliente en sesión; documentos siempre con logo Viuda Vila.

### 2.9 SII / AEAT
- Arquitectura delegada: Laravel construye el JSON (SII 1.1, `RegistroLRFacturasEmitidas` /
  `Recibidas`, desglose IVA por tipo) y lo envía por HTTP a un **microservicio externo**
  (`API_SII_URL`) que hace el SOAP con certificado. Credenciales por empresa
  (`SiiCredential`: certificado en storage + password cifrada). Respuestas → `sii_status`,
  CSV, `SiiReport`. Estaba en desarrollo activo (slice F).

### 2.10 Otros módulos
- **RRHH**: empleados, contratos (vencimientos programados), horarios/fichajes firmados,
  calendarios laborales, tipos de jornada, planning, vehículos, selección de personal.
- **Proyectos**: docs, pedidos/compras por proyecto, balance económico, estadísticas.
- **Producción**: órdenes de trabajo con productos, patrones, lotes.
- **SAT**: servicio de asistencia técnica con items y envíos.
- **Tareas** (kanban con estados, mensajes, subtareas), **incidencias**, **procedimientos**
  con revisiones, manual (handbook).
- **Comunicación**: chat en salas (Pusher), notificaciones database, mensajes.
- **Formación/gamificación**: quizzes, ediciones, equipos (candidato a no migrar).
- **Consultas/KPI** (`QueryController`): ventas por comercial/producto/obra.
- **Import legacy DBF→MySQL**: pipeline por comandos (`legacy:run-all`, staging `_fx` →
  promote, locks, post-checks de integridad). *En v2 la migración será MySQL(v1)→MySQL(v2),
  mucho más simple; el pipeline DBF no se porta.*

### 2.11 Patrones transversales de v1
- Soft deletes masivos (excepto kardex de stock), auditoría `created_by`/`updated_by`,
  campos `*_old_num` de convivencia legacy.
- Numeración: `*_num` + `*_num_cardinal` + patrón por empresa + reset anual.
- Decimales: 3 en procesos de venta (4 en compras), 2 en contabilidad y totales.
- i18n es/en (`textos.php`, ~3.900 claves) + traducción de datos por registro (JSON).
- PDF con dompdf (+merger para albarán doble copia proveedor/cliente), importe en letras
  (`MoneyToWordsEs`), Excel import/export, PWA, crons vía scheduler + endpoints token.

---

## 3. Deuda técnica y carencias conocidas de v1 (a resolver en v2)

Origen: `openspec/BACKLOG.md`, `info_modulos.txt`, inventarios y hallazgos del análisis.

1. **MySQL strict mode desactivado** para tolerar GROUP BY incompletos → reescribir queries.
2. Controladores y modelos gigantes (Order 5.3k, Invoice 4.4k líneas) con lógica de negocio
   en modelo/controlador → extraer a Actions/Services testeados.
3. ~100 middleware Get/Post ad-hoc para tenencia y validación → Policies + FormRequests.
4. Patrones de numeración **serializados con PHP `serialize()`** → JSON + servicio dedicado.
5. Incoherencias de permisos (p. ej. `invoices.edit` protegido con `orders.edit`), rutas con
   nombres duplicados (`get-by-product-id`, `timetable.revision-horario`, `roles.set-permission`).
6. Tenant vía `session('currentCompany')` disperso por todo el código → contexto de empresa
   formalizado (global scopes / servicio CurrentCompany).
7. Cobertura de tests muy baja fuera de remesas; sin PHPStan/Pint en CI.
8. Editor de pedido duplicado (Livewire `OrderComponent` + legacy jQuery `addAndEditOrder.js`).
9. Jobs de email con envío real comentado; `QUEUE_CONNECTION=sync` por defecto.
10. App key de Pusher hardcodeada en Blade; certificados SII en storage local.
11. Funcionalidades pendientes de negocio (info_modulos): fórmula de stock mínimo (media
    mensual + plazo real de entrega del proveedor), chat de sala por obra, filtro por tags en
    tienda, opciones de envío de factura por cliente (postal/email), pagas y señales en
    asientos, permisos específicos para cambiar fecha de factura, etc.

---

## 4. Proyección de la nueva versión (v2)

### 4.1 Stack

| Capa | v1 | v2 |
|---|---|---|
| Framework | Laravel 9 / PHP 8.2 | **Laravel 13 / PHP 8.3+** — es la versión estable actual (publicada 2026-03-17; 12 fue la de 2025). El esqueleto ya tiene instalado 13.19.0; el nombre de carpeta `laravel12_erp` es solo histórico |
| Frontend | Blade + Livewire 2 + jQuery/Bootstrap 4 (INSPINIA) | **Inertia v2 + React + TypeScript + Tailwind 4** (shadcn/ui como base de design system) |
| Listados | yajra DataTables + ~150 endpoints ad-hoc | Componente **`<ServerTable>`** genérico (**Tabulator**, decidido 2026-07-14; sustituye a TanStack Table) + endpoints JSON convencionales `*/table` con whitelist de filtros/sort (contrato en `docs/standards/frontend.md` §3) |
| Formularios | Blade components + jQuery validate | react-hook-form + zod + kit propio de inputs (Select con búsqueda, DatePicker, WYSIWYG, Dropzone, TabsIdiomas) |
| Realtime | Pusher cloud | **Laravel Reverb** + Echo (notificaciones, chat) |
| Colas | sync (database en prod) | database/redis desde el día 1 + Horizon opcional |
| Permisos | Spatie Permission 5 | Spatie Permission 6 + **Policies por modelo** |
| PDF | dompdf + pdfmerger | dompdf (compatibilidad plantillas) — evaluar spatie/laravel-pdf después |
| Excel | maatwebsite/excel | maatwebsite/excel 3.x |
| Tests | PHPUnit escaso | **Pest + PHPStan + Pint en CI** desde el inicio |
| i18n | textos.php es/en | laravel lang JSON + i18n en React; spatie/laravel-translatable para datos |

### 4.2 Arquitectura backend (monolito modular)

```
app/
  Domains/
    Sales/          (presupuestos, pedidos, albaranes)
    Purchasing/     (compras, recepciones)
    Invoicing/      (facturas, series, patrones, agrupada, rectificativas)
    Treasury/       (efectos, remesas SEPA, caja, bancos)
    Accounting/     (plan contable, ledger, asientos, amortizaciones)
    Inventory/      (stock, almacenes, reservas, movimientos, inventario)
    Catalog/        (productos, precios, tarifas, familias/categorías)
    Partners/       (empresas, clientes/proveedores, obras, contactos)
    Tax/            (IVA, ámbitos, SII / VERI*FACTU)
    HR/             (empleados, horarios, calendarios)
    Projects/       (proyectos, órdenes de trabajo, tareas)
    Portal/         (tienda, carrito, acceso clientes)
    Admin/          (usuarios, roles, módulos, configuración)
  → cada dominio: Models, Actions, Services, Http (Controllers/Requests), Policies, Events
```

Decisiones clave:
- **Tenancy explícito**: `CurrentCompany` resuelto por middleware (sesión/header), global
  scope `BelongsToCompany`, nada de `session('currentCompany')` en queries.
- **Autorización**: Policies + permisos Spatie; adiós a los pares `*GetMiddleware`/`*PostMiddleware`.
- **Documentos con máquina de estados** (enums PHP 8 + transiciones validadas) en vez de
  constantes en config.
- **Modelo `Order` unificado — DECIDIDO (2026-07-13): se mantiene** como en v1 (la tabla
  acaba siendo colosal pero simplifica enormemente la programación, y facilita la migración
  de datos v1→v2). Mitigaciones en v2: máquina de estados explícita por rol del documento
  (presupuesto/pedido/compra), índices compuestos por `company_id` + flags + fechas, scopes
  nombrados (`budgets()`, `salesOrders()`, `purchaseOrders()`) y particionado/archivado si el
  volumen SaaS lo exige.
- **Ledger contable como fuente de verdad** (portar el diseño de v1: LedgerGenerator →
  SeatBuilder → Seat, idempotente y cuadrado): es de lo mejor de v1.
- **SEPA builders portables casi tal cual** (pain.008/pain.001 + XSD + AT-02 + RF ISO 11649),
  con sus tests existentes como referencia.
- **Numeración**: `NumberingService` por tipo de documento + patrón JSON por empresa, con
  bloqueo (SELECT ... FOR UPDATE) para evitar duplicados en concurrencia.
- **Importes**: casts decimal:2/3 consistentes (3 decimales venta, 2 contable) y utilidades
  compartidas de cálculo de línea (descuentos en cascada + IVA + abonos) testeadas en unit.
- **API DataTables** de v1 desaparece como tal: los índices son páginas Inertia y cada
  listado expone un único endpoint JSON convencional `*/table` (contrato Tabulator con
  whitelist de filtros/sort vía `TableRequest` + `TableBuilder`, misma Policy que el index;
  ver `docs/standards/frontend.md` §3). Deferred props / partial reloads para paneles pesados.

### 4.3 Arquitectura frontend

```
resources/js/
  app.tsx, ssr.tsx
  Layouts/AppLayout.tsx        (Sidebar data-driven + Topbar: selector empresa/obra,
                                buscador, notificaciones realtime, idioma)
  Components/
    Table/ServerTable.tsx      (cubre los ~123 listados: columnas + filtros + export)
    Form/*                     (kit de inputs, Select-búsqueda, DatePicker, WYSIWYG, Dropzone)
    Records/RecordEditLayout.tsx, ActionBar.tsx, Tabs.tsx, Modal.tsx
    Lines/LineItemsEditor.tsx  (EL componente clave: unifica OrderComponent +
                                DeliveryComponent + addAndEditOrder.js — tabla editable
                                inline, buscador de artículo, 4 descuentos, stock por
                                almacén, histórico, drag&drop, totales/margen/IVA)
  Pages/<Dominio>/<Entidad>/{Index,Create,Edit}.tsx
  lib/ (formato importes es-ES, IBAN, MOQ/múltiplos, cálculo de línea)
```

- **Menú data-driven**: endpoint/props con el árbol ya filtrado por permisos + módulos activos
  de la empresa (sustituye a los JSON de `storage/json/` + @can en Blade).
- Pantallas patrón: "cabecera de acciones + tabs + tabla de líneas + modales" (pedido, factura,
  albarán, recepción, presupuesto comparten esqueleto).
- Estado global mínimo: carrito de tienda y notificaciones (Zustand/Context); el resto vive en
  props Inertia.

### 4.4 Mejoras y ampliaciones respecto a v1

1. **Cumplimiento fiscal 2026**: además del SII, evaluar **VERI*FACTU / factura electrónica
   (Ley Crea y Crece)** — v1 no lo contempla y la obligación es inminente para el perfil de
   empresa. Diseñar el dominio Tax para soportar ambos.
2. Resolver todo el §3 (deuda técnica) por diseño, no por parches.
3. Funcionalidades pendientes de negocio de v1 (stock mínimo con plazo real de proveedor,
   filtros por tags en tienda, chat por obra, envío de facturas según preferencia del
   cliente...) como backlog inicial de v2.
4. Dashboard/KPIs modernos (el `QueryController` de v1 como semilla del módulo de informes).
5. Auditoría transversal homogénea (activity log) en lugar de campos ad-hoc.
6. API REST/token (Sanctum) de primera clase para futuras integraciones (v1 tenía una API v1
   mínima).

### 4.5 SaaS multi-tenant sin empresa preferente (decidido 2026-07-13)

v1 está construido alrededor de una empresa preferente (`COMPANY_DEFAULT_ID_` = Viuda Vila):
el login del portal, la tienda (proveedor siempre Viuda Vila), entradas de menú "solo Viuda
Vila" y la semántica fija de series (09/00/06/04). **En v2 no hay empresa preferente: es un
SaaS que usan muchas empresas simultáneamente en igualdad de condiciones.**

**Modelo de aislamiento recomendado: base de datos única compartida con scoping estricto por
`company_id`** (global scope + Policies + tests de aislamiento cruzado). Razones:

1. El esquema v1 ya es multi-empresa por diseño (94 migraciones con `company_id`, series,
   patrones y módulos por empresa) — se hereda el modelo, endureciendo su aplicación.
2. Preserva el efecto red del portal B2B: una empresa cliente puede serlo de **varios**
   proveedores-tenant con una única cuenta (la relación `customer_providers` ya modela esto).
3. Operación y migraciones mucho más simples que N bases de datos.

Alternativa descartada por defecto: BD-por-tenant (p. ej. stancl/tenancy) — máximo
aislamiento físico, pero rompe la red cliente↔proveedor entre tenants y duplica cuentas.
Reevaluar solo si un cliente exige aislamiento físico contractualmente.

Conceptos a separar en v2:
- **Empresa** (registro en `companies`): puede actuar como cliente y/o proveedor de otras.
- **Suscripción/tenant**: empresa que contrata el SaaS. `CompanyModules` de v1 encaja como
  base del sistema de **planes** (plan = conjunto de módulos activados + límites).

Cambios concretos respecto a v1:
- Eliminar todo hardcode de empresa preferente (menús, tienda, login, notificaciones).
- Series y patrones de numeración 100% configurables por tenant (la semántica 09/00/06/04
  pasa a ser configuración de ejemplo/seed, no código).
- Branding por tenant: logo en documentos, plantillas PDF, colores.
- Portal/tienda generalizado: cada tenant publica su catálogo y da acceso a sus empresas
  cliente; un usuario puede pertenecer a N empresas y cambiar de contexto (el selector de
  empresa de v1 ya lo apuntaba).
- **Consola de plataforma** (operador del SaaS): gestión de tenants, planes, uso y soporte,
  separada del admin de cada empresa.
- Onboarding self-service: registro de empresa + asistente inicial (series, plan contable
  base, almacén, usuarios, datos fiscales/SEPA).
- Facturación del SaaS: Laravel Cashier (Stripe) u otro proveedor — por decidir.
- Operación SaaS: RGPD (export/borrado por tenant), backups/restauración lógica por tenant,
  cuotas por plan, jobs y crons siempre con `company_id` explícito (nunca dependientes de
  sesión), observabilidad por tenant.
- Rendimiento: índices compuestos `(company_id, …)` en todas las tablas grandes (`orders`,
  `items`, `stocks`, `seats`, `effects`); la tabla `orders` unificada crecerá con N empresas
  → prever archivado o particionado por ejercicio.

### 4.6 Plan por fases propuesto

| Fase | Contenido | Resultado |
|---|---|---|
| 0. Fundaciones | Auth, multi-empresa + CurrentCompany, RBAC + módulos por empresa, **base SaaS (tenants, planes=módulos, onboarding mínimo, consola de plataforma)**, AppLayout + menú data-driven, ServerTable, kit de formularios, CI (Pest/PHPStan/Pint) | Shell funcional multi-tenant con usuarios/roles/empresas |
| 1. Maestros | Empresas/clientes/proveedores (CustomerProviders), obras, productos + precios/tarifas, almacenes, geografía, IVA/ámbitos, series y patrones | Datos maestros gestionables |
| 2. Ciclo de venta | Presupuesto → pedido → albarán → factura + LineItemsEditor + stock/reservas + PDFs + numeración | Order-to-invoice completo |
| 3. Compras | Pedido compra, recepción (costes reales/portes), factura de compra, compra desde venta | Ciclo de compra completo |
| 4. Tesorería y contabilidad | Efectos, remesas SEPA (portar builders), caja/arqueo, ledger + asientos automáticos, plan contable, SII/VERI*FACTU | Cierre financiero |
| 5. Portal cliente | Tienda, carrito, presupuestos/pedidos de cliente, roles de cliente, notificaciones + chat (Reverb) | B2B online |
| 6. Resto | RRHH, proyectos/OT, SAT, tareas, incidencias, informes; decidir qué NO se migra (quizzes, staff picks...) | Paridad + extras |
| Transversal | **Migración de datos v1(MySQL) → v2(MySQL)** por comandos idempotentes con post-checks (heredar filosofía del pipeline legacy, no su código) | Corte controlado |

---

## 5. Lo que merece la pena portar casi tal cual desde v1

- Diseño **ledger → SeatBuilder → Seat** (contabilidad idempotente y cuadrada).
- **Builders SEPA** completos (pain.008/001, XSD, mandatos FRST/RCUR, AT-02, RF ISO 11649)
  y sus tests.
- Lógica de **generación de efectos** (plazos, días de pago, meses exentos) y de vencimientos.
- `NifResolver` (NIF histórico por fecha de documento).
- Reglas de negocio documentadas: series con semántica, decimales 3/2, facturación agrupada,
  rectificativas con concurso de acreedores, pagas y señales.
- Esquema conceptual de datos (entidades y pivotes), depurándolo (§4.2).
- `MoneyToWordsEs`, cálculo de línea con descuentos en cascada.

## 6. Decisiones

### Resueltas (2026-07-13)

1. **Framework: Laravel 13** (estable actual, publicado 2026-03-17; instalado 13.19.0). La
   carpeta se renombró a `laravel13_erp` para reflejarlo.
2. **Modelo `Order` unificado se mantiene**: tabla colosal pero simplifica la programación
   (mitigaciones en §4.2).
3. **v2 es un SaaS multi-tenant sin empresa preferente** (detalle en §4.5).

### Resueltas (2026-07-14) — base normativa de desarrollo

4. **Estándares de desarrollo normativos en `docs/standards/`** (playbook de módulo con
   checklists y vinculaciones, reglas de arquitectura R-*, mapa de dominios con matriz de
   dependencias, canon de librerías, catálogo de componentes, templates, glosario, tooling).
   Guardrails activos: `CLAUDE.md`, skill `/new-module`, generador `make:domain-entity` y
   tests de arquitectura (estos dos últimos, especificados en `docs/standards/tooling.md`,
   se implementan como primer change de Fase 0).
5. **Listados con Tabulator** en el componente `<ServerTable>` (sustituye a TanStack Table
   proyectado inicialmente): contrato de tabla en `docs/standards/frontend.md` §3.
6. **Naming: código en inglés con glosario normativo ES↔EN**
   (`docs/standards/glossary.md`): Obra→Worksite, Remesa→Remittance; se conservan términos
   asentados de v1 (Effect, Seat, Store, Item). UI siempre en español vía i18n.
7. **Alcance conceptual**: metodología + mapa global ahora; la ficha detallada de cada
   módulo (`docs/modules/`) se redacta al arrancarlo, como insumo de su propuesta OpenSpec.
8. **Metodología de agentes 10/20/30** (heredada de la operativa de v1, adaptada):
   10-architecture (propose) / 20-implementation (apply) / 30-testing (DoD + archive),
   con fichas canónicas agnósticas de herramienta en `docs/agents/` y adaptadores finos
   por tool: `.cursor/rules/`, `.claude/agents/` (modelo por rol: potente en 10, medio en
   20/30) y `AGENTS.md` (Codex). El handoff entre roles/modelos son los artefactos
   OpenSpec, lo que permite usar modelos baratos en implementación y potentes en diseño.
9. **E2E con Pest 4 browser testing** (Playwright por debajo), integrado en la suite PHP
   (`docs/standards/e2e.md`): sustituye al sandbox Playwright/TS de v1 (que existía por
   el Node 16). Los CRUD los cubren feature tests; E2E solo flujos críticos completos.
10. **Look & feel**: reseteo limpio respecto a Inspinia (v1), conservando solo el color por
    módulo en el sidebar. Plantilla de referencia para adaptar: `shadcn-admin` (satnaing,
    MIT). Branding por tenant limitado al logo (vista + documentos), sin theming completo
    por empresa. Detalle en `docs/standards/frontend.md` §2.

### Abiertas (validar con negocio/propietario)

1. **Aislamiento de tenancy**: propuesta BD única + scoping estricto (§4.5). Confirmar, y
   definir si algún cliente podría exigir BD dedicada.
2. **Monetización**: proveedor de cobro del SaaS (Cashier/Stripe u otro) y mapa de
   planes ↔ módulos ↔ límites.
3. **SII**: ¿mantener el microservicio SOAP externo o integrar el envío en el propio Laravel?
   ¿Se añade VERI*FACTU en v2? (siendo SaaS, el cumplimiento fiscal por tenant es crítico).
4. **Alcance del portal cliente** en v2 (¿paridad con v1 o rediseño?) y del chat.
5. **Módulos a no migrar**: quizzes/formación, staff picks, cheats… ¿se descartan?
6. **Multiidioma**: ¿es/en como v1? ¿catalán real esta vez? (en v1 la carpeta `ca` está vacía).
7. **Estrategia de migración de datos**: Viuda Vila entraría como primer tenant —
   ¿big-bang con corte o convivencia por módulos?
