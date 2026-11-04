<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel Boost is already in require-dev. MCP lives in `.mcp.json` (Laragon `php.exe` + `artisan boost:mcp`). Guidelines/skills are **selective** (no Livewire). Constitution: `docs/standards/kernel.md`. See `docs/standards/tooling.md` § MCP and `.cursor/rules/laravel-boost.mdc`.

```bash
php artisan boost:install --guidelines --skills
php artisan boost:update --no-discover
```

Do not run `boost:install --mcp` here: it would rewrite `.mcp.json` and drop the Laragon PHP path. Enable the server in Cursor MCP Settings.

## Seeders (producción y despliegue)

Tras migrar el esquema, hay que ejecutar los seeders de catálogo / datos maestros que
el ERP necesita. **Al crear un Seeder nuevo, documentarlo en esta sección** (comando
y si entra en `DatabaseSeeder` o solo vía `--class=`).

Orden típico en producción (o entorno limpio):

```bash
php artisan migrate --force

# Catálogo Admin (módulos, functionalities incl. countries, company ACME, roles plantilla…)
# + usuario Super Admin (admin@erp.com). Equivalente a DatabaseSeeder actual.
php artisan db:seed --force

# Geografía Country → Province → Town (datos v1, IDs preservados). NO está en
# DatabaseSeeder: hay que lanzarlo aparte.
php artisan db:seed --class=GeographySeeder --force

# Monedas globales (datos v1, IDs preservados; Active solo EUR/USD/GBP/CHF). NO está en
# DatabaseSeeder: hay que lanzarlo aparte. El Index de Monedas nunca importa.
php artisan db:seed --class=CurrenciesSeeder --force

# Bancos globales (enriched v1, IDs y status preservados). NO está en DatabaseSeeder.
# El Index de Bancos nunca importa.
php artisan db:seed --class=BanksSeeder --force

# Unidades globales (datos v1, IDs y status preservados). NO está en DatabaseSeeder.
# El Index de Unidades nunca importa.
php artisan db:seed --class=UnitsSeeder --force

# Tipos de IVA (v1 → España id=66). Requiere GeographySeeder antes. NO está en
# DatabaseSeeder. El Index nunca importa.
php artisan db:seed --class=VatTypesSeeder --force

# Grupos contables PGC (i18n fiscal.*, locale es). delete()+reset AI + insert.
# NO está en DatabaseSeeder.
php artisan db:seed --class=AccountingAccountTypesSeeder --force
```

Comandos sueltos (idempotentes o re-ejecutables según cada seeder):

| Seeder | Comando | Notas |
|---|---|---|
| `AdminCatalogSeeder` | `php artisan db:seed --class=AdminCatalogSeeder --force` | Incluido en `db:seed`. Crea/actualiza functionalities (p. ej. menú Países). |
| `UserSeeder` | `php artisan db:seed --class=UserSeeder --force` | Incluido en `db:seed`. Requiere company id=1 (sale de AdminCatalog). |
| `GeographySeeder` | `php artisan db:seed --class=GeographySeeder --force` | **Fuera** de `DatabaseSeeder`. Upsert por `id` desde `database/data/geography/*.json`. No re-sembrar a la ligera si ya hay FKs consumidoras. |
| `CurrenciesSeeder` | `php artisan db:seed --class=CurrenciesSeeder --force` | **Fuera** de `DatabaseSeeder`. Upsert por `id` desde `database/data/currencies/currencies.json`. Active: EUR, USD, GBP, CHF; resto Inactive. Símbolo null → mapa v1 (EUR/USD/AUD/CAD). Si falta el JSON avisa y se omite. No re-sembrar a la ligera si ya hay FKs consumidoras. |
| `BanksSeeder` | `php artisan db:seed --class=BanksSeeder --force` | **Fuera** de `DatabaseSeeder`. Upsert por `id` desde `database/data/banks/banks.enriched.json`. Status del JSON (1→active). Si falta el JSON avisa y se omite. No re-sembrar a la ligera si ya hay FKs consumidoras. |
| `UnitsSeeder` | `php artisan db:seed --class=UnitsSeeder --force` | **Fuera** de `DatabaseSeeder`. Upsert por `id` desde `database/data/units/units.json`. Status del JSON (1→active). Sin `translations`. Si falta el JSON avisa y se omite. |
| `VatTypesSeeder` | `php artisan db:seed --class=VatTypesSeeder --force` | **Fuera** de `DatabaseSeeder`. Upsert por `id` desde `database/data/vat-types/iva-types.json` con `country_id=66` (España). **Requiere** `GeographySeeder` previo. Si falta España, falla con error claro. |
| `AccountingAccountTypesSeeder` | `php artisan db:seed --class=AccountingAccountTypesSeeder --force` | **Fuera** de `DatabaseSeeder`. PGC España portado de v1; nombres vía `lang/*/fiscal.php` con `App::setLocale('es')`. `delete()` + reset AUTO_INCREMENT + insert (entorno limpio). |

Desarrollo local: mismos comandos sin `--force` si preferís confirmación interactiva.
Tras añadir una Functionality solo en código, re-ejecutar `AdminCatalogSeeder` (o
`db:seed`) en la BD existente; si no, el menú no verá el ítem.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
