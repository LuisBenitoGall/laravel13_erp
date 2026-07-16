# Arquitectura backend — reglas normativas

> Documento normativo. Todo código backend del proyecto DEBE cumplir estas reglas.
> Las excepciones se justifican por escrito en la propuesta OpenSpec del cambio que las introduce.
> Contexto y razones de negocio: [../vision-v2.md](../vision-v2.md).

## 1. Monolito modular: estructura de carpetas

Todo el código de negocio vive en `app/Domains/<Dominio>/`, con esta estructura fija:

```
app/
  Domains/
    <Dominio>/                  # p. ej. Catalog, Sales, Treasury (ver module-map.md)
      Models/                   # Eloquent models del dominio
      Enums/                    # enums PHP 8 (estados, tipos) con transiciones
      Actions/                  # un caso de uso mutador = una Action invocable
      Services/                 # lógica de consulta/cálculo compartida DENTRO del dominio
      Events/                   # eventos de dominio (públicos: otros dominios los escuchan)
      Listeners/                # listeners de eventos de OTROS dominios
      Http/
        Controllers/            # controllers delgados
        Requests/               # FormRequests (Store*/Update* + TableRequest si aplica)
        Resources/              # transformación modelo → props Inertia / JSON de tabla
      Policies/                 # una Policy por modelo
      Jobs/                     # jobs de cola (siempre con company_id explícito)
      Notifications/
      Exceptions/
  Support/                      # helpers SIN dominio (formato, dinero, fechas, IBAN...)
  Http/Middleware/              # middleware transversal (CurrentCompany, module_active...)
database/
  migrations/                   # todas las migraciones juntas (orden cronológico global)
  factories/ · seeders/
routes/
  web.php                       # solo require de routes/domains/*.php
  domains/<dominio>.php         # rutas de cada dominio
```

Reglas:

- **R-ARQ-01** Ningún modelo, controller ni service fuera de `app/Domains/*` o `app/Support`
  (excepto lo que el framework exige en `app/` raíz: Providers, Console, Middleware transversal).
- **R-ARQ-02** `app/Support` no conoce ningún dominio: prohibido importar `App\Domains\*`
  desde `App\Support`.
- **R-ARQ-03** Las migraciones NO se reparten por dominio: viven todas en
  `database/migrations` para preservar el orden cronológico global.

## 2. Comunicación entre dominios

Es un monolito con **una sola base de datos**: las relaciones Eloquent cruzan dominios con
normalidad (un `Order` de Sales pertenece a una `Company` de Partners). Lo que se regula es
la **escritura** y el **acoplamiento de lógica**:

- **R-DOM-01 Lectura libre**: cualquier dominio puede leer modelos de otro vía relaciones o
  queries (siempre tenant-scoped, ver §3).
- **R-DOM-02 Escritura solo del propietario**: un dominio NUNCA muta datos de otro
  directamente (ni `update()` sobre su modelo ni queries de escritura). Para provocar un
  cambio en otro dominio hay dos vías, por orden de preferencia:
  1. **Evento de dominio** (asíncrono conceptualmente): `InvoiceRegistered` → listener de
     Accounting genera el asiento. Es la vía por defecto para efectos colaterales.
  2. **Action pública del dominio propietario** (síncrono): cuando el llamante necesita el
     resultado en la misma transacción (p. ej. Sales llama a `ReserveStock` de Inventory y
     necesita saber si hay stock). Solo Actions, nunca Services internos de otro dominio.
- **R-DOM-03** Los `Services/` de un dominio son **privados** del dominio: prohibido
  importarlos desde otro dominio. La superficie pública de un dominio son sus Models
  (lectura), Actions y Events.
- **R-DOM-04** Los eventos de dominio se nombran en pasado (`OrderConfirmed`,
  `ReceivingCompleted`) y transportan IDs + datos inmutables, no modelos vivos.
- **R-DOM-05** Cadenas de efectos (venta → stock → contabilidad) se documentan en la matriz
  de vinculaciones de [module-map.md](module-map.md) ANTES de implementarse.

## 3. Multi-tenancy (SaaS, BD única)

Decisión de vision-v2 §4.5: BD única compartida, scoping estricto por `company_id`, sin
empresa preferente.

- **R-TEN-01** Toda tabla de datos de negocio lleva `company_id` (FK a `companies`) y todo
  índice útil empieza por `company_id` (índices compuestos `(company_id, ...)`).
  Excepciones (catálogos globales: países, provincias, tipos de IVA base...) se listan
  explícitamente en la ficha del módulo.
- **R-TEN-02** Todo modelo de negocio usa el trait `BelongsToCompany`, que aplica un global
  scope por la empresa activa y rellena `company_id` en `creating`.
- **R-TEN-03** La empresa activa se resuelve UNA vez por request en el middleware
  `SetCurrentCompany` y se accede vía el servicio inyectable `CurrentCompany`.
  **Prohibido** `session('currentCompany')` en queries, modelos o vistas (error capital de v1).
- **R-TEN-04** Jobs, comandos y listeners de cola reciben `company_id` explícito en el
  constructor y lo restauran en `CurrentCompany` al ejecutarse. Nada que corra fuera del
  ciclo request puede depender de sesión.
- **R-TEN-05** Cross-tenant = bug de seguridad. Cada entidad incluye el **test de
  aislamiento obligatorio**: usuario de empresa A no ve/edita/borra registros de empresa B
  (ver template de test). Sin ese test la entidad no se considera terminada.
- **R-TEN-06** Nada de empresa "preferente" hardcodeada: ni IDs de empresa, ni semántica
  fija de series (09/00/06/04 de v1 pasa a ser seed de ejemplo), ni menús condicionados a
  una empresa concreta.
- **R-TEN-07** Los `exists`/`unique` de validación siempre acotados al tenant:
  `Rule::exists('obras','id')->where('company_id', $currentCompanyId)`.

## 4. Autorización

- **R-AUT-01** Una **Policy por modelo**, registrada, con `viewAny/view/create/update/delete`
  (+ `restore/forceDelete` si soft delete, + habilidades específicas: `confirm`, `invoice`...).
  La Policy comprueba (a) pertenencia al tenant y (b) permiso Spatie.
- **R-AUT-02** Permisos Spatie con naming `<entidad>.<acción>` (`invoices.create`,
  `orders.change-date`). **Importante**: el segmento no es el `Module` SaaS-activable
  (Ventas, Contabilidad...) sino el slug de la entidad/pantalla concreta — en el catálogo
  de Admin, el de su `Functionality` (R-AUT-07). Cada entidad declara sus permisos en el
  seeder de permisos de su dominio, o los auto-provisiona el generador
  ([tooling.md](tooling.md) §1); nunca se crean permisos "sueltos".
- **R-AUT-03** Roles por empresa (patrón v1 `company_id/rol`); Super Admin de plataforma con
  bypass vía `Gate::before`.
- **R-AUT-04** **Prohibidos los middleware ad-hoc de autorización** por módulo (los ~100
  pares `*Get/*Post` de v1). Autorización = Policy (+ `authorize()` del FormRequest).
- **R-AUT-05** El middleware `EnsureModuleIsActive:<módulo>` protege las rutas de cada módulo
  activable (planes SaaS = módulos por empresa).
- **R-AUT-06** El permiso que protege una ruta/acción coincide con el módulo real de la
  entidad (en v1 había facturas protegidas con `orders.edit`: prohibido).
- **R-AUT-07** Catálogo `Module` / `Functionality` (Admin; excepción global de R-TEN-01, sin
  `company_id`): `Module` es la unidad SaaS-activable (gate de plan vía `CompanyModules` +
  `EnsureModuleIsActive`); `Functionality` es la unidad de permiso/menú dentro de un módulo
  (slug **inmutable** tras crearse — lo usan los nombres de permiso y las asignaciones de
  rol —, `label` editable, `status` activo/inactivo, reasignable de `module_id`). Al crear
  una `Functionality` se auto-provisionan sus 5 permisos `viewAny/view/create/update/delete`
  (nunca los 7 de v1 `index/create/show/search/edit/update/destroy`: `search` se absorbe en
  `viewAny`, `edit`+`update` se funden en `update`).
- **R-AUT-08** Visibilidad de menú (un único mecanismo para los 3 niveles de `Module` —
  admin-only/básico/opcional —, no los dos sistemas paralelos de v1
  `basic-menu.json`/`secondary-menu.json`):
  - Un `Module` se muestra si `CompanyModules` lo tiene activo para la empresa **y**
    (el usuario tiene el permiso comodín `<module-slug>.access` **o** tiene al menos un
    permiso de alguna `Functionality` del módulo).
  - Una `Functionality` se muestra si el usuario tiene el comodín `<module-slug>.access`
    de su módulo (que **implica** el `viewAny` de todas sus Functionalities) **o** su
    propio permiso `viewAny`.
  - v1 hacía un AND estricto (exigía el comodín de módulo aunque el usuario tuviera permiso
    de una Functionality concreta, dejando módulos enteros invisibles): prohibido replicar
    ese comportamiento.

## 5. Capas y responsabilidades

- **R-CAP-01 Controllers delgados**: máximo orquestar (authorize → validated → Action →
  respuesta Inertia/redirect). Sin queries de escritura, sin reglas de negocio, sin
  transacciones. Objetivo: ningún controller > 200 líneas (los de v1 llegaban a 5.300).
- **R-CAP-02 Una Action por caso de uso mutador** (`CreateBudget`, `ConfirmOrder`,
  `RegisterInvoice`), método único `handle()`, transaccional (`DB::transaction`), dispara
  los eventos de dominio al final. Las Actions son la unidad natural de test.
- **R-CAP-03 Modelos finos**: relaciones, casts, scopes nombrados, accessors puros. La
  lógica de negocio vive en Actions/Services (los modelos de v1 de 2.000 líneas: prohibido).
- **R-CAP-04 FormRequest siempre** para toda entrada de escritura; `authorize()` delega en
  la Policy; los mensajes usan traducciones.
- **R-CAP-05 Resources para salida**: las props Inertia y las respuestas de tabla se
  construyen con Resources/arrays explícitos. Nunca pasar modelos crudos con todos sus
  atributos al frontend (fugas de columnas + acoplamiento).
- **R-CAP-06** Estados de documento = **enum PHP** con método `canTransitionTo()`; las
  transiciones inválidas lanzan excepción de dominio. Prohibidas las constantes numéricas en
  config (patrón v1).

## 6. Base de datos y datos

- **R-BD-01 MySQL strict mode ON** (`config/database.php` por defecto). Prohibido
  desactivarlo; los GROUP BY se escriben completos.
- **R-BD-02 Decimales**: dinero y cantidades con `decimal` (nunca float). Convención v1 que
  se mantiene: **3 decimales** en proceso de venta, **4** en costes de compra, **2** en
  contabilidad y totales de documento. Casts `decimal:N` en el modelo y columnas a juego.
- **R-BD-03 Soft deletes por defecto** en entidades de negocio + columnas de auditoría
  `created_by`/`updated_by` (trait `Auditable`). Excepción documentada: kardex de stock
  (histórico inmutable, sin soft delete, patrón v1).
- **R-BD-04** Prohibido `serialize()` PHP en columnas (patrones de numeración de v1):
  estructuras → columnas JSON con cast `array`/`AsCollection` o castables dedicados.
- **R-BD-05 Numeración de documentos** exclusivamente vía `NumberingService` (dominio
  Shared/Admin): patrón JSON por empresa y tipo de documento, `yearly_reset`, y obtención
  del siguiente número con bloqueo (`SELECT ... FOR UPDATE`) dentro de la transacción de la
  Action. Prohibido calcular números "a mano".
- **R-BD-06** Toda FK con índice; `onDelete` explícito y meditado (restrict por defecto en
  documentos; cascade solo en detalle propio, p. ej. `items` de un `order`).
- **R-BD-07** Migraciones inmutables una vez mergeadas: los cambios de esquema son nuevas
  migraciones.
- **R-BD-08** Datos traducibles por registro: `spatie/laravel-translatable` (columna JSON),
  no tablas espejo.
- **R-BD-09** **Prohibido el tipo de columna `ENUM` de MySQL** (`$table->enum()`): ata los
  valores al esquema y cada cambio exige un ALTER TABLE. Los estados se almacenan como
  `string(32)` con cast a enum PHP (R-CAP-06) — añadir/quitar estados es solo código.
  Estados configurables por el usuario/tenant (p. ej. columnas de un kanban) no son enums:
  van a tabla propia.

## 7. Ledger contable (portado de v1 — intocable en espíritu)

- **R-LED-01** La contabilidad se genera SIEMPRE por la cadena
  `LedgerGenerator (idempotente) → SeatBuilder → Seat::saveSeat()` transaccional con cuadre
  debe=haber (tolerancia 0,01). Ningún módulo escribe asientos por otra vía.
- **R-LED-02** Todo módulo que genere efectos contables lo hace escuchando eventos de
  dominio (factura registrada, efecto cobrado, remesa liquidada, arqueo...) — nunca inline
  en la Action de origen.
- **R-LED-03** Precondiciones contables (`AccountingPreconditionsService`) se validan antes
  de operaciones que exigen configuración contable, con errores accionables para el usuario.

## 8. Convenciones de naming

Código **en inglés**; términos de negocio según el [glosario normativo](glossary.md)
(Obra→`Worksite`, Remesa→`Remittance`, Efecto→`Effect`, Asiento→`Seat`...). La UI siempre en
español (y demás idiomas) vía i18n; el usuario nunca ve identificadores.

| Elemento | Convención | Ejemplo |
|---|---|---|
| Modelo | singular StudlyCase | `DeliveryNote`, `Worksite` |
| Tabla | plural snake_case | `delivery_notes`, `worksites` |
| Pivote | singular_singular alfabético | `customer_provider`, `effect_invoice` |
| Action | verbo + objeto | `ConfirmOrder`, `GenerateRemittanceXml` |
| Evento | objeto + participio | `OrderConfirmed`, `EffectPaid` |
| Policy / Factory / Seeder | `<Modelo>Policy` / `<Modelo>Factory` / `<Modelo>Seeder` | `InvoicePolicy` |
| FormRequest | `Store<Modelo>Request` / `Update<Modelo>Request` | `StoreProductRequest` |
| Ruta (nombre) | `<módulo>.<entidad>.<acción>` | `sales.budgets.store` |
| Ruta (URI) | kebab-case plural | `/sales/delivery-notes` |
| Permiso | `<módulo>.<acción>` kebab | `invoices.create`, `orders.change-date` |
| Columna estado | `status` (enum cast) | `OrderStatus::Confirmed` |
| Página React | `Pages/<Dominio>/<Entidad>/{Index,Create,Edit,Show}.tsx` | `Pages/Sales/Budgets/Index.tsx` |

## 9. Otros transversales

- **R-TRV-01 Colas reales desde el día 1**: `QUEUE_CONNECTION=database` mínimo; emails,
  PDF pesados y notificaciones SIEMPRE por cola (v1 tenía el envío real comentado: prohibido
  el patrón "lo activamos ya en producción").
- **R-TRV-02 Auditoría transversal** con `spatie/laravel-activitylog` en entidades de
  negocio relevantes (documentos, maestros, configuración), además de `created_by/updated_by`.
- **R-TRV-03 Secretos** solo en `.env`/config: nada de claves en código o vistas (app key de
  Pusher en Blade de v1: prohibido). Certificados (SII) cifrados en storage con password en
  vault/env.
- **R-TRV-04 Scheduler**: tareas programadas con `company_id` explícito iterando tenants;
  prohibidos endpoints-cron por token salvo integración externa justificada.
- **R-TRV-05 i18n**: claves JSON de Laravel (`lang/es.json`, `en`, `ca`) + i18n en React.
  Ningún literal de UI hardcodeado ni en PHP ni en TSX.
- **R-TRV-06 Fechas** en BD siempre UTC/`datetime`; formateo local (es-ES) solo en
  presentación (helpers de `Support`/`lib`).
- **R-TRV-07** Calidad en CI obligatoria: Pest (tests), PHPStan (nivel acordado en
  [tooling.md](tooling.md)), Pint. Un PR no se mergea en rojo.

## 10. Anti-patrones v1 expresamente prohibidos (resumen)

1. `session('currentCompany')` disperso → `CurrentCompany` (R-TEN-03).
2. Middleware `*Get/*Post` de autorización → Policies (R-AUT-04).
3. Controllers/modelos de miles de líneas → Actions/Services (R-CAP-01/03).
4. `serialize()` PHP en BD → JSON (R-BD-04).
5. MySQL sin strict mode → strict ON (R-BD-01).
6. Constantes de estado en config → enums con transiciones (R-CAP-06).
7. Permisos incoherentes con el módulo → R-AUT-06.
8. Empresa preferente hardcodeada → R-TEN-06.
9. Numeración manual → `NumberingService` (R-BD-05).
10. Colas en sync / envíos comentados → R-TRV-01.
11. Menú duplicado básico/secundario con lógica de visibilidad distinta → mecanismo único
    `Module`/`Functionality` (R-AUT-08).
12. Visibilidad de módulo con AND estricto sobre un permiso comodín (oculta funcionalidades
    con permiso propio) → comodín que implica, o permiso propio, nunca ambos obligatorios
    (R-AUT-08).
