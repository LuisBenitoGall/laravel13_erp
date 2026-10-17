---
name: v1-reference
description: >-
  Cómo consultar el ERP v1 (Viuda Vila / laravel8) como referencia funcional de
  solo lectura. Usar al portar reglas de negocio, comparar pantallas, inventariar
  entidades, o cuando el usuario mencione v1, Viuda Vila, o laravel8_erp.
---

# Referencia v1 (solo lectura)

Ruta: `C:\laragon\www\laravel8_erp_viudavila_github`.

Sirve para **entender** qué hace el negocio hoy. No es plantilla de código v2.

## Qué sí copiar (concepto, no código)

- Flujos (presupuesto → pedido → albarán → factura, remesas, kardex…).
- Campos y reglas que el usuario o el spec identifiquen.
- Inventario: `info_modulos.txt`, `openspec/inventory/`, modelos Eloquent.

## Qué no copiar nunca

| v1 | v2 |
|---|---|
| Livewire + Blade + jQuery + Bootstrap/Inspinia | Inertia v3 + React 19 + Tailwind 4 |
| yajra DataTables | `<ServerTable>` (Tabulator) |
| `session('currentCompany')` disperso | `CurrentCompany` |
| Middleware `*Get/*Post` | Policy + permiso Functionality |
| Menús `basic-menu.json` / `secondary-menu.json` | `MenuBuilder` + 3 capas |
| Permisos `index/show/search/edit/update/destroy` | `viewAny/view/create/update/delete` |
| `serialize()` en columnas | JSON + casts |
| MySQL sin strict / `ENUM` de columna | strict ON + string + enum PHP |
| Empresa preferente (series 09/00/06/04) | seed de ejemplo, no semántica |

## Protocolo

1. Localizar el equivalente v1 (modelo, controller, pantalla).
2. Resumir la **regla de negocio**, no el código.
3. Solo entra en v2 si está en kernel, architecture, vision o en el spec/design
   del change. Si no: anotar A/B, no implementar “como v1”.
4. No abrir PRs contra el repo v1. No mezclar namespaces.

El stack y las invariantes las manda `docs/standards/kernel.md`, no v1.
