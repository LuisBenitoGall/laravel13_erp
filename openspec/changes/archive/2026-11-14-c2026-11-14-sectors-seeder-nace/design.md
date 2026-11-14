# Design

## Context

See proposal.md — Why. `SectorsSeeder` upserta 8 slugs genéricos.
Locales: `es`/`en`/`ca`. CRUD Sectores ya en producción del change
archivado `admin-sectors-catalog`.

## Goals / Non-Goals

**Goals:** lista NACE A–S (19); ES/EN/CA; upsert; ejecutar seed.

**Non-Goals:** CRUD; T/U; divisiones; borrar slugs legacy; Brand.

**E2E:** no aplica.

**Excepciones a standards:** ninguna.

## Decisions

1. **Fuente**  
   NACE Rev. 2 / CNAE-2009 **secciones** A–S (INE/Idescat). Sin T/U.

2. **Inventario fijo** (slug + labels oficiales adaptados):

| slug | es | en | ca |
|---|---|---|---|
| agricultura-ganaderia-silvicultura-pesca | Agricultura, ganadería, silvicultura y pesca | Agriculture, forestry and fishing | Agricultura, ramaderia, silvicultura i pesca |
| industrias-extractivas | Industrias extractivas | Mining and quarrying | Indústries extractives |
| industria-manufacturera | Industria manufacturera | Manufacturing | Indústria manufacturera |
| energia-gas-vapor-aire | Suministro de energía eléctrica, gas, vapor y aire acondicionado | Electricity, gas, steam and air conditioning supply | Subministrament d’energia elèctrica, gas, vapor i aire condicionat |
| agua-saneamiento-residuos | Suministro de agua, saneamiento, gestión de residuos y descontaminación | Water supply; sewerage, waste management and remediation activities | Subministrament d’aigua; sanejament, gestió de residus i descontaminació |
| construccion | Construcción | Construction | Construcció |
| comercio-reparacion-vehiculos | Comercio al por mayor y al por menor; reparación de vehículos de motor y motocicletas | Wholesale and retail trade; repair of motor vehicles and motorcycles | Comerç a l’engròs i al detall; reparació de vehicles de motor i motocicletes |
| transporte-almacenamiento | Transporte y almacenamiento | Transportation and storage | Transport i emmagatzematge |
| hosteleria | Hostelería | Accommodation and food service activities | Hostaleria |
| informacion-comunicaciones | Información y comunicaciones | Information and communication | Informació i comunicacions |
| finanzas-seguros | Actividades financieras y de seguros | Financial and insurance activities | Activitats financeres i d’assegurances |
| inmobiliarias | Actividades inmobiliarias | Real estate activities | Activitats immobiliàries |
| profesionales-cientificas-tecnicas | Actividades profesionales, científicas y técnicas | Professional, scientific and technical activities | Activitats professionals, científiques i tècniques |
| administrativas-servicios-auxiliares | Actividades administrativas y servicios auxiliares | Administrative and support service activities | Activitats administratives i serveis auxiliars |
| administracion-publica-defensa | Administración pública y defensa; seguridad social obligatoria | Public administration and defence; compulsory social security | Administració pública i defensa; seguretat social obligatòria |
| educacion | Educación | Education | Educació |
| sanidad-servicios-sociales | Actividades sanitarias y de servicios sociales | Human health and social work activities | Activitats sanitàries i de serveis socials |
| artisticas-recreativas-entretenimiento | Actividades artísticas, recreativas y de entretenimiento | Arts, entertainment and recreation | Activitats artístiques, recreatives i d’entreteniment |
| otros-servicios | Otros servicios | Other service activities | Altres serveis |

3. **Legacy slugs** (`comercio`, `tecnologia`, `servicios`, …)  
   **No borrar ni Inactive** en este change (brief). Quedan filas huérfanas
   si ya se sembraron; el seeder solo upserta la lista NACE.  
   *Alt. descartada:* marcar Inactive automáticamente.

4. **Mecánica**  
   Conservar upsert actual: por slug fijo de la constante; restore;
   forceFill name+Active; create si no existe (slug de la lista, no
   regenerar salvo vacío).

5. **Apply**  
   Tras editar el seeder: `php artisan db:seed --class=SectorsSeeder`
   (php Laragon). Test Feature opcional: count ≥19 / slugs presentes.

## Risks / Trade-offs

- [Filas legacy + NACE] → listado con más de 19 hasta limpieza manual.
  Aceptado.  
- [Pivotes a slugs viejos] → no se tocan; attribution sigue válida.

## Migration Plan

1. Reescribir constante `SECTORS` en `SectorsSeeder`.  
2. Seed + verificar count/slugs.  
Rollback: revertir fichero seeder (datos BD quedan).

## Open Questions

Ninguna.
