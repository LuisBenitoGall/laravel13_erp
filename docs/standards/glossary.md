# Glosario normativo ES ↔ EN

> Documento normativo. El código va **en inglés** con estas traducciones fijas: no se
> improvisan sinónimos (si un término no está, se añade aquí ANTES de usarlo en código).
> Columna "v1" = nombre en el proyecto viejo, para orientar la migración de datos y la
> lectura del código de referencia.

## Documentos y ciclo de venta/compra

| Español (negocio/UI) | Código v2 | v1 | Notas |
|---|---|---|---|
| Presupuesto | `Budget` | Order (`is_budget`) | Rol del modelo `Order` unificado |
| Pedido (venta) | `SalesOrder` (scope) | Order (`is_order`) | Scope `salesOrders()` sobre `Order` |
| Pedido de compra | `PurchaseOrder` (scope) | Order (`purchase_num`) | Scope `purchaseOrders()` |
| Agrupación (de pedidos) | `OrderGroup` | Order (`is_group`) | |
| Albarán | `DeliveryNote` | Delivery | |
| Recepción (de compra) | `Receiving` | Receiving | |
| Factura | `Invoice` | Invoice | |
| Factura agrupada | `GroupedInvoice` | GroupedInvoice | |
| Abono | `Refund` | refund | Se mantiene el término v1 |
| Factura rectificativa | `CorrectiveInvoice` | rectificativa | |
| Ticket de caja | `CashTicket` | Cash | |
| Arqueo de caja | `CashBalance` | CashBalance | |
| Línea (de documento) | `Item` / `<Doc>Line` | Item | `Item` en Order (continuidad v1); `InvoiceLine` etc. en el resto |
| Serie (de factura) | `InvoiceSeries` | InvoiceSerie | Corrige el singular/plural de v1 |
| Patrón de numeración | `NumberingPattern` | *Pattern | JSON, nunca serialize (R-BD-04) |
| Previsión (facturación) | `Forecast` | OrderPrevisions | |

## Tesorería y contabilidad

| Español | Código v2 | v1 | Notas |
|---|---|---|---|
| Efecto (vencimiento) | `Effect` | Effect | Se conserva el término v1 (establecido) |
| Remesa | `Remittance` | Remittance | |
| Impagado | `UnpaidEffect` / estado `Unpaid` | impagado | R-codes SEPA |
| Mandato (SEPA) | `Mandate` | mandato | FRST/RCUR |
| Asiento contable | `Seat` | Seat | Se conserva (todo el diseño ledger de v1 gira en torno a él) |
| Apunte (línea de asiento) | `SeatLine` | — | |
| Cuenta contable | `AccountingAccount` | AccountingAccount | |
| Plan contable | `ChartOfAccounts` | — | |
| Amortización | `Amortization` | Amortization | |
| Pagas y señales | `Deposit` | pagas y señales | Anticipos descontados del efecto |
| Diario (contable) | `Journal` | diario (DG…) | |

## Terceros, catálogo y stock

| Español | Código v2 | v1 | Notas |
|---|---|---|---|
| Empresa | `Company` | Company | Tenant Y tercero a la vez (vision §4.5) |
| Cliente / Proveedor | `Customer` / `Provider` | customer/provider | Roles de la relación `CustomerProvider` |
| Relación cliente-proveedor | `CustomerProvider` | CustomerProviders | Singular en v2 |
| Obra | `Worksite` | Obra | Decisión de naming 2026-07-14 |
| UTE | `JointVenture` | UTE | UI muestra "UTE" |
| Contacto | `Contact` | Contact | |
| NIF (histórico) | `CompanyIdentifier` + `NifResolver` | ídem | Se porta tal cual |
| Producto / Artículo | `Product` | Product | UI: "artículo" |
| Tarifa | `Rate` | Rate | 5 tarifas venta + tipológicas A/B/C |
| Precio (histórico) | `ProductPrice` | ProductPrices | Singular en v2 |
| Familia / Categoría | `Family` / `Category` | ídem | |
| Almacén | `Store` | Store | Continuidad v1 (no `Warehouse`) |
| Kardex / movimiento stock | `Stock` (movimiento) | Stock | Histórico inmutable, sin soft delete |
| Stock consolidado | `StockTotal` | StockTotal | |
| Reserva de stock | `StockReserve` | StockReserve | |
| Rotura de stock | `StockOut` | rotura | |
| Lote / Nº de serie | `Lot` / `SerialNumber` | ídem | |
| Catálogo de proveedor | `ProductProvider` | ProductProviders | |

## Fiscal, plataforma y otros

| Español | Código v2 | v1 | Notas |
|---|---|---|---|
| IVA | `Vat` / `VatType` | IVA | `vat_rate`, `vat_amount` |
| Recargo de equivalencia | `EquivalenceSurcharge` | — | |
| IRPF (retención) | `IrpfWithholding` | IRPF | Término legal español, se conserva IRPF |
| Ámbito (fiscal) | `TaxScope` | ámbito | nacional/intracom./extracom. |
| SII | `Sii*` (`SiiCredential`…) | ídem | Nombre oficial AEAT |
| VERI*FACTU | `Verifactu*` | — | Nuevo en v2 |
| Módulo (activable) | `Module` | Module | Planes SaaS = módulos |
| Plan (suscripción) | `Plan` | — | Platform |
| Tenant / suscriptor | `Tenant` | — | Empresa que contrata el SaaS |
| Empleado / Fichaje | `Employee` / `TimeEntry` | Employee/fichaje | |
| Orden de trabajo | `WorkOrder` | OT | |
| Incidencia | `Issue` | incidencia | |
| Tarea | `Task` | Task | |
| Importe en letras | `MoneyToWordsEs` | ídem | Se porta tal cual |

## Reglas del glosario

1. **El código nunca mezcla**: si el glosario dice `Worksite`, no existe ningún `Obra` en
   código v2 (ni variables, ni rutas, ni claves i18n: `partners.worksites.*`).
2. La **UI siempre en español** (y ca/en) vía i18n: el usuario ve "Obra", "Efecto",
   "Remesa" — nunca los identificadores ingleses.
3. Términos legales españoles sin traducción útil se conservan como identificador
   (`Irpf`, `Sii`, `Verifactu`, `Nif`): traducirlos crearía más confusión que valor.
4. Continuidad v1 deliberada en términos ya asentados (`Effect`, `Seat`, `Store`, `Item`):
   facilita leer el código de referencia y la migración de datos. No se "corrigen" a
   inglés más puro (`JournalEntry`, `Warehouse`) — decisión cerrada 2026-07-14.
