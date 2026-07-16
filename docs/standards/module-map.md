# Mapa de dominios y matriz de vinculaciones

> Documento normativo **vivo**: se actualiza en el mismo PR que introduce o cambia una
> dependencia entre dominios (playbook §8). Origen funcional: [../vision-v2.md](../vision-v2.md) §2 y §4.2.

## 1. Dominios y capas

Los dominios se organizan en 4 capas. Regla general: **se depende hacia abajo** (una capa
usa las inferiores); las dependencias hacia arriba u horizontales van SIEMPRE por eventos
(R-DOM-02).

```
Capa 4 — Extensiones     Portal · Projects · HR · Platform
Capa 3 — Financiera      Invoicing · Treasury · Accounting · Tax
Capa 2 — Operaciones     Sales · Purchasing · Inventory
Capa 1 — Maestros        Partners · Catalog · Admin (+ Support sin dominio)
```

| Dominio | Propósito | Entidades núcleo (v1 de referencia) | Fase |
|---|---|---|---|
| **Admin** | Usuarios, roles/permisos, módulos por empresa, configuración, series y patrones de numeración, menú | User, Role, Module, Functionality, CompanyModules, `*Pattern`, InvoiceSerie | 0–1 |
| **Platform** | Consola del operador SaaS: tenants, planes, uso, soporte, onboarding | Tenant/Subscription, Plan | 0 |
| **Partners** | Empresas (tenant y tercero), clientes↔proveedores, obras, contactos, NIF histórico | Company, CustomerProviders, Worksite (Obra), Contact, CompanyIdentifier | 1 |
| **Catalog** | Productos, precios versionados, tarifas (5 + A/B/C), familias/categorías/tags, unidades, catálogo proveedor | Product, ProductPrices, Rate, Family, Category, ProductProviders | 1 |
| **Inventory** | Multi-almacén, kardex, consolidado, reservas, lotes, nº serie, inventario | Store, Stock, StockTotal, StockReserve, Lot, SerialNumber | 2 |
| **Sales** | Presupuesto → pedido → albarán (modelo `Order` unificado, lado venta), previsiones, equipos comerciales | Order, Item, OrderVersion, ItemRevision, DeliveryNote, OrderPrevisions | 2 |
| **Purchasing** | Pedido de compra, recepciones, costes reales/portes, compra desde venta | Order (lado compra), Receiving, ReceivingLine | 3 |
| **Invoicing** | Facturas venta/compra, agrupadas, abonos, rectificativas, tickets de caja | Invoice, InvoiceLine, Cash, CashBalance | 2–3 |
| **Treasury** | Efectos, remesas SEPA (pain.008/001), impagados, bancos, caja | Effect, Remittance, Bank, Mandate | 4 |
| **Accounting** | Plan contable, ledger→asientos, amortizaciones, balances, extractos | AccountingAccount, Seat, SeatLine, Amortization | 4 |
| **Tax** | IVA/ámbitos, SII, VERI*FACTU, credenciales fiscales por tenant | VatType, SiiCredential, SiiReport | 4 |
| **Portal** | Tienda B2B, carrito, presupuestos/pedidos de cliente, roles cliente, chat por obra | Cart, roles cliente, ChatRoom | 5 |
| **Projects** | Proyectos, órdenes de trabajo, producción, SAT, tareas/incidencias | Project, WorkOrder, SatTicket, Task | 6 |
| **HR** | Empleados, contratos, fichajes, calendarios, planning, vehículos | Employee, Contract, TimeEntry, WorkCalendar | 6 |

`Support/` (sin dominio): formato es-ES, dinero/decimales, importe en letras
(`MoneyToWordsEs`), IBAN/BIC, fechas. `Shared` conceptual: `NumberingService` y branding de
documentos viven en **Admin** (configuración por empresa).

`Module` y `Functionality` (Admin) son la primera excepción real a R-TEN-01: catálogo
**global**, sin `company_id` — `Module` es la unidad SaaS-activable por empresa
(`CompanyModules`); `Functionality` es la unidad de permiso y de entrada de menú dentro de
un módulo. Detalle de la política de permisos/menú: [architecture.md](architecture.md)
R-AUT-07/R-AUT-08.

## 2. Matriz de dependencias (quién usa a quién)

`D` = dependencia directa permitida (lectura/Actions). `E` = solo por eventos.
Fila = dominio que depende; columna = dominio del que depende.

| ↓ usa → | Admin | Partners | Catalog | Inventory | Sales | Purchasing | Invoicing | Treasury | Accounting | Tax |
|---|---|---|---|---|---|---|---|---|---|---|
| **Partners** | D | — | | | | | | | | |
| **Catalog** | D | D | — | | | | | | | |
| **Inventory** | D | D | D | — | | | | | | |
| **Sales** | D | D | D | D | — | | | | | |
| **Purchasing** | D | D | D | D | D¹ | — | | | | |
| **Invoicing** | D | D | D | E² | D | D | — | | | |
| **Treasury** | D | D | | | | | D | — | | |
| **Accounting** | D | D | | | E³ | E³ | E³ | E³ | — | |
| **Tax** | D | D | | | | | D | | | — |
| **Portal** | D | D | D | D | D⁴ | | D | | | |
| **Projects** | D | D | D | D | D | D | D | | | |
| **HR** | D | D | | | | | | | | |
| **Platform** | D | D | | | | | | | | |

1. Compra desde venta (envío directo de fábrica) — comparte el modelo `Order`.
2. Facturar albarán ajusta stock SOLO vía eventos/Actions de Inventory.
3. **Toda la contabilidad se alimenta por eventos** (R-LED-02): ningún dominio llama a
   Accounting directamente para escribir; Accounting escucha.
4. El portal desemboca en el motor estándar de pedidos de Sales (borrador + notificación).

Una dependencia que no esté en esta matriz **no se puede introducir** sin actualizar este
documento en el mismo PR y validar que no crea ciclos.

## 3. Cadenas de eventos canónicas (vinculaciones)

Las cadenas de efectos entre dominios, con su evento y quién escucha. Todo módulo nuevo que
participe en una cadena añade aquí su fila (playbook §6).

| Evento (emisor) | Listeners (dominio → efecto) |
|---|---|
| `OrderConfirmed` (Sales) | Inventory → reservar stock · Sales → numerar pedido (ya en Action) · Notifications |
| `DeliveryNoteIssued` (Sales) | Inventory → salida de kardex · Portal → notificar cliente |
| `ReceivingCompleted` (Purchasing) | Inventory → entrada kardex · Catalog → recalcular coste real/comercial (portes) |
| `InvoiceRegistered` (Invoicing) | Accounting → LedgerGenerator (asiento venta/compra) · Treasury → `Effect::generateForInvoice` (plazos, días de pago, meses exentos) · Tax → encolar SII/VERI*FACTU |
| `InvoiceRectified` (Invoicing) | Accounting → asiento rectificativo · Inventory → reintegro stock · Tax → SII |
| `CashTicketClosed` (Invoicing) | Accounting → CashSeatService |
| `EffectPaid` / `EffectUnpaid` (Treasury) | Accounting → EffectSeatService (diario DG) · Notifications |
| `RemittanceSettled` (Treasury) | Accounting → RemittanceSeatService · Treasury → estados de efectos |
| `TenantProvisioned` (Platform) | Admin → seeds de series/patrones/roles · Accounting → plan contable base · Inventory → almacén por defecto |
| `ModuleToggled` (Platform/Admin) | Admin → recomputar menú/permisos efectivos |

## 4. Reglas de crecimiento del mapa

1. Dominio nuevo → fila+columna en la matriz, capa asignada, ficha en `docs/modules/`.
2. Prohibidos los ciclos de dependencia directa (`D`); si aparecen, uno de los sentidos se
   convierte en evento (`E`).
3. Si dos dominios se llaman constantemente entre sí, es señal de que la frontera está mal
   trazada: proponer fusión o re-partición vía OpenSpec, no parchear.
4. Los tests de arquitectura ([tooling.md](tooling.md)) verifican mecánicamente lo que
   pueden (imports entre dominios); esta matriz es la fuente de verdad que configuran.
