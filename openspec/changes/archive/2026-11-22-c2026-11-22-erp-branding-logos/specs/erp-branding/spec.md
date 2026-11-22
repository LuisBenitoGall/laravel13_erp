# Spec Delta

## Purpose

Identidad visual del producto Coopmercio: assets públicos de marca, favicon/manifest
en el documento HTML, nombre de aplicación, URLs de logo ampliado y símbolo, y
tokens de color de marca (`#10A37F` / `#633D56`) del ERP.

## ADDED Requirements

### Requirement: Nombre de marca Coopmercio
El sistema SHALL usar «Coopmercio» como nombre de producto por defecto
(`APP_NAME` / configuración de aplicación) de modo que el título del documento
HTML refleje esa marca.

#### Scenario: Título de documento
- **WHEN** un visitante carga cualquier página Inertia del ERP
- **THEN** el título del documento incluye el nombre Coopmercio (salvo override
  explícito de entorno)

### Requirement: Favicon y manifest públicos
El sistema SHALL servir en la raíz pública el set de favicon Coopmercio
(SVG, ICO, PNG 16/32/48/192/512, apple-touch-icon) y un `site.webmanifest`
con name/short_name Coopmercio e iconos 192/512. El documento HTML raíz
SHALL declarar los links `rel=icon` (SVG e ICO), `apple-touch-icon` y
`manifest`.

#### Scenario: Links en el head
- **WHEN** se renderiza el shell Blade Inertia
- **THEN** el head incluye favicon SVG, favicon ICO, apple-touch-icon y
  site.webmanifest apuntando a paths públicos existentes

#### Scenario: Manifest Coopmercio
- **WHEN** se solicita `/site.webmanifest`
- **THEN** la respuesta declara name y short_name Coopmercio e iconos
  192 y 512

### Requirement: Assets de logo ERP en público
El sistema SHALL publicar bajo `/images/branding/` al menos el wordmark
horizontal color y el símbolo color de Coopmercio (SVG), importados del kit
de identidad del producto. SHALL NOT depender del placeholder legacy
`/images/erp-logo.svg` como default.

#### Scenario: Wordmark y símbolo accesibles
- **WHEN** un cliente solicita las URLs por defecto de logo ampliado y de marca
- **THEN** ambas responden 200 con el asset Coopmercio correspondiente

### Requirement: Shared props de branding
El sistema SHALL compartir con Inertia un objeto `branding` con
`name`, `logo_url` (wordmark ampliado), `mark_url` (símbolo) y los hex de
marca primary/secondary, configurables por entorno con defaults a los assets
y colores Coopmercio.

#### Scenario: Props compartidas
- **WHEN** una respuesta Inertia incluye las props compartidas
- **THEN** `branding.logo_url` y `branding.mark_url` apuntan a los defaults
  de marca (o a overrides `APP_LOGO_URL` / `APP_MARK_URL` si existen) y los
  colores primary/secondary reflejan `#10A37F` y `#633D56` salvo override

### Requirement: Tokens de color de marca Coopmercio
El sistema SHALL definir en el tema CSS del front tokens de marca con primary
`#10A37F` y secondary `#633D56` (más hover/muted/ink derivados). Los acentos
de producto del shell, formularios, componentes UI y auth que hoy usan
`teal-*` o `#0f766e` SHALL usar esos tokens. SHALL NOT alterar colores
semánticos de error/warning ni el `color` por Module del menú.

#### Scenario: Primary y secondary en tema
- **WHEN** se inspecciona el tema CSS compilado o fuente `@theme`
- **THEN** existen tokens de marca con primary `#10A37F` y secondary `#633D56`

#### Scenario: Acentos de producto usan brand
- **WHEN** se renderiza UI de producto con acento (botón primary, focus ring,
  link de marca, panel auth)
- **THEN** el color de acento corresponde al primary de marca, no al teal
  genérico previo (`#0f766e` / `teal-700` de Tailwind por defecto)

#### Scenario: Secondary disponible
- **WHEN** un componente necesita el acento secundario de marca
- **THEN** puede usar el token secondary `#633D56` (o su utilidad Tailwind
  equivalente)
