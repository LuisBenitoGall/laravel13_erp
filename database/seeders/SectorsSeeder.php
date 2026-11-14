<?php

namespace Database\Seeders;

use App\Domains\Admin\Enums\SectorStatus;
use App\Domains\Admin\Models\Sector;
use App\Support\Strings\UniqueSlug;
use Illuminate\Database\Seeder;

/**
 * Sectores NACE Rev. 2 / CNAE secciones A–S (sin T/U), ES/EN/CA. Upsert por slug.
 * NO está en DatabaseSeeder; Index no importa. No borra slugs legacy ajenos a esta lista.
 *
 * Invocación: `php artisan db:seed --class=SectorsSeeder`
 */
class SectorsSeeder extends Seeder
{
    /** @var list<array{slug: string, name: array{es: string, en: string, ca: string}}> */
    private const SECTORS = [
        [
            'slug' => 'agricultura-ganaderia-silvicultura-pesca',
            'name' => [
                'es' => 'Agricultura, ganadería, silvicultura y pesca',
                'en' => 'Agriculture, forestry and fishing',
                'ca' => 'Agricultura, ramaderia, silvicultura i pesca',
            ],
        ],
        [
            'slug' => 'industrias-extractivas',
            'name' => [
                'es' => 'Industrias extractivas',
                'en' => 'Mining and quarrying',
                'ca' => 'Indústries extractives',
            ],
        ],
        [
            'slug' => 'industria-manufacturera',
            'name' => [
                'es' => 'Industria manufacturera',
                'en' => 'Manufacturing',
                'ca' => 'Indústria manufacturera',
            ],
        ],
        [
            'slug' => 'energia-gas-vapor-aire',
            'name' => [
                'es' => 'Suministro de energía eléctrica, gas, vapor y aire acondicionado',
                'en' => 'Electricity, gas, steam and air conditioning supply',
                'ca' => "Subministrament d'energia elèctrica, gas, vapor i aire condicionat",
            ],
        ],
        [
            'slug' => 'agua-saneamiento-residuos',
            'name' => [
                'es' => 'Suministro de agua, saneamiento, gestión de residuos y descontaminación',
                'en' => 'Water supply; sewerage, waste management and remediation activities',
                'ca' => "Subministrament d'aigua; sanejament, gestió de residus i descontaminació",
            ],
        ],
        [
            'slug' => 'construccion',
            'name' => [
                'es' => 'Construcción',
                'en' => 'Construction',
                'ca' => 'Construcció',
            ],
        ],
        [
            'slug' => 'comercio-reparacion-vehiculos',
            'name' => [
                'es' => 'Comercio al por mayor y al por menor; reparación de vehículos de motor y motocicletas',
                'en' => 'Wholesale and retail trade; repair of motor vehicles and motorcycles',
                'ca' => "Comerç a l'engròs i al detall; reparació de vehicles de motor i motocicletes",
            ],
        ],
        [
            'slug' => 'transporte-almacenamiento',
            'name' => [
                'es' => 'Transporte y almacenamiento',
                'en' => 'Transportation and storage',
                'ca' => 'Transport i emmagatzematge',
            ],
        ],
        [
            'slug' => 'hosteleria',
            'name' => [
                'es' => 'Hostelería',
                'en' => 'Accommodation and food service activities',
                'ca' => 'Hostaleria',
            ],
        ],
        [
            'slug' => 'informacion-comunicaciones',
            'name' => [
                'es' => 'Información y comunicaciones',
                'en' => 'Information and communication',
                'ca' => 'Informació i comunicacions',
            ],
        ],
        [
            'slug' => 'finanzas-seguros',
            'name' => [
                'es' => 'Actividades financieras y de seguros',
                'en' => 'Financial and insurance activities',
                'ca' => "Activitats financeres i d'assegurances",
            ],
        ],
        [
            'slug' => 'inmobiliarias',
            'name' => [
                'es' => 'Actividades inmobiliarias',
                'en' => 'Real estate activities',
                'ca' => 'Activitats immobiliàries',
            ],
        ],
        [
            'slug' => 'profesionales-cientificas-tecnicas',
            'name' => [
                'es' => 'Actividades profesionales, científicas y técnicas',
                'en' => 'Professional, scientific and technical activities',
                'ca' => 'Activitats professionals, científiques i tècniques',
            ],
        ],
        [
            'slug' => 'administrativas-servicios-auxiliares',
            'name' => [
                'es' => 'Actividades administrativas y servicios auxiliares',
                'en' => 'Administrative and support service activities',
                'ca' => 'Activitats administratives i serveis auxiliars',
            ],
        ],
        [
            'slug' => 'administracion-publica-defensa',
            'name' => [
                'es' => 'Administración pública y defensa; seguridad social obligatoria',
                'en' => 'Public administration and defence; compulsory social security',
                'ca' => 'Administració pública i defensa; seguretat social obligatòria',
            ],
        ],
        [
            'slug' => 'educacion',
            'name' => [
                'es' => 'Educación',
                'en' => 'Education',
                'ca' => 'Educació',
            ],
        ],
        [
            'slug' => 'sanidad-servicios-sociales',
            'name' => [
                'es' => 'Actividades sanitarias y de servicios sociales',
                'en' => 'Human health and social work activities',
                'ca' => 'Activitats sanitàries i de serveis socials',
            ],
        ],
        [
            'slug' => 'artisticas-recreativas-entretenimiento',
            'name' => [
                'es' => 'Actividades artísticas, recreativas y de entretenimiento',
                'en' => 'Arts, entertainment and recreation',
                'ca' => "Activitats artístiques, recreatives i d'entreteniment",
            ],
        ],
        [
            'slug' => 'otros-servicios',
            'name' => [
                'es' => 'Otros servicios',
                'en' => 'Other service activities',
                'ca' => 'Altres serveis',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::SECTORS as $row) {
            $slug = $row['slug'];
            $existing = Sector::withTrashed()->where('slug', $slug)->first();

            if ($existing !== null) {
                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->forceFill([
                    'name' => $row['name'],
                    'status' => SectorStatus::Active,
                ])->save();

                continue;
            }

            $uniqueSlug = UniqueSlug::from(
                $row['name']['es'],
                fn (string $candidate): bool => Sector::withTrashed()->where('slug', $candidate)->exists(),
            );

            Sector::query()->create([
                'slug' => $slug !== '' ? $slug : $uniqueSlug,
                'name' => $row['name'],
                'status' => SectorStatus::Active,
            ]);
        }

        $this->command?->info('SectorsSeeder: '.count(self::SECTORS).' sectores NACE A–S.');
    }
}
