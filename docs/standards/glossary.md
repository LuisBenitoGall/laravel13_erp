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
| Cuenta (SaaS / plantilla) | `Account` | Account | Catálogo global Admin (sin `company_id`); Functionality `accounts`; ≠ Module `cuenta`, ≠ AccountingAccount |
| Cuenta de empresa (SaaS) | `CompanyAccount` | company_accounts | Vínculo tenant↔Account; ≤1 Active; BelongsToCompany; renovación en Mi cuenta |
| Cuenta contable | `AccountingAccount` | AccountingAccount | |
| Plan contable | `ChartOfAccounts` | — | |
| Amortización | `Amortization` | Amortization | |
| Pagas y señales | `Deposit` | pagas y señales | Anticipos descontados del efecto |
| Diario (contable) | `Journal` | diario (DG…) | |

## Terceros, catálogo y stock

| Español | Código v2 | v1 | Notas |
|---|---|---|---|
| Empresa | `Company` | Company | Tenant Y tercero a la vez (vision §4.5); jerarquía opcional `parent_id` (Admin) |
| Delegación | `Company` con `parent_id` ≠ null (Functionality `delegations`) | — | Company hija de la matriz de sesión (sociedad con NIF); alta propia sin `company_accounts`, ≥1 `Workplace` de la matriz asociado (pivote), creador en `user_companies`; **no** es CurrentCompany ni sale en el switcher (solo matrices); ≠ dueña de sedes ni jerarquía genérica de Mis empresas |
| Extracto (empresa) | `excerpt` | — | Texto breve multi-idioma (JSON Spatie); ≤1000/locale; Edit Info |
| Descripción (empresa) | `description` | — | HTML rico multi-idioma (JSON Spatie + Wysiwyg); ≤20000/locale; Edit Info |
| Directorio | `company-directory` | Directorio | Descubrimiento de companies Active+NIF+≥1 sector (Module `empresas`); ≠ Mis empresas |
| Cliente / Proveedor | `Customer` / `Provider` | customer/provider | Roles de la relación `CustomerProvider` |
| Relación cliente-proveedor | `CustomerProvider` | CustomerProviders | Singular en v2 |
| Favorito (vista) | `Preference` | Favorito | Acceso rápido a un path del ERP (name+url); scope user+company de sesión; tope 20; CTA dashboard con color Module; ≠ favoritos de producto |
| Nota interna | `Note` | Note | Post-it personal del user (`title`+`note`); **sin** `company_id`; tope 50/user; modal topbar + dashboard; ≠ Albarán (`DeliveryNote`) |
| Contenido | `Content` | Content | Pieza CMS ligera Admin; kinds Help (UI «Información») y Article (UI «Contenido»); ancla `code`; `company_id` nullable (alta = null); ≠ `Note` ni WordPress |
| Información (contenido) | `ContentKind::Help` | type=1 | Tip/ancla HelpTip; Create elige este tipo |
| Contenido (kind) | `ContentKind::Article` | type=2 | Pieza editorial; Edit real = change futuro |
| Ancla (contenido) | `code` | code | Identificador estable HelpTip; convención `{helpScope}.{fieldKey}` (ej. `companies.nif`). Create Help: code **explícito** (sin slugify del nombre; admite `.` y `-`); Create Article: auto-slug desde nombre; inmutable en Edit; `helpCode` en Field = override del derive |
| Configuración de la empresa | hub `company-settings` | Company settings | Hub/onboarding de la company en sesión (Functionality bajo `empresas`); readiness = nif+logo+≥1 workplace Active; ≠ Configuración SA |
| Centro de trabajo | `Workplace` | Workplace | Sede/dirección operativa; en el grupo societario `company_id` = **matriz** (`BelongsToCompany`); se asocia a Delegaciones solo vía mapa sede–delegación; ≠ Obra (`Worksite`) ni `Workspace` |
| Principal (centro) | `featured` | — | Flag `Workplace.featured`; UI «Principal»; ≤1/company; dirección default facturación/pedidos (consumo = changes futuros) |
| Mapa sede–delegación | pivote `workplace_delegation` (`Workplace::delegations()` / `Company::workplacesViaMap()`) | — | Única relación N:N entre `Workplace` de la matriz y Delegaciones hijas; se gestiona en Edit Workplace con `workplaces.update`; `company_id` del pivote = delegación (sin `BelongsToCompany` en el pivote) |
| Delegación preferida (de sede) | `workplace_delegation.is_preferred` | — | ≤1 por `Workplace` entre las vinculadas; opcional (0 es válido); UI «Delegación preferida»; default de sociedad por sede (consumo = changes futuros, p. ej. pedidos); no existe preferida global por delegación |
| Obra | `Worksite` | Obra | Decisión de naming 2026-07-14; ≠ Centro de trabajo (`Workplace`) |
| UTE | `JointVenture` | UTE | UI muestra "UTE" |
| Contacto | `Contact` | Contact | |
| Teléfono | `Phone` | Phone | Tabla polimórfica `phones` (`phoneable`); UI ficha User en tab contactos |
| Galería (activo) | `MediaAsset` | Galería | Imagen reutilizable por URL; `media_assets`; `company_id` nullable; Functionality `gallery`; ≠ `UserImage` ni thumbs WP |
| Selector de Galería | `MediaPicker` | — | `Form/MediaPicker`; diálogo reutilizable (búsqueda + scroll infinito vía `admin.gallery.picker`); UI «Elegir imagen…»; ≠ Index Galería |
| Imagen de usuario | `UserImage` | — | `user_images`; `featured` = avatar; `public` solo con `User.is_public` |
| Documento de usuario | `UserDoc` | — | `user_docs`; personal del User (no company); disco privado; share = change futuro |
| Vínculo user–empresa | `UserCompany` / `user_companies` | — | Membresía/acceso ERP; `position`/`department` nullable en pivot; ≠ Empleo |
| Empleo / periodo laboral | `Employee` | Employee | Periodo laboral RRHH (`employees`); `BelongsToCompany`; ≠ `UserCompany`; historial = N filas; ≤1 `active` por user+company |
| Tipo de contrato laboral | `ContractType` | — | Catálogo por company (`contract_types`); duration en días; ≠ periodo `Employee` |
| Apellidos | `surname` | surname | Campo de `User`; UI «Apellidos»; obligatorio en registro (columna nullable en BD) |
| Perfil público | `is_public` | — | Flag de `User`; visible en directorio «Todos los usuarios» solo si true; default true en Create company-users |
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
| Módulo (activable) | `Module` | Module | Planes SaaS = módulos. Catálogo global (sin `company_id`); gate de plan vía `CompanyModules` |
| Funcionalidad | `Functionality` | Functionality | **No confundir con `Module`**: es la unidad de permiso y de entrada de submenú dentro de un módulo (R-AUT-07). Catálogo global también |
| Permiso (Spatie) | `Permission` (Spatie; name `{functionalitySlug}.{acción}`) | Permission | Catálogo global guard `web`; canónicos 5+`{module}.access` vía CreateFunctionality; custom vía Admin Permissions; vínculo a Functionality por prefijo, sin FK |
| País | `Country` | País | Catálogo global geografía (Admin); sin `company_id` |
| Banco | `Bank` | Banco | Catálogo global Admin (sin `company_id`); Functionality `banks` bajo menú Configuración |
| Unidad | `Unit` | Unidad | Catálogo global Admin (sin `company_id`); name único; symbol max 4; Functionality `units`; Catalog consume a futuro |
| Sector | `Sector` | Sector | Catálogo global Admin (sin `company_id`); name traducible JSON; Functionality `sectors`; N:N Company↔Sector |
| Método de pago | `PaymentMethod` | Método de pago | Catálogo global Admin (sin `company_id`); name traducible JSON; Functionality `payment-methods`; selección por empresa más adelante |
| Tipo de IVA | `VatType` | Tipo de IVA | Catálogo global Admin por `Country` (sin `company_id`); `rate` + RE nullable; Functionality `vat-types`; Tax consume a futuro |
| Grupo contable | `AccountingAccountType` | Grupo contable | Catálogo jerárquico PGC Admin (sin `company_id`/status); `autoreference`/`code`/`name`/`mode`; Functionality `accounting-account-types`; Accounting consume a futuro |
| Moneda | `Currency` | Moneda | Catálogo global Admin (sin `company_id`); `code` ISO 4217 y `number` inmutables tras el alta; Functionality `currencies` |
| Cotización / tipo de cambio | `ExchangeRate` | Conversión monedas | Catálogo global Admin (sin `company_id`); par dirigido `from`→`to`, `rate` 6 dec, `rate_date`; vigente = última ≤ D; Functionality `exchange-rates` |
| Provincia | `Province` | Provincia | Hija de `Country`; catálogo global |
| Población | `Town` | Población | Hija de `Province`; código `Town` como v1 (no Municipality) |
| Rol manager (empresa) | `manager` (Spatie Role + `company_id`) | `{id}/manager` | Máximo dentro de la empresa; UI «Manager»; plantilla universal `company_id` NULL |
| Rol guest / invitado (empresa) | `guest` (Spatie Role + `company_id`) | `{id}/basico` | Nace sin permisos; UI «Invitado»; plantilla universal `company_id` NULL |
| Rol custom (empresa) | Spatie Role `company_id` = company, `universal=false`, nombre ≠ manager\|guest | — | Creado por el manager del tenant; matriz acotada a módulos autorizados∩activos |
| Rol universal | Spatie Role `universal=true`, `company_id` NULL | `universal` | Plantilla para tenants; se materializa por company al alta |
| Rol de plataforma (genérico) | Spatie Role `universal=false`, `company_id` NULL | — | Solo plataforma; no usable por tenants |
| Super Admin | `Super Admin` (`company_id` NULL) | Super Admin | Rol plataforma inmutable; bypass Gate::before |
| Plan (suscripción) | `Plan` | — | Platform |
| Tenant / suscriptor | `Tenant` | — | Empresa que contrata el SaaS |
| Empleado (periodo) / Fichaje | `Employee` / `TimeEntry` | Employee/fichaje | Ver fila Empleo; fichaje = oleadas posteriores |
| Orden de trabajo | `WorkOrder` | OT | |
| Incidencia | `Issue` | incidencia | |
| Tarea | `Task` | Task | |
| Importe en letras | `MoneyToWordsEs` | ídem | Se porta tal cual |

## Reglas del glosario

1. **El código nunca mezcla**: si el glosario dice `Worksite`, no existe ningún `Obra` en
   código v2 (ni variables, ni rutas). Las **claves i18n** son índices UI en **castellano
   snake_case** (`obra`, `efecto`, `nombre`), no el schema jerárquico inglés
   (`admin.worksites.*`); genéricos reutilizables; familias con prefijo `_`.
2. La **UI siempre en español** (y ca/en) vía i18n: el usuario ve "Obra", "Efecto",
   "Remesa" — nunca los identificadores ingleses del dominio.
3. Términos legales españoles sin traducción útil se conservan como identificador
   (`Irpf`, `Sii`, `Verifactu`, `Nif`): traducirlos crearía más confusión que valor.
4. Continuidad v1 deliberada en términos ya asentados (`Effect`, `Seat`, `Store`, `Item`):
   facilita leer el código de referencia y la migración de datos. No se "corrigen" a
   inglés más puro (`JournalEntry`, `Warehouse`) — decisión cerrada 2026-07-14.
