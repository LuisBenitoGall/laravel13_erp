<?php

namespace Database\Seeders;

use App\Domains\Admin\Models\AccountingAccountType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * Grupos contables PGC España (portado de v1).
 *
 * Nombres vía claves `fiscal.*` con locale `es` canónico.
 * delete() + reset AUTO_INCREMENT + insert (entorno limpio). NO está en DatabaseSeeder.
 *
 * Invocación: `php artisan db:seed --class=AccountingAccountTypesSeeder`
 */
class AccountingAccountTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Clasificación obtenida del Plan General de Contabilidad: https://www.plangeneralcontable.com/?tit=listado-de-contenidos&name=GeTia&contentId=mod_list&contGroupId=ctg_25
     */
    public function run(): void
    {
        App::setLocale('es');

        DB::table('accounting_account_types')->delete();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `accounting_account_types` AUTO_INCREMENT = 1');
        }

        $data = [
            1 => [
                'name' => ucfirst(__('fiscal.financiacion_basica')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.capital')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.capital_social')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.fondo_social')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.capital')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.socios_desembolsos_no_exigidos')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.socios_desembolsos_no_exigidos_capital_social')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.socios_desembolsos_no_exigidos_pendiente_inscripcion')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.socios_aportaciones_no_dinerarias_pendientes')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.socios_aportaciones_no_dinerarias_pendientes_capital_social')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.socios_aportaciones_no_dinerarias_pendientes_pendiente_inscripcion')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.acciones_participaciones_propias_situaciones_especiales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.acciones_participaciones_propias_reduccion_capital')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.reservas')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.prima_emision_asuncion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.patrimonio_neto_emision_instrumentos_financieros_compuestos')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.patrimonio_neto_emision_instrumentos_financieros_compuestos')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.resto_instrumentos_patrimonio_neto')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.reserva_legal')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.reservas_voluntarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.reservas_especiales')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.reservas_acciones_participaciones_sociedad_dominante')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.reservas_estatutarias')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.reserva_capital_amortizado')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.reserva_fondo_comercio')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.reserva_acciones_propias')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.reserva_perdidas_ganancias_actuariales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.aportaciones_socios_propietarios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.diferencias_ajuste_capital_euros')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.resultado_pendientes_aplicacion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.remanente')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.resultados_negativos_ejercicios_anteriores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.resultados_ejercicio')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.subvenciones_donaciones_ajustes_cambio_valor')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.subvenciones_oficiales_capital')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.donaciones_legados_capital')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.otras_subvenciones_donaciones_legados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.ajustes_valoracion_instrumentos_financieros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.operaciones_cobertura')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.cobertura_flujos_efectivo')), 'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.cobertura_inversion_neta_negocio_extranjero')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.diferencias_conversion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.ajustes_valoracion_activos_no_corrientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.ingresos_fiscales_varios_ejercicios')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.ingresos_fiscales_diferencias_permanentes')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.ingresos_fiscales_deducciones_bonificaciones')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.provisiones')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.provision_retribuciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.provision_impuestos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.provision_responsabilidades')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.provision_desmantelamiento')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.provision_actuaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.provision_reestructuraciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.provisiones_transacciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.deudas_largo_plazo')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.acciones_largo_plazo_pasivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.desembolsos_no_exigidos_participaciones_pasivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.aportaciones_participaciones_pasivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.deudas_largo_plazo_vinculadas')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deudas_largo_plazo_entidades_vinculadas')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.proveedores_inmovilizado_largo_plazo_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.acreedores_arrendamiento_financiero_largo_plazo_vinculadas')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.otras_deudas_largo_plazo_vinculadas')),
                                'group' => false,
                                'level4' => [
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.otras_deudas_grupo')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.otras_deudas_asociadas')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.otras_deudas_vinculadas')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '7' => [
                        'name' => ucfirst(__('fiscal.deudas_largo_plazo_recibidos')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deudas_entidades_credito')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.deudas_a_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.deudas_transformables_subvenciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.proveedores_inmovilizado_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.acreedores_arrendamiento_financiero_largo_plazo')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.efectos_pagar_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.pasivos_derivados_financieros_largo_plazo')),
                                'group' => false,
                                'level4' => [
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.pasivos_derivados_financieros_largo_plazo_cartera_negociación')),
                                        'group' => false,
                                    ],
                                    '8' => [
                                        'name' => ucfirst(__('fiscal.pasivos_derivados_financieros_largo_plazo_instrumentos_cobertura')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.obligaciones_bonos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.obligaciones_bonos_convertibles')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.deudas_representadas_valores_negociables')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '8' => [
                        'name' => ucfirst(__('fiscal.fianzas_depositos_largo_plazo')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.fianzas_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.anticipos_ventas_prestaciones_servicios_largo_plazo')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.depositos_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.garantias_financieras_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.situaciones_transitorias_financiacion')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.acciones_participaciones_emitidas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.suscriptores_acciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.capital_pendiente_inscripcion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.acciones_participaciones_pasivos_financieros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.suscriptores_pasivos_financieros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.acciones_pasivos_financieros_pendientes_inscripcion')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            2 => [
                'name' => ucfirst(__('fiscal.inmovilizado')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.inmovilizaciones_intangibles')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.gastos_investigacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.desarrollo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.concesiones_administrativas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.propiedad_industrial')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.fondo_comercio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.derechos_traspaso')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.aplicaciones_informaticas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.anticipos_inmovilizaciones_intangibles')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.inmovilizaciones_materiales')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.terrenos_bienes_naturales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.construcciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.instalaciones_tecnicas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.maquinaria')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.utillaje')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.otras_instalaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.mobiliario')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.equipos_procesos_informacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.elementos_transporte')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otro_inmovilizado_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.inversiones_inmobiliarias')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.inversiones_terrenos_bienes_naturales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.inversiones_construcciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.inmovilizaciones_materiales_curso')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.adaptacion_terrenos_bienes_naturales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.construcciones_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.instalaciones_tecnicas_montaje')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.maquinaria_montaje')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.equipos_procesos_informacion_montaje')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.anticipos_inmovilizaciones_materiales')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.inversiones_financieras_largo_plazo_vinculadas')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.participaciones_largo_plazo_vinculadas')),
                                'group' => false,
                                'level4' => [
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.participacion_largo_plazo_empresas_grupo')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.participacion_largo_plazo_empresas_asociadas')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.participaciones_largo_plazo_otras_vinculadas')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.valores_representativos_largo_plazo_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.creditos_largo_plazo_partes_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.desembolsos_pendientes_participaciones_largo_plazo_vinculadas')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.otras_inversiones_financieras_largo_plazo')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.inversiones_financieras_largo_plazo_instrumentos_patrimonio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.valores_representativos_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.creditos_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.creditos_largo_plazo_enajenacion_inmovilizado')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.creditos_largo_plazo_personal')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.activos_derivados_financieros_largo_plazo')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.activos_financieros_largo_plazo_negociacion')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.activos_financieros_largo_plazo_cobertura')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.activos_retribuciones_largo_definida')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.imposiciones_largo_plazo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.desembolsos_pendientes_neto_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.fianzas_depositos_largo')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.fianzas_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.depositos_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '8' => [
                        'name' => ucfirst(__('fiscal.amortizacion_acumulada_inmovilizado')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.amortizacion_intangible')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.amortizacion_material')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.amortizacion_inmobiliarias')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.deterioro_no_corrientes')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deterioro_intangible')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.deterioro_material')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.deterioro_inmobiliarias')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.deterioro_largo_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.deterioro_deuda_largo_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.deterioro_creditos_largo_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.deterioro_neto_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.deterioro_deuda_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.deterioro_valor_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            3 => [
                'name' => ucfirst(__('fiscal.existencias')),
                'group' => 'a',
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.comerciales')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.materias_primas')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.materias_primas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.materias_primas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.otros_aprovisionamientos')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.elementos_conjuntos_incorporables')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.combustibles')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.repuestos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.materiales_diversos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.embalajes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.envases')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.material_oficina')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.productos_curso')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.productos_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.productos_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.productos_semiterminados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.productos_semiterminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.productos_semiterminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.productos_terminados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.productos_terminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.productos_terminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.subproductos_residuos_recuperados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.subproductos_residuos_recuperados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.subproductos_residuos_recuperados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.deterioro_existencias')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deterioro_mercaderias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.deterioro_primas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.deterioro_aprovisionamientos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.deterioro_productos_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.deterioro_semiterminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.deterioro_terminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.deterioro_subproductos_residuos_recuperados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            4 => [
                'name' => ucfirst(__('fiscal.acreedores_deudores')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.proveedores')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.proveedores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.proveedores_comerciales_pagar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.proveedores_empresas_grupo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.proveedores_asociadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.proveedores_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.envases_proveedores')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.anticipos_proveedores')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.acreedores_varios')),
                        'group' => 'p',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.acreedores_servicios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.acreedores_comerciales_pagar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.acreedores_operaciones_comun')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.clientes')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.clientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.clientes_comerciales_cobrar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.clientes_factoring')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.clientes_empresas_grupo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.clientes_asociadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.clientes_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.clientes_dudoso_cobro')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.envases_devolver_clientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.anticipos_clientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.deudores_varios')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deudores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.deudores_comerciales_cobrar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.deudores_cobro')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.deudores_comun')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.personal')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.anticipos_remuneraciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.remuneraciones_pendientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.remuneraciones_sistemas_definida_pendientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '7' => [
                        'name' => ucfirst(__('fiscal.administraciones_publicas')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.publica_deudora_conceptos')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.publica_iva')),
                                        'group' => false,
                                    ],
                                    '8' => [
                                        'name' => ucfirst(__('fiscal.publica_subvenciones_concedidas')),
                                        'group' => false,
                                    ],
                                    '9' => [
                                        'name' => ucfirst(__('fiscal.publica_devolucion_impuestos')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.organismos_social_deudores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.publica_iva_soportado')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.publica_retenciones_cuenta')),
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.activos_impuesto_diferido')),
                                'group' => 'a',
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.activos_temporarias_deducibles')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.derechos_deducciones_bonificaciones_pendientes')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.credito_perdidas_ejercicio')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.publica_fiscales')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.publica_acreedora_iva')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.publica_practicadas')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.publica_impuesto_sociedades')),
                                        'group' => false,
                                    ],
                                    '8' => [
                                        'name' => ucfirst(__('fiscal.publica_subvenciones_reintegrar')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.organismos_social_acreedores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.publica_iva_repercutido')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.pasivos_temporarias_imponibles')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '8' => [
                        'name' => ucfirst(__('fiscal.ajustes_periodificacion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.gastos_anticipados')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.ingresos_anticipados')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.deterioro_creditos_corto')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deterioro_comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.deterioro_comerciales_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.provisiones_comerciales')),
                                'group' => 'p',
                                'level4' => [
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.provision_onerosos')),
                                        'group' => false,
                                    ],
                                    '9' => [
                                        'name' => ucfirst(__('fiscal.provision_comerciales')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            5 => [
                'name' => ucfirst(__('fiscal.cuentas_financieras')),
                'group' => 'p',
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.emprestitos_especiales_analogas_corto')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.obligaciones_bonos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.obligaciones_bonos_convertibles_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.acciones_corto_pasivo')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.deudas_negociables_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.intereses_analogas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.dividendos_emisiones_pasivo')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.valores_amortizados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.deudas_corto_vinculadas')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deudas_corto_credito_vinculadas')),
                                'group' => false,
                                'level4' => [
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.deudas_grupo')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.deudas_asociadas')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.deudas_empresas_vinculadas')),
                                        'group' => 'p',
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.proveedores_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.acreedores_corto_vinculadas')),
                                'group' => false,
                                'level4' => [
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.acreedores_grupo')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.acreedores_asociadas')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.acreedores_vinculadas')),
                                        'group' => 'p',
                                    ],
                                ],
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.otras_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.deudas_corto_recibidos')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.deudas_corto_credito')),
                                'group' => 'p',
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.prestamos_corto_credito')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.deudas_corto_dispuesto')),
                                        'group' => false,
                                    ],
                                    '7' => [
                                        'name' => ucfirst(__('fiscal.deudas_operaciones_confirming')),
                                        'group' => false,
                                    ],
                                    '8' => [
                                        'name' => ucfirst(__('fiscal.deudas_efectos_descontados')),
                                        'group' => false,
                                    ],
                                    '9' => [
                                        'name' => ucfirst(__('fiscal.deudas_operaciones_factoring')),
                                        'group' => 'p',
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.deudas_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.deudas_corto_transformables')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.proveedores_inmovilizado_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.acreedores_financiero_corto')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.efectos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.dividendo_pagar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_credito')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_deudas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.provisiones_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.inversiones_corto_vinculadas')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.participaciones_corto_vinculadas')),
                                'group' => false,
                                'level4' => [
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.participaciones_corto_grupo')),
                                        'group' => false,
                                    ],
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.participaciones_corto_asociadas')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.participaciones_corto_otras_vinculadas')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.valores_deuda_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.creditos_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_financieras_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_creditos_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.dividendo_cobrar_inversiones_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.desembolsos_participaciones_corto_vinculadas')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.otras_inversiones_corto')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.inversiones_temporales_patrimonio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.valores_deuda_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.creditos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.creditos_corto_enajenacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.creditos_corto_personal')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.dividendo_cobrar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_representativos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.intereses_corto_creditos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.imposiciones_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.desembolsos_patrimonio_corto')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.otras_cuentas_no_bancarias')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.titular_explotacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.cuenta_socios_administradores')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.cuenta_otras_entidades')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.cuentas_fusiones')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.socios_sociedad_disuelta')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.socios_fusion')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.socios_escindida')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.socios_escision')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.cuenta_temporales_bienes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.partidas_aplicacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.desembolsos_participaciones_neto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.dividendo_cuenta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.socios_desembolsos_exigidos')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.socios_exigidos_ordinarias')),
                                        'group' => 'a',
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.socios_exigidos_pasivos')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.derivados_corto')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.activos_corto_negociacion')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.activos_corto_cobertura')),
                                        'group' => false,
                                    ],
                                    '5' => [
                                        'name' => ucfirst(__('fiscal.pasivos_corto_negociacion')),
                                        'group' => false,
                                    ],
                                    '8' => [
                                        'name' => ucfirst(__('fiscal.pasivos_corto_cobertura')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.fianzas_recibidos_corto')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.fianzas_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.depositos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.fianzas_constituidas_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.depositos_constituidos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.intereses_anticipado')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.intereses_cobrados_anticipado')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.garantias_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '7' => [
                        'name' => ucfirst(__('fiscal.tesoreria')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.caja_euros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.caja_extranjera')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.bancos_credito_euros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.bancos_credito_extranjera')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.bancos_ahorro_euros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.bancos_ahorro_extranjera')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.inversiones_corto_liquidez')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '8' => [
                        'name' => ucfirst(__('fiscal.activos_mantenidos_venta_asociados')),
                        'group' => 'a',
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.inmovilizado')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.inversiones_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.inversiones_financieras')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.existencias_comerciales_cobrar')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.otros_activos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.provisiones')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.deudas_especiales')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.deudas_vinculadas')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.acreedores_cuentas_pagar')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otros_pasivos')),
                                'group' => 'p',
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.deterioro_inversiones_corto_venta')),
                        'group' => 'a',
                        'level3' => [
                            '3' => [
                                'name' => ucfirst(__('fiscal.deterioro_participaciones_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.deterioro_valores_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.deterioro_creditos_corto_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.deterioro_valores_deuda_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.deterioro_valor_creditos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.deterioro_activos_venta')),
                                'group' => 'a',
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            6 => [
                'name' => ucfirst(__('fiscal.compras_gastos')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.compras')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.compras_mercaderias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.compras_primas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.compras_aprovisionamientos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.descuentos_compras_pronto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.trabajos_empresas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.devoluciones_compras_similares')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.rappels_compras')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.variacion_existencias')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.variacion_mercaderias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.variacion_primas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.variacion_aprovisionamientos')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.servicios_exteriores')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.gastos_ejercicio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.arrendamientos_canones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.reparaciones_conservacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.servicios_independientes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.transportes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.primas_seguros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.servicios_similares')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.publicidad_publicas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.suministros')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otros_servicios')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.tributos')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.impuesto_beneficios')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.impuesto_corriente')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.impuesto_diferido')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.otros_tributos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.ajustes_beneficios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.ajustes_indirecta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.devolucion_impuestos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.ajustes_positivos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.ajustes_positivos_indirecta')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.gastos_personal')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.sueldos_salarios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.indemnizaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.seguridad_empresa')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.retribuciones_largo_definida')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.retribuciones_prestacion')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.contribuciones_anuales')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.otros_costes')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.retribuciones_personal_patrimonio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otros_sociales')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.otros_gestion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_incobrables')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.resultados_comun')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.beneficio_gestor')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.perdida_participe')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otras_corriente')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.gastos_financieros')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.gastos_provisiones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.intereses_bonos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.intereses_deudas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.perdidas_instrumentos_razonable')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.perdidas_negociacion')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.perdidas_empresa')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.perdidas_venta')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.perdidas_cobertura')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.dividendos_pasivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.intereses_factoring')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.perdidas_deuda')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.perdidas_comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.diferencias_cambio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otros_financieros')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '7' => [
                        'name' => ucfirst(__('fiscal.perdidas_excepcionales')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.perdidas_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.perdidas_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.perdidas_vinculadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.perdidas_propias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.gastos_excepcionales')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '8' => [
                        'name' => ucfirst(__('fiscal.dotaciones_amortizaciones')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.amortizacion_inmovilizado_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.amortizacion_inmovilizado_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.amortizacion_inversiones_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.perdidas_dotaciones')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_deterioro_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.perdidas_deterioro_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.perdidas_deterioro_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.perdidas_deterioro_existencias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.perdidas_deterioro_comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.dotacion_comerciales')),
                                'group' => false,
                                'level4' => [
                                    '4' => [
                                        'name' => ucfirst(__('fiscal.dotacion_contratos_onerosos')),
                                        'group' => false,
                                    ],
                                    '9' => [
                                        'name' => ucfirst(__('fiscal.dotacion_otras_comerciales')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.perdidas_valores_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.perdidas_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.perdidas_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.perdidas_creditos_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            7 => [
                'name' => ucfirst(__('fiscal.ventas_ingresos')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.ventas_propia_servicios')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.ventas_mercaderias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.ventas_terminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.ventas_semiterminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.ventas_residuos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.ventas_embalajes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.prestacion_servicios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.descuentos_pronto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.devoluciones_similares')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.rappels_ventas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.variacion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.variacion_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.variacion_semiterminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.variacion_terminados')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.variacion_recuperados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.trabajos_realizados_empresa')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.trabajos_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.trabajos_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.trabajos_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.trabajos_curso')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.subvenciones_legados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.subvenciones_explotacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.subvenciones_ejercicio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.otras_ejercicio')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.otros_ingresos_gestion')),
                        'group' => false,
                        'level3' => [
                            '1' => [
                                'name' => ucfirst(__('fiscal.resultados_operaciones_comun')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.perdida_gestor')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.beneficio_participe')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.ingresos_arrendamientos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.ingresos_explotacion')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.ingresos_comisiones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.ingresos_personal')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.ingresos_diversos')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.ingresos_financieros')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.ingresos_patrimonio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.ingresos_deuda')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.ingresos_creditos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.beneficios_razonable')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.beneficios_negociacion')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.beneficios_empresa')),
                                        'group' => false,
                                    ],
                                    '2' => [
                                        'name' => ucfirst(__('fiscal.beneficios_venta')),
                                        'group' => false,
                                    ],
                                    '3' => [
                                        'name' => ucfirst(__('fiscal.beneficios_cobertura')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.beneficios_deuda')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.ingresos_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.diferencias_positivas_cambio')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.otros_ingresos_financieros')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '7' => [
                        'name' => ucfirst(__('fiscal.beneficios_excepcionales')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.beneficios_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.beneficios_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.beneficios_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.beneficios_participaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.diferencia_negocios')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.beneficios_propias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.ingresos_excepcionales')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.excesos_deterioro')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.reversion_intangible')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.reversion_material')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.reversion_inmobiliarias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.reversion_existencias')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.reversion_comerciales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.exceso_provisiones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.reversion_deuda_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.reversion_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.reversion_corto')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '9' => [
                                'name' => ucfirst(__('fiscal.reversion_deterioro')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            8 => [
                'name' => ucfirst(__('fiscal.gastos_patrimonio_neto')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.gastos_activos_financieros')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_disponibles_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.gastos_cobertura')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_efectivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.perdidas_extranjero')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_efectivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.transferencia_extranjero')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.gastos_conversion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.diferencias_negativas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.transferencia_positivas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '3' => [
                        'name' => ucfirst(__('fiscal.impuestos_beneficios')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.impuestos_beneficios')),
                                'group' => false,
                                'level4' => [
                                    '0' => [
                                        'name' => ucfirst(__('fiscal.impuesto_corriente')),
                                        'group' => false,
                                    ],
                                    '1' => [
                                        'name' => ucfirst(__('fiscal.impuesto_diferido')),
                                        'group' => false,
                                    ],
                                ],
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.ajustes_negativos')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.ingresos_permanentes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '5' => [
                                'name' => ucfirst(__('fiscal.ingresos_bonificaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '6' => [
                                'name' => ucfirst(__('fiscal.transferencia_permanentes')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '7' => [
                                'name' => ucfirst(__('fiscal.transferencia_bonificaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '8' => [
                                'name' => ucfirst(__('fiscal.ajustes_imposicion_beneficios')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.transferencias_legados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.transferencia_capital')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.transferencia_donaciones')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_legados')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.gastos_definida')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_actuariales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.ajustes_largo')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.gastos_venta')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.perdidas_activos_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_beneficios_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.gastos_previos')),
                        'group' => false,
                        'level3' => [
                            '1' => [
                                'name' => ucfirst(__('fiscal.deterioro_grupo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.deterioro_asociadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
            9 => [
                'name' => ucfirst(__('fiscal.ingresos_patrimonio_neto')),
                'group' => false,
                'level2' => [
                    '0' => [
                        'name' => ucfirst(__('fiscal.ingresos_activos_financieros')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.beneficios_financieros_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_activos_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '1' => [
                        'name' => ucfirst(__('fiscal.ingresos_cobertura')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.beneficios_efectivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.beneficios_extranjero')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_perdidas_efectivo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.transferencia_perdidas_extranjero')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '2' => [
                        'name' => ucfirst(__('fiscal.ingresos_conversion')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.diferencias_positivas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.transferencia_negativas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '4' => [
                        'name' => ucfirst(__('fiscal.ingresos_legados')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.ingresos_de')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.ingresos_de')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '5' => [
                        'name' => ucfirst(__('fiscal.ingresos_definida')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.ganancias_actuariales')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '1' => [
                                'name' => ucfirst(__('fiscal.ajustes_definida')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '6' => [
                        'name' => ucfirst(__('fiscal.ingresos_venta')),
                        'group' => false,
                        'level3' => [
                            '0' => [
                                'name' => ucfirst(__('fiscal.beneficios_activos_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.transferencia_grupos_venta')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                    '9' => [
                        'name' => ucfirst(__('fiscal.ingresos_grupo_previos')),
                        'group' => false,
                        'level3' => [
                            '1' => [
                                'name' => ucfirst(__('fiscal.recuperacion_grupo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '2' => [
                                'name' => ucfirst(__('fiscal.recuperacion_asociadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '3' => [
                                'name' => ucfirst(__('fiscal.transferencia_previos_grupo')),
                                'group' => false,
                                'level4' => false,
                            ],
                            '4' => [
                                'name' => ucfirst(__('fiscal.transferencia_previos_asociadas')),
                                'group' => false,
                                'level4' => false,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($data as $i1 => $r1) {
            $aat1 = new AccountingAccountType;
            $aat1->autoreference = '0';
            $aat1->code = $i1;
            $aat1->name = $r1['name'];
            $aat1->mode = (isset($r1['group']) && is_string($r1['group']) && $r1['group'] !== '') ? $r1['group'] : null;
            $aat1->save();

            if (isset($r1['level2'])) {
                foreach ($r1['level2'] as $i2 => $r2) {
                    $aat2 = new AccountingAccountType;
                    $aat2->autoreference = $aat1->id;
                    $aat2->code = $i1.$i2;
                    $aat2->name = $r2['name'];
                    $aat2->mode = (isset($r2['group']) && is_string($r2['group']) && $r2['group'] !== '') ? $r2['group'] : null;
                    $aat2->save();

                    if (isset($r2['level3'])) {
                        foreach ($r2['level3'] as $i3 => $r3) {
                            $aat3 = new AccountingAccountType;
                            $aat3->autoreference = $aat2->id;
                            $aat3->code = $i1.$i2.$i3;
                            $aat3->name = $r3['name'];
                            $aat3->mode = (isset($r3['group']) && is_string($r3['group']) && $r3['group'] !== '') ? $r3['group'] : null;
                            $aat3->save();

                            if ($r3['level4']) {
                                foreach ($r3['level4'] as $i4 => $r4) {
                                    $aat4 = new AccountingAccountType;
                                    $aat4->autoreference = $aat3->id;
                                    $aat4->code = $i1.$i2.$i3.$i4;
                                    $aat4->name = $r4['name'];
                                    $aat4->mode = (isset($r4['group']) && is_string($r4['group']) && $r4['group'] !== '') ? $r4['group'] : null;
                                    $aat4->save();
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
